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
    // INDEX - Semua pengajuan masuk dengan filter terpadu
    // ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $tipe   = $request->get('tipe', 'perizinan');
        $status = $request->get('status', 'menunggu');
        $jenis  = $request->get('jenis', 'semua'); // Filter baru: tetap/kontrak/semua

        // Base Query builder untuk filter jenis karyawan
        $filterJenis = function($q) use ($jenis) {
            if ($jenis !== 'semua') {
                $q->whereHas('karyawan', fn($query) => $query->where('jenis_karyawan', $jenis));
            }
        };

        // Perizinan
        $perizinan = Perizinan::with('karyawan')
            ->where($filterJenis)
            ->when($status !== 'semua', fn($q) => $q->where('status_approval', $status))
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'page_p');

        // Lembur
        $lembur = Lembur::with('karyawan')
            ->where($filterJenis)
            ->when($status !== 'semua', fn($q) => $q->where('status_approval', $status))
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'page_l');

        // Dinas Luar
        $dinasLuar = DinasLuar::with('karyawan')
            ->where($filterJenis)
            ->when($status !== 'semua', fn($q) => $q->where('status_approval', $status))
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'page_d');

        $jumlahMenunggu = [
            'perizinan' => Perizinan::where('status_approval', 'menunggu')->count(),
            'lembur'    => Lembur::where('status_approval', 'menunggu')->count(),
            'dinas_luar'=> DinasLuar::where('status_approval', 'menunggu')->count(),
        ];

        return view('pimpinan.approval.index', compact(
            'perizinan', 'lembur', 'dinasLuar',
            'jumlahMenunggu', 'tipe', 'status', 'jenis'
        ));
    }

    public function showPerizinan(Perizinan $perizinan)
    {
        $perizinan->load('karyawan.user');
        return view('pimpinan.approval.show-perizinan', compact('perizinan'));
    }

    // ─────────────────────────────────────────────────────────────────
    // PERIZINAN
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
        ]);

        $this->prosesEfekPerizinan($perizinan);
        $this->kirimEmailPerizinan($perizinan, 'disetujui');

        return back()->with('success', "Pengajuan {$perizinan->labelJenis()} {$perizinan->karyawan->nama} berhasil disetujui.");
    }

    public function tolakPerizinan(Request $request, Perizinan $perizinan)
    {
        $request->validate(['alasan_tolak' => 'required|string|min:5|max:500']);

        if ($perizinan->status_approval !== 'menunggu') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $perizinan->update([
            'status_approval' => 'ditolak',
            'approved_by'     => Auth::user()->id,
            'approved_at'     => now(),
            'alasan_tolak'    => $request->alasan_tolak,
        ]);

        $this->kirimEmailPerizinan($perizinan, 'ditolak', $request->alasan_tolak);

        return back()->with('success', "Pengajuan {$perizinan->labelJenis()} {$perizinan->karyawan->nama} telah ditolak.");
    }

    // ─────────────────────────────────────────────────────────────────
    // LEMBUR
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

        return back()->with('success', "Pengajuan lembur {$lembur->karyawan->nama} berhasil disetujui.");
    }

    public function tolakLembur(Request $request, Lembur $lembur)
    {
        $request->validate(['alasan_tolak' => 'required|string|min:5|max:500']);

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

        return back()->with('success', "Pengajuan lembur {$lembur->karyawan->nama} telah ditolak.");
    }

    // ─────────────────────────────────────────────────────────────────
    // DINAS LUAR
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

        $this->prosesEfekDinasLuar($dinasLuar);
        $this->kirimEmailDinas($dinasLuar, 'disetujui');

        return back()->with('success', "Pengajuan dinas luar kota {$dinasLuar->karyawan->nama} berhasil disetujui.");
    }

    public function tolakDinas(Request $request, DinasLuar $dinasLuar)
    {
        $request->validate(['alasan_tolak' => 'required|string|min:5|max:500']);

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

        return back()->with('success', "Pengajuan dinas luar kota {$dinasLuar->karyawan->nama} telah ditolak.");
    }

    // ─────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────

    private function prosesEfekPerizinan(Perizinan $perizinan): void
    {
        $karyawan = $perizinan->karyawan;
        $mulai    = Carbon::parse($perizinan->tanggal_mulai);
        $selesai  = Carbon::parse($perizinan->tanggal_selesai);

        $statusAbsensi = match ($perizinan->jenis_izin) {
            'cuti'           => 'cuti',
            'izin_pribadi'   => 'izin',
            'sakit_surat',
            'sakit_no_surat' => 'sakit',
            default          => 'izin',
        };

        $tanggal = $mulai->copy();
        while ($tanggal->lte($selesai)) {
            Absensi::updateOrCreate(
                ['karyawan_id' => $karyawan->id, 'tanggal' => $tanggal->toDateString()],
                ['status' => $statusAbsensi, 'is_telat' => false]
            );
            $tanggal->addDay();
        }

        if ($perizinan->memotongCuti()) {
            $kuota = KuotaCuti::where('karyawan_id', $karyawan->id)
                               ->where('tahun', now()->year)
                               ->first();
            if ($kuota) {
                $kuota->pakai($perizinan->jumlah_hari);
            }
        }
    }

    private function prosesEfekDinasLuar(DinasLuar $dinasLuar): void
    {
        $karyawan = $dinasLuar->karyawan;
        $mulai    = Carbon::parse($dinasLuar->tanggal_berangkat);
        $selesai  = Carbon::parse($dinasLuar->tanggal_kembali);

        $tanggal = $mulai->copy();
        while ($tanggal->lte($selesai)) {
            Absensi::updateOrCreate(
                ['karyawan_id' => $karyawan->id, 'tanggal' => $tanggal->toDateString()],
                ['status' => 'dinas_luar', 'is_telat' => false]
            );
            $tanggal->addDay();
        }
    }

    private function kirimEmailPerizinan(Perizinan $perizinan, string $status, ?string $alasan = null): void 
    {
        $email = $perizinan->karyawan->email;
        if (!$email) return;

        $keterangan = $perizinan->labelJenis() . ", {$perizinan->jumlah_hari} hari ({$perizinan->tanggal_mulai->format('d M Y')} — {$perizinan->tanggal_selesai->format('d M Y')})";

        try {
            Mail::to($email)->send(new NotifikasiApproval($perizinan->karyawan->nama, $perizinan->labelJenis(), $status, $keterangan, $alasan));
        } catch (\Exception $e) {
            \Log::error('Gagal kirim email perizinan: ' . $e->getMessage());
        }
    }

    private function kirimEmailLembur(Lembur $lembur, string $status, ?string $alasan = null): void 
    {
        $email = $lembur->karyawan->email;
        if (!$email) return;

        $keterangan = "Lembur tanggal {$lembur->tanggal->format('d M Y')}, {$lembur->jam_mulai} — {$lembur->jam_selesai} ({$lembur->formatDurasi()})";

        try {
            Mail::to($email)->send(new NotifikasiApproval($lembur->karyawan->nama, 'Lembur', $status, $keterangan, $alasan));
        } catch (\Exception $e) {
            \Log::error('Gagal kirim email lembur: ' . $e->getMessage());
        }
    }

    private function kirimEmailDinas(DinasLuar $dinasLuar, string $status, ?string $alasan = null): void 
    {
        $email = $dinasLuar->karyawan->email;
        if (!$email) return;

        $keterangan = "Dinas luar kota ke {$dinasLuar->tujuan}, {$dinasLuar->tanggal_berangkat->format('d M Y')} — {$dinasLuar->tanggal_kembali->format('d M Y')}";

        try {
            Mail::to($email)->send(new NotifikasiApproval($dinasLuar->karyawan->nama, 'Dinas Luar Kota', $status, $keterangan, $alasan));
        } catch (\Exception $e) {
            \Log::error('Gagal kirim email dinas luar: ' . $e->getMessage());
        }
    }
}