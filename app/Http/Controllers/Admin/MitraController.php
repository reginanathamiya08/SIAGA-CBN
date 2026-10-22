<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMitraRequest;
use App\Http\Requests\Admin\UpdateMitraRequest;
use App\Models\Mitra;
use App\Models\Shift;
use Illuminate\Http\Request;

class MitraController extends Controller
{
    public function index()
    {
        $kantorPusat = Mitra::where('is_pusat', true)
                            ->withCount([
                                'penempatan' => fn($q) => $q->where('status', 'aktif')
                            ])
                            ->first();

        // Karyawan tetap tidak masuk tabel penempatan, hitung terpisah dan gabungkan
        if ($kantorPusat) {
            $jumlahKaryawanTetap = \App\Models\User::where('is_active', true)
                ->whereHas('role', fn($q) => $q->where('slug', 'karyawan_tetap'))
                ->count();
            // Gabungkan: penempatan_count (karyawan kontrak di pusat) + karyawan tetap
            $kantorPusat->penempatan_count = ($kantorPusat->penempatan_count ?? 0) + $jumlahKaryawanTetap;
        }

        $mitraInduk = Mitra::whereNull('mitra_induk_id')
                           ->where('is_pusat', false)
                           ->with(['cabang' => function ($q) {
                               $q->withCount([
                                   'penempatan' => fn($p) => $p->where('status', 'aktif')
                               ]);
                           }])
                           ->withCount([
                               'cabang',
                               'penempatan' => fn($q) => $q->where('status', 'aktif')
                           ])
                           ->orderBy('nama_mitra')
                           ->get();

        $totalMitra  = Mitra::whereNull('mitra_induk_id')->count();
        $totalCabang = Mitra::whereNotNull('mitra_induk_id')->count();
        $totalAktif  = \App\Models\DetailRiwayatPenempatan::where('status', 'aktif')->count();

        return view('admin.mitra.index', compact(
            'kantorPusat', 'mitraInduk', 'totalMitra', 'totalCabang', 'totalAktif'
        ));
    }

    public function create()
    {
        $daftarInduk = Mitra::whereNull('mitra_induk_id')
                            ->orderBy('nama_mitra')
                            ->get(['id','nama_mitra']);

        $hasPusat = Mitra::where('is_pusat', true)->exists();

        return view('admin.mitra.create', compact('daftarInduk', 'hasPusat'));
    }

    public function store(StoreMitraRequest $request)
    {
        $data = $request->validated();
        $shifts = $data['shifts'] ?? null;
        unset($data['shifts']);

        if (!empty($data['jam_masuk']))  $data['jam_masuk']  = substr($data['jam_masuk'], 0, 5);
        if (!empty($data['jam_pulang'])) $data['jam_pulang'] = substr($data['jam_pulang'], 0, 5);

        $data['is_cabang'] = !empty($data['mitra_induk_id']);
        $data['is_pusat']  = $request->has('is_pusat');

        if ($data['is_pusat']) {
            Mitra::query()->update(['is_pusat' => false]);
        }

        $mitra = Mitra::create($data);

        if ($shifts) {
            foreach ($shifts as $shiftData) {
                if (!empty($shiftData['jam_mulai'])) {
                    $shiftData['is_lintas_hari'] = isset($shiftData['is_lintas_hari']) && $shiftData['is_lintas_hari'] == 1;
                    if (!empty($shiftData['jam_mulai']))    $shiftData['jam_mulai']    = substr($shiftData['jam_mulai'], 0, 5);
                    if (!empty($shiftData['jam_selesai']))  $shiftData['jam_selesai']  = substr($shiftData['jam_selesai'], 0, 5);
                    if (!empty($shiftData['window_start'])) $shiftData['window_start'] = substr($shiftData['window_start'], 0, 5);
                    if (!empty($shiftData['window_end']))   $shiftData['window_end']   = substr($shiftData['window_end'], 0, 5);
                    $mitra->shifts()->create($shiftData);
                }
            }
        }

        $tipe = $data['is_cabang'] ? 'Cabang mitra' : 'Mitra';

        return redirect()
            ->route('admin.mitra.index')
            ->with('success', "{$tipe} berhasil ditambahkan.");
    }

    public function show(Mitra $mitra)
    {
        $mitra->load([
            'induk',
            'cabang',
            'penempatan' => fn($q) => $q->where('status','aktif')->with('karyawan'),
        ]);

        $karyawanTetap = collect();
        if ($mitra->is_pusat) {
            $karyawanTetap = \App\Models\User::where('is_active', true)
                                            ->whereHas('role', fn($q) => $q->where('slug', 'karyawan_tetap'))
                                            ->get();
        }

        $riwayat = $mitra->penempatan()
                         ->with('karyawan')
                         ->latest()
                         ->paginate(10);

        return view('admin.mitra.show', compact('mitra', 'riwayat', 'karyawanTetap'));
    }

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

    public function update(UpdateMitraRequest $request, Mitra $mitra)
    {
        $data = $request->validated();
        $shifts = $data['shifts'] ?? null;
        unset($data['shifts']);

        if (!empty($data['jam_masuk']))  $data['jam_masuk']  = substr($data['jam_masuk'], 0, 5);
        if (!empty($data['jam_pulang'])) $data['jam_pulang'] = substr($data['jam_pulang'], 0, 5);

        $data['is_cabang'] = !empty($data['mitra_induk_id']);
        $data['is_pusat']  = $request->has('is_pusat');

        if ($data['is_pusat']) {
            Mitra::where('id', '!=', $mitra->id)->update(['is_pusat' => false]);
        }

        $mitra->update($data);

        if ($shifts) {
            foreach ($shifts as $shiftData) {
                $shiftData['is_lintas_hari'] = isset($shiftData['is_lintas_hari']) && $shiftData['is_lintas_hari'] == 1;

                if (!empty($shiftData['jam_mulai']))    $shiftData['jam_mulai']    = substr($shiftData['jam_mulai'], 0, 5);
                if (!empty($shiftData['jam_selesai']))  $shiftData['jam_selesai']  = substr($shiftData['jam_selesai'], 0, 5);
                if (!empty($shiftData['window_start'])) $shiftData['window_start'] = substr($shiftData['window_start'], 0, 5);
                if (!empty($shiftData['window_end']))   $shiftData['window_end']   = substr($shiftData['window_end'], 0, 5);

                if (!empty($shiftData['id'])) {
                    $shiftId = $shiftData['id'];
                    unset($shiftData['id']);
                    Shift::where('id', $shiftId)->update($shiftData);
                } else if (!empty($shiftData['jam_mulai'])) {
                    $mitra->shifts()->create($shiftData);
                }
            }
        }

        return redirect()
            ->route('admin.mitra.index')
            ->with('success', 'Data mitra berhasil diperbarui.');
    }

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