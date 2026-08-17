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
        $bulan     = $request->input('bulan');
        $tahun     = $request->input('tahun');
        $periodeId = $request->input('periode_id');
        $mitraId   = $request->input('mitra_id');

        $semuaPeriode = PeriodeGaji::orderBy('tanggal_mulai', 'desc')->get();
        $semuaMitra   = Mitra::where('id', '!=', 'MTR-00001')
            ->orderByRaw('COALESCE(mitra_induk_id, id), is_cabang ASC, nama_mitra ASC')
            ->get();

        // Jika bulan & tahun dipilih dari dropdown, cari periode yang cocok
        if ($bulan && $tahun) {
            $periodeMatched = $this->findPeriodeByBulanTahun($semuaPeriode, $bulan, $tahun);
            if ($periodeMatched) {
                $periodeId = $periodeMatched->id;
            } else {
                if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    \Carbon\Carbon::setLocale('id');
                    $namaBulanLabel = \Carbon\Carbon::create($tahun, (int)$bulan)->translatedFormat('F') . ' ' . $tahun;
                    return response()->json([
                        'success' => false,
                        'message' => "Periode penggajian untuk bulan {$namaBulanLabel} belum diterbitkan atau belum dibuat oleh Admin."
                    ]);
                }

                // Fallback ke periode aktif/terbaru agar tidak membuka halaman kosong
                $periodeProses = $semuaPeriode->firstWhere('status', 'proses');
                $selectedP     = $periodeProses ?: $semuaPeriode->first();
                if ($selectedP) {
                    $periodeId = $selectedP->id;
                    $bulan     = $selectedP->tanggal_selesai ? $selectedP->tanggal_selesai->month : ($selectedP->tanggal_mulai ? $selectedP->tanggal_mulai->month : null);
                    $tahun     = $selectedP->tanggal_selesai ? $selectedP->tanggal_selesai->year : ($selectedP->tanggal_mulai ? $selectedP->tanggal_mulai->year : null);
                }
            }
        } elseif ($periodeId) {
            $p = $semuaPeriode->find($periodeId);
            if ($p) {
                $bulan = $p->tanggal_selesai ? $p->tanggal_selesai->month : ($p->tanggal_mulai ? $p->tanggal_mulai->month : null);
                $tahun = $p->tanggal_selesai ? $p->tanggal_selesai->year : ($p->tanggal_mulai ? $p->tanggal_mulai->year : null);
            }
        } else {
            // Default ke bulan & tahun periode aktif (proses) atau terbaru
            $periodeProses = $semuaPeriode->firstWhere('status', 'proses');
            $selectedP     = $periodeProses ?: $semuaPeriode->first();
            if ($selectedP) {
                $periodeId = $selectedP->id;
                $bulan     = $selectedP->tanggal_selesai ? $selectedP->tanggal_selesai->month : ($selectedP->tanggal_mulai ? $selectedP->tanggal_mulai->month : null);
                $tahun     = $selectedP->tanggal_selesai ? $selectedP->tanggal_selesai->year : ($selectedP->tanggal_mulai ? $selectedP->tanggal_mulai->year : null);
            }
        }

        $selectedPeriode = $periodeId ? $semuaPeriode->find($periodeId) : null;

        $query = SlipGajiPeriode::with(['karyawan', 'karyawan.penempatanAktif.mitra', 'periodeGaji']);

        if ($periodeId) {
            $query->where('periode_id', $periodeId);
        } else {
            $query->whereRaw('1 = 0');
        }

        $allSlips = $query->get();

        // Memisahkan Karyawan Tetap dan Kontrak
        $slipTetap   = $allSlips->filter(fn($s) => $s->karyawan?->isTetap() ?? false);
        $slipKontrak = $allSlips->filter(fn($s) => $s->karyawan?->isKontrak() ?? false);

        // Filter Mitra hanya memengaruhi Karyawan Kontrak
        if ($mitraId) {
            $slipKontrak = $slipKontrak->filter(function ($s) use ($mitraId) {
                return ($s->karyawan?->penempatanAktif?->mitra_id ?? '') == $mitraId;
            });
        }

        $periodeAvailable = $semuaPeriode->map(function($p) {
            return [
                'id'     => $p->id,
                'name'   => $p->nama_periode,
                'month'  => $p->tanggal_selesai ? $p->tanggal_selesai->month : ($p->tanggal_mulai ? $p->tanggal_mulai->month : null),
                'year'   => $p->tanggal_selesai ? $p->tanggal_selesai->year : ($p->tanggal_mulai ? $p->tanggal_mulai->year : null),
            ];
        })->values();

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            $htmlBanner = view('Pimpinan.MonitoringGaji._status_banner', compact('selectedPeriode'))->render();
            $htmlTable  = view('Pimpinan.MonitoringGaji._table_content', compact('slipTetap', 'slipKontrak', 'selectedPeriode', 'periodeId'))->render();

            return response()->json([
                'success'          => true,
                'htmlBanner'       => $htmlBanner,
                'htmlTable'        => $htmlTable,
                'countTetap'       => $slipTetap->count(),
                'countKontrak'     => $slipKontrak->count(),
                'periodeId'        => $periodeId,
                'submitUrl'        => route('pimpinan.monitoring-gaji.submit', $periodeId ?: 'none'),
                'exportUrl'        => route('pimpinan.monitoring-gaji.export', array_filter(['bulan' => $bulan, 'tahun' => $tahun, 'mitra_id' => $mitraId, 'periode_id' => $periodeId])),
            ]);
        }

        return view('Pimpinan.MonitoringGaji.index', compact(
            'slipTetap',
            'slipKontrak',
            'semuaPeriode',
            'semuaMitra',
            'selectedPeriode',
            'periodeId',
            'bulan',
            'tahun',
            'mitraId',
            'periodeAvailable'
        ));
    }

    public function export(Request $request)
    {
        $bulan     = $request->input('bulan');
        $tahun     = $request->input('tahun');
        $periodeId = $request->input('periode_id');
        $mitraId   = $request->input('mitra_id');

        $semuaPeriode = PeriodeGaji::all();
        if ($bulan && $tahun && !$periodeId) {
            $periodeMatched = $this->findPeriodeByBulanTahun($semuaPeriode, $bulan, $tahun);
            $periodeId = $periodeMatched ? $periodeMatched->id : null;
        }

        if (!$periodeId) {
            \Carbon\Carbon::setLocale('id');
            $namaBulanLabel = ($bulan && $tahun) ? \Carbon\Carbon::create($tahun, (int)$bulan)->translatedFormat('F') . ' ' . $tahun : 'periode yang dipilih';
            return back()->with('error', "Belum ada periode / data penggajian untuk {$namaBulanLabel}.");
        }

        $query = SlipGajiPeriode::with(['karyawan', 'karyawan.penempatanAktif.mitra', 'periodeGaji'])
            ->where('periode_id', $periodeId);

        $allSlips = $query->get();

        $slipTetap   = $allSlips->filter(fn($s) => $s->karyawan?->isTetap() ?? false);
        $slipKontrak = $allSlips->filter(fn($s) => $s->karyawan?->isKontrak() ?? false);

        if ($mitraId) {
            $slipKontrak = $slipKontrak->filter(function ($s) use ($mitraId) {
                return ($s->karyawan?->penempatanAktif?->mitra_id ?? '') == $mitraId;
            });
        }

        if ($slipTetap->isEmpty() && $slipKontrak->isEmpty()) {
            return back()->with('error', 'Tidak ada data gaji untuk diekspor.');
        }

        \Carbon\Carbon::setLocale('id');
        $labelPeriode = ($bulan && $tahun) ? \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F_Y') : 'Semua_Periode';
        $namaFile     = "Monitoring_Gaji_CBN_{$labelPeriode}.xlsx";

        $spreadsheet = new Spreadsheet();

        // ── Sheet 1: Karyawan Tetap ───────────────────────────────────────
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Karyawan Tetap');
        $this->isiSheetGaji($sheet1, 'KARYAWAN TETAP', $slipTetap, false);

        // ── Sheet 2: Karyawan Kontrak ─────────────────────────────────────
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Karyawan Kontrak');
        $this->isiSheetGaji($sheet2, 'KARYAWAN KONTRAK', $slipKontrak, true);

        $spreadsheet->setActiveSheetIndex(0);

        if (ob_get_length()) ob_end_clean();

        $writer = new Xlsx($spreadsheet);

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
     * Helper privat untuk membuat struktur Sheet Gaji di Excel.
     */
    private function isiSheetGaji($sheet, string $titleType, $slips, bool $isKontrak)
    {
        // Banner Header
        $sheet->setCellValue('A1', "DAFTAR REKAPITULASI GAJI {$titleType} — PT CBN");
        $sheet->mergeCells('A1:M1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E3A5F'));

        $totalPengeluaran = $slips->sum('gaji_bersih');
        $sheet->setCellValue('A2', "TOTAL PENGELUARAN GAJI: Rp " . number_format($totalPengeluaran, 0, ',', '.') . " | TOTAL KARYAWAN: " . $slips->count() . " ORANG");
        $sheet->mergeCells('A2:M2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));

        $headers = [
            'No.', 'NIK', 'Nama Karyawan', 'Jabatan', 'Divisi',
            $isKontrak ? 'Mitra / Cabang' : 'Lokasi',
            'Gaji Pokok', 'Tunjangan', 'Uang Makan', 'Uang Transport',
            'Lembur', 'Potongan', 'Gaji Bersih'
        ];

        $startRow = 4;
        foreach ($headers as $col => $header) {
            $cellAddr = Coordinate::stringFromColumnIndex($col + 1) . $startRow;
            $sheet->setCellValue($cellAddr, $header);
            $sheet->getStyle($cellAddr)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle($cellAddr)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF1E3A5F');
            $sheet->getStyle($cellAddr)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $row = $startRow + 1;
        if ($slips->count() > 0) {
            foreach ($slips->values() as $i => $slip) {
                $k = $slip->karyawan;
                $m = $k?->penempatanAktif?->mitra?->nama_mitra ?? ($k?->isTetap() ? 'Kantor CBN' : '-');

                $data = [
                    $i + 1,
                    $k?->nip ?? '-',
                    $k?->nama ?? '-',
                    $k?->jabatan ?? '-',
                    $k?->labelDivisi() ?? '-',
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

                    if ($col >= 6) {
                        $sheet->getStyle($cellAddr)->getNumberFormat()->setFormatCode('#,##0');
                    }

                    if ($row % 2 === 0) {
                        $sheet->getStyle($cellAddr)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFF8FAFC');
                    }

                    $sheet->getStyle($cellAddr)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }
                $row++;
            }

            // Row Total Pengeluaran
            $sheet->setCellValue("A{$row}", "TOTAL PENGELUARAN");
            $sheet->mergeCells("A{$row}:F{$row}");
            $sheet->getStyle("A{$row}:F{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $sheet->setCellValue("M{$row}", $totalPengeluaran);
            $sheet->getStyle("M{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("M{$row}")->getFont()->setBold(true);

            $sheet->getStyle("A{$row}:M{$row}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE2E8F0');
        } else {
            $sheet->setCellValue("A{$row}", "Tidak ada data slip gaji untuk kategori ini.");
        }

        foreach (range(1, count($headers)) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }
    }

    /**
     * Helper privat untuk mencocokkan periode penggajian berdasarkan bulan & tahun.
     */
    private function findPeriodeByBulanTahun($semuaPeriode, $bulan, $tahun)
    {
        if (!$bulan || !$tahun) return null;
        \Carbon\Carbon::setLocale('id');
        $namaBulanIndo = strtolower(\Carbon\Carbon::create(null, (int)$bulan)->translatedFormat('F'));

        // Priority 1: Match nama_periode (misal "Juni 2026")
        $matchName = $semuaPeriode->first(function($p) use ($tahun, $namaBulanIndo) {
            $namaLow = strtolower($p->nama_periode);
            return str_contains($namaLow, $namaBulanIndo) && str_contains($namaLow, (string)$tahun);
        });
        if ($matchName) return $matchName;

        // Priority 2: Match tanggal_selesai (misal 25 Juni 2026 -> Periode Juni)
        $matchSelesai = $semuaPeriode->first(function($p) use ($bulan, $tahun) {
            return $p->tanggal_selesai && $p->tanggal_selesai->month == $bulan && $p->tanggal_selesai->year == $tahun;
        });
        if ($matchSelesai) return $matchSelesai;

        // Priority 3: Fallback tanggal_mulai
        return $semuaPeriode->first(function($p) use ($bulan, $tahun) {
            return $p->tanggal_mulai && $p->tanggal_mulai->month == $bulan && $p->tanggal_mulai->year == $tahun;
        });
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
