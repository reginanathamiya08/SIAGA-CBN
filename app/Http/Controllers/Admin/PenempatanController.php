<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\Mitra;
use App\Models\Penempatan;
use Illuminate\Http\Request;

class PenempatanController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    // INDEX - Daftar semua penempatan aktif
    // ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Penempatan::with(['karyawan','mitra'])
                           ->orderBy('created_at','desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('mitra_id')) {
            $query->where('mitra_id', $request->mitra_id);
        }
        if ($request->filled('cari')) {
            $query->whereHas('karyawan', fn($q) =>
                $q->where('nama','LIKE','%'.$request->cari.'%')
            );
        }

        $penempatan = $query->paginate(15)->withQueryString();

        // Data untuk filter dropdown
        $daftarMitra = Mitra::orderBy('nama_mitra')->get(['id','nama_mitra','is_cabang']);

        // Statistik
        $stats = [
            'aktif'    => Penempatan::where('status','aktif')->count(),
            'selesai'  => Penempatan::where('status','selesai')->count(),
            'tersedia' => Karyawan::where('jenis_karyawan','kontrak')
                                  ->where('is_active', true)
                                  ->whereDoesntHave('penempatan', fn($q) =>
                                      $q->where('status','aktif')
                                  )->count(),
        ];

        return view('admin.penempatan.index', compact(
            'penempatan', 'daftarMitra', 'stats'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // CREATE - Form plotting karyawan ke mitra
    // ─────────────────────────────────────────────────────────────────
    public function create(Request $request)
    {
        // Karyawan kontrak yang belum punya penempatan aktif
        $karyawanTersedia = Karyawan::with('user')
            ->where('jenis_karyawan','kontrak')
            ->where('is_active', true)
            ->whereDoesntHave('penempatan', fn($q) => $q->where('status','aktif'))
            ->orderBy('nama')
            ->get();

        // Semua mitra (induk + cabang)
        $daftarMitra = Mitra::with('induk')
                            ->orderBy('is_cabang')
                            ->orderBy('nama_mitra')
                            ->get();

        // Jika ada karyawan yang dipilih dari halaman lain (misal dari pool di dashboard)
        $karyawanDipilih = $request->filled('karyawan_id')
            ? Karyawan::find($request->karyawan_id)
            : null;

        return view('admin.penempatan.create', compact(
            'karyawanTersedia', 'daftarMitra', 'karyawanDipilih'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // STORE - Simpan penempatan baru
    // ─────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'karyawan_id'   => 'required|exists:karyawan,id',
            'mitra_id'      => 'required|exists:mitra,id',
            'tanggal_mulai' => 'required|date',
        ], [
            'karyawan_id.required'   => 'Karyawan wajib dipilih.',
            'karyawan_id.exists'     => 'Karyawan tidak ditemukan.',
            'mitra_id.required'      => 'Mitra wajib dipilih.',
            'mitra_id.exists'        => 'Mitra tidak ditemukan.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_mulai.date'     => 'Format tanggal tidak valid.',
        ]);

        // Cek apakah karyawan sudah punya penempatan aktif
        $sudahAktif = Penempatan::where('karyawan_id', $request->karyawan_id)
                                ->where('status','aktif')
                                ->exists();

        if ($sudahAktif) {
            return back()->withInput()
                ->with('error', 'Karyawan ini sudah memiliki penempatan aktif. Selesaikan penempatan lama terlebih dahulu.');
        }

        Penempatan::create([
            'karyawan_id'   => $request->karyawan_id,
            'mitra_id'      => $request->mitra_id,
            'tanggal_mulai' => $request->tanggal_mulai,
            'status'        => 'aktif',
        ]);

        return redirect()
            ->route('admin.penempatan.index')
            ->with('success', 'Karyawan berhasil ditempatkan ke mitra.');
    }

    // ─────────────────────────────────────────────────────────────────
    // SELESAI - Akhiri penempatan aktif
    // ─────────────────────────────────────────────────────────────────
    public function selesai(Request $request, Penempatan $penempatan)
    {
        $request->validate([
            'tanggal_selesai' => 'required|date|after_or_equal:' . $penempatan->tanggal_mulai,
        ], [
            'tanggal_selesai.required'         => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal'   => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ]);

        $penempatan->update([
            'status'          => 'selesai',
            'tanggal_selesai' => $request->tanggal_selesai,
        ]);

        return back()->with('success', 'Penempatan berhasil diakhiri.');
    }
}