<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mitra;
use App\Models\PeriodeGaji;
use App\Models\SlipGajiPeriode;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanGajiController extends Controller
{
    public function index(Request $request)
    {
        $bulan         = $request->input('bulan', now()->month);
        $tahun         = $request->input('tahun', now()->year);
        $periodeId     = $request->input('periode_id');
        $mitraId       = $request->input('mitra_id');
        $jenisKaryawan = $request->input('jenis_karyawan_id', 'tetap');

        $semuaPeriode = PeriodeGaji::orderBy('tanggal_mulai', 'desc')->get();
        $semuaMitra   = Mitra::where('is_pusat', false)->orderByRaw('COALESCE(mitra_induk_id, id), is_cabang ASC, nama_mitra ASC')->get();

        $query = SlipGajiPeriode::with(['karyawan', 'karyawan.penempatanAktif.mitra', 'periodeGaji'])
            ->whereHas('karyawan', fn($q) => $q->whereHas('role', fn($r) => $r->whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak'])));

        if ($jenisKaryawan) {
            $roleSlug = in_array($jenisKaryawan, ['tetap', 'karyawan_tetap', 'JNS-00001']) ? 'karyawan_tetap' : 'karyawan_kontrak';
            $query->whereHas('karyawan', fn($q) => $q->whereHas('role', fn($r) => $r->where('slug', $roleSlug)));
        }

        if ($bulan) {
            $query->whereHas('periodeGaji', fn($q) => $q->whereMonth('tanggal_selesai', $bulan));
        }

        if ($tahun) {
            $query->whereHas('periodeGaji', fn($q) => $q->whereYear('tanggal_selesai', $tahun));
        }

        if ($periodeId) {
            $query->where('periode_id', $periodeId);
        }

        if ($mitraId && in_array($jenisKaryawan, ['kontrak', 'karyawan_kontrak', 'JNS-00002'])) {
            $query->whereHas('karyawan.penempatanAktif', function ($q) use ($mitraId) {
                $q->where('mitra_id', $mitraId);
            });
        }

        $slipGaji = $query->get();

        return view('admin.laporan.gaji', compact(
            'slipGaji',
            'semuaPeriode',
            'semuaMitra',
            'periodeId',
            'bulan',
            'tahun',
            'mitraId',
            'jenisKaryawan'
        ));
    }

    public function export(Request $request)
    {
        $bulan         = $request->input('bulan', now()->month);
        $tahun         = $request->input('tahun', now()->year);
        $periodeId     = $request->input('periode_id');
        $mitraId       = $request->input('mitra_id');
        $jenisKaryawan = $request->input('jenis_karyawan_id', 'tetap');

        $periode = PeriodeGaji::find($periodeId);
        $mitra   = Mitra::find($mitraId);

        $query = SlipGajiPeriode::with(['karyawan', 'karyawan.penempatanAktif.mitra', 'periodeGaji'])
            ->whereHas('karyawan', fn($q) => $q->whereHas('role', fn($r) => $r->whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak'])));

        if ($jenisKaryawan) {
            $roleSlug = in_array($jenisKaryawan, ['tetap', 'karyawan_tetap', 'JNS-00001']) ? 'karyawan_tetap' : 'karyawan_kontrak';
            $query->whereHas('karyawan', fn($q) => $q->whereHas('role', fn($r) => $r->where('slug', $roleSlug)));
        }

        if ($bulan) {
            $query->whereHas('periodeGaji', fn($q) => $q->whereMonth('tanggal_selesai', $bulan));
        }

        if ($tahun) {
            $query->whereHas('periodeGaji', fn($q) => $q->whereYear('tanggal_selesai', $tahun));
        }

        if ($periodeId) {
            $query->where('periode_id', $periodeId);
        }

        if ($mitraId && $jenisKaryawan === 'JNS-00002') {
            $query->whereHas('karyawan.penempatanAktif', function ($q) use ($mitraId) {
                $q->where('mitra_id', $mitraId);
            });
        }

        $slipGaji = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Gaji');

        $headers = [
            'No.', 'NIK', 'Nama Karyawan', 'Email', 'Jabatan', 'Mitra', 'Periode',
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
                $k?->nip ?? '-',
                $k?->nama ?? '-',
                $k?->email ?? '-',
                $k?->jabatan ?? '-',
                $m,
                $slip->periodeGaji?->nama_periode ?? '-',
                $slip->total_hadir,
                $slip->total_telat,
                $slip->total_izin,
                $slip->total_alfa,
                $slip->total_cuti,
                $slip->getNominal('Gaji Pokok'),
                $slip->getNominal('Uang Makan'),
                $slip->getNominal('Uang Transport'),
                $slip->total_potongan,
                $slip->gaji_bersih,
            ];

            foreach ($data as $col => $value) {
                $cellAddr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . $row;
                $sheet->setCellValue($cellAddr, $value);
                
                // Format Currency for salary columns (starting from Gaji Pokok)
                if ($col >= 12) {
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
