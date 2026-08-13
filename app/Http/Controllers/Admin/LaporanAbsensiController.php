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

        // Data untuk dropdown filter
        $semuaMitra    = Mitra::orderByRaw('COALESCE(mitra_induk_id, id), is_cabang ASC, nama_mitra ASC')->get();
        $semuaKaryawan = User::with(['penempatanAktif.mitra'])
                             ->where('is_active', true)
                             ->whereHas('role', fn($q) => $q->whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak']))
                             ->when($jenisKaryawan, function($q) use ($jenisKaryawan) {
                                 $roleSlug = in_array($jenisKaryawan, ['tetap', 'karyawan_tetap', 'JNS-00001']) ? 'karyawan_tetap' : 'karyawan_kontrak';
                                 $q->whereHas('role', fn($r) => $r->where('slug', $roleSlug));
                             })
                             ->orderBy('nama')
                             ->get();
        $mitraPusat    = Mitra::where('is_pusat', true)->first();

        // Bangun query absensi
        $query = Absensi::with(['karyawan', 'karyawan.penempatanAktif.mitra', 'mitra'])
            ->whereHas('karyawan', fn($q) => $q->whereHas('role', fn($r) => $r->whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak'])))
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
        if ($divisi || $jenisKaryawan) {
            $query->whereHas('karyawan', function ($q) use ($divisi, $jenisKaryawan) {
                if ($divisi) {
                    $q->where('divisi', $divisi);
                }
                if ($jenisKaryawan) {
                    $roleSlug = in_array($jenisKaryawan, ['tetap', 'karyawan_tetap', 'JNS-00001']) ? 'karyawan_tetap' : 'karyawan_kontrak';
                    $q->whereHas('role', fn($r) => $r->where('slug', $roleSlug));
                }
            });
        }

        $absensiList = $query->get();

        // Hitung rekap per karyawan
        $rekap = $this->hitungRekap($absensiList, $bulan, $tahun);

        // Total hari kerja dalam bulan tsb (Senin–Jumat)
        $totalHariKerja = $this->hitungHariKerja($bulan, $tahun);

        return view('Admin.Laporan.absensi', compact(
            'absensiList',
            'rekap',
            'semuaMitra',
            'semuaKaryawan',
            'mitraPusat',
            'totalHariKerja',
            'bulan',
            'tahun',
            'mitraId',
            'divisi',
            'jenisKaryawan',
            'karyawanId'
        ));
    }

    /**
     * Export laporan absensi ke Excel (menggunakan PhpSpreadsheet).
     */
    public function export(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:2099',
        ]);

        $bulan         = (int) $request->input('bulan');
        $tahun         = (int) $request->input('tahun');
        $mitraId       = $request->input('mitra_id');
        $divisi        = $request->input('divisi');
        $jenisKaryawan = $request->input('jenis_karyawan_id');
        $karyawanId    = $request->input('user_id');

        $query = Absensi::with(['karyawan', 'karyawan.penempatanAktif.mitra', 'mitra'])
            ->whereHas('karyawan', fn($q) => $q->whereHas('role', fn($r) => $r->whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak'])))
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal')
            ->orderBy('user_id');

        if ($mitraId)    $query->where('mitra_id', $mitraId);
        if ($karyawanId) $query->where('user_id', $karyawanId);
        if ($divisi || $jenisKaryawan) {
            $query->whereHas('karyawan', function ($q) use ($divisi, $jenisKaryawan) {
                if ($divisi)        $q->where('divisi', $divisi);
                if ($jenisKaryawan) {
                    $roleSlug = in_array($jenisKaryawan, ['tetap', 'karyawan_tetap', 'JNS-00001']) ? 'karyawan_tetap' : 'karyawan_kontrak';
                    $q->whereHas('role', fn($r) => $r->where('slug', $roleSlug));
                }
            });
        }

        $absensiList    = $query->get();
        $rekap          = $this->hitungRekap($absensiList, $bulan, $tahun);
        $totalHariKerja = $this->hitungHariKerja($bulan, $tahun);

        // Nama file
        Carbon::setLocale('id');
        $namaBulan = Carbon::create($tahun, $bulan)->translatedFormat('F');
        $namaMitra = $mitraId ? (Mitra::find($mitraId)?->nama_mitra ?? 'Mitra') : 'Semua';
        $namaFile  = "Laporan_Absensi_{$namaBulan}{$tahun}_{$namaMitra}.xlsx";

        // ── Buat Spreadsheet ──────────────────────────────────────────────
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // ── Sheet 1: Detail Harian ────────────────────────────────────────
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Detail Harian');

        $headers = [
            'No.', 'ID Karyawan', 'Nama Karyawan', 'Jabatan', 'Divisi',
            'Mitra / Cabang', 'Tanggal', 'Jam Masuk', 'Jam Pulang',
            'Status Kehadiran', 'Keterangan',
        ];

        // Tulis header sheet 1
        foreach ($headers as $col => $header) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '1';
            $sheet1->setCellValue($cell, $header);
            $sheet1->getStyle($cell)->getFont()->setBold(true);
            $sheet1->getStyle($cell)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF2E75B6');
            $sheet1->getStyle($cell)->getFont()->getColor()->setARGB('FFFFFFFF');
            $sheet1->getStyle($cell)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // Data rows sheet 1
        $row = 2;
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

            // PERBAIKAN: Cek apakah statusnya membutuhkan jam atau tidak
            $isHadir = in_array($abs->status, ['hadir', 'telat']);

            $data = [
                $i + 1,
                $k?->nip ?? '-',
                $k?->nama ?? '-',
                $k?->jabatan ?? '-',
                $k?->labelDivisi() ?? '-',
                $namaLokasi,
                $abs->tanggal?->format('d/m/Y') ?? '-',
                // Jika tidak hadir (izin/sakit/dll), tampilkan '-'
                $isHadir ? ($abs->waktu_masuk?->format('H:i') ?? '-') : '-',
                // Jika tidak hadir, tampilkan '-'. Jika hadir tapi belum absen pulang, baru muncul teksnya.
                $isHadir ? ($abs->waktu_pulang?->format('H:i') ?? 'Belum Absen Pulang') : '-',
                $statusLabel,
                $abs->keterangan ?? '', // Mengambil keterangan dari database
            ];

            foreach ($data as $col => $value) {
                $cellAddr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . $row;
                $sheet1->setCellValue($cellAddr, $value);
                if ($row % 2 === 0) {
                    $sheet1->getStyle($cellAddr)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFF2F2F2');
                }
            }
            $row++;
        }

        // Auto width sheet 1
        foreach (range(1, count($headers)) as $col) {
            $sheet1->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        // ── Sheet 2: Rekap Per Karyawan ───────────────────────────────────
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Rekap Per Karyawan');

        $headers2 = [
            'No.', 'NIK', 'Nama', 'Jabatan', 'Divisi', 'Mitra / Cabang',
            'Total Hadir', 'Total Telat', 'Total Alfa',
            'Total Izin', 'Total Sakit', 'Total Cuti', 'Total Dinas',
            '% Kehadiran',
        ];

        // Tulis header sheet 2
        foreach ($headers2 as $col => $header) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '1';
            $sheet2->setCellValue($cell, $header);
            $sheet2->getStyle($cell)->getFont()->setBold(true);
            $sheet2->getStyle($cell)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF2E75B6');
            $sheet2->getStyle($cell)->getFont()->getColor()->setARGB('FFFFFFFF');
            $sheet2->getStyle($cell)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // Data rows sheet 2
        $row2 = 2;
        foreach ($rekap as $i => $r) {
            $persen = $totalHariKerja > 0 ? round(($r['hadir'] / $totalHariKerja) * 100, 1) : 0;
            $data2  = [
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

            foreach ($data2 as $col => $value) {
                $cellAddr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . $row2;
                $sheet2->setCellValue($cellAddr, $value);

                if ($row2 % 2 === 0) {
                    $sheet2->getStyle($cellAddr)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFF2F2F2');
                }

                // Merahkan baris jika kehadiran < 80%
                if ($persen < 80) {
                    $sheet2->getStyle($cellAddr)->getFont()->getColor()->setARGB('FFCC0000');
                }
            }
            $row2++;
        }

        // Auto width sheet 2
        foreach (range(1, count($headers2)) as $col) {
            $sheet2->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        // Kembali ke sheet pertama
        $spreadsheet->setActiveSheetIndex(0);

        // ── PERBAIKAN DOWNLOAD: Bersihkan output buffer ───────────────────
        if (ob_get_length()) ob_end_clean();

        // ── Stream langsung ke browser ────────────────────────────────────
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $namaFile, [
            'Content-Type'              => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition'       => 'attachment; filename="' . $namaFile . '"',
            'Cache-Control'             => 'max-age=0, no-store, no-cache, must-revalidate',
            'Pragma'                    => 'public',
        ]);
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
