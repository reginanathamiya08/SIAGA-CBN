<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Karyawan\StoreDinasLuarRequest;
use App\Models\DinasLuar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DinasLuarController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────────
    public function index()
    {
        $karyawan = Auth::user()->karyawan;

        // Hanya karyawan tetap CBN
        if (!$karyawan->isTetap()) {
            return redirect()->route('karyawan.dashboard')
                ->with('error', 'Fitur dinas luar kota hanya tersedia untuk karyawan tetap CBN.');
        }

        $dinasLuar = DinasLuar::where('karyawan_id', $karyawan->id)
                              ->orderBy('created_at', 'desc')
                              ->paginate(10);

        return view('karyawan.dinas-luar.index', compact('dinasLuar', 'karyawan'));
    }

    // ─────────────────────────────────────────────────────────────────
    // CREATE
    // ─────────────────────────────────────────────────────────────────
    public function create()
    {
        $karyawan = Auth::user()->karyawan;

        if (!$karyawan->isTetap()) {
            return redirect()->route('karyawan.dashboard')
                ->with('error', 'Fitur dinas luar kota hanya tersedia untuk karyawan tetap CBN.');
        }

        return view('karyawan.dinas-luar.create', compact('karyawan'));
    }

    // ─────────────────────────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────────────────────────
    public function store(StoreDinasLuarRequest $request)
    {
        $karyawan = Auth::user()->karyawan;
        $data     = $request->validated();

        // Upload surat tugas
        $filePath = null;
        if ($request->hasFile('file_surat_tugas')) {
            $filePath = $request->file('file_surat_tugas')
                                ->store('dinas-luar', 'public');
        }

        DinasLuar::create([
            'karyawan_id'       => $karyawan->id,
            'tujuan'            => $data['tujuan'],
            'tanggal_berangkat' => $data['tanggal_berangkat'],
            'tanggal_kembali'   => $data['tanggal_kembali'],
            'file_surat_tugas'  => $filePath,
            'status_approval'   => 'menunggu',
        ]);

        return redirect()
            ->route('karyawan.dinas-luar.index')
            ->with('success', 'Pengajuan dinas luar kota berhasil dikirim. Menunggu persetujuan pimpinan.');
    }

    // ─────────────────────────────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────────────────────────────
    public function show(DinasLuar $dinasLuar)
    {
        $karyawan = Auth::user()->karyawan;

        if ($dinasLuar->karyawan_id !== $karyawan->id) {
            abort(403);
        }

        return view('karyawan.dinas-luar.show', compact('dinasLuar'));
    }

    // ─────────────────────────────────────────────────────────────────
    // DESTROY - Batalkan
    // ─────────────────────────────────────────────────────────────────
    public function destroy(DinasLuar $dinasLuar)
    {
        $karyawan = Auth::user()->karyawan;

        if ($dinasLuar->karyawan_id !== $karyawan->id) {
            abort(403);
        }

        if ($dinasLuar->status_approval !== 'menunggu') {
            return back()->with('error', 'Hanya pengajuan yang masih menunggu yang bisa dibatalkan.');
        }

        if ($dinasLuar->file_surat_tugas) {
            Storage::disk('public')->delete($dinasLuar->file_surat_tugas);
        }

        $dinasLuar->delete();

        return redirect()
            ->route('karyawan.dinas-luar.index')
            ->with('success', 'Pengajuan dinas luar kota berhasil dibatalkan.');
    }
}