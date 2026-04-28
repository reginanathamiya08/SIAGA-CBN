<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Models\Perizinan;
use App\Models\Lembur;
use App\Models\DinasLuar;
use App\Models\Absensi;
use App\Models\KuotaCuti;
use App\Mail\NotifikasiApproval;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ApprovalController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    // INDEX - Semua pengajuan masuk (perizinan + lembur + dinas luar)
    // ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $tipe   = $request->get('tipe', 'perizinan');
        $status = $request->get('status', 'menunggu');

        // Perizinan
        $perizinan = Perizinan::with('karyawan')
            ->when($status !== 'semua', fn($q) => $q->where('status_approval', $status))
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'page_perizinan');

        // Lembur
        $lembur = Lembur::with('karyawan')
            ->when($status !== 'semua', fn($q) => $q->where('status_approval', $status))
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'page_lembur');

        // Dinas Luar
        $dinasLuar = DinasLuar::with('karyawan')
            ->when($status !== 'semua', fn($q) => $q->where('status_approval', $status))
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'page_dinas');

        // Jumlah menunggu untuk badge
        $jumlahMenunggu = [
            'perizinan' => Perizinan::where('status_approval', 'menunggu')->count(),
            'lembur'    => Lembur::where('status_approval', 'menunggu')->count(),
            'dinas_luar'=> DinasLuar::where('status_approval', 'menunggu')->count(),
        ];

        return view('pimpinan.approval.index', compact(
            'perizinan', 'lembur', 'dinasLuar',
            'jumlahMenunggu', 'tipe', 'status'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // DETAIL PERIZINAN
    // ─────────────────────────────────────────────────────────────────
    public function showPerizinan(Perizinan $perizinan)
    {
        $perizinan->load('karyawan.user');
        return view('pimpinan.approval.show-perizinan', compact('perizinan'));
    }

    // ─────────────────────────────────────────────────────────────────
    // APPROVE PERIZINAN
    // ─────────────────────────────────────────────────────────────────
    public function approvePerizinan(Request $request, Perizinan $perizinan)
    {
        if ($perizinan->status_approval !== 'menunggu') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $perizinan->update([
            'status_approval' => 'disetujui',
            'approved_by'     => Auth::user()->id,
            'approved_at'     => now(),
            'alasan_tolak'    => null,
        ]);

        // Proses efek sesuai jenis izin
        $this->prosesEfekPerizinan($perizinan);

        // Kirim email notifikasi ke karyawan
        $this->kirimEmailPerizinan($perizinan, 'disetujui');

        return back()->with('success',
            "Pengajuan {$perizinan->labelJenis()} {$perizinan->karyawan->nama} berhasil disetujui."
        );
    }

    // ─────────────────────────────────────────────────────────────────
    // TOLAK PERIZINAN
    // ─────────────────────────────────────────────────────────────────
    public function tolakPerizinan(Request $request, Perizinan $perizinan)
    {
        $request->validate([
            'alasan_tolak' => 'required|string|min:10|max:500',
        ], [
            'alasan_tolak.required' => 'Alasan penolakan wajib diisi.',
            'alasan_tolak.min'      => 'Alasan penolakan minimal 10 karakter.',
        ]);

        if ($perizinan->status_approval !== 'menunggu') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $perizinan->update([
            'status_approval' => 'ditolak',
            'approved_by'     => Auth::user()->id,
            'approved_at'     => now(),
            'alasan_tolak'    => $request->alasan_tolak,
        ]);

        // Kirim email notifikasi ke karyawan
        $this->kirimEmailPerizinan($perizinan, 'ditolak', $request->alasan_tolak);

        return back()->with('success',
            "Pengajuan {$perizinan->labelJenis()} {$perizinan->karyawan->nama} telah ditolak."
        );
    }

    // ─────────────────────────────────────────────────────────────────
    // APPROVE LEMBUR
    // ─────────────────────────────────────────────────────────────────
    public function approveLembur(Request $request, Lembur $lembur)
    {
        if ($lembur->status_approval !== 'menunggu') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $lembur->update([
            'status_approval' => 'disetujui',
            'approved_by'     => Auth::user()->id,
            'approved_at'     => now(),
        ]);

        $this->kirimEmailLembur($lembur, 'disetujui');

        return back()->with('success',
            "Pengajuan lembur {$lembur->karyawan->nama} berhasil disetujui."
        );
    }

    // ─────────────────────────────────────────────────────────────────
    // TOLAK LEMBUR
    // ─────────────────────────────────────────────────────────────────
    public function tolakLembur(Request $request, Lembur $lembur)
    {
        $request->validate([
            'alasan_tolak' => 'required|string|min:10|max:500',
        ]);

        if ($lembur->status_approval !== 'menunggu') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $lembur->update([
            'status_approval' => 'ditolak',
            'approved_by'     => Auth::user()->id,
            'approved_at'     => now(),
            'alasan_tolak'    => $request->alasan_tolak,
        ]);

        $this->kirimEmailLembur($lembur, 'ditolak', $request->alasan_tolak);

        return back()->with('success',
            "Pengajuan lembur {$lembur->karyawan->nama} telah ditolak."
        );
    }

    // ─────────────────────────────────────────────────────────────────
    // APPROVE DINAS LUAR
    // ─────────────────────────────────────────────────────────────────
    public function approveDinas(Request $request, DinasLuar $dinasLuar)
    {
        if ($dinasLuar->status_approval !== 'menunggu') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $dinasLuar->update([
            'status_approval' => 'disetujui',
            'approved_by'     => Auth::user()->id,
            'approved_at'     => now(),
        ]);

        $this->kirimEmailDinas($dinasLuar, 'disetujui');

        return back()->with('success',
            "Pengajuan dinas luar kota {$dinasLuar->karyawan->nama} berhasil disetujui."
        );
    }

    // ─────────────────────────────────────────────────────────────────
    // TOLAK DINAS LUAR
    // ─────────────────────────────────────────────────────────────────
    public function tolakDinas(Request $request, DinasLuar $dinasLuar)
    {
        $request->validate([
            'alasan_tolak' => 'required|string|min:10|max:500',
        ]);

        if ($dinasLuar->status_approval !== 'menunggu') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $dinasLuar->update([
            'status_approval' => 'ditolak',
            'approved_by'     => Auth::user()->id,
            'approved_at'     => now(),
            'alasan_tolak'    => $request->alasan_tolak,
        ]);

        $this->kirimEmailDinas($dinasLuar, 'ditolak', $request->alasan_tolak);

        return back()->with('success',
            "Pengajuan dinas luar kota {$dinasLuar->karyawan->nama} telah ditolak."
        );
    }

    // ─────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────

    /**
     * Proses efek approval perizinan ke data absensi & kuota cuti.
     */
    private function prosesEfekPerizinan(Perizinan $perizinan): void
    {
        $karyawan = $perizinan->karyawan;
        $mulai    = Carbon::parse($perizinan->tanggal_mulai);
        $selesai  = Carbon::parse($perizinan->tanggal_selesai);

        // Tentukan status absensi berdasarkan jenis izin
        $statusAbsensi = match ($perizinan->jenis_izin) {
            'cuti'           => 'cuti',
            'izin_pribadi'   => 'izin',
            'sakit_surat',
            'sakit_no_surat' => 'sakit',
            default          => 'izin',
        };

        // Buat record absensi untuk setiap hari izin
        $tanggal = $mulai->copy();
        while ($tanggal->lte($selesai)) {
            Absensi::updateOrCreate(
                [
                    'karyawan_id' => $karyawan->id,
                    'tanggal'     => $tanggal->toDateString(),
                ],
                [
                    'status'   => $statusAbsensi,
                    'is_telat' => false,
                ]
            );
            $tanggal->addDay();
        }

        // Kurangi kuota cuti jika jenis izin memotong cuti
        if ($perizinan->memotongCuti()) {
            $kuota = KuotaCuti::where('karyawan_id', $karyawan->id)
                              ->where('tahun', now()->year)
                              ->first();
            if ($kuota) {
                $kuota->pakai($perizinan->jumlah_hari);
            }
        }
    }

    /**
     * Kirim email notifikasi approval perizinan.
     */
    private function kirimEmailPerizinan(
        Perizinan $perizinan,
        string $status,
        ?string $alasan = null
    ): void {
        $email = $perizinan->karyawan->email;
        if (!$email) return;

        $keterangan = $perizinan->labelJenis() .
            ", {$perizinan->jumlah_hari} hari " .
            "({$perizinan->tanggal_mulai->format('d M Y')} — {$perizinan->tanggal_selesai->format('d M Y')})";

        try {
            Mail::to($email)->send(new NotifikasiApproval(
                namaKaryawan:   $perizinan->karyawan->nama,
                jenisAjuan:     $perizinan->labelJenis(),
                statusApproval: $status,
                keterangan:     $keterangan,
                alasanTolak:    $alasan,
            ));
        } catch (\Exception $e) {
            // Log error tapi jangan gagalkan proses approval
            \Log::error('Gagal kirim email perizinan: ' . $e->getMessage());
        }
    }

    /**
     * Kirim email notifikasi approval lembur.
     */
    private function kirimEmailLembur(
        Lembur $lembur,
        string $status,
        ?string $alasan = null
    ): void {
        $email = $lembur->karyawan->email;
        if (!$email) return;

        $keterangan = "Lembur tanggal {$lembur->tanggal->format('d M Y')}, " .
            "{$lembur->jam_mulai} — {$lembur->jam_selesai} " .
            "({$lembur->formatDurasi()})";

        try {
            Mail::to($email)->send(new NotifikasiApproval(
                namaKaryawan:   $lembur->karyawan->nama,
                jenisAjuan:     'Lembur',
                statusApproval: $status,
                keterangan:     $keterangan,
                alasanTolak:    $alasan,
            ));
        } catch (\Exception $e) {
            \Log::error('Gagal kirim email lembur: ' . $e->getMessage());
        }
    }

    /**
     * Kirim email notifikasi approval dinas luar.
     */
    private function kirimEmailDinas(
        DinasLuar $dinasLuar,
        string $status,
        ?string $alasan = null
    ): void {
        $email = $dinasLuar->karyawan->email;
        if (!$email) return;

        $keterangan = "Dinas luar kota ke {$dinasLuar->tujuan}, " .
            "{$dinasLuar->tanggal_berangkat->format('d M Y')} — " .
            "{$dinasLuar->tanggal_kembali->format('d M Y')}";

        try {
            Mail::to($email)->send(new NotifikasiApproval(
                namaKaryawan:   $dinasLuar->karyawan->nama,
                jenisAjuan:     'Dinas Luar Kota',
                statusApproval: $status,
                keterangan:     $keterangan,
                alasanTolak:    $alasan,
            ));
        } catch (\Exception $e) {
            \Log::error('Gagal kirim email dinas luar: ' . $e->getMessage());
        }
    }
}