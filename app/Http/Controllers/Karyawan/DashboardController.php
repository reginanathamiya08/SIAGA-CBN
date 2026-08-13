<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\DetailPerizinan;
use App\Models\KuotaPerizinan;
use App\Models\SlipGajiPeriode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        \App\Helpers\AttendanceHelper::runAutoAlfaDeduction();

        $user     = Auth::user();
        $karyawan = $user->karyawan;
        $today    = Carbon::today();

        // Absensi hari ini
        $absensiHariIni = Absensi::where('user_id', $karyawan->id)
                                 ->whereDate('tanggal', $today)
                                 ->first();

        // Rekap bulan ini
        $rekapBulan = Absensi::where('user_id', $karyawan->id)
                             ->whereMonth('tanggal', $today->month)
                             ->whereYear('tanggal', $today->year)
                             ->selectRaw('status, COUNT(*) as total')
                             ->groupBy('status')
                             ->pluck('total', 'status');

        // Kuota cuti/perizinan tahun ini
        $kuotaPerizinan = KuotaPerizinan::where('user_id', $karyawan->id)
                              ->where('tahun', $today->year)
                              ->first();

        // Pengajuan terakhir (3 data)
        $pengajuanTerakhir = $karyawan->perizinan()
                                      ->with('jenisPerizinan')
                                      ->latest()
                                      ->take(3)
                                      ->get();

        // Slip gaji terbaru
        $slipTerbaru = SlipGajiPeriode::where('user_id', $karyawan->id)
                               ->where('status', 'diterbitkan')
                               ->with('periode')
                               ->latest()
                               ->first();

        return view('karyawan.dashboard', compact(
            'karyawan',
            'absensiHariIni',
            'rekapBulan',
            'kuotaPerizinan',
            'pengajuanTerakhir',
            'slipTerbaru',
        ));
    }
}
