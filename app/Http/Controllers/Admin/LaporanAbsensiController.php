<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\User;
use App\Models\Mitra;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LaporanAbsensiController extends Controller
{
    /**
     * Tampilkan halaman laporan absensi dengan filter.
     */
    public function index(Request $request)
    {
        $bulan         = $request->integer('bulan', now()->month);
        $tahun         = $request->integer('tahun', now()->year);
        $mitraId       = $request->input('mitra_id');
        $divisi        = $request->input('divisi');
        $jenisKaryawan = $request->input('jenis_karyawan_id', 'tetap');
        $karyawanId    = $request->input('user_id');

        // Hitung default minggu berdasarkan hari ini (misal tgl 17 = Minggu 3)
        $defaultWeek = 1;
        if ($bulan == now()->month && $tahun == now()->year) {
            $currentDay = now()->day;
            if ($currentDay <= 7) $defaultWeek = 1;
            elseif ($currentDay <= 14) $defaultWeek = 2;
            elseif ($currentDay <= 21) $defaultWeek = 3;
            else $defaultWeek = 4;
        }

        $roleSlug = in_array($jenisKaryawan, ['tetap', 'karyawan_tetap', 'JNS-00001']) ? 'karyawan_tetap' : 'karyawan_kontrak';

        // Data untuk dropdown filter
        $semuaMitra    = Mitra::orderByRaw('COALESCE(mitra_induk_id, id), is_cabang ASC, nama_mitra ASC')->get();
        $semuaKaryawan = User::with(['penempatanAktif.mitra'])
                             ->where('is_active', true)
                             ->whereHas('role', fn($r) => $r->where('slug', $roleSlug))
                             ->orderBy('nama')
                             ->get();
        $mitraPusat    = Mitra::where('is_pusat', true)->first();

        $isTetap = ($roleSlug === 'karyawan_tetap');

        // Divisi khusus Karyawan Tetap vs Karyawan Kontrak
        if ($isTetap) {
            $semuaDivisi = [
                'adm_umum'       => 'Adm & Umum',
                'keuangan'       => 'Keuangan',
                'koordinator_cs' => 'Koordinator CS',
            ];
        } else {
            $semuaDivisi = [
                'hc'   => 'HC',
                'umum' => 'Umum',
            ];
        }

        // Query absensi utama bulanan
        $query = Absensi::with(['karyawan', 'karyawan.penempatanAktif.mitra', 'mitra'])
            ->whereHas('karyawan', fn($q) => $q->whereHas('role', fn($r) => $r->where('slug', $roleSlug)))
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal')
            ->orderBy('user_id');

        if ($mitraId) {
            $query->where('mitra_id', $mitraId);
        }
        if ($karyawanId) {
            $query->where('user_id', $karyawanId);
        }
        if ($divisi) {
            $query->whereHas('karyawan', function ($q) use ($divisi) {
                $q->where(function($sub) use ($divisi) {
                    $sub->where('divisi', $divisi)
                        ->orWhere('divisi', str_replace('_', ' ', $divisi))
                        ->orWhere('divisi', strtolower(str_replace(' ', '_', $divisi)));
                });
            });
        }

        $absensiList = $query->get();

        // Hitung rekap bulanan utuh
        $rekap = $this->hitungRekap($absensiList, $bulan, $tahun);

        // Total hari kerja dalam bulan tsb (Senin–Jumat)
        $totalHariKerja = $this->hitungHariKerja($bulan, $tahun);

        return view('admin.laporan.absensi', compact(
            'absensiList',
            'rekap',
            'semuaMitra',
            'semuaKaryawan',
            'semuaDivisi',
            'mitraPusat',
            'totalHariKerja',
            'bulan',
            'tahun',
            'defaultWeek',
            'mitraId',
            'divisi',
            'jenisKaryawan',
            'karyawanId'
        ));
    }

    /**
     * Export laporan absensi ke Excel dengan 2 Sheet (Sheet 1: Karyawan Tetap, Sheet 2: Karyawan Kontrak).
     */
    public function export(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:2099',
        ]);

        $bulan      = (int) $request->input('bulan');
        $tahun      = (int) $request->input('tahun');
        $mitraId    = $request->input('mitra_id');
        $divisi     = $request->input('divisi');
        $karyawanId = $request->input('user_id');

        $totalHariKerja = $this->hitungHariKerja($bulan, $tahun);

        Carbon::setLocale('id');
        $namaBulan = Carbon::create($tahun, $bulan)->translatedFormat('F');
        $namaFile  = "Laporan_Absensi_CBN_{$namaBulan}_{$tahun}.xlsx";

        // Query Karyawan Tetap
        $queryTetap = Absensi::with(['karyawan', 'mitra'])
            ->whereHas('karyawan', fn($q) => $q->whereHas('role', fn($r) => $r->where('slug', 'karyawan_tetap')))
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal')
            ->orderBy('user_id');
        if ($karyawanId) $queryTetap->where('user_id', $karyawanId);
        if ($divisi)     $queryTetap->whereHas('karyawan', fn($q) => $q->where('divisi', $divisi));
        $absensiTetap = $queryTetap->get();
        $rekapTetap   = $this->hitungRekap($absensiTetap, $bulan, $tahun);

        // Query Karyawan Kontrak
        $queryKontrak = Absensi::with(['karyawan', 'karyawan.penempatanAktif.mitra', 'mitra'])
            ->whereHas('karyawan', fn($q) => $q->whereHas('role', fn($r) => $r->where('slug', 'karyawan_kontrak')))
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal')
            ->orderBy('user_id');
        if ($mitraId)    $queryKontrak->where('mitra_id', $mitraId);
        if ($karyawanId) $queryKontrak->where('user_id', $karyawanId);
        if ($divisi)     $queryKontrak->whereHas('karyawan', fn($q) => $q->where('divisi', $divisi));
        $absensiKontrak = $queryKontrak->get();
        $rekapKontrak   = $this->hitungRekap($absensiKontrak, $bulan, $tahun);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // ── Sheet 1: Karyawan Tetap ───────────────────────────────────────
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Karyawan Tetap');
        $this->isiSheetAbsensi($sheet1, 'KARYAWAN TETAP', $namaBulan, $tahun, $rekapTetap, $absensiTetap, $totalHariKerja, false);

        // ── Sheet 2: Karyawan Kontrak ─────────────────────────────────────
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Karyawan Kontrak');
        $this->isiSheetAbsensi($sheet2, 'KARYAWAN KONTRAK', $namaBulan, $tahun, $rekapKontrak, $absensiKontrak, $totalHariKerja, true);

        // Kembali ke Sheet 1
        $spreadsheet->setActiveSheetIndex(0);

        // Clean output buffer & download
        if (ob_get_length()) ob_end_clean();

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $namaFile, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $namaFile . '"',
            'Cache-Control'       => 'max-age=0, no-store, no-cache, must-revalidate',
            'Pragma'              => 'public',
        ]);
    }

    /**
     * Helper privat untuk mengisi struktur sheet Excel absensi per kategori karyawan.
     */
    private function isiSheetAbsensi($sheet, string $titleType, string $namaBulan, int $tahun, array $rekap, $absensiList, int $totalHariKerja, bool $isKontrak)
    {
        // Banner Header Sheet
        $sheet->setCellValue('A1', "LAPORAN ABSENSI {$titleType} — PT CBN");
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E3A5F'));

        $sheet->setCellValue('A2', "PERIODE: " . strtoupper($namaBulan) . " {$tahun} | TOTAL HARI KERJA: {$totalHariKerja} HARI");
        $sheet->mergeCells('A2:L2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));

        // Bagian I: Rekapitulasi
        $sheet->setCellValue('A4', 'I. REKAPITULASI KEHADIRAN BULANAN');
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(11);

        $headersRekap = [
            'No.', 'NIK', 'Nama Karyawan', 'Jabatan', 'Divisi',
            $isKontrak ? 'Mitra / Cabang' : 'Lokasi',
            'Hadir', 'Telat', 'Alfa', 'Izin', 'Sakit', 'Cuti', 'Dinas', '% Kehadiran'
        ];

        $startRowRekap = 5;
        foreach ($headersRekap as $col => $header) {
            $cellAddr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . $startRowRekap;
            $sheet->setCellValue($cellAddr, $header);
            $sheet->getStyle($cellAddr)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle($cellAddr)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF1E3A5F');
            $sheet->getStyle($cellAddr)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        $rowR = $startRowRekap + 1;
        if (count($rekap) > 0) {
            foreach ($rekap as $i => $r) {
                $persen = $totalHariKerja > 0 ? round(($r['hadir'] / $totalHariKerja) * 100, 1) : 0;
                $rowData = [
                    $i + 1,
                    $r['nik'],
                    $r['nama'],
                    $r['jabatan'],
                    $r['divisi'],
                    $r['mitra'],
                    $r['hadir'],
                    $r['telat'],
                    $r['alfa'],
                    $r['izin'],
                    $r['sakit'],
                    $r['cuti'],
                    $r['dinas'],
                    $persen . '%',
                ];

                foreach ($rowData as $col => $value) {
                    $cellAddr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . $rowR;
                    $sheet->setCellValue($cellAddr, $value);

                    if ($rowR % 2 === 0) {
                        $sheet->getStyle($cellAddr)->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFF8FAFC');
                    }

                    if ($persen < 80) {
                        $sheet->getStyle($cellAddr)->getFont()->getColor()->setARGB('FFDC2626');
                    }
                }
                $rowR++;
            }
        } else {
            $sheet->setCellValue("A{$rowR}", 'Tidak ada data rekap');
            $rowR++;
        }

        // Bagian II: Detail Absensi Harian
        $rowR += 2;
        $sheet->setCellValue("A{$rowR}", 'II. DETAIL ABSENSI HARIAN');
        $sheet->getStyle("A{$rowR}")->getFont()->setBold(true)->setSize(11);
        $rowR++;

        $headersDetail = [
            'No.', 'NIK', 'Nama Karyawan', 'Jabatan', 'Divisi',
            $isKontrak ? 'Mitra / Cabang' : 'Lokasi',
            'Tanggal', 'Jam Masuk', 'Jam Pulang', 'Status Kehadiran', 'Keterangan'
        ];

        $startRowDetail = $rowR;
        foreach ($headersDetail as $col => $header) {
            $cellAddr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . $startRowDetail;
            $sheet->setCellValue($cellAddr, $header);
            $sheet->getStyle($cellAddr)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle($cellAddr)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF2563EB');
            $sheet->getStyle($cellAddr)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        $rowD = $startRowDetail + 1;
        if (count($absensiList) > 0) {
            foreach ($absensiList as $i => $abs) {
                $k          = $abs->karyawan;
                $namaLokasi = $abs->mitra?->nama_mitra ?? ($k?->isTetap() ? 'Kantor CBN' : '-');

                $statusLabel = match ($abs->status) {
                    'hadir'      => $abs->is_telat ? 'Telat' : 'Tepat Waktu',
                    'telat'      => 'Telat',
                    'alfa'       => 'Alfa',
                    'izin'       => 'Izin Pribadi',
                    'sakit'      => 'Sakit',
                    'cuti'       => 'Cuti',
                    'dinas_luar' => 'Dinas Luar Kota',
                    default      => ucfirst($abs->status),
                };

                $isHadir = in_array($abs->status, ['hadir', 'telat']);

                $rowData = [
                    $i + 1,
                    $k?->nip ?? '-',
                    $k?->nama ?? '-',
                    $k?->jabatan ?? '-',
                    $k?->labelDivisi() ?? '-',
                    $namaLokasi,
                    $abs->tanggal?->format('d/m/Y') ?? '-',
                    $isHadir ? ($abs->waktu_masuk?->format('H:i') ?? '-') : '-',
                    $isHadir ? ($abs->waktu_pulang?->format('H:i') ?? 'Belum Pulang') : '-',
                    $statusLabel,
                    $abs->keterangan ?? '',
                ];

                foreach ($rowData as $col => $value) {
                    $cellAddr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . $rowD;
                    $sheet->setCellValue($cellAddr, $value);

                    if ($rowD % 2 === 0) {
                        $sheet->getStyle($cellAddr)->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFF8FAFC');
                    }
                }
                $rowD++;
            }
        } else {
            $sheet->setCellValue("A{$rowD}", 'Tidak ada data detail absensi');
        }

        // Auto width untuk seluruh kolom
        foreach (range(1, max(count($headersRekap), count($headersDetail))) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }
    }

    // ── Helper ──────────────────────────────────────────────────────────

    /**
     * Hitung rekap per karyawan dari koleksi absensi.
     */
    private function hitungRekap($absensiList, int $bulan, int $tahun): array
    {
        $grouped = $absensiList->groupBy('user_id');
        $rekap   = [];

        foreach ($grouped as $karyawanId => $rows) {
            $k               = $rows->first()->karyawan;
            $aktifPenempatan = $k?->penempatanAktif?->mitra?->nama_mitra ?? ($k?->isTetap() ? 'Kantor CBN' : '-');

            $rekap[] = [
                'user_id' => $karyawanId,
                'nik'         => $k?->nip ?? '-',
                'nama'        => $k?->nama ?? '-',
                'jabatan'     => $k?->jabatan ?? '-',
                'divisi'      => $k?->labelDivisi() ?? '-',
                'mitra'       => $aktifPenempatan,
                'hadir'       => $rows->whereIn('status', ['hadir', 'telat'])->count(),
                'telat'       => $rows->where('is_telat', true)->count(),
                'alfa'        => $rows->where('status', 'alfa')->count(),
                'izin'        => $rows->where('status', 'izin')->count(),
                'sakit'       => $rows->where('status', 'sakit')->count(),
                'cuti'        => $rows->where('status', 'cuti')->count(),
                'dinas'       => $rows->where('status', 'dinas_luar')->count(),
            ];
        }

        return $rekap;
    }

    /**
     * Hitung jumlah hari kerja (Senin–Jumat) dalam bulan & tahun tertentu.
     */
    private function hitungHariKerja(int $bulan, int $tahun): int
    {
        $start   = Carbon::create($tahun, $bulan, 1);
        $end     = $start->copy()->endOfMonth();

        // Jika periode adalah bulan berjalan, batasi perhitungan hari kerja sampai hari ini
        if ($bulan === now()->month && $tahun === now()->year) {
            $end = now();
        }

        return \App\Helpers\AttendanceHelper::countWorkingDays($start, $end);
    }

    public function storeManual(Request $request)
    {
        $request->validate([
            'user_id'      => 'required|exists:users,id',
            'tanggal'      => 'required|date',
            'status'       => 'required|in:hadir,telat,alfa',
            'mitra_id'     => 'required|exists:mitra,id',
            'waktu_masuk'  => 'nullable|string',
            'waktu_pulang' => 'nullable|string',
        ]);

        $tanggal = Carbon::parse($request->tanggal);
        
        $exists = Absensi::where('user_id', $request->user_id)
                         ->whereDate('tanggal', $tanggal->toDateString())
                         ->first();

        if ($exists) {
            return back()->with('error', 'Data absensi karyawan pada tanggal tersebut sudah ada. Silakan edit data yang sudah ada.');
        }

        $waktuMasuk = null;
        if ($request->filled('waktu_masuk')) {
            $waktuMasuk = Carbon::parse($request->tanggal . ' ' . $request->waktu_masuk);
        }

        $waktuPulang = null;
        if ($request->filled('waktu_pulang')) {
            $waktuPulang = Carbon::parse($request->tanggal . ' ' . $request->waktu_pulang);
        }

        Absensi::create([
            'user_id'      => $request->user_id,
            'tanggal'      => $tanggal->toDateString(),
            'status'       => $request->status,
            'mitra_id'     => $request->mitra_id,
            'waktu_masuk'  => $waktuMasuk,
            'waktu_pulang' => $waktuPulang,
            'is_telat'     => $request->status === 'telat',
        ]);

        return back()->with('success', 'Absensi manual berhasil ditambahkan.');
    }

    public function updateManual(Request $request, Absensi $absensi)
    {
        $request->validate([
            'status'       => 'required|in:hadir,telat,alfa,izin,sakit,cuti,dinas_luar',
            'mitra_id'     => 'required|exists:mitra,id',
            'waktu_masuk'  => 'nullable|string',
            'waktu_pulang' => 'nullable|string',
        ]);

        $waktuMasuk = null;
        if ($request->filled('waktu_masuk')) {
            $waktuMasuk = Carbon::parse($absensi->tanggal->toDateString() . ' ' . $request->waktu_masuk);
        }

        $waktuPulang = null;
        if ($request->filled('waktu_pulang')) {
            $waktuPulang = Carbon::parse($absensi->tanggal->toDateString() . ' ' . $request->waktu_pulang);
        }

        $absensi->update([
            'status'       => $request->status,
            'mitra_id'     => $request->mitra_id,
            'waktu_masuk'  => $waktuMasuk,
            'waktu_pulang' => $waktuPulang,
            'is_telat'     => $request->status === 'telat',
        ]);

        return back()->with('success', 'Absensi berhasil diperbarui.');
    }
}
