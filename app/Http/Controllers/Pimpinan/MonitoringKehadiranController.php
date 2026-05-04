<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Karyawan;
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
        $today         = Carbon::today();
        $mitraId       = $request->input('mitra_id');
        $statusFilter  = $request->input('status');
        $tab           = $request->input('tab', 'tetap');

        // 1. Hitung Statistik Keseluruhan (untuk Pie Chart Global jika ada)
        $semuaAbsensiHariIni = Absensi::with('karyawan')->whereDate('tanggal', $today)->get();
        $semuaKaryawanAktif = Karyawan::where('is_active', true)->get();

        // 2. Hitung Statistik Per Jenis (Tetap & Kontrak) - DIBUTUHKAN VIEW
        $stats = [];
        $pieDataTetap = [];
        $pieDataKontrak = [];

        foreach (['tetap', 'kontrak'] as $jenis) {
            $totalJ = $semuaKaryawanAktif->where('jenis_karyawan', $jenis)->count();
            $absJ   = $semuaAbsensiHariIni->filter(fn($a) => ($a->karyawan?->jenis_karyawan ?? '') === $jenis);

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

        // 3. Tentukan Data yang Aktif (Berdasarkan TAB)
        if ($tab === 'semua') {
            $totalKaryawan = $semuaKaryawanAktif->count();
            $absensiAktif  = $semuaAbsensiHariIni;
        } else {
            $totalKaryawan = $stats[$tab]['total'] ?? 0;
            $absensiAktif  = $semuaAbsensiHariIni->filter(fn($a) => ($a->karyawan?->jenis_karyawan ?? '') === $tab);
        }

        $hadirCount  = $absensiAktif->whereIn('status', ['hadir', 'telat'])->count();
        $telatCount  = $absensiAktif->where('is_telat', true)->count();
        $izinCount   = $absensiAktif->whereIn('status', ['izin', 'sakit', 'cuti', 'dinas_luar'])->count();
        $alfaCount   = $absensiAktif->where('status', 'alfa')->count();
        $belumHadir  = max(0, $totalKaryawan - $hadirCount - $izinCount - $alfaCount);
        $persenHadir = $totalKaryawan > 0 ? round(($hadirCount / $totalKaryawan) * 100) : 0;

        $pieData = [
            'tepat_waktu' => $hadirCount - $telatCount,
            'telat'       => $telatCount,
            'izin'        => $izinCount,
            'alfa'        => $alfaCount,
            'belum'       => $belumHadir,
        ];

        // 4. Query untuk TABEL (Filter Mitra & Status)
        $queryTabel = Absensi::with(['karyawan.penempatanAktif.mitra', 'mitra'])
            ->whereDate('tanggal', $today);

        if ($tab !== 'semua') {
            $queryTabel->whereHas('karyawan', fn($q) => $q->where('jenis_karyawan', $tab));
        }

        if ($mitraId) $queryTabel->where('mitra_id', $mitraId);

        if ($statusFilter) {
            if ($statusFilter === 'telat') $queryTabel->where('is_telat', true);
            else $queryTabel->where('status', $statusFilter);
        }

        $absensiHariIni = $queryTabel->latest('waktu_masuk')->get();
        $semuaMitra     = Mitra::orderBy('nama_mitra')->get();

        return view('Pimpinan.Monitoring.index', compact(
            'today', 'totalKaryawan', 'hadirCount', 'telatCount',
            'izinCount', 'alfaCount', 'belumHadir', 'persenHadir',
            'pieData', 'absensiHariIni', 'semuaMitra',
            'mitraId', 'statusFilter', 'tab',
            'stats', 'pieDataTetap', 'pieDataKontrak'
        ));
    }

    public function statistik(Request $request)
    {
        $dari          = $request->input('dari')   ? Carbon::parse($request->input('dari'))   : Carbon::now()->startOfMonth();
        $sampai        = $request->input('sampai') ? Carbon::parse($request->input('sampai')) : Carbon::now()->endOfDay();
        $mitraId       = $request->input('mitra_id');
        $divisi        = $request->input('divisi');
        $jenisKaryawan = $request->input('jenis_karyawan');
        $karyawanId    = $request->input('karyawan_id');

        $query = Absensi::with(['karyawan', 'mitra'])
            ->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
            ->orderBy('tanggal');

        if ($mitraId)    $query->where('mitra_id', $mitraId);
        if ($karyawanId) $query->where('karyawan_id', $karyawanId);
        if ($divisi || $jenisKaryawan) {
            $query->whereHas('karyawan', function ($q) use ($divisi, $jenisKaryawan) {
                if ($divisi)        $q->where('divisi', $divisi);
                if ($jenisKaryawan) $q->where('jenis_karyawan', $jenisKaryawan);
            });
        }

        $absensiList    = $query->get();
        $totalHariKerja = $this->hitungHariKerja($dari, $sampai);

        $trenPerHari = $absensiList
            ->groupBy(fn($a) => $a->tanggal->format('d/m'))
            ->map(fn($rows) => $rows->whereNotIn('status', ['hadir', 'telat'])->count());

        $top10 = $absensiList
            ->groupBy('karyawan_id')
            ->map(fn($rows) => [
                'nama'  => $rows->first()->karyawan?->nama ?? '-',
                'total' => $rows->where('is_telat', true)->count()
                         + $rows->where('status', 'alfa')->count(),
            ])
            ->sortByDesc('total')
            ->take(10)
            ->values();

        $rekapTabel = $absensiList->groupBy('karyawan_id')->map(function ($rows) use ($totalHariKerja) {
            $k      = $rows->first()->karyawan;
            $hadir  = $rows->whereIn('status', ['hadir', 'telat'])->count();
            $persen = $totalHariKerja > 0 ? round(($hadir / $totalHariKerja) * 100, 1) : 0;

            return [
                'id'      => $k?->id,
                'nama'    => $k?->nama ?? '-',
                'jabatan' => $k?->jabatan ?? '-',
                'mitra'   => $rows->first()->mitra?->nama_mitra ?? ($k?->isTetap() ? 'Kantor CBN' : '-'),
                'hadir'   => $hadir,
                'telat'   => $rows->where('is_telat', true)->count(),
                'alfa'    => $rows->where('status', 'alfa')->count(),
                'izin'    => $rows->where('status', 'izin')->count(),
                'sakit'   => $rows->where('status', 'sakit')->count(),
                'cuti'    => $rows->where('status', 'cuti')->count(),
                'dinas'   => $rows->where('status', 'dinas_luar')->count(),
                'persen'  => $persen,
            ];
        })->values();

        $semuaMitra    = Mitra::orderBy('nama_mitra')->get();
        $semuaKaryawan = Karyawan::where('is_active', true)->orderBy('nama')->get();

        return view('Pimpinan.Monitoring.statistik', compact(
            'dari', 'sampai', 'trenPerHari', 'top10', 'rekapTabel',
            'totalHariKerja', 'semuaMitra', 'semuaKaryawan',
            'mitraId', 'divisi', 'jenisKaryawan', 'karyawanId',
        ));
    }

    public function detail(Request $request, Karyawan $karyawan)
    {
        $dari   = $request->input('dari')
            ? Carbon::parse($request->input('dari'))->startOfDay()
            : Carbon::now()->startOfMonth();
        $sampai = $request->input('sampai')
            ? Carbon::parse($request->input('sampai'))->endOfDay()
            : Carbon::now()->endOfDay();
        $statusFilter = $request->input('status');

        $query = Absensi::with('mitra')
            ->where('karyawan_id', $karyawan->id)
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
            'hadir' => $riwayat->whereIn('status', ['hadir', 'telat'])->count(),
            'telat' => $riwayat->where('is_telat', true)->count(),
            'alfa'  => $riwayat->where('status', 'alfa')->count(),
            'izin'  => $riwayat->where('status', 'izin')->count(),
            'sakit' => $riwayat->where('status', 'sakit')->count(),
            'cuti'  => $riwayat->where('status', 'cuti')->count(),
            'dinas' => $riwayat->where('status', 'dinas_luar')->count(),
        ];

        $kuotaCuti  = $karyawan->kuotaCutiTahunIni();
        $kalender = $riwayat->keyBy(fn($a) => $a->tanggal->format('Y-m-d'))
            ->map(fn($a) => $this->warnaBadgeStatus($a));

        return view('Pimpinan.Monitoring.detail', compact(
            'karyawan', 'riwayat', 'rekap', 'kuotaCuti',
            'kalender', 'dari', 'sampai', 'statusFilter',
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

            $totalKary = \App\Models\Penempatan::where('mitra_id', $m->id)
                ->where('status', 'aktif')->count();
            $totalSlot = $totalKary * $totalHariKerja;
            $hadir     = $absMitra->whereIn('status', ['hadir', 'telat'])->count();
            $persen    = $totalSlot > 0 ? round(($hadir / $totalSlot) * 100) : 0;

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
            $penempatan = \App\Models\Penempatan::with('karyawan')
                ->where('mitra_id', $mitraId)
                ->where('status', 'aktif')
                ->get();

            foreach ($penempatan as $p) {
                $k    = $p->karyawan;
                $absK = Absensi::where('karyawan_id', $k->id)
                    ->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
                    ->get();

                $hadir  = $absK->whereIn('status', ['hadir', 'telat'])->count();
                $persen = $totalHariKerja > 0 ? round(($hadir / $totalHariKerja) * 100, 1) : 0;

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

        $semuaMitra = Mitra::orderBy('nama_mitra')->get();
        return view('Pimpinan.Monitoring.per-mitra', compact(
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
        $jenisKaryawan = $request->input('jenis_karyawan');
        $karyawanId    = $request->input('karyawan_id');

        $query = Absensi::with(['karyawan.user', 'mitra'])
            ->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
            ->orderBy('tanggal')->orderBy('karyawan_id');

        if ($mitraId)    $query->where('mitra_id', $mitraId);
        if ($karyawanId) $query->where('karyawan_id', $karyawanId);
        if ($divisi || $jenisKaryawan) {
            $query->whereHas('karyawan', function ($q) use ($divisi, $jenisKaryawan) {
                if ($divisi)        $q->where('divisi', $divisi);
                if ($jenisKaryawan) $q->where('jenis_karyawan', $jenisKaryawan);
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
                $i + 1, $k?->user?->username ?? '-', $k?->nama ?? '-', $k?->jabatan ?? '-',
                $abs->mitra?->nama_mitra ?? ($k?->isTetap() ? 'Kantor CBN' : '-'),
                $abs->tanggal?->format('d/m/Y'), $abs->waktu_masuk?->format('H:i'), $gpsMsk,
                $abs->waktu_pulang?->format('H:i'), $gpsPlg, $this->labelStatus($abs), ''
            ];
            foreach ($data as $col => $val) {
                $sheet1->setCellValue(Coordinate::stringFromColumnIndex($col + 1) . $rowNum, $val);
            }
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
}