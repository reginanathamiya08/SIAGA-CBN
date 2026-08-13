<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\SlipGajiPeriode;
use Illuminate\Support\Facades\Auth;

class SlipGajiController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    // INDEX - Daftar slip gaji milik karyawan yang login
    // ─────────────────────────────────────────────────────────────────
    public function index()
    {
        $karyawan = Auth::user()->karyawan;

        $slipGaji = SlipGajiPeriode::with('periodeGaji')
                            ->join('periode_gaji', 'periode_gaji.id', '=', 'slip_gaji_periode.periode_id')
                            ->where('slip_gaji_periode.user_id', $karyawan->id)
                            ->where('slip_gaji_periode.status', 'diterbitkan')
                            ->orderBy('periode_gaji.tanggal_mulai', 'desc')
                            ->select('slip_gaji_periode.*')
                            ->paginate(12);

        return view('karyawan.slip-gaji.index', compact('slipGaji', 'karyawan'));
    }

    // ─────────────────────────────────────────────────────────────────
    // SHOW - Detail slip gaji (bisa dicetak)
    // ─────────────────────────────────────────────────────────────────
    public function show(SlipGajiPeriode $slipGaji)
    {
        $karyawan = Auth::user();

        // Pastikan slip ini milik karyawan yang login
        if ($slipGaji->user_id !== $karyawan->id) {
            abort(403, 'Akses ditolak.');
        }

        $slipGaji->load(['karyawan', 'karyawan.komponenGaji', 'periodeGaji']);

        return view('karyawan.slip-gaji.show', compact('slipGaji'));
    }

    public function officialSlip(SlipGajiPeriode $slipGaji)
    {
        $karyawan = Auth::user();

        if ($slipGaji->user_id !== $karyawan->id) {
            abort(403, 'Akses ditolak.');
        }

        $slipGaji->load(['karyawan', 'karyawan.komponenGaji', 'periodeGaji']);

        $admUmum = \App\Models\User::where('jabatan', 'Staff Administrasi & Umum')
                                    ->where('nama', '!=', 'Administrator Utama')
                                    ->first();

        return view('admin.penggajian.slip_official', compact('slipGaji', 'admUmum'));
    }
}
