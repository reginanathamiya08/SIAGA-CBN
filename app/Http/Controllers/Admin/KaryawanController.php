<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreKaryawanRequest;
use App\Http\Requests\Admin\UpdateKaryawanRequest;
use App\Models\Role;
use App\Models\User;
use App\Models\KuotaPerizinan;
use App\Models\DetailGajiKomponen;
use App\Models\Configuration;
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
    // ─────────────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = User::with('role')
            ->whereHas('role', fn($q) => $q->whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak']))
            ->orderBy('created_at', 'desc');

        if ($request->filled('jenis')) {
            $jenis = $request->jenis;
            $slugTarget = in_array($jenis, ['tetap', 'karyawan_tetap', 'JNS-00001']) ? 'karyawan_tetap' : 'karyawan_kontrak';
            $query->whereHas('role', fn($q) => $q->where('slug', $slugTarget));
        }
        if ($request->filled('divisi')) $query->where('divisi', $request->divisi);
        if ($request->filled('status')) $query->where('is_active', $request->status === 'aktif');
        if ($request->filled('cari'))   $query->where('nama', 'LIKE', '%' . $request->cari . '%');

        $karyawan = $query->paginate(15)->withQueryString();

        $statsQuery = User::whereHas('role', fn($q) => $q->whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak']));

        $stats = [
            'total'    => (clone $statsQuery)->count(),
            'tetap'    => (clone $statsQuery)->whereHas('role', fn($q) => $q->where('slug', 'karyawan_tetap'))->count(),
            'kontrak'  => (clone $statsQuery)->whereHas('role', fn($q) => $q->where('slug', 'karyawan_kontrak'))->count(),
            'nonaktif' => (clone $statsQuery)->where('is_active', false)->count(),
        ];

        return view('admin.karyawan.index', compact('karyawan', 'stats'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // CREATE
    // ─────────────────────────────────────────────────────────────────────
    public function create()
    {
        $roles          = Role::whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak'])->get();
        $divisiTetap    = UsernameGeneratorService::daftarDivisi('karyawan_tetap');
        $divisiKontrak  = UsernameGeneratorService::daftarDivisi('karyawan_kontrak');
        $jabatanMap     = [
            'keuangan'       => UsernameGeneratorService::daftarJabatan('karyawan_tetap', 'keuangan'),
            'koordinator_cs' => UsernameGeneratorService::daftarJabatan('karyawan_tetap', 'koordinator_cs'),
            'adm_umum'       => UsernameGeneratorService::daftarJabatan('karyawan_tetap', 'adm_umum'),
            'manajemen'      => UsernameGeneratorService::daftarJabatan('karyawan_tetap', 'manajemen'),
            'HC'             => UsernameGeneratorService::daftarJabatan('karyawan_kontrak', 'HC'),
            'umum'           => UsernameGeneratorService::daftarJabatan('karyawan_kontrak', 'umum'),
        ];
        $dokumenWajib   = UsernameGeneratorService::jabatanDenganDokumenKhusus();
        $jabatanShift   = UsernameGeneratorService::jabatanShift();
        $jabatanAtasUmr = UsernameGeneratorService::jabatanAtasUmr();

        return view('admin.karyawan.create', compact(
            'roles', 'divisiTetap', 'divisiKontrak', 'jabatanMap',
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

            // 1. Generate username & Ambil Role
            $role     = Role::findOrFail($data['role_id']);
            $roleSlug = $role->slug;
            $username = $this->usernameService->generate($roleSlug, $data['divisi'] ?? null);

            // 3. Tentukan flag otomatis
            $jabatan          = $data['jabatan'];
            $gaji_atas_umr    = UsernameGeneratorService::isAtasUmr($jabatan);
            $is_shift         = UsernameGeneratorService::isShift($jabatan);
            $uang_makan_mitra = UsernameGeneratorService::uangMakanDibayarMitra($data['divisi'] ?? null);

            // 4. Buat data karyawan (sekaligus akun)
            $karyawan = User::create([
                'role_id'             => $role->id,
                'nip'                 => $username,
                'password'            => Hash::make($data['password']),
                'nama'                => $data['nama'],
                'email'               => $data['email'],
                'divisi'              => $data['divisi'] ?? null,
                'jabatan'             => $jabatan,
                'pendidikan'          => $data['pendidikan'],
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

            // 6. Buat kuota perizinan
            $jatahCuti = Configuration::getValue('kuota_cuti_tahunan', 12);
            KuotaPerizinan::create([
                'user_id'     => $karyawan->id,
                'tahun'       => now()->year,
                'kuota_total' => $jatahCuti,
                'terpakai'    => 0,
                'sisa'        => $jatahCuti,
            ]);

            // 7. Buat komponen gaji default
            $standardSalaries = Cache::get('standar_gaji_jabatan', []);
            $defaultGaji = $standardSalaries[$jabatan] ?? 0;

            if ($defaultGaji == 0) {
                $defaultGaji = DetailGajiKomponen::whereHas('user', function($q) use ($jabatan) {
                    $q->where('jabatan', $jabatan);
                })
                ->whereNull('slip_gaji_periode_id')
                ->where('komponen_gaji_id', 'MKG-00001')
                ->max('nominal') ?? 0;
            }

            if ($defaultGaji == 0 && !$gaji_atas_umr) {
                $defaultGaji = Configuration::getValue('umr_tahun_ini', 2994031);
            }

            $karyawan->updateKomponenGaji([
                'gaji_pokok'      => $defaultGaji,
                'uang_makan'      => $uang_makan_mitra ? null : Configuration::getValue('uang_makan_default', 35000),
                'uang_transport'  => $uang_makan_mitra ? null : Configuration::getValue('uang_transport_default', 45000),
                'persen_bpjs_kes' => Configuration::getValue('persen_bpjs_kes', 9.24),
                'persen_bpjs_tk'  => Configuration::getValue('persen_bpjs_tk', 5.00),
            ]);

            DB::commit();

            return redirect()
                ->route('admin.karyawan.show', $karyawan->id)
                ->with('success', "Karyawan berhasil ditambahkan! NIP karyawan: {$username}");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────────────────────────────────
    public function show(User $karyawan)
    {
        $karyawan->load([
            'role',
            'dokumen',
            'penempatanAktif.mitra',
            'komponenGaji',
            'kuotaPerizinan' => fn ($q) => $q->where('tahun', now()->year),
        ]);

        return view('admin.karyawan.show', compact('karyawan'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────────────────────────────────
    public function edit(User $karyawan)
    {
        $karyawan->load('role', 'dokumen');
        $roles      = Role::whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak'])->get();
        $divisiList = UsernameGeneratorService::daftarDivisi($karyawan->role?->slug ?? 'karyawan_tetap');
        $jabatanMap = [
            'keuangan'       => UsernameGeneratorService::daftarJabatan('karyawan_tetap', 'keuangan'),
            'koordinator_cs' => UsernameGeneratorService::daftarJabatan('karyawan_tetap', 'koordinator_cs'),
            'adm_umum'       => UsernameGeneratorService::daftarJabatan('karyawan_tetap', 'adm_umum'),
            'HC'             => UsernameGeneratorService::daftarJabatan('karyawan_kontrak', 'HC'),
            'umum'           => UsernameGeneratorService::daftarJabatan('karyawan_kontrak', 'umum'),
        ];
        $daftarJabatan = UsernameGeneratorService::daftarJabatan(
            $karyawan->role?->slug ?? 'karyawan_tetap',
            old('divisi', $karyawan->divisi)
        );

        return view('admin.karyawan.edit', compact('karyawan', 'roles', 'divisiList', 'daftarJabatan', 'jabatanMap'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────────────────────────────────
    public function update(UpdateKaryawanRequest $request, User $karyawan)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            if (isset($data['jabatan'])) {
                $data['gaji_atas_umr'] = UsernameGeneratorService::isAtasUmr($data['jabatan']);
                $data['is_shift']      = UsernameGeneratorService::isShift($data['jabatan']);
            }

            if (isset($data['divisi'])) {
                $data['uang_makan_by_mitra'] = UsernameGeneratorService::uangMakanDibayarMitra($data['divisi']);
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
    public function toggleStatus(User $karyawan)
    {
        $status = !$karyawan->is_active;
        $karyawan->update(['is_active' => $status]);

        $label = $status ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun {$karyawan->nama} berhasil {$label}.");
    }

    // ─────────────────────────────────────────────────────────────────────
    // RESET PASSWORD
    // ─────────────────────────────────────────────────────────────────────
    public function resetPassword(Request $request, User $karyawan)
    {
        $request->validate([
            'password_baru'              => 'required|string|min:6|confirmed',
            'password_baru_confirmation' => 'required',
        ], [
            'password_baru.required'   => 'Password baru wajib diisi.',
            'password_baru.min'        => 'Password minimal 6 karakter.',
            'password_baru.confirmed'  => 'Konfirmasi password tidak cocok.',
        ]);

        $karyawan->update([
            'password' => Hash::make($request->password_baru),
        ]);

        return back()->with('success', "Password {$karyawan->nama} berhasil direset.");
    }

    // ─────────────────────────────────────────────────────────────────────
    // HAPUS DOKUMEN
    // ─────────────────────────────────────────────────────────────────────
    public function hapusDokumen(User $karyawan, int $dokumenId)
    {
        $dokumen = $karyawan->dokumen()->findOrFail($dokumenId);
        Storage::disk('public')->delete($dokumen->file_path);
        $dokumen->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }
}
