<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DetailGajiKomponen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class GajiMassalController extends Controller
{
    public function index()
    {
        // 1. DATA KARYAWAN TETAP (Berdasarkan Pendidikan)
        $levels = ['S3', 'S2', 'S1', 'D3', 'SMA/SMK'];
        
        $roleTetapIds = \App\Models\Role::where('slug', 'karyawan_tetap')->pluck('id');
        $roleKontrakIds = \App\Models\Role::where('slug', 'karyawan_kontrak')->pluck('id');

        $dbTetapSalaries = DetailGajiKomponen::query()->join('users', 'detail_gaji_komponen.user_id', '=', 'users.id')
            ->whereNull('detail_gaji_komponen.slip_gaji_periode_id')
            ->where('detail_gaji_komponen.komponen_gaji_id', 'MKG-00001')
            ->whereIn('users.role_id', $roleTetapIds)
            ->select('users.pendidikan', DB::raw('MAX(detail_gaji_komponen.nominal) as gaji'))
            ->whereNotNull('users.pendidikan')
            ->groupBy('users.pendidikan')
            ->pluck('gaji', 'pendidikan')->toArray();

        $cachedTetap = Cache::get('standar_gaji_pendidikan', []);
        $currentTetapSalaries = array_merge($dbTetapSalaries, $cachedTetap);

        // 2. DATA KARYAWAN KONTRAK
        // Divisi HC (Berdasarkan Tamatan: SMA vs D3/S1)
        $gajiKontrakHcSma = Cache::get('standar_gaji_kontrak_hc_sma', 3200000);
        $gajiKontrakHcD3S1 = Cache::get('standar_gaji_kontrak_hc_d3_s1', 3800000);

        // Divisi Umum (UMR Berjalan)
        $umrTahunIni = (float) \App\Models\Configuration::getValue('umr_tahun_ini', 2994031);

        $jabatanHCList = ['Marketing', 'Call Centre', 'Card Center', 'Teknisi', 'Monitoring ATM Dan Jaringan', 'PPID', 'PPI'];
        $jabatanUmumList = ['CS', 'CS ATM', 'Ekspedisi'];

        // Statistik
        $statsTetap = [];
        foreach ($levels as $lv) { 
            $statsTetap[$lv] = User::query()->whereHas('role', fn($r) => $r->where('slug', 'karyawan_tetap'))->where('pendidikan', $lv)->count(); 
        }

        $countKontrakHcSma = User::query()->whereHas('role', fn($r) => $r->where('slug', 'karyawan_kontrak'))
            ->where(function($q) use ($jabatanHCList) {
                $q->whereIn('jabatan', $jabatanHCList)->orWhere('divisi', 'HC');
            })
            ->whereIn('pendidikan', ['SMA', 'SMK', 'SMA/SMK'])
            ->count();

        $countKontrakHcD3S1 = User::query()->whereHas('role', fn($r) => $r->where('slug', 'karyawan_kontrak'))
            ->where(function($q) use ($jabatanHCList) {
                $q->whereIn('jabatan', $jabatanHCList)->orWhere('divisi', 'HC');
            })
            ->whereNotIn('pendidikan', ['SMA', 'SMK', 'SMA/SMK'])
            ->count();

        $countKontrakUmum = User::query()->whereHas('role', fn($r) => $r->where('slug', 'karyawan_kontrak'))
            ->where(function($q) use ($jabatanUmumList) {
                $q->whereIn('jabatan', $jabatanUmumList)->orWhere('divisi', 'umum');
            })
            ->count();

        $totalKaryawan = User::query()->where('is_active', true)->whereHas('role', fn($r) => $r->whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak']))->count();
        $countSudahIsi = User::where('is_active', true)
            ->whereHas('role', fn($r) => $r->whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak']))
            ->whereHas('komponenGaji', fn($q) => $q->where('komponen_gaji_id', 'MKG-00001')->where('nominal', '>', 0))
            ->count();
        $countBelumIsi = $totalKaryawan - $countSudahIsi;

        $currentSalaries = array_merge($currentTetapSalaries, [
            'kontrak_hc_sma'   => $gajiKontrakHcSma,
            'kontrak_hc_d3_s1' => $gajiKontrakHcD3S1,
            'umr_tahun_ini'    => $umrTahunIni,
        ]);

        return view('admin.gaji-massal.index', compact(
            'levels',
            'currentSalaries',
            'statsTetap',
            'jabatanHCList',
            'jabatanUmumList',
            'countKontrakHcSma',
            'countKontrakHcD3S1',
            'countKontrakUmum',
            'countBelumIsi',
            'countSudahIsi',
            'totalKaryawan'
        ));
    }

    private function cleanNominal($nominal)
    {
        if (!$nominal) return 0;
        $clean = preg_replace('/[^0-9]/', '', $nominal);
        return (float) $clean;
    }

    public function updateTetap(Request $request)
    {
        $request->validate(['gaji' => 'required|array']);
        
        $cache = Cache::get('standar_gaji_pendidikan', []);
        foreach($request->gaji as $lv => $nominal) {
            if ($nominal !== null && $nominal !== '') { 
                $cache[$lv] = $this->cleanNominal($nominal); 
            }
        }
        Cache::put('standar_gaji_pendidikan', $cache, 60*24*30);

        DB::transaction(function() use ($request) {
            foreach($request->gaji as $lv => $nominal) {
                if ($nominal === null || $nominal === '') continue;
                $cleanVal = $this->cleanNominal($nominal);
                
                $karyawanIds = User::query()->whereHas('role', fn($r) => $r->where('slug', 'karyawan_tetap'))->where('pendidikan', $lv)->pluck('id');
                foreach($karyawanIds as $id) {
                    $user = User::find($id);
                    if ($user) {
                        $user->updateKomponenGaji(['gaji_pokok' => $cleanVal]);
                    }
                }
            }
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Gaji Karyawan Tetap (Tamatan) berhasil diperbarui.']);
        }

        return redirect()->route('admin.komponen-gaji-karyawan.index')->with(['success' => 'Gaji Karyawan Tetap (Tamatan) berhasil diperbarui.', 'mode' => 'tetap']);
    }

    public function updateKontrakHc(Request $request)
    {
        $request->validate([
            'gaji_sma'   => 'required',
            'gaji_d3_s1' => 'required',
        ]);

        $gajiSma  = $this->cleanNominal($request->gaji_sma);
        $gajiD3S1 = $this->cleanNominal($request->gaji_d3_s1);

        Cache::put('standar_gaji_kontrak_hc_sma', $gajiSma, 60*24*30);
        Cache::put('standar_gaji_kontrak_hc_d3_s1', $gajiD3S1, 60*24*30);

        $jabatanHCList = ['Marketing', 'Call Centre', 'Card Center', 'Teknisi', 'Monitoring ATM Dan Jaringan', 'PPID', 'PPI'];

        DB::transaction(function() use ($jabatanHCList, $gajiSma, $gajiD3S1) {
            $kontrakHc = User::whereHas('role', fn($r) => $r->where('slug', 'karyawan_kontrak'))
                ->where(function($q) use ($jabatanHCList) {
                    $q->whereIn('jabatan', $jabatanHCList)->orWhere('divisi', 'HC');
                })->get();

            foreach ($kontrakHc as $user) {
                $gaji = in_array(strtoupper($user->pendidikan), ['SMA', 'SMK', 'SMA/SMK']) ? $gajiSma : $gajiD3S1;
                $user->updateKomponenGaji(['gaji_pokok' => $gaji]);
            }
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Gaji Kontrak Divisi HC berhasil diperbarui berdasarkan tamatan.']);
        }

        return redirect()->route('admin.komponen-gaji-karyawan.index')->with(['success' => 'Gaji Kontrak Divisi HC berhasil diperbarui.', 'mode' => 'kontrak']);
    }

    public function updateUmr(Request $request)
    {
        $request->validate(['nominal_umr' => 'required']);
        $nominal = $this->cleanNominal($request->nominal_umr);
        
        \App\Models\Configuration::setValue('umr_tahun_ini', $nominal);
        Cache::put('umr_tahun_ini', $nominal, 60*24*30);

        $jabatanUmumList = ['CS', 'CS ATM', 'Ekspedisi'];

        $kontrakUmum = User::whereHas('role', fn($r) => $r->where('slug', 'karyawan_kontrak'))
            ->where(function($q) use ($jabatanUmumList) {
                $q->whereIn('jabatan', $jabatanUmumList)->orWhere('divisi', 'umum');
            })->get();

        foreach ($kontrakUmum as $user) {
            $user->updateKomponenGaji(['gaji_pokok' => $nominal]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Gaji UMR Kontrak berhasil diperbarui.']);
        }

        return redirect()->route('admin.komponen-gaji-karyawan.index')->with(['success' => "Gaji UMR Kontrak berhasil diperbarui.", 'mode' => 'kontrak']);
    }

    public function updateSpesialis(Request $request)
    {
        return $this->updateKontrakHc($request);
    }
}
