<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\KomponenGaji;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KomponenGajiController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    // INDEX - Daftar komponen gaji semua karyawan
    // ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Karyawan::with(['user', 'komponenGaji', 'penempatanAktif.mitra'])
                         ->where('is_active', true)
                         ->orderBy('jenis_karyawan')
                         ->orderBy('nama');

        if ($request->filled('jenis')) {
            $query->where('jenis_karyawan', $request->jenis);
        }
        if ($request->filled('divisi')) {
            $val = $request->divisi;
            // Jika nilai adalah kategori besar (HC/umum) atau divisi tetap
            if (in_array($val, ['HC', 'umum', 'keuangan', 'koordinator_cs', 'adm_umum'])) {
                $query->where('divisi', $val);
            } else {
                // Jika selain itu, kita anggap itu adalah pencarian Jabatan spesifik
                $query->where('jabatan', $val);
            }
        }
        if ($request->filled('cari')) {
            $query->where('nama', 'LIKE', '%' . $request->cari . '%');
        }

        $karyawan = $query->paginate(15)->withQueryString();
        $totalKaryawan = Karyawan::where('is_active', true)->count();

        // Statistik
        $stats = [
            'belum_diisi'  => KomponenGaji::where('gaji_pokok', 0)->count(),
            'sudah_diisi'  => KomponenGaji::where('gaji_pokok', '>', 0)->count(),
            'total'        => $totalKaryawan,
        ];

        // ─────────────────────────────────────────────────────────────────
        // DATA UNTUK MODAL AUTO-FILL GAJI
        // ─────────────────────────────────────────────────────────────────
        
        // 1. KARYAWAN TETAP
        $targetDivisiTetap = [
            'keuangan' => ['Staff Keuangan'],
            'koordinator_cs' => ['Koordinator CS'],
            'adm_umum' => ['Staff Administrasi & Umum']
        ];
        $rawJabatanTetap = Karyawan::where('jenis_karyawan', 'tetap')->get(['divisi', 'jabatan'])->groupBy('divisi');
        $jabatanTetapByDivisi = collect();
        foreach ($targetDivisiTetap as $div => $defaultJabs) {
            $dbJabs = isset($rawJabatanTetap[$div]) ? $rawJabatanTetap[$div]->pluck('jabatan')->unique()->toArray() : [];
            $mergedJabs = array_unique(array_merge($defaultJabs, $dbJabs));
            sort($mergedJabs);
            $jabatanTetapByDivisi[$div] = $mergedJabs;
        }

        // 2. KARYAWAN KONTRAK
        $jabatanHCSpesialisKontrak = ['Marketing', 'Call Centre', 'Card Center', 'Teknisi', 'Monitoring ATM Dan Jaringan', 'PPI'];
        $jabatanHCUmrKontrak = ['Satpam', 'Sopir', 'Pramusaji', 'Pramubakti', 'E-Channel', 'Juru Parkir'];
        $jabatanUmumKontrak = ['CS', 'CS ATM', 'Ekspedisi'];

        // Ambil Gaji dari Database
        $dbSalaries = KomponenGaji::join('karyawan', 'komponen_gaji.karyawan_id', '=', 'karyawan.id')
            ->select('karyawan.jabatan', \Illuminate\Support\Facades\DB::raw('MAX(gaji_pokok) as gaji'))
            ->groupBy('karyawan.jabatan')
            ->pluck('gaji', 'jabatan')->toArray();

        // Gabungkan dengan Cache
        $cachedSalaries = \Illuminate\Support\Facades\Cache::get('standar_gaji_jabatan', []);
        $currentSalaries = array_merge($dbSalaries, $cachedSalaries);

        return view('admin.komponen-gaji.index', compact(
            'karyawan', 
            'stats',
            'jabatanTetapByDivisi', 
            'jabatanHCSpesialisKontrak', 
            'jabatanHCUmrKontrak', 
            'jabatanUmumKontrak',
            'currentSalaries'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // EDIT - Form edit komponen gaji satu karyawan
    // ─────────────────────────────────────────────────────────────────
    public function edit(Karyawan $karyawan)
    {
        $karyawan->load(['user', 'komponenGaji', 'penempatanAktif.mitra']);

        // Ambil UMR Sumatera Barat tahun berjalan
        $umrTahunIni = config('cbn.umr_tahun_ini', 2994031);

        // Buat komponen gaji jika belum ada atau pre-fill jika gaji masih 0
        if (!$karyawan->komponenGaji || $karyawan->komponenGaji->gaji_pokok == 0) {
            $jabatan = $karyawan->jabatan;
            $standardSalaries = \Illuminate\Support\Facades\Cache::get('standar_gaji_jabatan', []);
            $defaultGaji = $standardSalaries[$jabatan] ?? 0;

            if ($defaultGaji == 0) {
                $defaultGaji = KomponenGaji::whereHas('karyawan', function($q) use ($jabatan) {
                    $q->where('jabatan', $jabatan);
                })->where('gaji_pokok', '>', 0)->max('gaji_pokok') ?? 0;
            }

            if ($defaultGaji == 0 && !$karyawan->gaji_atas_umr) {
                $defaultGaji = $umrTahunIni;
            }

            if (!$karyawan->komponenGaji) {
                KomponenGaji::create([
                    'karyawan_id'     => $karyawan->id,
                    'gaji_pokok'      => $defaultGaji,
                    'uang_makan'      => $karyawan->uang_makan_by_mitra ? null : 35000,
                    'uang_transport'  => $karyawan->uang_makan_by_mitra ? null : 45000,
                    'persen_bpjs_kes' => 9.24,
                    'persen_bpjs_tk'  => 5.00,
                ]);
            } else if ($karyawan->komponenGaji->gaji_pokok == 0 && $defaultGaji > 0) {
                // Jangan update otomatis ke DB di sini agar user bisa review di form,
                // tapi kita kirim nilai default ke view.
                $karyawan->komponenGaji->gaji_pokok = $defaultGaji;
            }
        }

        return view('admin.komponen-gaji.edit', compact('karyawan', 'umrTahunIni'));
    }

    // ─────────────────────────────────────────────────────────────────
    // UPDATE - Simpan komponen gaji
    // ─────────────────────────────────────────────────────────────────
    public function update(Request $request, Karyawan $karyawan)
    {
        $rules = [
            'gaji_pokok'      => 'required|numeric|min:0',
            'persen_bpjs_kes' => 'required|numeric|min:0|max:100',
            'persen_bpjs_tk'  => 'required|numeric|min:0|max:100',
        ];

        // Uang makan & transport hanya wajib jika dibayar CBN
        if (!$karyawan->uang_makan_by_mitra) {
            $rules['uang_makan']     = 'required|numeric|min:0';
            $rules['uang_transport'] = 'required|numeric|min:0';
        }

        $request->validate($rules, [
            'gaji_pokok.required'      => 'Gaji pokok wajib diisi.',
            'gaji_pokok.numeric'       => 'Gaji pokok harus berupa angka.',
            'gaji_pokok.min'           => 'Gaji pokok tidak boleh negatif.',
            'uang_makan.required'      => 'Uang makan wajib diisi.',
            'uang_transport.required'  => 'Uang transport wajib diisi.',
            'persen_bpjs_kes.required' => 'Persentase BPJS Kesehatan wajib diisi.',
            'persen_bpjs_tk.required'  => 'Persentase BPJS Ketenagakerjaan wajib diisi.',
        ]);

        $karyawan->komponenGaji()->updateOrCreate(
            ['karyawan_id' => $karyawan->id],
            [
                'gaji_pokok'      => $request->gaji_pokok,
                'uang_makan'      => $karyawan->uang_makan_by_mitra ? null : $request->uang_makan,
                'uang_transport'  => $karyawan->uang_makan_by_mitra ? null : $request->uang_transport,
                'persen_bpjs_kes' => $request->persen_bpjs_kes,
                'persen_bpjs_tk'  => $request->persen_bpjs_tk,
                'updated_by'      => Auth::id(),
            ]
        );

        return redirect()
            ->route('admin.komponen-gaji.index')
            ->with('success', "Komponen gaji {$karyawan->nama} berhasil disimpan.");
    }

    // ─────────────────────────────────────────────────────────────────
    // UPDATE BULK - Update BPJS semua karyawan sekaligus
    // ─────────────────────────────────────────────────────────────────
    public function updateBulkBpjs(Request $request)
    {
        $request->validate([
            'persen_bpjs_kes' => 'required|numeric|min:0|max:100',
            'persen_bpjs_tk'  => 'required|numeric|min:0|max:100',
        ]);

        KomponenGaji::query()->update([
            'persen_bpjs_kes' => $request->persen_bpjs_kes,
            'persen_bpjs_tk'  => $request->persen_bpjs_tk,
            'updated_by'      => Auth::id(),
        ]);

        return back()->with('success',
            "Persentase BPJS semua karyawan berhasil diperbarui: " .
            "Kesehatan {$request->persen_bpjs_kes}%, Ketenagakerjaan {$request->persen_bpjs_tk}%."
        );
    }
}