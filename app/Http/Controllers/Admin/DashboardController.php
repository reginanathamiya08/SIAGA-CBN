<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Mitra;
use App\Models\Absensi;
use App\Models\DetailPerizinan;
use App\Models\Lembur;
use App\Models\SlipGajiPeriode;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // Statistik kartu atas
        $stats = [
            'karyawan_tetap'    => User::where('is_active', true)
                                    ->whereHas('role', fn($q) => $q->where('slug', 'karyawan_tetap'))
                                    ->count(),
            'karyawan_kontrak'  => User::where('is_active', true)
                                    ->whereHas('role', fn($q) => $q->where('slug', 'karyawan_kontrak'))
                                    ->count(),
            'mitra_aktif'       => Mitra::whereNull('mitra_induk_id')->count(),
            'karyawan_tersedia' => \App\Models\DetailRiwayatPenempatan::where('status', 'tersedia')->count(),
        ];

        // Kehadiran hari ini (Lebih Akurat)
        $totalKaryawan = $stats['karyawan_tetap'] + $stats['karyawan_kontrak'];
        
        $kehadiran = Absensi::whereDate('tanggal', $today)
                                ->selectRaw('status, COUNT(*) as total, SUM(CASE WHEN is_telat = 1 THEN 1 ELSE 0 END) as total_telat')
                                ->groupBy('status')
                                ->get()
                                ->pluck('total', 'status');

        $hadirHariIni  = ($kehadiran['hadir'] ?? 0) + ($kehadiran['telat'] ?? 0);
        $telatHariIni  = Absensi::whereDate('tanggal', $today)->where('is_telat', true)->count();
        $izinSakit     = ($kehadiran['izin'] ?? 0) + ($kehadiran['sakit'] ?? 0) + ($kehadiran['cuti'] ?? 0);
        
        // Persentase Kehadiran
        $terdataHariIni = $hadirHariIni + $izinSakit;
        $persenHadir    = $totalKaryawan > 0
                            ? round(($terdataHariIni / $totalKaryawan) * 100)
                            : 0;

        // Absensi terbaru (10 data)
        $absensiTerbaru = Absensi::with(['karyawan.role'])
                                 ->whereDate('tanggal', $today)
                                 ->latest('waktu_masuk')
                                 ->take(10)
                                 ->get();

        // Pengajuan menunggu approval
        $pengajuanMenunggu = collect()
            ->merge(
                DetailPerizinan::with(['karyawan', 'jenisPerizinan'])
                    ->where('status_approval', 'menunggu')
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(fn ($p) => [
                        'type'        => 'perizinan',
                        'model'       => $p,
                        'karyawan'    => $p->karyawan,
                        'label'       => $p->labelJenis(),
                        'created_at'  => $p->created_at,
                    ])
            )
            ->merge(
                Lembur::with('karyawan')
                    ->where('status_approval', 'menunggu')
                    ->latest()
                    ->take(3)
                    ->get()
                    ->map(fn ($l) => [
                        'type'       => 'lembur',
                        'model'      => $l,
                        'karyawan'   => $l->karyawan,
                        'label'      => 'Lembur',
                        'created_at' => $l->created_at,
                    ])
            )
            ->sortByDesc('created_at')
            ->take(5)
            ->values();

        // Karyawan kontrak dengan status tersedia (pool)
        $poolKaryawan = User::with(['penempatan' => fn ($q) => $q->where('status', 'tersedia')])
                                ->whereHas('role', fn ($q) => $q->where('slug', 'karyawan_kontrak'))
                                ->where('is_active', true)
                                ->whereHas('penempatan', fn ($q) => $q->where('status', 'tersedia'))
                                ->take(5)
                                ->get();

        return view('admin.dashboard', compact(
            'stats',
            'hadirHariIni',
            'telatHariIni',
            'izinSakit',
            'persenHadir',
            'absensiTerbaru',
            'pengajuanMenunggu',
            'poolKaryawan',
            'totalKaryawan',
        ));
    }
}
