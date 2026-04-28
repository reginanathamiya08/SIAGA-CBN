<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\Absensi;
use App\Models\Perizinan;
use App\Models\Lembur;
use App\Models\DinasLuar;
use App\Models\SlipGaji;
use App\Models\PeriodeGaji;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $totalKaryawan = Karyawan::where('is_active', true)->count();
        $hadirHariIni  = Absensi::whereDate('tanggal', $today)
                                ->whereIn('status', ['hadir', 'telat'])
                                ->count();
        $persenHadir   = $totalKaryawan > 0
                            ? round(($hadirHariIni / $totalKaryawan) * 100)
                            : 0;

        // Total pengajuan menunggu
        $totalMenunggu = Perizinan::where('status_approval', 'menunggu')->count()
                       + Lembur::where('status_approval', 'menunggu')->count()
                       + DinasLuar::where('status_approval', 'menunggu')->count();

        // Periode gaji terakhir
        $periodeAktif = PeriodeGaji::whereIn('status', ['draft', 'proses'])->latest()->first();

        // Rekap ketidakhadiran bulan ini
        $rekapBulanIni = Absensi::whereMonth('tanggal', $today->month)
                                ->whereYear('tanggal', $today->year)
                                ->selectRaw('status, COUNT(*) as total')
                                ->groupBy('status')
                                ->pluck('total', 'status');

        return view('pimpinan.dashboard', compact(
            'totalKaryawan',
            'hadirHariIni',
            'persenHadir',
            'totalMenunggu',
            'periodeAktif',
            'rekapBulanIni',
        ));
    }
}