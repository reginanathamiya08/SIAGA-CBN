<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\SlipGaji;
use Illuminate\Support\Facades\Auth;

class SlipGajiController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    // INDEX - Daftar slip gaji milik karyawan yang login
    // ─────────────────────────────────────────────────────────────────
    public function index()
    {
        $karyawan = Auth::user()->karyawan;

        $slipGaji = SlipGaji::with('periodeGaji')
                            ->join('periode_gaji', 'periode_gaji.id', '=', 'slip_gaji.periode_id')
                            ->where('slip_gaji.karyawan_id', $karyawan->id)
                            ->where('slip_gaji.status', 'diterbitkan')
                            ->orderBy('periode_gaji.tanggal_mulai', 'desc')
                            ->select('slip_gaji.*')
                            ->paginate(12);

        return view('karyawan.slip-gaji.index', compact('slipGaji', 'karyawan'));
    }

    // ─────────────────────────────────────────────────────────────────
    // SHOW - Detail slip gaji (bisa dicetak)
    // ─────────────────────────────────────────────────────────────────
    public function show(SlipGaji $slipGaji)
    {
        $karyawan = Auth::user()->karyawan;

        // Pastikan slip ini milik karyawan yang login
        if ($slipGaji->karyawan_id !== $karyawan->id) {
            abort(403, 'Akses ditolak.');
        }

        $slipGaji->load(['karyawan.user', 'karyawan.komponenGaji', 'periodeGaji']);

        return view('karyawan.slip-gaji.show', compact('slipGaji'));
    }
}