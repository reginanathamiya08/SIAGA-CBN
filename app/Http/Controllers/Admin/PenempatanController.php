<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Mitra;
use App\Models\DetailRiwayatPenempatan;
use Illuminate\Http\Request;

class PenempatanController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    // INDEX - Daftar semua penempatan aktif
    // ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = DetailRiwayatPenempatan::with(['karyawan','mitra'])
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

        // Ambil daftar jabatan unik untuk filter karyawan tetap
        $daftarJabatan = User::where('is_active', true)
                                 ->whereHas('role', fn($q) => $q->where('slug', 'karyawan_tetap'))
                                 ->distinct()
                                 ->pluck('jabatan');

        // Ambil data Karyawan Tetap (Otomatis Pusat)
        $queryTetap = User::where('is_active', true)
                             ->whereHas('role', fn($q) => $q->where('slug', 'karyawan_tetap'));
        
        if ($request->filled('cari')) {
            $queryTetap->where('nama', 'LIKE', '%'.$request->cari.'%');
        }

        // Filter berdasarkan jabatan jika ada
        if ($request->filled('jabatan')) {
            $queryTetap->where('jabatan', $request->jabatan);
        }

        // Karyawan tetap hanya muncul jika filter mitra kosong atau mitra_id adalah Pusat
        $kantorPusat = Mitra::where('is_pusat', true)->first();
        $showTetap = true;
        if ($request->filled('mitra_id') && (!$kantorPusat || $request->mitra_id != $kantorPusat->id)) {
            $showTetap = false;
        }
        if ($request->filled('status') && $request->status !== 'aktif') {
            $showTetap = false;
        }

        $karyawanTetap = $showTetap ? $queryTetap->get() : collect();

        // Data untuk filter dropdown
        $daftarMitra = Mitra::orderByRaw('COALESCE(mitra_induk_id, id), is_cabang ASC, nama_mitra ASC')->get(['id','nama_mitra','is_cabang','is_pusat']);

        // Statistik
        $stats = [
            'aktif'    => DetailRiwayatPenempatan::where('status','aktif')->count() + 
                         User::where('is_active', true)
                             ->whereHas('role', fn($q) => $q->where('slug', 'karyawan_tetap'))
                             ->count(),
            'selesai'  => DetailRiwayatPenempatan::where('status','selesai')->count(),
            'tersedia' => User::whereHas('role', fn($q) => $q->where('slug', 'karyawan_kontrak'))
                                  ->where('is_active', true)
                                  ->whereDoesntHave('penempatan', fn($q) =>
                                      $q->where('status','aktif')
                                  )->count(),
        ];

        return view('admin.penempatan.index', compact(
            'penempatan', 'karyawanTetap', 'kantorPusat', 'daftarMitra', 'daftarJabatan', 'stats'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // CREATE - Form plotting karyawan ke mitra
    // ─────────────────────────────────────────────────────────────────
    public function create(Request $request)
    {
        // Karyawan kontrak yang belum punya penempatan aktif
        $karyawanTersedia = User::whereHas('role', fn($q) => $q->where('slug', 'karyawan_kontrak'))
            ->where('is_active', true)
            ->whereDoesntHave('penempatan', fn($q) => $q->where('status','aktif'))
            ->orderBy('nama')
            ->get();

        $daftarMitra = Mitra::with('cabang')
                            ->orderByRaw('COALESCE(mitra_induk_id, id), is_cabang ASC, nama_mitra ASC')
                            ->get();

        // Jika ada karyawan yang dipilih dari halaman lain (misal dari pool di dashboard)
        $karyawanDipilih = $request->filled('user_id')
            ? User::find($request->user_id)
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
            'user_id'   => 'required|exists:users,id',
            'mitra_id'      => 'required|exists:mitra,id',
            'tanggal_mulai' => 'required|date',
        ], [
            'user_id.required'   => 'Karyawan wajib dipilih.',
            'user_id.exists'     => 'Karyawan tidak ditemukan.',
            'mitra_id.required'      => 'Mitra wajib dipilih.',
            'mitra_id.exists'        => 'Mitra tidak ditemukan.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_mulai.date'     => 'Format tanggal tidak valid.',
        ]);

        // Cek apakah karyawan sudah punya penempatan aktif
        $sudahAktif = DetailRiwayatPenempatan::where('user_id', $request->user_id)
                                ->where('status','aktif')
                                ->exists();

        if ($sudahAktif) {
            return back()->withInput()
                ->with('error', 'Karyawan ini sudah memiliki penempatan aktif. Selesaikan penempatan lama terlebih dahulu.');
        }

        DetailRiwayatPenempatan::create([
            'user_id'   => $request->user_id,
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
    public function selesai(Request $request, DetailRiwayatPenempatan $penempatan)
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
