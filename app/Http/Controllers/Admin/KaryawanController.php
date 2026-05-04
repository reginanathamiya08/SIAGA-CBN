<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreKaryawanRequest;
use App\Http\Requests\Admin\UpdateKaryawanRequest;
use App\Models\User;
use App\Models\Karyawan;
use App\Models\KuotaCuti;
use App\Models\KomponenGaji;
use App\Services\UsernameGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class KaryawanController extends Controller
{
    public function __construct(
        protected UsernameGeneratorService $usernameService
    ) {}

    // ─────────────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Karyawan::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('jenis'))  $query->where('jenis_karyawan', $request->jenis);
        if ($request->filled('divisi')) $query->where('divisi', $request->divisi);
        if ($request->filled('status')) $query->where('is_active', $request->status === 'aktif');
        if ($request->filled('cari'))   $query->where('nama', 'LIKE', '%' . $request->cari . '%');

        $karyawan = $query->paginate(15)->withQueryString();

        $stats = [
            'total'    => Karyawan::count(),
            'tetap'    => Karyawan::where('jenis_karyawan', 'tetap')->count(),
            'kontrak'  => Karyawan::where('jenis_karyawan', 'kontrak')->count(),
            'nonaktif' => Karyawan::where('is_active', false)->count(),
        ];

        return view('admin.karyawan.index', compact('karyawan', 'stats'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // CREATE
    // ─────────────────────────────────────────────────────────────────────
    public function create()
    {
        $divisiTetap    = UsernameGeneratorService::daftarDivisi('tetap');
        $divisiKontrak  = UsernameGeneratorService::daftarDivisi('kontrak');
        $jabatanMap     = [
            'keuangan'       => UsernameGeneratorService::daftarJabatan('tetap', 'keuangan'),
            'koordinator_cs' => UsernameGeneratorService::daftarJabatan('tetap', 'koordinator_cs'),
            'adm_umum'       => UsernameGeneratorService::daftarJabatan('tetap', 'adm_umum'),
            'HC'             => UsernameGeneratorService::daftarJabatan('kontrak', 'HC'),
            'umum'           => UsernameGeneratorService::daftarJabatan('kontrak', 'umum'),
        ];
        $dokumenWajib   = UsernameGeneratorService::jabatanDenganDokumenKhusus();
        $jabatanShift   = UsernameGeneratorService::jabatanShift();
        $jabatanAtasUmr = UsernameGeneratorService::jabatanAtasUmr();

        return view('admin.karyawan.create', compact(
            'divisiTetap', 'divisiKontrak', 'jabatanMap',
            'dokumenWajib', 'jabatanShift', 'jabatanAtasUmr'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────────────────────────────
    public function store(StoreKaryawanRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            // 1. Generate username
            $role     = $data['jenis_karyawan'] === 'tetap' ? 'karyawan_tetap' : 'karyawan_kontrak';
            $username = $this->usernameService->generate($role, $data['divisi'] ?? null);

            // 2. Buat akun user
            $user = User::create([
                'username'  => $username,
                'password'  => Hash::make($data['password']),
                'role'      => $role,
                'is_active' => true,
            ]);

            // 3. Tentukan flag otomatis
            $jabatan          = $data['jabatan'];
            $gaji_atas_umr    = UsernameGeneratorService::isAtasUmr($jabatan);
            $is_shift         = UsernameGeneratorService::isShift($jabatan);
            $uang_makan_mitra = UsernameGeneratorService::uangMakanDibayarMitra($data['divisi'] ?? null);

            // 4. Buat data karyawan
            $karyawan = Karyawan::create([
                'user_id'             => $user->id,
                'nama'                => $data['nama'],
                'email'               => $data['email'],
                'jenis_karyawan'      => $data['jenis_karyawan'],
                'divisi'              => $data['divisi'] ?? null,
                'jabatan'             => $jabatan,
                'tanggal_masuk'       => $data['tanggal_masuk'],
                'no_hp'               => $data['no_hp'] ?? null,
                'gaji_atas_umr'       => $gaji_atas_umr,
                'is_shift'            => $is_shift,
                'uang_makan_by_mitra' => $uang_makan_mitra,
                'is_active'           => true,
            ]);

            // 5. Upload dokumen wajib
            if ($request->hasFile('file_dokumen') && !empty($data['jenis_dokumen'])) {
                $path = $request->file('file_dokumen')->store('dokumen-karyawan', 'public');
                $karyawan->dokumen()->create([
                    'jenis_dokumen' => $data['jenis_dokumen'],
                    'file_path'     => $path,
                    'uploaded_at'   => now(),
                ]);
            }

            // 6. Buat kuota cuti
            KuotaCuti::create([
                'karyawan_id' => $karyawan->id,
                'tahun'       => now()->year,
                'kuota_total' => 12,
                'terpakai'    => 0,
                'sisa'        => 12,
            ]);

            // 7. Buat komponen gaji default
            $standardSalaries = Cache::get('standar_gaji_jabatan', []);
            $defaultGaji = $standardSalaries[$jabatan] ?? 0;

            if ($defaultGaji == 0) {
                $defaultGaji = KomponenGaji::whereHas('karyawan', function($q) use ($jabatan) {
                    $q->where('jabatan', $jabatan);
                })->max('gaji_pokok') ?? 0;
            }

            if ($defaultGaji == 0 && !$gaji_atas_umr) {
                $defaultGaji = config('cbn.umr_tahun_ini', 2994031);
            }

            KomponenGaji::create([
                'karyawan_id'     => $karyawan->id,
                'gaji_pokok'      => $defaultGaji,
                'uang_makan'      => $uang_makan_mitra ? null : 35000,
                'uang_transport'  => $uang_makan_mitra ? null : 45000,
                'persen_bpjs_kes' => 9.24,
                'persen_bpjs_tk'  => 5.00,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.karyawan.show', $karyawan->id)
                ->with('success', "Karyawan berhasil ditambahkan! Username login: {$username}");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────────────────────────────────
    public function show(Karyawan $karyawan)
    {
        $karyawan->load([
            'user',
            'dokumen',
            'penempatanAktif.mitra',
            'komponenGaji',
            'kuotaCuti' => fn ($q) => $q->where('tahun', now()->year),
        ]);

        return view('admin.karyawan.show', compact('karyawan'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────────────────────────────────
    public function edit(Karyawan $karyawan)
    {
        $karyawan->load('user', 'dokumen');
        $daftarJabatan = UsernameGeneratorService::daftarJabatan(
            $karyawan->jenis_karyawan,
            $karyawan->divisi
        );

        return view('admin.karyawan.edit', compact('karyawan', 'daftarJabatan'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────────────────────────────────
    public function update(UpdateKaryawanRequest $request, Karyawan $karyawan)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            if (isset($data['jabatan'])) {
                $data['gaji_atas_umr'] = UsernameGeneratorService::isAtasUmr($data['jabatan']);
                $data['is_shift']      = UsernameGeneratorService::isShift($data['jabatan']);
            }

            $karyawan->update($data);

            if ($request->hasFile('file_dokumen') && !empty($data['jenis_dokumen'])) {
                $path = $request->file('file_dokumen')->store('dokumen-karyawan', 'public');
                $karyawan->dokumen()->create([
                    'jenis_dokumen' => $data['jenis_dokumen'],
                    'file_path'     => $path,
                    'uploaded_at'   => now(),
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.karyawan.show', $karyawan->id)
                ->with('success', 'Data karyawan berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Gagal memperbarui: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // TOGGLE STATUS
    // ─────────────────────────────────────────────────────────────────────
    public function toggleStatus(Karyawan $karyawan)
    {
        $status = !$karyawan->is_active;
        $karyawan->update(['is_active' => $status]);
        $karyawan->user->update(['is_active' => $status]);

        $label = $status ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun {$karyawan->nama} berhasil {$label}.");
    }

    // ─────────────────────────────────────────────────────────────────────
    // RESET PASSWORD
    // ─────────────────────────────────────────────────────────────────────
    public function resetPassword(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'password_baru'              => 'required|string|min:6|confirmed',
            'password_baru_confirmation' => 'required',
        ], [
            'password_baru.required'   => 'Password baru wajib diisi.',
            'password_baru.min'        => 'Password minimal 6 karakter.',
            'password_baru.confirmed'  => 'Konfirmasi password tidak cocok.',
        ]);

        $karyawan->user->update([
            'password' => Hash::make($request->password_baru),
        ]);

        return back()->with('success', "Password {$karyawan->nama} berhasil direset.");
    }

    // ─────────────────────────────────────────────────────────────────────
    // HAPUS DOKUMEN
    // ─────────────────────────────────────────────────────────────────────
    public function hapusDokumen(Karyawan $karyawan, int $dokumenId)
    {
        $dokumen = $karyawan->dokumen()->findOrFail($dokumenId);
        Storage::disk('public')->delete($dokumen->file_path);
        $dokumen->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }
}