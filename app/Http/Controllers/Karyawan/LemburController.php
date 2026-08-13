<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Karyawan\StoreLemburRequest;
use App\Models\Lembur;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LemburController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    // INDEX - Daftar pengajuan lembur milik karyawan login
    // ─────────────────────────────────────────────────────────────────
    public function index()
    {
        $karyawan = Auth::user()->karyawan;

        // Hanya karyawan tetap yang bisa lembur
        if (!$karyawan->isTetap()) {
            return redirect()->route('karyawan.dashboard')
                ->with('error', 'Fitur lembur hanya tersedia untuk karyawan tetap CBN.');
        }

        $lembur = Lembur::where('user_id', $karyawan->id)
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);

        return view('karyawan.lembur.index', compact('lembur', 'karyawan'));
    }

    // ─────────────────────────────────────────────────────────────────
    // CREATE - Form ajukan lembur
    // ─────────────────────────────────────────────────────────────────
    public function create()
    {
        $karyawan = Auth::user()->karyawan;

        if (!$karyawan->isTetap()) {
            return redirect()->route('karyawan.dashboard')
                ->with('error', 'Fitur lembur hanya tersedia untuk karyawan tetap CBN.');
        }

        return view('karyawan.lembur.create', compact('karyawan'));
    }

    // ─────────────────────────────────────────────────────────────────
    // STORE - Simpan pengajuan lembur
    // ─────────────────────────────────────────────────────────────────
    public function store(StoreLemburRequest $request)
    {
        $karyawan = Auth::user()->karyawan;
        $data     = $request->validated();

        // Hitung total jam lembur
        $mulai   = Carbon::parse($data['tanggal'] . ' ' . $data['jam_mulai']);
        $selesai = Carbon::parse($data['tanggal'] . ' ' . $data['jam_selesai']);

        // Jika jam selesai < jam mulai, berarti melewati tengah malam
        if ($selesai->lt($mulai)) {
            $selesai->addDay();
        }

        $totalJam = round($mulai->diffInMinutes($selesai) / 60, 2);

        // Cek apakah sudah ada pengajuan lembur pada tanggal yang sama
        $sudahAda = Lembur::where('user_id', $karyawan->id)
                          ->where('tanggal', $data['tanggal'])
                          ->whereIn('status_approval', ['menunggu', 'disetujui'])
                          ->exists();

        if ($sudahAda) {
            return back()->withInput()
                ->with('error', 'Sudah ada pengajuan lembur pada tanggal tersebut yang masih aktif.');
        }

        Lembur::create([
            'user_id'     => $karyawan->id,
            'tanggal'         => $data['tanggal'],
            'jam_mulai'       => $data['jam_mulai'],
            'jam_selesai'     => $data['jam_selesai'],
            'total_jam'       => $totalJam,
            'keterangan'      => $data['keterangan'] ?? null,
            'status_approval' => 'menunggu',
        ]);

        // Kirim notifikasi ke Pimpinan
        $pimpinans = \App\Models\User::whereHas('role', fn($q) => $q->where('slug', 'pimpinan'))->get();
        foreach ($pimpinans as $pimpinan) {
            \App\Models\Notification::send(
                $pimpinan->id,
                'Persetujuan Lembur Baru ⏱️',
                "Karyawan {$karyawan->nama} mengajukan lembur pada tanggal " . date('d/m/Y', strtotime($data['tanggal'])) . " ({$totalJam} jam).",
                'warning',
                route('pimpinan.approval.index')
            );
        }

        return redirect()
            ->route('karyawan.lembur.index')
            ->with('success', 'Pengajuan lembur berhasil dikirim. Menunggu persetujuan pimpinan.');
    }

    // ─────────────────────────────────────────────────────────────────
    // SHOW - Detail lembur
    // ─────────────────────────────────────────────────────────────────
    public function show(Lembur $lembur)
    {
        $karyawan = Auth::user()->karyawan;

        if ($lembur->user_id !== $karyawan->id) {
            abort(403);
        }

        return view('karyawan.lembur.show', compact('lembur'));
    }

    // ─────────────────────────────────────────────────────────────────
    // PRINT SLIP - Cetak Slip Lembur (H+1)
    // ─────────────────────────────────────────────────────────────────
    public function printSlip(Lembur $lembur)
    {
        $authUser = Auth::user();

        // Admin, Pimpinan, dan pemilik pengajuan lembur yang bersangkutan diizinkan melihat/mencetak
        if (!$authUser->isAdmin() && !$authUser->isPimpinan() && $lembur->user_id !== $authUser->id) {
            abort(403);
        }

        if ($lembur->status_approval !== 'disetujui') {
            if ($authUser->isKaryawan()) {
                return redirect()->route('karyawan.lembur.show', $lembur->id)
                    ->with('error', 'Slip lembur hanya dapat dicetak untuk pengajuan yang telah disetujui.');
            }
            abort(400, 'Slip lembur hanya dapat dicetak untuk pengajuan yang telah disetujui.');
        }

        // Hitung rincian nominal lembur
        $user = $lembur->karyawan; // Relasi ke User
        $kg = $user->komponenGaji;
        $gajiPokok = $kg ? (float) $kg->gaji_pokok : 0.0;
        $upahPerJam = floor($gajiPokok / 173);

        $isHoliday = \App\Helpers\AttendanceHelper::isHolidayOrWeekend($lembur->tanggal);
        $jamLembur = (float) $lembur->total_jam;

        $upahLembur = 0.0;
        $uangMakanLembur = 0.0;
        $breakdown = [];

        if ($isHoliday) {
            // Hari Libur
            if ($jamLembur <= 8) {
                $upahLembur = round(2.0 * $upahPerJam);
                $breakdown[] = "Jam 1 s.d 8 (Flat 2x): 2.0 x Rp " . number_format($upahPerJam, 0, ',', '.') . " = Rp " . number_format($upahLembur, 0, ',', '.');
            } elseif ($jamLembur == 9) {
                $upahLembur = round(5.0 * $upahPerJam);
                $breakdown[] = "Jam ke-9 (Flat 5x): 5.0 x Rp " . number_format($upahPerJam, 0, ',', '.') . " = Rp " . number_format($upahLembur, 0, ',', '.');
            } else {
                $upahLembur = round(9.0 * $upahPerJam);
                $breakdown[] = "Jam 10 ke atas (Flat 9x): 9.0 x Rp " . number_format($upahPerJam, 0, ',', '.') . " = Rp " . number_format($upahLembur, 0, ',', '.');
            }
            $uangMakanLembur = 40000.0; // Flat Uang Makan Lembur Hari Libur
        } else {
            // Hari Kerja
            $jamPertama = min(1.0, $jamLembur);
            $jamSisa = max(0.0, $jamLembur - 1.0);
            
            $nominalJamPertama = round($jamPertama * 1.5 * $upahPerJam);
            $nominalJamSisa = round($jamSisa * 2.0 * $upahPerJam);
            $upahLembur = $nominalJamPertama + $nominalJamSisa;

            if ($jamPertama > 0) {
                $breakdown[] = "1 Jam Pertama (1.5x): {$jamPertama} jam x 1.5 x Rp " . number_format($upahPerJam, 0, ',', '.') . " = Rp " . number_format($nominalJamPertama, 0, ',', '.');
            }
            if ($jamSisa > 0) {
                $breakdown[] = "Jam Selanjutnya (2.0x): {$jamSisa} jam x 2.0 x Rp " . number_format($upahPerJam, 0, ',', '.') . " = Rp " . number_format($nominalJamSisa, 0, ',', '.');
            }
        }

        $totalDibayarkan = $upahLembur + $uangMakanLembur;

        return view('karyawan.lembur.print_slip', compact(
            'lembur',
            'user',
            'upahPerJam',
            'isHoliday',
            'upahLembur',
            'uangMakanLembur',
            'breakdown',
            'totalDibayarkan'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // DESTROY - Batalkan pengajuan (hanya yang masih menunggu)
    // ─────────────────────────────────────────────────────────────────
    public function destroy(Lembur $lembur)
    {
        $karyawan = Auth::user()->karyawan;

        if ($lembur->user_id !== $karyawan->id) {
            abort(403);
        }

        if ($lembur->status_approval !== 'menunggu') {
            return back()->with('error', 'Hanya pengajuan yang masih menunggu yang bisa dibatalkan.');
        }

        $lembur->delete();

        return redirect()
            ->route('karyawan.lembur.index')
            ->with('success', 'Pengajuan lembur berhasil dibatalkan.');
    }
}
