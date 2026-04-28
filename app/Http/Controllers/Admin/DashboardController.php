<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\Mitra;
use App\Models\Absensi;
use App\Models\Perizinan;
use App\Models\Lembur;
use App\Models\DinasLuar;
use App\Models\SlipGaji;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // Statistik kartu atas
        $stats = [
            'karyawan_tetap'    => Karyawan::where('jenis_karyawan', 'tetap')->where('is_active', true)->count(),
            'karyawan_kontrak'  => Karyawan::where('jenis_karyawan', 'kontrak')->where('is_active', true)->count(),
            'mitra_aktif'       => Mitra::whereNull('mitra_induk_id')->count(),
            'karyawan_tersedia' => \App\Models\Penempatan::where('status', 'tersedia')->count(),
        ];

        // Kehadiran hari ini
        $totalKaryawan = $stats['karyawan_tetap'] + $stats['karyawan_kontrak'];
        $hadirHariIni  = Absensi::whereDate('tanggal', $today)
                                ->whereIn('status', ['hadir', 'telat'])
                                ->count();
        $telatHariIni  = Absensi::whereDate('tanggal', $today)
                                ->where('is_telat', true)
                                ->count();
        $persenHadir   = $totalKaryawan > 0
                            ? round(($hadirHariIni / $totalKaryawan) * 100)
                            : 0;

        // Absensi terbaru (10 data)
        $absensiTerbaru = Absensi::with(['karyawan.user'])
                                 ->whereDate('tanggal', $today)
                                 ->latest('waktu_masuk')
                                 ->take(10)
                                 ->get();

        // Pengajuan menunggu approval
        $pengajuanMenunggu = collect()
            ->merge(
                Perizinan::with('karyawan')
                    ->where('status_approval', 'menunggu')
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(fn ($p) => [
                        'type'        => 'perizinan',
                        'model'       => $p,
                        'karyawan'    => $p->karyawan,
                        'label'       => ucfirst(str_replace('_', ' ', $p->jenis_izin)),
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
            ->merge(
                DinasLuar::with('karyawan')
                    ->where('status_approval', 'menunggu')
                    ->latest()
                    ->take(3)
                    ->get()
                    ->map(fn ($d) => [
                        'type'       => 'dinas_luar',
                        'model'      => $d,
                        'karyawan'   => $d->karyawan,
                        'label'      => 'Dinas Luar Kota',
                        'created_at' => $d->created_at,
                    ])
            )
            ->sortByDesc('created_at')
            ->take(5)
            ->values();

        // Karyawan kontrak dengan status tersedia (pool)
        $poolKaryawan = Karyawan::with(['penempatan' => fn ($q) => $q->where('status', 'tersedia')])
                                ->where('jenis_karyawan', 'kontrak')
                                ->where('is_active', true)
                                ->whereHas('penempatan', fn ($q) => $q->where('status', 'tersedia'))
                                ->take(5)
                                ->get();

        return view('admin.dashboard', compact(
            'stats',
            'hadirHariIni',
            'telatHariIni',
            'persenHadir',
            'absensiTerbaru',
            'pengajuanMenunggu',
            'poolKaryawan',
            'totalKaryawan',
        ));
    }
}