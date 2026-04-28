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

        $lembur = Lembur::where('karyawan_id', $karyawan->id)
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
        $sudahAda = Lembur::where('karyawan_id', $karyawan->id)
                          ->where('tanggal', $data['tanggal'])
                          ->whereIn('status_approval', ['menunggu', 'disetujui'])
                          ->exists();

        if ($sudahAda) {
            return back()->withInput()
                ->with('error', 'Sudah ada pengajuan lembur pada tanggal tersebut yang masih aktif.');
        }

        Lembur::create([
            'karyawan_id'     => $karyawan->id,
            'tanggal'         => $data['tanggal'],
            'jam_mulai'       => $data['jam_mulai'],
            'jam_selesai'     => $data['jam_selesai'],
            'total_jam'       => $totalJam,
            'keterangan'      => $data['keterangan'] ?? null,
            'status_approval' => 'menunggu',
        ]);

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

        if ($lembur->karyawan_id !== $karyawan->id) {
            abort(403);
        }

        return view('karyawan.lembur.show', compact('lembur'));
    }

    // ─────────────────────────────────────────────────────────────────
    // DESTROY - Batalkan pengajuan (hanya yang masih menunggu)
    // ─────────────────────────────────────────────────────────────────
    public function destroy(Lembur $lembur)
    {
        $karyawan = Auth::user()->karyawan;

        if ($lembur->karyawan_id !== $karyawan->id) {
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