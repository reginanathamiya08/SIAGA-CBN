<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\KomponenGaji;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class GajiMassalController extends Controller
{
    public function index()
    {
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

        // Ambil Gaji dari Database (yang sudah ada orangnya)
        $dbSalaries = KomponenGaji::join('karyawan', 'komponen_gaji.karyawan_id', '=', 'karyawan.id')
            ->select('karyawan.jabatan', DB::raw('MAX(gaji_pokok) as gaji'))
            ->groupBy('karyawan.jabatan')
            ->pluck('gaji', 'jabatan')->toArray();

        // Gabungkan: Nilai dari Cache (Input Standar User) menimpa Nilai DB (Kondisi Saat Ini)
        // Agar jika di DB masih 0, angka yang baru saja diinput user tidak hilang.
        $cachedSalaries = Cache::get('standar_gaji_jabatan', []);
        $currentSalaries = array_merge($dbSalaries, $cachedSalaries);

        // Statistik
        $countTetap = Karyawan::where('jenis_karyawan', 'tetap')->count();
        $countKontrakHC = Karyawan::where('jenis_karyawan', 'kontrak')->where('divisi', 'HC')->count();
        $countKontrakUmum = Karyawan::where('jenis_karyawan', 'kontrak')->where('divisi', 'umum')->count();
        $totalKaryawan = Karyawan::count();
        $countSudahIsi = KomponenGaji::whereNotNull('gaji_pokok')->where('gaji_pokok', '>', 0)->count();
        $countBelumIsi = $totalKaryawan - $countSudahIsi;

        return view('admin.gaji-massal.index', compact(
            'jabatanTetapByDivisi', 
            'jabatanHCSpesialisKontrak', 
            'jabatanHCUmrKontrak', 
            'jabatanUmumKontrak',
            'currentSalaries',
            'countTetap', 
            'countKontrakHC', 
            'countKontrakUmum',
            'countBelumIsi',
            'countSudahIsi'
        ));
    }

    public function updateUmr(Request $request)
    {
        $request->validate(['nominal_umr' => 'required|numeric|min:0', 'target' => 'required']);
        
        $jabatans = ($request->target == 'hc_umr') 
            ? ['Satpam', 'Sopir', 'Pramusaji', 'Pramubakti', 'E-Channel', 'Juru Parkir']
            : ['CS', 'CS ATM', 'Ekspedisi'];

        // Simpan ke Cache agar angka tetap muncul di view
        $cache = Cache::get('standar_gaji_jabatan', []);
        foreach($jabatans as $j) { $cache[$j] = $request->nominal_umr; }
        Cache::put('standar_gaji_jabatan', $cache, 60*24*30); // 30 hari

        $karyawanIds = Karyawan::where('jenis_karyawan', 'kontrak')
            ->where('divisi', ($request->target == 'hc_umr' ? 'HC' : 'umum'))
            ->whereIn('jabatan', $jabatans)->pluck('id');

        foreach ($karyawanIds as $id) {
            KomponenGaji::updateOrCreate(['karyawan_id' => $id], ['gaji_pokok' => $request->nominal_umr]);
        }

        return back()->with(['success' => "Gaji UMR berhasil diperbarui.", 'mode' => 'kontrak']);
    }

    public function updateSpesialis(Request $request)
    {
        $request->validate(['gaji' => 'required|array']);
        $mode = 'tetap';
        
        // Simpan ke Cache agar angka tetap muncul di view
        $cache = Cache::get('standar_gaji_jabatan', []);
        foreach($request->gaji as $jabatan => $nominal) {
            if ($nominal !== null && $nominal !== '') {
                $cache[$jabatan] = $nominal;
            }
        }
        Cache::put('standar_gaji_jabatan', $cache, 60*24*30);

        DB::transaction(function() use ($request, &$mode) {
            foreach ($request->gaji as $jabatan => $nominal) {
                if ($nominal !== null && $nominal !== '') {
                    $karyawans = Karyawan::where('jabatan', $jabatan)->get();
                    if($karyawans->isNotEmpty() && $karyawans->first()->jenis_karyawan == 'kontrak') $mode = 'kontrak';
                    
                    foreach ($karyawans as $kar) {
                        KomponenGaji::updateOrCreate(['karyawan_id' => $kar->id], ['gaji_pokok' => $nominal]);
                    }
                }
            }
        });

        return back()->with(['success' => "Gaji pokok berhasil diperbarui.", 'mode' => $mode]);
    }
}