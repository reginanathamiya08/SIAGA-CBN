<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Models\Mitra;
use App\Models\PeriodeGaji;
use App\Models\SlipGaji;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class MonitoringGajiController extends Controller
{
    public function index(Request $request)
    {
        $periodeId = $request->input('periode_id');
        $mitraId   = $request->input('mitra_id');

        $semuaPeriode = PeriodeGaji::orderBy('tanggal_mulai', 'desc')->get();
        $semuaMitra   = Mitra::orderBy('nama_mitra')->get();

        $query = SlipGaji::with(['karyawan.user', 'karyawan.penempatanAktif.mitra', 'periodeGaji']);

        if ($periodeId) {
            $query->where('periode_id', $periodeId);
        }

        if ($mitraId) {
            $query->whereHas('karyawan.penempatanAktif', function ($q) use ($mitraId) {
                $q->where('mitra_id', $mitraId);
            });
        }

        $slipGaji = $query->get();

        // Statistik Ringkasan
        $totalPengeluaran = $slipGaji->sum('gaji_bersih');
        $totalKaryawan    = $slipGaji->count();
        $rataRataGaji     = $totalKaryawan > 0 ? $totalPengeluaran / $totalKaryawan : 0;

        return view('Pimpinan.MonitoringGaji.index', compact(
            'slipGaji',
            'semuaPeriode',
            'semuaMitra',
            'periodeId',
            'mitraId',
            'totalPengeluaran',
            'totalKaryawan',
            'rataRataGaji'
        ));
    }

    public function export(Request $request)
    {
        $periodeId = $request->input('periode_id');
        $mitraId   = $request->input('mitra_id');

        $periode = PeriodeGaji::find($periodeId);
        $mitra   = Mitra::find($mitraId);

        $query = SlipGaji::with(['karyawan.user', 'karyawan.penempatanAktif.mitra', 'periodeGaji']);

        if ($periodeId) {
            $query->where('periode_id', $periodeId);
        }

        if ($mitraId) {
            $query->whereHas('karyawan.penempatanAktif', function ($q) use ($mitraId) {
                $q->where('mitra_id', $mitraId);
            });
        }

        $slipGaji = $query->get();

        if ($slipGaji->isEmpty()) {
            return back()->with('error', 'Tidak ada data gaji untuk diekspor.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Monitoring Gaji');

        $headers = [
            'No.', 'NIK', 'Nama Karyawan', 'Jabatan', 'Mitra', 
            'Gaji Pokok', 'Tunjangan', 'Uang Makan', 'Uang Transport', 
            'Lembur', 'Potongan', 'Gaji Bersih'
        ];

        // Styling Header
        foreach ($headers as $col => $header) {
            $cell = Coordinate::stringFromColumnIndex($col + 1) . '1';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E3A5F']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
        }

        $row = 2;
        foreach ($slipGaji as $i => $slip) {
            $k = $slip->karyawan;
            $m = $k?->penempatanAktif?->mitra?->nama_mitra ?? ($k?->isTetap() ? 'Kantor CBN' : '-');

            $data = [
                $i + 1,
                $k?->user?->username ?? '-',
                $k?->nama ?? '-',
                $k?->jabatan ?? '-',
                $m,
                $slip->gaji_pokok,
                $slip->total_tunjangan,
                $slip->uang_makan,
                $slip->uang_transport,
                $slip->total_lembur,
                $slip->total_potongan,
                $slip->gaji_bersih,
            ];

            foreach ($data as $col => $value) {
                $cellAddr = Coordinate::stringFromColumnIndex($col + 1) . $row;
                $sheet->setCellValue($cellAddr, $value);
                
                if ($col >= 5) {
                    $sheet->getStyle($cellAddr)->getNumberFormat()->setFormatCode('#,##0');
                }

                if ($row % 2 === 0) {
                    $sheet->getStyle($cellAddr)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFF2F2F2');
                }
                
                $sheet->getStyle($cellAddr)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            }
            $row++;
        }

        foreach (range(1, count($headers)) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        $namaPeriode = $periode ? str_replace(' ', '_', $periode->nama_periode) : 'Semua_Periode';
        $namaMitra   = $mitra ? str_replace(' ', '_', $mitra->nama_mitra) : 'Semua_Mitra';
        $namaFile    = "Monitoring_Gaji_{$namaPeriode}_{$namaMitra}.xlsx";

        if (ob_get_length()) ob_end_clean();

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $namaFile, [
            'Content-Type'              => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition'       => 'attachment; filename="' . $namaFile . '"',
            'Cache-Control'             => 'max-age=0, no-store, no-cache, must-revalidate',
            'Pragma'                    => 'public',
        ]);
    }
}
