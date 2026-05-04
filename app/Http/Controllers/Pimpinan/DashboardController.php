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
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->subDays(6);

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

        // Tren Kehadiran 7 Hari Terakhir
        $trenKehadiran = Absensi::whereBetween('tanggal', [$startOfWeek, $today])
            ->whereIn('status', ['hadir', 'telat'])
            ->selectRaw('tanggal, COUNT(*) as total')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get()
            ->pluck('total', 'tanggal')
            ->mapWithKeys(fn($val, $key) => [Carbon::parse($key)->format('d/m') => $val]);

        // Isi tanggal yang kosong di tren
        $labels = [];
        $values = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('d/m');
            $labels[] = $date;
            $values[] = $trenKehadiran[$date] ?? 0;
        }

        // Pengajuan Terbaru (Pending)
        $pengajuanTerbaru = collect();
        
        $perizinan = Perizinan::with('karyawan')->where('status_approval', 'menunggu')->latest()->take(5)->get()->map(function($item) {
            $item->tipe = 'Perizinan';
            $item->icon = 'file-text';
            $item->color = 'purple';
            return $item;
        });
        
        $lembur = Lembur::with('karyawan')->where('status_approval', 'menunggu')->latest()->take(5)->get()->map(function($item) {
            $item->tipe = 'Lembur';
            $item->icon = 'clock';
            $item->color = 'amber';
            return $item;
        });

        $dinas = DinasLuar::with('karyawan')->where('status_approval', 'menunggu')->latest()->take(5)->get()->map(function($item) {
            $item->tipe = 'Dinas Luar';
            $item->icon = 'map-pin';
            $item->color = 'blue';
            return $item;
        });

        $pengajuanTerbaru = $pengajuanTerbaru->concat($perizinan)->concat($lembur)->concat($dinas)
            ->sortByDesc('created_at')
            ->take(5);

        // Absensi Terbaru Hari Ini
        $absensiTerbaru = Absensi::with('karyawan')
            ->whereDate('tanggal', $today)
            ->whereIn('status', ['hadir', 'telat'])
            ->latest('waktu_masuk')
            ->take(5)
            ->get();

        return view('pimpinan.dashboard', compact(
            'totalKaryawan',
            'hadirHariIni',
            'persenHadir',
            'totalMenunggu',
            'periodeAktif',
            'rekapBulanIni',
            'labels',
            'values',
            'pengajuanTerbaru',
            'absensiTerbaru'
        ));
    }
}