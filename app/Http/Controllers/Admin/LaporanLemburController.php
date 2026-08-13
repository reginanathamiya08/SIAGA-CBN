<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lembur;
use App\Models\User;
use App\Models\Mitra;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanLemburController extends Controller
{
    /**
     * Tampilkan halaman rekap / laporan lembur karyawan untuk Admin.
     */
    public function index(Request $request)
    {
        $bulan          = $request->input('bulan'); // null = semua bulan
        $tahun          = $request->input('tahun', now()->year);
        $statusApproval = $request->input('status_approval', 'semua');
        $jenisKaryawan  = $request->input('jenis_karyawan_id');
        $divisi         = $request->input('divisi');
        $karyawanId     = $request->input('user_id');

        // Dropdown data karyawan untuk filter
        $semuaKaryawan = User::where('is_active', true)
                             ->whereHas('role', fn($q) => $q->whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak']))
                             ->when($jenisKaryawan, function($q) use ($jenisKaryawan) {
                                 $roleSlug = in_array($jenisKaryawan, ['tetap', 'karyawan_tetap', 'JNS-00001']) ? 'karyawan_tetap' : 'karyawan_kontrak';
                                 $q->whereHas('role', fn($r) => $r->where('slug', $roleSlug));
                             })
                             ->orderBy('nama')
                             ->get();

        // Query Lembur
        $query = Lembur::with(['karyawan', 'karyawan.komponenGaji', 'approver'])
            ->whereHas('karyawan', fn($q) => $q->whereHas('role', fn($r) => $r->whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak'])))
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc');

        if ($bulan) {
            $query->whereMonth('tanggal', $bulan);
        }

        if ($tahun) {
            $query->whereYear('tanggal', $tahun);
        }

        if ($statusApproval && $statusApproval !== 'semua') {
            $query->where('status_approval', $statusApproval);
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

        $lemburList = $query->get();

        // Hitung Statistik Ringkasan
        $ringkasan = [
            'total_pengajuan'      => $lemburList->count(),
            'total_disetujui'      => $lemburList->where('status_approval', 'disetujui')->count(),
            'total_menunggu'       => $lemburList->where('status_approval', 'menunggu')->count(),
            'total_ditolak'        => $lemburList->where('status_approval', 'ditolak')->count(),
            'total_jam_disetujui'  => $lemburList->where('status_approval', 'disetujui')->sum('total_jam'),
            'total_nominal_lembur' => $lemburList->where('status_approval', 'disetujui')->sum(fn($item) => $item->hitungNominal()),
        ];

        return view('Admin.Laporan.lembur', compact(
            'lemburList',
            'ringkasan',
            'semuaKaryawan',
            'bulan',
            'tahun',
            'statusApproval',
            'jenisKaryawan',
            'divisi',
            'karyawanId'
        ));
    }

    /**
     * Export rekap lembur ke file Excel (.xlsx).
     */
    public function export(Request $request)
    {
        $bulan          = $request->input('bulan');
        $tahun          = $request->input('tahun', now()->year);
        $statusApproval = $request->input('status_approval', 'semua');
        $jenisKaryawan  = $request->input('jenis_karyawan_id');
        $divisi         = $request->input('divisi');
        $karyawanId     = $request->input('user_id');

        $query = Lembur::with(['karyawan', 'karyawan.komponenGaji', 'approver'])
            ->whereHas('karyawan', fn($q) => $q->whereHas('role', fn($r) => $r->whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak'])))
            ->orderBy('tanggal', 'asc');

        if ($bulan) {
            $query->whereMonth('tanggal', $bulan);
        }

        if ($tahun) {
            $query->whereYear('tanggal', $tahun);
        }

        if ($statusApproval && $statusApproval !== 'semua') {
            $query->where('status_approval', $statusApproval);
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

        $lemburList = $query->get();

        Carbon::setLocale('id');
        $namaBulan = $bulan ? Carbon::create($tahun, $bulan)->translatedFormat('F') : 'Semua Bulan';
        $namaFile  = "Laporan_Rekap_Lembur_{$namaBulan}_{$tahun}.xlsx";

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Lembur');

        // Header Informasi Dokumen
        $sheet->setCellValue('A1', 'REKAPITULASI PENGAJUAN LEMBUR KARYAWAN');
        $sheet->setCellValue('A2', "Periode: {$namaBulan} {$tahun}");
        $sheet->setCellValue('A3', 'PT CITRA BANGUN NAGARI');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2:A3')->getFont()->setBold(true)->setSize(11);

        $headers = [
            'No.', 'ID Lembur', 'NIP', 'Nama Karyawan', 'Jabatan / Divisi',
            'Tanggal', 'Jam Mulai', 'Jam Selesai', 'Total Jam', 'Keperluan / Tugas',
            'Status Approval', 'Disetujui/Ditolak Oleh', 'Estimasi Upah & Makan (Rp)'
        ];

        $startRow = 5;
        foreach ($headers as $col => $header) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . $startRow;
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF1E3A5F');
            $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle($cell)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
        }

        $row = $startRow + 1;
        foreach ($lemburList as $i => $item) {
            $k = $item->karyawan;
            $nominal = $item->isDisetujui() ? $item->hitungNominal() : 0;

            $data = [
                $i + 1,
                $item->id,
                $k?->nip ?? '-',
                $k?->nama ?? '-',
                ($k?->jabatan ?? '-') . ($k?->divisi ? " ({$k->divisi})" : ''),
                $item->tanggal->format('d/m/Y'),
                substr($item->jam_mulai, 0, 5),
                substr($item->jam_selesai, 0, 5),
                $item->formatDurasi(),
                $item->keterangan ?? '-',
                strtoupper($item->status_approval),
                $item->approver?->nama ?? '-',
                $nominal
            ];

            foreach ($data as $col => $val) {
                $cellAddr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . $row;
                $sheet->setCellValue($cellAddr, $val);

                if ($col === 0 || $col === 1 || $col === 5 || $col === 6 || $col === 7 || $col === 8 || $col === 10) {
                    $sheet->getStyle($cellAddr)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                if ($col === 12) {
                    $sheet->getStyle($cellAddr)->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle($cellAddr)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }

                if ($row % 2 === 0) {
                    $sheet->getStyle($cellAddr)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFF8FAFC');
                }
            }
            $row++;
        }

        // Auto width for columns
        foreach (range(1, count($headers)) as $col) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // Output spreadsheet
        $writer = new Xlsx($spreadsheet);
        
        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $namaFile, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
