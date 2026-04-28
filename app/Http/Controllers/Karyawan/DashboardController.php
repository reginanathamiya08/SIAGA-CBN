<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Perizinan;
use App\Models\KuotaCuti;
use App\Models\SlipGaji;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user     = Auth::user();
        $karyawan = $user->karyawan;
        $today    = Carbon::today();

        // Absensi hari ini
        $absensiHariIni = Absensi::where('karyawan_id', $karyawan->id)
                                 ->whereDate('tanggal', $today)
                                 ->first();

        // Rekap bulan ini
        $rekapBulan = Absensi::where('karyawan_id', $karyawan->id)
                             ->whereMonth('tanggal', $today->month)
                             ->whereYear('tanggal', $today->year)
                             ->selectRaw('status, COUNT(*) as total')
                             ->groupBy('status')
                             ->pluck('total', 'status');

        // Kuota cuti tahun ini
        $kuotaCuti = KuotaCuti::where('karyawan_id', $karyawan->id)
                              ->where('tahun', $today->year)
                              ->first();

        // Pengajuan terakhir (3 data)
        $pengajuanTerakhir = Perizinan::where('karyawan_id', $karyawan->id)
                                      ->latest()
                                      ->take(3)
                                      ->get();

        // Slip gaji terbaru
        $slipTerbaru = SlipGaji::where('karyawan_id', $karyawan->id)
                               ->where('status', 'diterbitkan')
                               ->with('periode')
                               ->latest()
                               ->first();

        return view('karyawan.dashboard', compact(
            'karyawan',
            'absensiHariIni',
            'rekapBulan',
            'kuotaCuti',
            'pengajuanTerakhir',
            'slipTerbaru',
        ));
    }
}