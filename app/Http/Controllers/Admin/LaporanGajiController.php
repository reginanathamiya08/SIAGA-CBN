<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mitra;
use App\Models\PeriodeGaji;
use App\Models\SlipGaji;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanGajiController extends Controller
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

        return view('Admin.Laporan.gaji', compact(
            'slipGaji',
            'semuaPeriode',
            'semuaMitra',
            'periodeId',
            'mitraId'
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

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Gaji');

        $headers = [
            'No.', 'NIK', 'Nama Karyawan', 'Jabatan', 'Mitra', 
            'Hadir', 'Telat', 'Izin/Sakit', 'Alfa', 'Cuti',
            'Gaji Pokok', 'Uang Makan', 'Uang Transport', 
            'Total Potongan', 'Gaji Bersih'
        ];

        // Header Styling
        foreach ($headers as $col => $header) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '1';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF2E75B6');
            $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle($cell)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
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
                $slip->total_hadir,
                $slip->total_telat,
                $slip->total_izin,
                $slip->total_alfa,
                $slip->total_cuti,
                $slip->gaji_pokok,
                $slip->uang_makan,
                $slip->uang_transport,
                $slip->total_potongan,
                $slip->gaji_bersih,
            ];

            foreach ($data as $col => $value) {
                $cellAddr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . $row;
                $sheet->setCellValue($cellAddr, $value);
                
                // Format Currency for salary columns (starting from Gaji Pokok)
                if ($col >= 10) {
                    $sheet->getStyle($cellAddr)->getNumberFormat()
                        ->setFormatCode('#,##0');
                }

                if ($row % 2 === 0) {
                    $sheet->getStyle($cellAddr)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFF2F2F2');
                }
            }
            $row++;
        }

        // Auto width
        foreach (range(1, count($headers)) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        $namaPeriode = $periode ? str_replace(' ', '_', $periode->nama_periode) : 'Semua_Periode';
        $namaMitra   = $mitra ? str_replace(' ', '_', $mitra->nama_mitra) : 'Semua_Mitra';
        $namaFile    = "Laporan_Gaji_{$namaPeriode}_{$namaMitra}.xlsx";

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
