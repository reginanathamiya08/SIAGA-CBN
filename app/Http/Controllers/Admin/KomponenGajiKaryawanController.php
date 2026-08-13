<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DetailGajiKomponen;
use App\Models\KomponenGaji;
use App\Models\Configuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KomponenGajiKaryawanController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    // INDEX - Daftar komponen gaji semua karyawan
    // ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = User::with(['komponenGaji', 'penempatanAktif.mitra', 'role'])
                         ->where('is_active', true)
                         ->whereHas('role', fn($q) => $q->whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak']))
                         ->orderBy('role_id')
                         ->orderBy('nama');

        if ($request->filled('jenis')) {
            $roleSlug = in_array($request->jenis, ['tetap', 'karyawan_tetap', 'JNS-00001']) ? 'karyawan_tetap' : 'karyawan_kontrak';
            $query->whereHas('role', fn($q) => $q->where('slug', $roleSlug));
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
            $search = $request->cari;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'LIKE', '%' . $search . '%')
                  ->orWhere('jabatan', 'LIKE', '%' . $search . '%');
            });
        }

        $karyawan = $query->paginate(15)->withQueryString();
        $totalKaryawan = User::where('is_active', true)
                             ->whereHas('role', fn($q) => $q->whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak']))
                             ->count();

        // Statistik
        $stats = [
            'belum_diisi'  => User::where('is_active', true)->whereHas('role', fn($r) => $r->whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak']))->whereDoesntHave('komponenGaji', fn($q) => $q->where('komponen_gaji_id', 'MKG-00001')->where('nominal', '>', 0))->count(),
            'sudah_diisi'  => User::where('is_active', true)->whereHas('role', fn($r) => $r->whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak']))->whereHas('komponenGaji', fn($q) => $q->where('komponen_gaji_id', 'MKG-00001')->where('nominal', '>', 0))->count(),
            'total'        => $totalKaryawan,
        ];

        // ─────────────────────────────────────────────────────────────────
        // DATA UNTUK MODAL AUTO-FILL GAJI (HYBRID: TETAP & KONTRAK)
        // ─────────────────────────────────────────────────────────────────
        
        // 1. DATA TETAP (Pendidikan)
        $levels = ['S3', 'S2', 'S1', 'D3', 'SMA/SMK'];
        
        // 2. DATA KONTRAK (Jabatan)
        $jabatanHCSpesialisKontrak = ['Marketing', 'Call Centre', 'Card Center', 'Teknisi', 'Monitoring ATM Dan Jaringan', 'PPI'];
        $jabatanHCUmrKontrak = ['Satpam', 'Sopir', 'Pramusaji', 'Pramubakti', 'E-Channel', 'Juru Parkir'];
        $jabatanUmumKontrak = ['CS', 'CS ATM', 'Ekspedisi'];

        // Ambil Gaji dari Database
        $dbSalaries = DetailGajiKomponen::join('users', 'detail_gaji_komponen.user_id', '=', 'users.id')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->whereNull('detail_gaji_komponen.slip_gaji_periode_id')
            ->where('detail_gaji_komponen.komponen_gaji_id', 'MKG-00001')
            ->select('users.jabatan', 'users.pendidikan', 'roles.slug as role_slug', 'detail_gaji_komponen.nominal as gaji_pokok')
            ->get();

        $currentSalaries = [];
        // Map Gaji Pendidikan (Tetap)
        $cachedTetap = \Illuminate\Support\Facades\Cache::get('standar_gaji_pendidikan', []);
        $defaultTetapFallbacks = [
            'S3'      => 7500000,
            'S2'      => 6500000,
            'S1'      => 5500000,
            'D3'      => 4000000,
            'SMA/SMK' => 3500000,
        ];
        foreach($levels as $lv) {
            $dbVal = $dbSalaries->where('role_slug', 'karyawan_tetap')->where('pendidikan', $lv)->max('gaji_pokok') ?? 0;
            $currentSalaries['tetap_'.$lv] = $cachedTetap[$lv] ?? ($dbVal > 0 ? $dbVal : ($defaultTetapFallbacks[$lv] ?? 0));
        }

        // Map Gaji Jabatan (Kontrak)
        $cachedKontrak = \Illuminate\Support\Facades\Cache::get('standar_gaji_jabatan', []);
        $allKontrakJabs = array_merge($jabatanHCSpesialisKontrak, $jabatanHCUmrKontrak, $jabatanUmumKontrak);
        $umrTahunIni = Configuration::getValue('umr_tahun_ini', 2994031);
        foreach($allKontrakJabs as $jab) {
            $dbVal = $dbSalaries->where('role_slug', 'karyawan_kontrak')->where('jabatan', $jab)->max('gaji_pokok') ?? 0;
            $currentSalaries['kontrak_'.$jab] = $cachedKontrak[$jab] ?? ($dbVal > 0 ? $dbVal : $umrTahunIni);
        }

        $masterComponents = KomponenGaji::orderBy('id', 'asc')->get();
        $protectedIds = [
            'MKG-00001', 'MKG-00002', 'MKG-00003', 'MKG-00004', 'MKG-00005',
            'MKG-00006', 'MKG-00007', 'MKG-00008', 'MKG-00009', 'MKG-00010'
        ];

        return view('admin.komponen-gaji.index', compact(
            'karyawan', 
            'stats',
            'levels',
            'jabatanHCSpesialisKontrak',
            'jabatanHCUmrKontrak',
            'jabatanUmumKontrak',
            'currentSalaries',
            'masterComponents',
            'protectedIds'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // EDIT - Form edit komponen gaji satu karyawan
    // ─────────────────────────────────────────────────────────────────
    public function edit(User $karyawan)
    {
        $karyawan->load(['komponenGaji', 'penempatanAktif.mitra']);

        // Ambil UMR Sumatera Barat tahun berjalan
        $umrTahunIni = Configuration::getValue('umr_tahun_ini', 2994031);

        // Buat komponen gaji jika belum ada atau pre-fill jika gaji masih 0
        if ($karyawan->gaji_pokok == 0) {
            $jabatan = $karyawan->jabatan;
            $pendidikan = $karyawan->pendidikan;
            $defaultGaji = 0;

            if ($karyawan->isKaryawanTetap()) {
                // Karyawan Tetap -> Berdasarkan Pendidikan
                $cachedTetap = \Illuminate\Support\Facades\Cache::get('standar_gaji_pendidikan', []);
                $defaultGaji = $cachedTetap[$pendidikan] ?? 0;
            } else {
                // Karyawan Kontrak
                $jabatanHCList = ['Marketing', 'Call Centre', 'Card Center', 'Teknisi', 'Monitoring ATM Dan Jaringan', 'PPID', 'PPI'];
                $isHC = in_array($jabatan, $jabatanHCList) || $karyawan->divisi === 'HC';

                if ($isHC) {
                    // Divisi HC -> Berdasarkan Tamatan (SMA/SMK vs D3/S1)
                    if (in_array(strtoupper($pendidikan), ['SMA', 'SMK', 'SMA/SMK'])) {
                        $defaultGaji = \Illuminate\Support\Facades\Cache::get('standar_gaji_kontrak_hc_sma', 3200000);
                    } else {
                        $defaultGaji = \Illuminate\Support\Facades\Cache::get('standar_gaji_kontrak_hc_d3_s1', 3800000);
                    }
                } else {
                    // Divisi Umum -> UMR Berjalan
                    $defaultGaji = $umrTahunIni;
                }
            }

            if ($defaultGaji == 0 && !$karyawan->gaji_atas_umr) {
                $defaultGaji = $umrTahunIni;
            }

            if ($karyawan->komponenGaji->isEmpty()) {
                $karyawan->updateKomponenGaji([
                    'gaji_pokok'      => $defaultGaji,
                    'uang_makan'      => $karyawan->uang_makan_by_mitra ? null : Configuration::getValue('uang_makan_default', 35000),
                    'uang_transport'  => $karyawan->uang_makan_by_mitra ? null : Configuration::getValue('uang_transport_default', 45000),
                ]);
                $karyawan->load('komponenGaji');
            } else if ($karyawan->gaji_pokok == 0 && $defaultGaji > 0) {
                $gpRow = $karyawan->komponenGaji->firstWhere('komponen_gaji_id', 'MKG-00001');
                if ($gpRow) {
                    $gpRow->nominal = $defaultGaji;
                } else {
                    $tempRow = new DetailGajiKomponen([
                        'komponen_gaji_id' => 'MKG-00001',
                        'nominal' => $defaultGaji
                    ]);
                    $karyawan->komponenGaji->push($tempRow);
                }
            }
        }

        return view('admin.komponen-gaji.edit', compact('karyawan', 'umrTahunIni'));
    }

    // ─────────────────────────────────────────────────────────────────
    // UPDATE - Simpan komponen gaji
    // ─────────────────────────────────────────────────────────────────
    public function update(Request $request, User $karyawan)
    {
        $rules = [
            'gaji_pokok'      => 'required|numeric|min:0',
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
        ]);

        $karyawan->updateKomponenGaji([
            'gaji_pokok'      => $request->gaji_pokok,
            'uang_makan'      => $karyawan->uang_makan_by_mitra ? null : $request->uang_makan,
            'uang_transport'  => $karyawan->uang_makan_by_mitra ? null : $request->uang_transport,
        ]);

        return redirect()
            ->route('admin.komponen-gaji-karyawan.index')
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

        $employees = User::where('is_active', true)
            ->whereHas('role', fn($q) => $q->whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak']))
            ->get();

        foreach ($employees as $emp) {
            $emp->updateKomponenGaji([
                'persen_bpjs_kes' => $request->persen_bpjs_kes,
                'persen_bpjs_tk'  => $request->persen_bpjs_tk,
            ]);
        }

        return back()->with('success',
            "Persentase BPJS semua karyawan berhasil diperbarui: " .
            "Kesehatan {$request->persen_bpjs_kes}%, Ketenagakerjaan {$request->persen_bpjs_tk}%."
        );
    }
}
