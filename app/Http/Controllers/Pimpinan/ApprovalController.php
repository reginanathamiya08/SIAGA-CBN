<?php
 
namespace App\Http\Controllers\Pimpinan;
 
use App\Http\Controllers\Controller;
use App\Models\DetailPerizinan;
use App\Models\Lembur;
use App\Models\Absensi;
use App\Models\KuotaPerizinan;
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
                $slugTarget = in_array($jenis, ['tetap', 'karyawan_tetap', 'JNS-00001']) ? 'karyawan_tetap' : 'karyawan_kontrak';
                $q->whereHas('karyawan', fn($query) => $query->whereHas('role', fn($r) => $r->where('slug', $slugTarget)));
            }
        };
 
        // Perizinan
        $perizinan = DetailPerizinan::with(['karyawan.role', 'jenisPerizinan', 'rekanKerja'])
            ->where($filterJenis)
            ->when($status !== 'semua', fn($q) => $q->where('status_approval', $status))
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'page_p');
 
        // Lembur
        $lembur = Lembur::with(['karyawan.role'])
            ->where($filterJenis)
            ->when($status !== 'semua', fn($q) => $q->where('status_approval', $status))
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'page_l');
 
        $jumlahMenunggu = [
            'perizinan' => DetailPerizinan::where('status_approval', 'menunggu')->count(),
            'lembur'    => Lembur::where('status_approval', 'menunggu')->count(),
        ];

        if ($tipe === 'perizinan') {
            $countTetap   = DetailPerizinan::when($status !== 'semua', fn($q) => $q->where('status_approval', $status))
                ->whereHas('karyawan', fn($q) => $q->whereHas('role', fn($r) => $r->where('slug', 'karyawan_tetap')))
                ->count();
            $countKontrak = DetailPerizinan::when($status !== 'semua', fn($q) => $q->where('status_approval', $status))
                ->whereHas('karyawan', fn($q) => $q->whereHas('role', fn($r) => $r->where('slug', 'karyawan_kontrak')))
                ->count();
        } else {
            $countTetap   = Lembur::when($status !== 'semua', fn($q) => $q->where('status_approval', $status))
                ->whereHas('karyawan', fn($q) => $q->whereHas('role', fn($r) => $r->where('slug', 'karyawan_tetap')))
                ->count();
            $countKontrak = Lembur::when($status !== 'semua', fn($q) => $q->where('status_approval', $status))
                ->whereHas('karyawan', fn($q) => $q->whereHas('role', fn($r) => $r->where('slug', 'karyawan_kontrak')))
                ->count();
        }
        $countSemua = $countTetap + $countKontrak;

        $countQuery = function($statusVal) use ($tipe, $filterJenis) {
            if ($tipe === 'perizinan') {
                return DetailPerizinan::where($filterJenis)
                    ->when($statusVal !== 'semua', fn($q) => $q->where('status_approval', $statusVal))
                    ->count();
            } else {
                return Lembur::where($filterJenis)
                    ->when($statusVal !== 'semua', fn($q) => $q->where('status_approval', $statusVal))
                    ->count();
            }
        };

        $countMenunggu = $countQuery('menunggu');
        $countDisetujui = $countQuery('disetujui');
        $countDitolak   = $countQuery('ditolak');
        $countSemuaStatus = $countQuery('semua');

        return view('pimpinan.approval.index', compact(
            'perizinan', 'lembur',
            'jumlahMenunggu', 'tipe', 'status', 'jenis',
            'countTetap', 'countKontrak', 'countSemua',
            'countMenunggu', 'countDisetujui', 'countDitolak', 'countSemuaStatus'
        ));
    }

    public function showPerizinan(DetailPerizinan $perizinan)
    {
        $perizinan->load(['karyawan', 'jenisPerizinan']);
        return view('pimpinan.approval.show-perizinan', compact('perizinan'));
    }

    // ─────────────────────────────────────────────────────────────────
    // PERIZINAN
    // ─────────────────────────────────────────────────────────────────
    public function approvePerizinan(Request $request, DetailPerizinan $perizinan)
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

        \App\Models\Notification::send(
            $perizinan->user_id,
            'Izin Disetujui ✅',
            "Pengajuan {$perizinan->labelJenis()} Anda pada tanggal {$perizinan->tanggal_mulai->format('d M Y')} telah disetujui.",
            'success',
            route('karyawan.perizinan.show', $perizinan->id, false)
        );

        return back()->with('success', "Pengajuan {$perizinan->labelJenis()} {$perizinan->karyawan->nama} berhasil disetujui.");
    }

    public function tolakPerizinan(Request $request, DetailPerizinan $perizinan)
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

        \App\Models\Notification::send(
            $perizinan->user_id,
            'Izin Ditolak ❌',
            "Pengajuan {$perizinan->labelJenis()} Anda ditolak. Alasan: {$request->alasan_tolak}",
            'danger',
            route('karyawan.perizinan.show', $perizinan->id, false)
        );

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

        \App\Models\Notification::send(
            $lembur->user_id,
            'Lembur Disetujui ✅',
            "Pengajuan lembur Anda pada tanggal {$lembur->tanggal->format('d M Y')} telah disetujui.",
            'success',
            route('karyawan.lembur.index', [], false)
        );

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

        \App\Models\Notification::send(
            $lembur->user_id,
            'Lembur Ditolak ❌',
            "Pengajuan lembur Anda ditolak. Alasan: {$request->alasan_tolak}",
            'danger',
            route('karyawan.lembur.index', [], false)
        );

        return back()->with('success', "Pengajuan lembur {$lembur->karyawan->nama} telah ditolak.");
    }

    // ─────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────

    private function prosesEfekPerizinan(DetailPerizinan $perizinan): void
    {
        $karyawan = $perizinan->karyawan;
        $mulai    = Carbon::parse($perizinan->tanggal_mulai);
        $selesai  = Carbon::parse($perizinan->tanggal_selesai);

        $statusAbsensi = match ($perizinan->jenisPerizinan->slug) {
            'cuti'           => 'cuti',
            'izin_pribadi'   => 'izin',
            'sakit_surat',
            'sakit_no_surat' => 'sakit',
            'dinas_luar'     => 'dinas_luar',
            default          => 'izin',
        };

        // Cari mitra_id aktif untuk karyawan ini
        $mitraId = null;
        if ($karyawan->isTetap()) {
            // Karyawan Tetap -> Cari mitra pusat (PT CBN)
            $mitraPusat = \App\Models\Mitra::where('is_pusat', true)->first();
            if ($mitraPusat) {
                $mitraId = $mitraPusat->id;
            }
        } else {
            // Karyawan Kontrak -> Cari dari penempatan aktif
            $penempatan = $karyawan->penempatanAktif()->first();
            if ($penempatan) {
                $mitraId = $penempatan->mitra_id;
            }
        }

        $tanggal = $mulai->copy();
        while ($tanggal->lte($selesai)) {
            Absensi::updateOrCreate(
                ['user_id' => $karyawan->id, 'tanggal' => $tanggal->toDateString()],
                ['status' => $statusAbsensi, 'is_telat' => false, 'mitra_id' => $mitraId]
            );
            $tanggal->addDay();
        }

        if ($perizinan->memotongCuti()) {
            $perizinan->kuotaPerizinan->pakai($perizinan->jumlah_hari);
        }
    }

    private function kirimEmailPerizinan(DetailPerizinan $perizinan, string $status, ?string $alasan = null): void 
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
}
