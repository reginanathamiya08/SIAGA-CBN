<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Absensi;
use App\Models\DetailPerizinan;
use App\Models\Lembur;
use App\Models\SlipGajiPeriode;
use App\Models\PeriodeGaji;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        \App\Helpers\AttendanceHelper::runAutoAlfaDeduction();

        $today = Carbon::today();
        $startOfWeek = Carbon::now()->subDays(6);

        $totalKaryawan = User::where('is_active', true)->count();
        $hadirHariIni  = Absensi::whereDate('tanggal', $today)
                                ->whereIn('status', ['hadir', 'telat'])
                                ->count();
        $persenHadir   = $totalKaryawan > 0
                            ? round(($hadirHariIni / $totalKaryawan) * 100)
                            : 0;

        // Total pengajuan menunggu (termasuk persetujuan gaji)
        $totalMenunggu = DetailPerizinan::where('status_approval', 'menunggu')->count()
                       + Lembur::where('status_approval', 'menunggu')->count()
                       + PeriodeGaji::where('status', 'proses')->count();

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
        
        $perizinan = DetailPerizinan::with(['karyawan', 'jenisPerizinan'])->where('status_approval', 'menunggu')->latest()->take(5)->get()->map(function($item) {
            $item->tipe = $item->jenisPerizinan?->slug === 'dinas_luar' ? 'Dinas Luar' : 'Perizinan';
            $item->icon = $item->jenisPerizinan?->slug === 'dinas_luar' ? 'map-pin' : 'file-text';
            $item->color = $item->jenisPerizinan?->slug === 'dinas_luar' ? 'emerald' : 'purple';
            return $item;
        });
        
        $lembur = Lembur::with('karyawan')->where('status_approval', 'menunggu')->latest()->take(5)->get()->map(function($item) {
            $item->tipe = 'Lembur';
            $item->icon = 'clock';
            $item->color = 'amber';
            return $item;
        });

        $pengajuanTerbaru = $pengajuanTerbaru->concat($perizinan)->concat($lembur)
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
