<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMitraRequest;
use App\Http\Requests\Admin\UpdateMitraRequest;
use App\Models\Mitra;
use Illuminate\Http\Request;

class MitraController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    // INDEX - Daftar semua mitra induk beserta cabangnya
    // ─────────────────────────────────────────────────────────────────
    public function index()
    {
        // Ambil Kantor Pusat secara spesifik
        $kantorPusat = Mitra::where('is_pusat', true)
                            ->withCount(['cabang', 'penempatan' => fn($q) => $q->where('status','aktif')])
                            ->first();

        // Jika ada kantor pusat, hitung karyawan tetap sebagai "penempatan" otomatis
        if ($kantorPusat) {
            $jumlahTetap = \App\Models\Karyawan::where('jenis_karyawan', 'tetap')
                                              ->where('is_active', true)
                                              ->count();
            // Kita tambahkan jumlah tetap ke penempatan_count
            $kantorPusat->penempatan_count += $jumlahTetap;
        }

        // Ambil Mitra Induk lainnya (yang bukan pusat)
        $mitraInduk = Mitra::withCount(['cabang', 'penempatan' => fn($q) => $q->where('status','aktif')])
                           ->whereNull('mitra_induk_id')
                           ->where('is_pusat', false)
                           ->orderBy('nama_mitra')
                           ->get();

        $totalMitra    = Mitra::whereNull('mitra_induk_id')->count();
        $totalCabang   = Mitra::whereNotNull('mitra_induk_id')->count();
        
        $totalAktifKontrak = \App\Models\Penempatan::where('status','aktif')->count();
        $totalAktifTetap   = \App\Models\Karyawan::where('jenis_karyawan', 'tetap')
                                                 ->where('is_active', true)
                                                 ->count();
        $totalAktif = $totalAktifKontrak + $totalAktifTetap;

        return view('admin.mitra.index', compact(
            'kantorPusat', 'mitraInduk', 'totalMitra', 'totalCabang', 'totalAktif'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // CREATE - Form tambah mitra
    // ─────────────────────────────────────────────────────────────────
    public function create()
    {
        // Untuk dropdown mitra induk saat tambah cabang
        $daftarInduk = Mitra::whereNull('mitra_induk_id')
                            ->orderBy('nama_mitra')
                            ->get(['id','nama_mitra']);

        $hasPusat = Mitra::where('is_pusat', true)->exists();

        return view('admin.mitra.create', compact('daftarInduk', 'hasPusat'));
    }

    // ─────────────────────────────────────────────────────────────────
    // STORE - Simpan mitra baru
    // ─────────────────────────────────────────────────────────────────
    public function store(StoreMitraRequest $request)
    {
        $data = $request->validated();

        // Jika ada mitra_induk_id, ini adalah cabang
        $data['is_cabang'] = !empty($data['mitra_induk_id']);
        $data['is_pusat']  = $request->has('is_pusat');

        if ($data['is_pusat']) {
            Mitra::where('id', '>', 0)->update(['is_pusat' => false]);
        }

        $mitra = Mitra::create($data);

        // Simpan Konfigurasi Shift jika ada
        if ($request->has('shifts')) {
            foreach ($request->shifts as $shiftData) {
                if (!empty($shiftData['jam_mulai'])) {
                    $mitra->shifts()->create($shiftData);
                }
            }
        }

        $tipe = $data['is_cabang'] ? 'Cabang mitra' : 'Mitra';
        return redirect()
            ->route('admin.mitra.index')
            ->with('success', "{$tipe} berhasil ditambahkan.");
    }

    // ─────────────────────────────────────────────────────────────────
    // SHOW - Detail mitra beserta karyawan aktif
    // ─────────────────────────────────────────────────────────────────
    public function show(Mitra $mitra)
    {
        $mitra->load([
            'induk',
            'cabang',
            'penempatan' => fn($q) => $q->where('status','aktif')
                                        ->with('karyawan'),
        ]);

        // Riwayat penempatan (semua status)
        $riwayat = $mitra->penempatan()
                         ->with('karyawan')
                         ->latest()
                         ->paginate(10);

        return view('admin.mitra.show', compact('mitra', 'riwayat'));
    }

    // ─────────────────────────────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────────────────────────────
    public function edit(Mitra $mitra)
    {
        $daftarInduk = Mitra::whereNull('mitra_induk_id')
                            ->where('id', '!=', $mitra->id)
                            ->orderBy('nama_mitra')
                            ->get(['id','nama_mitra']);

        $hasPusat = Mitra::where('is_pusat', true)
                         ->where('id', '!=', $mitra->id)
                         ->exists();

        return view('admin.mitra.edit', compact('mitra', 'daftarInduk', 'hasPusat'));
    }

    // ─────────────────────────────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────────────────────────────
    public function update(UpdateMitraRequest $request, Mitra $mitra)
    {
        $data = $request->validated();
        $data['is_cabang'] = !empty($data['mitra_induk_id']);
        $data['is_pusat']  = $request->has('is_pusat');

        if ($data['is_pusat']) {
            Mitra::where('id', '!=', $mitra->id)->update(['is_pusat' => false]);
        }

        $mitra->update($data);

        // Update Konfigurasi Shift
        if ($request->has('shifts')) {
            foreach ($request->shifts as $shiftData) {
                if (!empty($shiftData['id'])) {
                    // Update existing shift
                    \App\Models\Shift::where('id', $shiftData['id'])->update(array_diff_key($shiftData, ['id' => 1]));
                } else if (!empty($shiftData['jam_mulai'])) {
                    // Create new shift for this mitra
                    $mitra->shifts()->create($shiftData);
                }
            }
        }

        return redirect()
            ->route('admin.mitra.index')
            ->with('success', 'Data mitra berhasil diperbarui.');
    }

    // ─────────────────────────────────────────────────────────────────
    // DESTROY - Hapus mitra (hanya jika tidak ada karyawan aktif)
    // ─────────────────────────────────────────────────────────────────
    public function destroy(Mitra $mitra)
    {
        $aktif = $mitra->penempatan()->where('status','aktif')->count();
        if ($aktif > 0) {
            return back()->with('error',
                "Tidak bisa menghapus mitra yang masih memiliki {$aktif} karyawan aktif.");
        }

        $mitra->delete();
        return redirect()
            ->route('admin.mitra.index')
            ->with('success', 'Mitra berhasil dihapus.');
    }
}