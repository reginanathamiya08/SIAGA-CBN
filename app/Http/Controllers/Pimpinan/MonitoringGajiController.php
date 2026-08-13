<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Models\Mitra;
use App\Models\PeriodeGaji;
use App\Models\SlipGajiPeriode;
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
        $semuaMitra   = Mitra::where('id', '!=', 'MTR-00001')
            ->orderByRaw('COALESCE(mitra_induk_id, id), is_cabang ASC, nama_mitra ASC')
            ->get();

        // Jika belum ada filter periode, cari apakah ada periode berstatus 'proses' (Menunggu Persetujuan),
        // atau fallback ke periode terbaru yang ada
        if (!$periodeId) {
            $periodeProses = $semuaPeriode->firstWhere('status', 'proses');
            if ($periodeProses) {
                $periodeId = $periodeProses->id;
            } else {
                $periodeTerbaru = $semuaPeriode->first();
                if ($periodeTerbaru) {
                    $periodeId = $periodeTerbaru->id;
                }
            }
        }

        $query = SlipGajiPeriode::with(['karyawan', 'karyawan.penempatanAktif.mitra', 'periodeGaji']);

        if ($periodeId) {
            $query->where('periode_id', $periodeId);
        }

        $allSlips = $query->get();

        // Memisahkan Karyawan Tetap dan Kontrak
        $slipTetap = $allSlips->filter(fn($s) => $s->karyawan?->isTetap() ?? false);
        $slipKontrak = $allSlips->filter(fn($s) => $s->karyawan?->isKontrak() ?? false);

        // Filter Mitra hanya memengaruhi Karyawan Kontrak
        if ($mitraId) {
            $slipKontrak = $slipKontrak->filter(function ($s) use ($mitraId) {
                return ($s->karyawan?->penempatanAktif?->mitra_id ?? '') == $mitraId;
            });
        }

        // Statistik Ringkasan (Gabungan hasil filter)
        $totalPengeluaran = $slipTetap->sum('gaji_bersih') + $slipKontrak->sum('gaji_bersih');
        $totalKaryawan    = $slipTetap->count() + $slipKontrak->count();
        $rataRataGaji     = $totalKaryawan > 0 ? $totalPengeluaran / $totalKaryawan : 0;

        return view('Pimpinan.MonitoringGaji.index', compact(
            'slipTetap',
            'slipKontrak',
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

        $query = SlipGajiPeriode::with(['karyawan', 'karyawan.penempatanAktif.mitra', 'periodeGaji']);

        if ($periodeId) {
            $query->where('periode_id', $periodeId);
        }

        $allSlips = $query->get();

        // Memisahkan Karyawan Tetap dan Kontrak
        $slipTetap = $allSlips->filter(fn($s) => $s->karyawan?->isTetap() ?? false);
        $slipKontrak = $allSlips->filter(fn($s) => $s->karyawan?->isKontrak() ?? false);

        // Filter Mitra hanya memengaruhi Karyawan Kontrak
        if ($mitraId) {
            $slipKontrak = $slipKontrak->filter(function ($s) use ($mitraId) {
                return ($s->karyawan?->penempatanAktif?->mitra_id ?? '') == $mitraId;
            });
        }

        // Gabungkan kembali untuk diekspor
        $slipGaji = $slipTetap->concat($slipKontrak);

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
                $k?->nip ?? '-',
                $k?->nama ?? '-',
                $k?->jabatan ?? '-',
                $m,
                $slip->getNominal('Gaji Pokok'),
                $slip->getNominal('Tunjangan Askes') + $slip->getNominal('Tunjangan Jamsostek') + $slip->getNominal('Tunjangan Pangan'),
                $slip->getNominal('Uang Makan'),
                $slip->getNominal('Uang Transport'),
                0,
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

    public function approve(PeriodeGaji $periode)
    {
        if ($periode->status !== 'proses') {
            return back()->with('error', 'Periode penggajian tidak sedang menunggu persetujuan.');
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $periode->update([
                'status'        => 'final',
                'finalisasi_at' => now(),
                'finalisasi_by' => \Illuminate\Support\Facades\Auth::id(),
            ]);

            // Update all slips in this period to diterbitkan
            $periode->slipGaji()->update([
                'status'         => 'diterbitkan',
                'diterbitkan_at' => now(),
            ]);

            // Send notification to each employee
            foreach ($periode->slipGaji as $slip) {
                \App\Models\Notification::send(
                    $slip->user_id,
                    'Slip Gaji Terbit! 💰',
                    "Slip gaji Anda untuk periode {$periode->nama_periode} telah diterbitkan. Silakan periksa di menu Slip Gaji.",
                    'success',
                    route('karyawan.slip-gaji.index')
                );
            }

            // Send notification to Admins
            $admins = \App\Models\User::whereHas('role', function($q) {
                $q->where('slug', 'admin');
            })->get();
            foreach ($admins as $admin) {
                \App\Models\Notification::send(
                    $admin->id,
                    'Penggajian Disetujui Pimpinan ✅',
                    "Penggajian periode {$periode->nama_periode} telah disetujui oleh Pimpinan dan slip gaji telah diterbitkan.",
                    'success',
                    route('admin.penggajian.show', $periode->id)
                );
            }

            \Illuminate\Support\Facades\DB::commit();
            return back()->with('success', "Periode penggajian {$periode->nama_periode} berhasil disetujui dan slip dirilis.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Gagal menyetujui penggajian: ' . $e->getMessage());
        }
    }

    public function reject(PeriodeGaji $periode)
    {
        if ($periode->status !== 'proses') {
            return back()->with('error', 'Periode penggajian tidak sedang menunggu persetujuan.');
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Revert DetailPerizinan linked to these slips
            $slipIds = $periode->slipGaji()->pluck('id')->toArray();
            \App\Models\DetailPerizinan::whereIn('slip_gaji_periode_id', $slipIds)
                                       ->update(['slip_gaji_periode_id' => null]);

            // Delete all slips (cascade deletes detail_gaji_komponen)
            $periode->slipGaji()->delete();

            // Set period status back to draft
            $periode->update([
                'status'        => 'draft',
                'finalisasi_at' => null,
                'finalisasi_by' => null,
            ]);

            // Send notification to Admins
            $admins = \App\Models\User::whereHas('role', function($q) {
                $q->where('slug', 'admin');
            })->get();
            foreach ($admins as $admin) {
                \App\Models\Notification::send(
                    $admin->id,
                    'Penggajian Ditolak Pimpinan ❌',
                    "Penggajian periode {$periode->nama_periode} ditolak oleh Pimpinan dan dikembalikan ke Draft.",
                    'danger',
                    route('admin.penggajian.show', $periode->id)
                );
            }

            \Illuminate\Support\Facades\DB::commit();
            return back()->with('success', "Periode penggajian {$periode->nama_periode} telah ditolak dan dikembalikan ke Draft.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Gagal menolak penggajian: ' . $e->getMessage());
        }
    }

    public function submit(Request $request, PeriodeGaji $periode)
    {
        if ($periode->status !== 'proses') {
            return back()->with('error', 'Periode penggajian tidak sedang menunggu persetujuan.');
        }

        $slipsData = $request->input('slips', []);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $anyRejected = false;

            foreach ($periode->slipGaji as $slip) {
                $decision = $slipsData[$slip->id] ?? ['status' => 'setuju', 'alasan' => null];
                
                if ($decision['status'] === 'tolak') {
                    $slip->update([
                        'status'       => 'ditolak',
                        'alasan_tolak' => $decision['alasan'] ?? 'Ditolak oleh Pimpinan tanpa alasan spesifik.',
                    ]);
                    $anyRejected = true;
                } else {
                    $slip->update([
                        'status'       => 'disetujui',
                        'alasan_tolak' => null,
                    ]);
                }
            }

            if ($anyRejected) {
                // Kembalikan status periode ke draft
                $periode->update([
                    'status'        => 'draft',
                    'finalisasi_at' => null,
                    'finalisasi_by' => null,
                ]);

                // Notifikasi ke Admin
                $admins = \App\Models\User::whereHas('role', function($q) {
                    $q->where('slug', 'admin');
                })->get();
                foreach ($admins as $admin) {
                    \App\Models\Notification::send(
                        $admin->id,
                        'Penggajian Perlu Revisi ⚠️',
                        "Penggajian periode {$periode->nama_periode} dikembalikan ke Draft karena beberapa slip ditolak oleh Pimpinan.",
                        'danger',
                        route('admin.penggajian.show', $periode->id)
                    );
                }

                \Illuminate\Support\Facades\DB::commit();
                return redirect()->route('pimpinan.monitoring-gaji.index')->with('success', 'Keputusan berhasil dikirim. Beberapa slip gaji ditolak dan dikembalikan ke Admin untuk direvisi.');
            } else {
                // Semua disetujui: finalisasi periode dan terbitkan semua slip
                $periode->update([
                    'status'        => 'final',
                    'finalisasi_at' => now(),
                    'finalisasi_by' => \Illuminate\Support\Facades\Auth::id(),
                ]);

                $periode->slipGaji()->update([
                    'status'         => 'diterbitkan',
                    'diterbitkan_at' => now(),
                ]);

                // Kirim notifikasi ke Karyawan
                foreach ($periode->slipGaji as $slip) {
                    \App\Models\Notification::send(
                        $slip->user_id,
                        'Slip Gaji Terbit! 💰',
                        "Slip gaji Anda untuk periode {$periode->nama_periode} telah diterbitkan. Silakan periksa di menu Slip Gaji.",
                        'success',
                        route('karyawan.slip-gaji.index')
                    );
                }

                // Kirim notifikasi ke Admin
                $admins = \App\Models\User::whereHas('role', function($q) {
                    $q->where('slug', 'admin');
                })->get();
                foreach ($admins as $admin) {
                    \App\Models\Notification::send(
                        $admin->id,
                        'Penggajian Disetujui Pimpinan ✅',
                        "Penggajian periode {$periode->nama_periode} telah disetujui oleh Pimpinan dan slip gaji telah diterbitkan.",
                        'success',
                        route('admin.penggajian.show', $periode->id)
                    );
                }

                \Illuminate\Support\Facades\DB::commit();
                return redirect()->route('pimpinan.monitoring-gaji.index')->with('success', 'Keputusan berhasil dikirim. Seluruh penggajian disetujui dan slip gaji telah diterbitkan.');
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Gagal mengirim keputusan penggajian: ' . $e->getMessage());
        }
    }
}
