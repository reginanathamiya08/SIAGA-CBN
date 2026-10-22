<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\User;
use App\Models\Mitra;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class MonitoringKehadiranController extends Controller
{
    public function index(Request $request)
    {
        \App\Helpers\AttendanceHelper::runAutoAlfaDeduction();

        $today         = Carbon::today();
        $mitraId       = $request->input('mitra_id');
        $statusFilter  = $request->input('status');

        // 1. Hitung Statistik Keseluruhan (untuk Pie Chart Global)
        $semuaAbsensiHariIni = Absensi::with('karyawan')->whereDate('tanggal', $today)->get();
        $semuaKaryawanAktif = User::where('is_active', true)
            ->whereHas('role', function($q) {
                $q->whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak']);
            })
            ->get();

        // 2. Hitung Statistik Per Jenis (Tetap & Kontrak) - DIBUTUHKAN VIEW
        $stats = [];
        $pieDataTetap = [];
        $pieDataKontrak = [];

        foreach (['tetap', 'kontrak'] as $jenis) {
            $slug = ($jenis === 'tetap') ? 'karyawan_tetap' : 'karyawan_kontrak';
            $totalJ = $semuaKaryawanAktif->filter(fn($u) => $u->role?->slug === $slug)->count();
            $absJ   = $semuaAbsensiHariIni->filter(fn($a) => ($a->karyawan?->role?->slug ?? '') === $slug);

            $hadirJ  = $absJ->whereIn('status', ['hadir', 'telat'])->count();
            $telatJ  = $absJ->where('is_telat', true)->count();
            $izinJ   = $absJ->whereIn('status', ['izin', 'sakit', 'cuti', 'dinas_luar'])->count();
            $alfaJ   = $absJ->where('status', 'alfa')->count();
            $belumJ  = max(0, $totalJ - $absJ->count());
            $persenJ = $totalJ > 0 ? round(($hadirJ / $totalJ) * 100) : 0;

            $stats[$jenis] = [
                'total'        => $totalJ,
                'hadir'        => $hadirJ,
                'telat'        => $telatJ,
                'izin'         => $izinJ,
                'belum'        => $belumJ,
                'alfa'         => $alfaJ,
                'persen_hadir' => $persenJ,
            ];

            $piePerJenis = [
                'tepat_waktu' => $hadirJ - $telatJ,
                'telat'       => $telatJ,
                'izin'        => $izinJ,
                'alfa'        => $alfaJ,
                'belum'       => $belumJ,
            ];

            if ($jenis === 'tetap') $pieDataTetap = $piePerJenis;
            else $pieDataKontrak = $piePerJenis;
        }

        // Statistik Global (untuk Pie Chart dan Ringkasan)
        $totalKaryawan = $semuaKaryawanAktif->count();
        $hadirCount  = $semuaAbsensiHariIni->whereIn('status', ['hadir', 'telat'])->count();
        $telatCount  = $semuaAbsensiHariIni->where('is_telat', true)->count();
        $izinCount   = $semuaAbsensiHariIni->whereIn('status', ['izin', 'sakit', 'cuti', 'dinas_luar'])->count();
        $alfaCount   = $semuaAbsensiHariIni->where('status', 'alfa')->count();
        $belumHadir  = max(0, $totalKaryawan - $hadirCount - $izinCount - $alfaCount);
        $persenHadir = $totalKaryawan > 0 ? round(($hadirCount / $totalKaryawan) * 100) : 0;

        $pieData = [
            'tepat_waktu' => $hadirCount - $telatCount,
            'telat'       => $telatCount,
            'izin'        => $izinCount,
            'alfa'        => $alfaCount,
            'belum'       => $belumHadir,
        ];

        // 3. Query untuk Tabel Karyawan Tetap
        $dbTetap = Absensi::with(['karyawan'])
            ->whereDate('tanggal', $today)
            ->whereHas('karyawan', fn($q) => $q->whereHas('role', fn($r) => $r->where('slug', 'karyawan_tetap')))
            ->get();

        $karyawanTetapList = User::where('is_active', true)
            ->whereHas('role', fn($q) => $q->where('slug', 'karyawan_tetap'))
            ->get();

        $mergedTetap = collect();
        foreach ($karyawanTetapList as $karyawan) {
            $existing = $dbTetap->firstWhere('user_id', $karyawan->id);
            if ($existing) {
                $mergedTetap->push($existing);
            } else {
                $virtualAbs = new Absensi([
                    'user_id' => $karyawan->id,
                    'tanggal' => $today,
                    'status'  => 'belum_absen',
                ]);
                $virtualAbs->setRelation('karyawan', $karyawan);
                $mergedTetap->push($virtualAbs);
            }
        }

        // Apply filters on Tetap
        if ($statusFilter) {
            if ($statusFilter === 'telat') {
                $mergedTetap = $mergedTetap->where('is_telat', true);
            } else {
                $mergedTetap = $mergedTetap->where('status', $statusFilter);
            }
        }

        // Sort Tetap: present/active status first, then name
        $absensiTetap = $mergedTetap->sortBy(function($a) {
            return ($a->status === 'belum_absen' ? 'z_' : 'a_') . strtolower($a->karyawan?->nama ?? '');
        })->values();


        // 4. Query untuk Tabel Karyawan Kontrak
        $dbKontrak = Absensi::with(['karyawan.penempatanAktif.mitra', 'mitra'])
            ->whereDate('tanggal', $today)
            ->whereHas('karyawan', fn($q) => $q->whereHas('role', fn($r) => $r->where('slug', 'karyawan_kontrak')))
            ->get();

        $karyawanKontrakList = User::with(['penempatanAktif.mitra'])
            ->where('is_active', true)
            ->whereHas('role', fn($q) => $q->where('slug', 'karyawan_kontrak'))
            ->get();

        $mergedKontrak = collect();
        foreach ($karyawanKontrakList as $karyawan) {
            $existing = $dbKontrak->firstWhere('user_id', $karyawan->id);
            if ($existing) {
                $mergedKontrak->push($existing);
            } else {
                $virtualAbs = new Absensi([
                    'user_id'  => $karyawan->id,
                    'tanggal'  => $today,
                    'status'   => 'belum_absen',
                    'mitra_id' => $karyawan->penempatanAktif?->mitra_id,
                ]);
                $virtualAbs->setRelation('karyawan', $karyawan);
                $virtualAbs->setRelation('mitra', $karyawan->penempatanAktif?->mitra);
                $mergedKontrak->push($virtualAbs);
            }
        }

        // Apply filters on Kontrak
        if ($mitraId) {
            $mergedKontrak = $mergedKontrak->filter(function($a) use ($mitraId) {
                return $a->mitra_id == $mitraId;
            });
        }

        if ($statusFilter) {
            if ($statusFilter === 'telat') {
                $mergedKontrak = $mergedKontrak->where('is_telat', true);
            } else {
                $mergedKontrak = $mergedKontrak->where('status', $statusFilter);
            }
        }

        // Sort Kontrak: present/active status first, then name
        $absensiKontrak = $mergedKontrak->sortBy(function($a) {
            return ($a->status === 'belum_absen' ? 'z_' : 'a_') . strtolower($a->karyawan?->nama ?? '');
        })->values();

        $semuaMitra     = Mitra::where('id', '!=', 'MTR-00001')
            ->orderByRaw('COALESCE(mitra_induk_id, id), is_cabang ASC, nama_mitra ASC')
            ->get();

        return view('pimpinan.monitoring.index', compact(
            'today', 'totalKaryawan', 'hadirCount', 'telatCount',
            'izinCount', 'alfaCount', 'belumHadir', 'persenHadir',
            'pieData', 'absensiTetap', 'absensiKontrak', 'semuaMitra',
            'mitraId', 'statusFilter',
            'stats', 'pieDataTetap', 'pieDataKontrak'
        ));
    }

    public function statistik(Request $request)
    {
        $dari          = $request->input('dari')   ? Carbon::parse($request->input('dari'))   : Carbon::now()->startOfMonth();
        $sampai        = $request->input('sampai') ? Carbon::parse($request->input('sampai')) : Carbon::now()->endOfDay();
        $mitraId       = $request->input('mitra_id');
        $divisi        = $request->input('divisi');
        $jenisKaryawan = $request->input('jenis_karyawan_id');
        $karyawanId    = $request->input('user_id');

        $query = Absensi::with(['karyawan', 'mitra'])
            ->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
            ->orderBy('tanggal');

        if ($mitraId)    $query->where('mitra_id', $mitraId);
        if ($karyawanId) $query->where('user_id', $karyawanId);
        if ($divisi || $jenisKaryawan) {
            $query->whereHas('karyawan', function ($q) use ($divisi, $jenisKaryawan) {
                if ($divisi)        $q->where('divisi', $divisi);
                if ($jenisKaryawan) {
                    $slugTarget = in_array($jenisKaryawan, ['tetap', 'karyawan_tetap', 'JNS-00001']) ? 'karyawan_tetap' : 'karyawan_kontrak';
                    $q->whereHas('role', fn($r) => $r->where('slug', $slugTarget));
                }
            });
        }

        $absensiList    = $query->get();
        $totalHariKerja = $this->hitungHariKerja($dari, $sampai);

        $trenPerHari = $absensiList
            ->groupBy(fn($a) => $a->tanggal->format('d/m'))
            ->map(fn($rows) => $rows->whereNotIn('status', ['hadir', 'telat'])->count());

        $top10 = $absensiList
            ->groupBy('user_id')
            ->map(fn($rows) => [
                'nama'  => $rows->first()->karyawan?->nama ?? '-',
                'total' => $rows->where('is_telat', true)->count()
                         + $rows->where('status', 'alfa')->count(),
            ])
            ->sortByDesc('total')
            ->take(10)
            ->values();

        $rekapTabel = $absensiList->groupBy('user_id')->map(function ($rows) use ($totalHariKerja) {
            $k      = $rows->first()->karyawan;
            $rows   = \App\Helpers\AttendanceHelper::hanyaHariKerja($rows);
            $masuk  = $rows->whereIn('status', ['hadir', 'telat'])->count();          // hadir tepat waktu + telat
            $hadir  = $rows->whereIn('status', ['hadir', 'telat'])->where('is_telat', false)->count(); // tepat waktu saja
            $persen = $totalHariKerja > 0 ? round(($masuk / $totalHariKerja) * 100, 1) : 0;
            $alfa   = $rows->where('status', 'alfa')->count();

            $evaluasiStatus = match(true) {
                $persen >= 90 => 'Sangat Layak',
                $persen >= 80 => 'Layak',
                default       => 'Perlu Evaluasi'
            };

            $evaluasiBadge = match(true) {
                $persen >= 90 => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                $persen >= 80 => 'bg-blue-100 text-blue-800 border-blue-200',
                default       => 'bg-rose-100 text-rose-800 border-rose-200'
            };

            return [
                'id'              => $k?->id,
                'nama'            => $k?->nama ?? '-',
                'jabatan'         => $k?->jabatan ?? '-',
                'is_tetap'        => $k?->isTetap() ?? false,
                'mitra'           => $rows->first()->mitra?->nama_mitra ?? ($k?->isTetap() ? 'Kantor CBN' : '-'),
                'hadir'           => $hadir,
                'telat'           => $rows->where('is_telat', true)->count(),
                'alfa'            => $alfa,
                'izin'            => $rows->where('status', 'izin')->count(),
                'sakit'           => $rows->where('status', 'sakit')->count(),
                'cuti'            => $rows->where('status', 'cuti')->count(),
                'dinas'           => $rows->where('status', 'dinas_luar')->count(),
                'persen'          => $persen,
                'evaluasi_status' => $evaluasiStatus,
                'evaluasi_badge'  => $evaluasiBadge,
            ];
        })->values();

        $semuaMitra    = Mitra::orderByRaw('COALESCE(mitra_induk_id, id), is_cabang ASC, nama_mitra ASC')->get();
        $semuaKaryawan = User::where('is_active', true)->orderBy('nama')->get();

        $countTetap = User::where('is_active', true)
            ->whereHas('role', fn($q) => $q->where('slug', 'karyawan_tetap'))
            ->count();
        $countKontrak = User::where('is_active', true)
            ->whereHas('role', fn($q) => $q->where('slug', 'karyawan_kontrak'))
            ->count();
        $countSemua = $countTetap + $countKontrak;

        // Stats Evaluasi Kontrak untuk Karyawan Kontrak
        $kontrakRekapList = $rekapTabel->where('is_tetap', false);
        $evalKontrakStats = [
            'sangat_layak'   => $kontrakRekapList->where('evaluasi_status', 'Sangat Layak')->count(),
            'layak'          => $kontrakRekapList->where('evaluasi_status', 'Layak')->count(),
            'perlu_evaluasi' => $kontrakRekapList->where('evaluasi_status', 'Perlu Evaluasi')->count(),
        ];

        return view('pimpinan.monitoring.statistik', compact(
            'dari', 'sampai', 'trenPerHari', 'top10', 'rekapTabel',
            'totalHariKerja', 'semuaMitra', 'semuaKaryawan',
            'mitraId', 'divisi', 'jenisKaryawan', 'karyawanId',
            'countTetap', 'countKontrak', 'countSemua', 'evalKontrakStats'
        ));
    }

    public function detail(Request $request, User $karyawan)
    {
        $dari   = $request->input('dari')
            ? Carbon::parse($request->input('dari'))->startOfDay()
            : Carbon::now()->startOfMonth();
        $sampai = $request->input('sampai')
            ? Carbon::parse($request->input('sampai'))->endOfDay()
            : Carbon::now()->endOfDay();
        $statusFilter = $request->input('status');

        $query = Absensi::with('mitra')
            ->where('user_id', $karyawan->id)
            ->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
            ->orderByDesc('tanggal');

        if ($statusFilter) {
            if ($statusFilter === 'telat') {
                $query->where('is_telat', true);
            } else {
                $query->where('status', $statusFilter);
            }
        }

        $riwayat = $query->get();

        $rekap = [
            'hadir' => $riwayat->whereIn('status', ['hadir', 'telat'])->where('is_telat', false)->count(),
            'telat' => $riwayat->where('is_telat', true)->count(),
            'alfa'  => $riwayat->where('status', 'alfa')->count(),
            'izin'  => $riwayat->where('status', 'izin')->count(),
            'sakit' => $riwayat->where('status', 'sakit')->count(),
            'cuti'  => $riwayat->where('status', 'cuti')->count(),
            'dinas' => $riwayat->where('status', 'dinas_luar')->count(),
        ];

        $kuotaCuti  = $karyawan->kuotaPerizinanTahunIni();
        $kalender = $riwayat->keyBy(fn($a) => $a->tanggal->format('Y-m-d'))
            ->map(fn($a) => $this->warnaBadgeStatus($a));

        // Track Record Bulanan untuk Evaluasi Perpanjangan Kontrak
        $trackRecordBulanan = $this->getTrackRecordBulanan($karyawan, 6);

        return view('pimpinan.monitoring.detail', compact(
            'karyawan', 'riwayat', 'rekap', 'kuotaCuti',
            'kalender', 'dari', 'sampai', 'statusFilter',
            'trackRecordBulanan'
        ));
    }

    public function perMitra(Request $request)
    {
        $bulan   = $request->integer('bulan', now()->month);
        $tahun   = $request->integer('tahun', now()->year);
        $mitraId = $request->input('mitra_id');

        $dari   = Carbon::create($tahun, $bulan)->startOfMonth();
        $sampai = Carbon::create($tahun, $bulan)->endOfMonth();
        $totalHariKerja = $this->hitungHariKerja($dari, $sampai);

        $semuaMitraInduk = Mitra::whereNull('mitra_induk_id')
            ->orWhere('is_cabang', false)
            ->orderBy('nama_mitra')
            ->get();

        $mitraQuery = $mitraId
            ? Mitra::where('id', $mitraId)->orWhere('mitra_induk_id', $mitraId)->get()
            : Mitra::all();

        $dataMitra = [];
        foreach ($mitraQuery as $m) {
            $absMitra = Absensi::where('mitra_id', $m->id)
                ->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
                ->get();
            $absMitra = \App\Helpers\AttendanceHelper::hanyaHariKerja($absMitra);

            $totalKary = \App\Models\DetailRiwayatPenempatan::where('mitra_id', $m->id)
                ->where('status', 'aktif')->count();
            $totalSlot = $totalKary * $totalHariKerja;
            $masuk     = $absMitra->whereIn('status', ['hadir', 'telat'])->count();
            $hadir     = $absMitra->whereIn('status', ['hadir', 'telat'])->where('is_telat', false)->count();
            $persen    = $totalSlot > 0 ? round(($masuk / $totalSlot) * 100) : 0;

            $dataMitra[] = [
                'mitra'          => $m,
                'total_karyawan' => $totalKary,
                'hadir'          => $hadir,
                'telat'          => $absMitra->where('is_telat', true)->count(),
                'alfa'           => $absMitra->where('status', 'alfa')->count(),
                'izin'           => $absMitra->whereIn('status', ['izin', 'sakit', 'cuti'])->count(),
                'persen'         => $persen,
            ];
        }

        $karyawanMitra = [];
        if ($mitraId) {
            $penempatan = \App\Models\DetailRiwayatPenempatan::with('karyawan')
                ->where('mitra_id', $mitraId)
                ->where('status', 'aktif')
                ->get();

            foreach ($penempatan as $p) {
                $k    = $p->karyawan;
                $absK = Absensi::where('user_id', $k->id)
                    ->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
                    ->get();
                $absK = \App\Helpers\AttendanceHelper::hanyaHariKerja($absK);

                $masuk  = $absK->whereIn('status', ['hadir', 'telat'])->count();
                $hadir  = $absK->whereIn('status', ['hadir', 'telat'])->where('is_telat', false)->count();
                $persen = $totalHariKerja > 0 ? round(($masuk / $totalHariKerja) * 100, 1) : 0;

                $karyawanMitra[] = [
                    'id'      => $k->id,
                    'nama'    => $k->nama,
                    'jabatan' => $k->jabatan,
                    'hadir'   => $hadir,
                    'telat'   => $absK->where('is_telat', true)->count(),
                    'alfa'    => $absK->where('status', 'alfa')->count(),
                    'izin'    => $absK->where('status', 'izin')->count(),
                    'sakit'   => $absK->where('status', 'sakit')->count(),
                    'cuti'    => $absK->where('status', 'cuti')->count(),
                    'dinas'   => $absK->where('status', 'dinas_luar')->count(),
                    'persen'  => $persen,
                ];
            }
        }

        $semuaMitra = Mitra::orderByRaw('COALESCE(mitra_induk_id, id), is_cabang ASC, nama_mitra ASC')->get();
        return view('pimpinan.monitoring.per-mitra', compact(
            'semuaMitraInduk', 'dataMitra', 'karyawanMitra',
            'mitraId', 'bulan', 'tahun', 'totalHariKerja',
            'semuaMitra',
        ));
    }

    public function export(Request $request)
    {
        $request->validate([
            'dari'   => 'required|date',
            'sampai' => 'required|date|after_or_equal:dari',
        ]);

        $dari          = Carbon::parse($request->input('dari'));
        $sampai        = Carbon::parse($request->input('sampai'));
        $mitraId       = $request->input('mitra_id');
        $divisi        = $request->input('divisi');
        $jenisKaryawan = $request->input('jenis_karyawan_id');
        $karyawanId    = $request->input('user_id');

        $query = Absensi::with(['karyawan', 'mitra'])
            ->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
            ->orderBy('tanggal', 'desc')->orderBy('user_id');

        if ($mitraId)    $query->where('mitra_id', $mitraId);
        if ($karyawanId) $query->where('user_id', $karyawanId);
        if ($divisi || $jenisKaryawan) {
            $query->whereHas('karyawan', function ($q) use ($divisi, $jenisKaryawan) {
                if ($divisi)        $q->where('divisi', $divisi);
                if ($jenisKaryawan) {
                    $slugTarget = in_array($jenisKaryawan, ['tetap', 'karyawan_tetap', 'JNS-00001']) ? 'karyawan_tetap' : 'karyawan_kontrak';
                    $q->whereHas('role', fn($r) => $r->where('slug', $slugTarget));
                }
            });
        }

        $absensiList    = $query->get();
        $totalHariKerja = $this->hitungHariKerja($dari, $sampai);

        if ($absensiList->isEmpty()) {
            return back()->with('error', 'Tidak ada data kehadiran untuk filter yang dipilih.');
        }

        $namaMitra = $mitraId ? (Mitra::find($mitraId)?->nama_mitra ?? 'Mitra') : 'Semua';
        $namaFile  = "Laporan_Kehadiran_{$dari->format('dmY')}_sd_{$sampai->format('dmY')}_{$namaMitra}.xlsx";

        $spreadsheet = new Spreadsheet();
        $sheet1 = $spreadsheet->getActiveSheet()->setTitle('Detail Harian');
        $sheet1->mergeCells('A1:L1');
        $sheet1->setCellValue('A1', "LAPORAN DETAIL KEHADIRAN  |  {$dari->format('d/m/Y')} s.d. {$sampai->format('d/m/Y')}  |  Mitra: {$namaMitra}");
        $sheet1->getStyle('A1')->applyFromArray($this->styleJudul());
        
        $h1 = ['No.','NIK','Nama','Jabatan','Mitra/Cabang','Tanggal','Jam Masuk','GPS Masuk','Jam Pulang','GPS Pulang','Status','Keterangan'];
        $this->tulisHeader($sheet1, 2, $h1);

        foreach ($absensiList as $i => $abs) {
            $rowNum  = $i + 3;
            $k       = $abs->karyawan;
            $gpsMsk  = ($abs->lat_masuk && $abs->long_masuk)  ? "{$abs->lat_masuk}, {$abs->long_masuk}"   : '-';
            $gpsPlg  = ($abs->lat_pulang && $abs->long_pulang) ? "{$abs->lat_pulang}, {$abs->long_pulang}" : '-';
            
            $data = [
                $i + 1, $k?->nip ?? '-', $k?->nama ?? '-', $k?->jabatan ?? '-',
                $abs->mitra?->nama_mitra ?? ($k?->isTetap() ? 'Kantor CBN' : '-'),
                $abs->tanggal?->format('d/m/Y'), $abs->waktu_masuk?->format('H:i'), $gpsMsk,
                $abs->waktu_pulang?->format('H:i'), $gpsPlg, $this->labelStatus($abs), ''
            ];
            foreach ($data as $col => $val) {
                $sheet1->setCellValue(Coordinate::stringFromColumnIndex($col + 1) . $rowNum, $val);
            }
        }

        // Sheet 2: Rekapitulasi & Evaluasi Perpanjangan Kontrak
        $sheet2 = $spreadsheet->createSheet()->setTitle('Rekap & Evaluasi Kontrak');
        $sheet2->mergeCells('A1:O1');
        $sheet2->setCellValue('A1', "REKAPITULASI KEHADIRAN & EVALUASI PERPANJANGAN KONTRAK  |  {$dari->format('d/m/Y')} s.d. {$sampai->format('d/m/Y')}");
        $sheet2->getStyle('A1')->applyFromArray($this->styleJudul());

        $h2 = ['No.', 'NIP', 'Nama Karyawan', 'Jabatan', 'Jenis Karyawan', 'Mitra / Cabang', 'Hari Kerja', 'Hadir', 'Telat', 'Alfa', 'Izin', 'Sakit', 'Cuti', 'Dinas', '% Kehadiran', 'Rekomendasi Evaluasi Kontrak'];
        $this->tulisHeader($sheet2, 2, $h2);

        $rekapGroup = $absensiList->groupBy('user_id');
        $idx = 1;
        foreach ($rekapGroup as $userId => $rows) {
            $rowNum = $idx + 2;
            $k      = $rows->first()->karyawan;
            $rows   = \App\Helpers\AttendanceHelper::hanyaHariKerja($rows);
            $masuk  = $rows->whereIn('status', ['hadir', 'telat'])->count();
            $hadir  = $rows->whereIn('status', ['hadir', 'telat'])->where('is_telat', false)->count();
            $persen = $totalHariKerja > 0 ? round(($masuk / $totalHariKerja) * 100, 1) : 0;
            $alfa   = $rows->where('status', 'alfa')->count();

            $evaluasiStatus = match(true) {
                $persen >= 90 && $alfa <= 2 => 'Sangat Layak Perpanjang Kontrak',
                $persen >= 80               => 'Layak Perpanjang Kontrak (Dengan Evaluasi)',
                default                     => 'Perlu Evaluasi Khusus Sebelum Perpanjangan'
            };

            $data2 = [
                $idx,
                $k?->nip ?? '-',
                $k?->nama ?? '-',
                $k?->jabatan ?? '-',
                $k?->isTetap() ? 'Tetap' : 'Kontrak',
                $rows->first()->mitra?->nama_mitra ?? ($k?->isTetap() ? 'Kantor CBN' : '-'),
                $totalHariKerja,
                $hadir,
                $rows->where('is_telat', true)->count(),
                $alfa,
                $rows->where('status', 'izin')->count(),
                $rows->where('status', 'sakit')->count(),
                $rows->where('status', 'cuti')->count(),
                $rows->where('status', 'dinas_luar')->count(),
                "{$persen}%",
                $evaluasiStatus,
            ];
            foreach ($data2 as $col => $val) {
                $sheet2->setCellValue(Coordinate::stringFromColumnIndex($col + 1) . $rowNum, $val);
            }
            $idx++;
        }

        $spreadsheet->setActiveSheetIndex(0);
        $tmpFile = tempnam(sys_get_temp_dir(), 'monitoring_');
        (new Xlsx($spreadsheet))->save($tmpFile);

        return response()->download($tmpFile, $namaFile)->deleteFileAfterSend(true);
    }

    private function hitungHariKerja(Carbon $dari, Carbon $sampai): int
    {
        $count = 0; $current = $dari->copy();
        while ($current->lte($sampai)) {
            if ($current->isWeekday()) $count++;
            $current->addDay();
        }
        return $count;
    }

    private function labelStatus(Absensi $abs): string
    {
        return match ($abs->status) {
            'hadir'      => $abs->is_telat ? 'Telat' : 'Tepat Waktu',
            'telat'      => 'Telat',
            'alfa'       => 'Alfa',
            'izin'       => 'Izin Pribadi',
            'sakit'      => 'Sakit',
            'cuti'       => 'Cuti',
            'dinas_luar' => 'Dinas Luar Kota',
            default      => ucfirst($abs->status),
        };
    }

    private function warnaBadgeStatus(Absensi $abs): string
    {
        return match ($abs->status) {
            'hadir'      => $abs->is_telat ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700',
            'alfa'       => 'bg-red-100 text-red-700',
            'izin'       => 'bg-purple-100 text-purple-700',
            'sakit'      => 'bg-blue-100 text-blue-700',
            'cuti'       => 'bg-sky-100 text-sky-700',
            'dinas_luar' => 'bg-indigo-100 text-indigo-700',
            default      => 'bg-slate-100 text-slate-600',
        };
    }

    private function styleJudul(): array
    {
        return [
            'font'      => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F3864']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
    }

    private function tulisHeader($sheet, int $row, array $headers): void
    {
        foreach ($headers as $col => $header) {
            $cell = Coordinate::stringFromColumnIndex($col + 1) . $row;
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->applyFromArray([
                'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2E75B6']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }
    }

    private function getTrackRecordBulanan(User $karyawan, int $jumlahBulan = 6): array
    {
        $records = [];
        $totalHadir = 0;
        $totalAlfa = 0;
        $totalTelat = 0;
        $totalHariKerjaAll = 0;

        for ($i = 0; $i < $jumlahBulan; $i++) {
            $date = Carbon::now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth   = ($i === 0) ? Carbon::now()->endOfDay() : $date->copy()->endOfMonth();

            $hariKerja = \App\Helpers\AttendanceHelper::countWorkingDays($startOfMonth, $endOfMonth);

            $absensiBulan = Absensi::where('user_id', $karyawan->id)
                ->whereBetween('tanggal', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                ->get();
            $absensiBulan = \App\Helpers\AttendanceHelper::hanyaHariKerja($absensiBulan);

            $masuk = $absensiBulan->whereIn('status', ['hadir', 'telat'])->count();
            $hadir = $absensiBulan->whereIn('status', ['hadir', 'telat'])->where('is_telat', false)->count();
            $telat = $absensiBulan->where('is_telat', true)->count();
            $alfa  = $absensiBulan->where('status', 'alfa')->count();
            $izin  = $absensiBulan->where('status', 'izin')->count();
            $sakit = $absensiBulan->where('status', 'sakit')->count();
            $cuti  = $absensiBulan->where('status', 'cuti')->count();
            $dinas = $absensiBulan->where('status', 'dinas_luar')->count();

            $persen = $hariKerja > 0 ? round(($masuk / $hariKerja) * 100, 1) : 0;

            $statusEvaluasi = match(true) {
                $persen >= 90 => 'Sangat Layak Perpanjang',
                $persen >= 80 => 'Layak Perpanjang',
                default       => 'Perlu Evaluasi'
            };

            $statusBadge = match(true) {
                $persen >= 90 => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                $persen >= 80 => 'bg-blue-100 text-blue-800 border border-blue-200',
                default       => 'bg-rose-100 text-rose-800 border border-rose-200'
            };

            $records[] = [
                'bulan_label'     => $date->translatedFormat('F Y'),
                'bulan_num'       => $date->month,
                'tahun'           => $date->year,
                'hari_kerja'      => $hariKerja,
                'hadir'           => $hadir,
                'telat'           => $telat,
                'alfa'            => $alfa,
                'izin'            => $izin,
                'sakit'           => $sakit,
                'cuti'            => $cuti,
                'dinas'           => $dinas,
                'persen'          => $persen,
                'status_evaluasi' => $statusEvaluasi,
                'status_badge'    => $statusBadge,
            ];

            $totalHadir += $hadir;
            $totalMasuk  = ($totalMasuk ?? 0) + $masuk;
            $totalAlfa  += $alfa;
            $totalTelat += $telat;
            $totalHariKerjaAll += $hariKerja;
        }

        $avgPersen = $totalHariKerjaAll > 0 ? round((($totalMasuk ?? 0) / $totalHariKerjaAll) * 100, 1) : 0;

        $rekomendasiTotal = match(true) {
            $avgPersen >= 90 && $totalAlfa <= 2 => 'Sangat Layak Perpanjang Kontrak',
            $avgPersen >= 80                    => 'Layak Perpanjang Kontrak (Dengan Evaluasi)',
            default                             => 'Perlu Evaluasi Khusus Sebelum Perpanjangan'
        };

        $badgeTotal = match(true) {
            $avgPersen >= 90 && $totalAlfa <= 2 => 'bg-emerald-600 text-white shadow-lg shadow-emerald-200/50',
            $avgPersen >= 80                    => 'bg-blue-600 text-white shadow-lg shadow-blue-200/50',
            default                             => 'bg-rose-600 text-white shadow-lg shadow-rose-200/50'
        };

        return [
            'list'              => $records,
            'avg_persen'        => $avgPersen,
            'total_alfa'        => $totalAlfa,
            'total_telat'       => $totalTelat,
            'total_hadir'       => $totalHadir,
            'total_hari_kerja'  => $totalHariKerjaAll,
            'rekomendasi_total' => $rekomendasiTotal,
            'badge_total'       => $badgeTotal,
        ];
    }
}
