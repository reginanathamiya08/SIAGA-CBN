<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Karyawan\StorePerizinanRequest;
use App\Models\Perizinan;
use App\Models\KuotaCuti;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PerizinanController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    // INDEX - Daftar pengajuan izin milik karyawan login
    // ─────────────────────────────────────────────────────────────────
    public function index()
    {
        $karyawan = Auth::user()->karyawan;

        $perizinan = Perizinan::where('karyawan_id', $karyawan->id)
                              ->orderBy('created_at', 'desc')
                              ->paginate(10);

        $kuotaCuti = KuotaCuti::where('karyawan_id', $karyawan->id)
                              ->where('tahun', now()->year)
                              ->first();

        return view('karyawan.perizinan.index', compact('perizinan', 'kuotaCuti', 'karyawan'));
    }

    // ─────────────────────────────────────────────────────────────────
    // CREATE - Form ajukan perizinan
    // ─────────────────────────────────────────────────────────────────
    public function create()
    {
        $karyawan  = Auth::user()->karyawan;
        $kuotaCuti = KuotaCuti::where('karyawan_id', $karyawan->id)
                              ->where('tahun', now()->year)
                              ->first();

        return view('karyawan.perizinan.create', compact('karyawan', 'kuotaCuti'));
    }

    // ─────────────────────────────────────────────────────────────────
    // STORE - Simpan pengajuan perizinan
    // ─────────────────────────────────────────────────────────────────
    public function store(StorePerizinanRequest $request)
    {
        $karyawan = Auth::user()->karyawan;
        $data     = $request->validated();

        // Hitung jumlah hari (exclude weekend? sesuai kebijakan CBN = kalender biasa)
        $mulai    = Carbon::parse($data['tanggal_mulai']);
        $selesai  = Carbon::parse($data['tanggal_selesai']);
        $jumlahHari = (int) $mulai->diffInDays($selesai) + 1;

        // Validasi sisa kuota cuti untuk jenis yang memotong cuti
        $jenisMemotongCuti = ['izin_pribadi', 'sakit_no_surat'];
        if (in_array($data['jenis_izin'], $jenisMemotongCuti)) {
            $kuota = KuotaCuti::where('karyawan_id', $karyawan->id)
                              ->where('tahun', now()->year)
                              ->first();

            if (!$kuota || $kuota->sisa < $jumlahHari) {
                return back()->withInput()
                    ->with('error', "Sisa kuota cuti tidak mencukupi. Sisa: " .
                        ($kuota->sisa ?? 0) . " hari, Dibutuhkan: {$jumlahHari} hari.");
            }
        }

        // Upload file bukti
        $filePath = null;
        if ($request->hasFile('file_bukti')) {
            $filePath = $request->file('file_bukti')
                                ->store('perizinan', 'public');
        }

        Perizinan::create([
            'karyawan_id'     => $karyawan->id,
            'jenis_izin'      => $data['jenis_izin'],
            'tanggal_mulai'   => $data['tanggal_mulai'],
            'tanggal_selesai' => $data['tanggal_selesai'],
            'jumlah_hari'     => $jumlahHari,
            'keterangan'      => $data['keterangan'] ?? null,
            'file_bukti'      => $filePath,
            'status_approval' => 'menunggu',
        ]);

        return redirect()
            ->route('karyawan.perizinan.index')
            ->with('success', 'Pengajuan perizinan berhasil dikirim. Menunggu persetujuan pimpinan.');
    }

    // ─────────────────────────────────────────────────────────────────
    // SHOW - Detail pengajuan
    // ─────────────────────────────────────────────────────────────────
    public function show(Perizinan $perizinan)
    {
        $karyawan = Auth::user()->karyawan;

        // Pastikan hanya milik karyawan yang login
        if ($perizinan->karyawan_id !== $karyawan->id) {
            abort(403);
        }

        return view('karyawan.perizinan.show', compact('perizinan'));
    }

    // ─────────────────────────────────────────────────────────────────
    // DESTROY - Batalkan pengajuan (hanya yang masih menunggu)
    // ─────────────────────────────────────────────────────────────────
    public function destroy(Perizinan $perizinan)
    {
        $karyawan = Auth::user()->karyawan;

        if ($perizinan->karyawan_id !== $karyawan->id) {
            abort(403);
        }

        if ($perizinan->status_approval !== 'menunggu') {
            return back()->with('error', 'Hanya pengajuan yang masih menunggu yang bisa dibatalkan.');
        }

        // Hapus file jika ada
        if ($perizinan->file_bukti) {
            Storage::disk('public')->delete($perizinan->file_bukti);
        }

        $perizinan->delete();

        return redirect()
            ->route('karyawan.perizinan.index')
            ->with('success', 'Pengajuan berhasil dibatalkan.');
    }
}