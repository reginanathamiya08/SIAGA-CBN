<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KomponenGaji;
use Illuminate\Http\Request;

class KomponenGajiController extends Controller
{
    // List of system-protected components that shouldn't be edited/deleted
    public static $protectedIds = [
        'MKG-00001',
        'MKG-00002',
        'MKG-00003',
        'MKG-00004',
        'MKG-00005',
        'MKG-00006',
        'MKG-00009',
        'MKG-00010',
    ];

    public function index()
    {
        return redirect()->route('admin.konfigurasi.index', ['tab' => 'komponen']);
    }

    public function create()
    {
        return redirect()->route('admin.konfigurasi.index', ['tab' => 'komponen']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_komponen' => 'required|string|max:255|unique:komponen_gaji,nama_komponen',
            'tipe' => 'required|in:pendapatan,potongan',
        ], [
            'nama_komponen.required' => 'Nama komponen wajib diisi.',
            'nama_komponen.unique' => 'Nama komponen ini sudah terdaftar.',
            'tipe.required' => 'Tipe komponen wajib dipilih.',
        ]);

        KomponenGaji::create([
            'nama_komponen' => $request->nama_komponen,
            'tipe' => $request->tipe,
        ]);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Komponen gaji baru berhasil ditambahkan.']);
        }

        return redirect()
            ->route('admin.konfigurasi.index', ['tab' => 'komponen'])
            ->with('success', 'Komponen gaji baru berhasil ditambahkan.');
    }

    public function edit($id)
    {
        return redirect()->route('admin.konfigurasi.index', ['tab' => 'komponen']);
    }

    public function update(Request $request, $id)
    {
        if (in_array($id, self::$protectedIds)) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Komponen bawaan sistem tidak dapat diubah.'], 403);
            }
            return redirect()
                ->route('admin.konfigurasi.index', ['tab' => 'komponen'])
                ->with('error', 'Komponen bawaan sistem tidak dapat diubah.');
        }

        $component = KomponenGaji::findOrFail($id);

        $request->validate([
            'nama_komponen' => 'required|string|max:255|unique:komponen_gaji,nama_komponen,' . $id,
            'tipe' => 'required|in:pendapatan,potongan',
        ], [
            'nama_komponen.required' => 'Nama komponen wajib diisi.',
            'nama_komponen.unique' => 'Nama komponen ini sudah terdaftar.',
            'tipe.required' => 'Tipe komponen wajib dipilih.',
        ]);

        $component->update([
            'nama_komponen' => $request->nama_komponen,
            'tipe' => $request->tipe,
        ]);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Komponen gaji berhasil diperbarui.']);
        }

        return redirect()
            ->route('admin.konfigurasi.index', ['tab' => 'komponen'])
            ->with('success', 'Komponen gaji berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        if (in_array($id, self::$protectedIds)) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Komponen bawaan sistem tidak dapat dihapus.'], 403);
            }
            return redirect()
                ->route('admin.konfigurasi.index', ['tab' => 'komponen'])
                ->with('error', 'Komponen bawaan sistem tidak dapat dihapus.');
        }

        $component = KomponenGaji::findOrFail($id);
        
        // Coba check apakah komponen ini sudah digunakan di slip gaji
        $sudahDigunakan = \App\Models\DetailGajiKomponen::where('komponen_gaji_id', $id)->exists();
        if ($sudahDigunakan) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Komponen ini tidak dapat dihapus karena sudah digunakan dalam slip gaji karyawan.'], 400);
            }
            return redirect()
                ->route('admin.konfigurasi.index', ['tab' => 'komponen'])
                ->with('error', 'Komponen ini tidak dapat dihapus karena sudah digunakan dalam slip gaji karyawan.');
        }

        $component->delete();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Komponen gaji berhasil dihapus.']);
        }

        return redirect()
            ->route('admin.konfigurasi.index', ['tab' => 'komponen'])
            ->with('success', 'Komponen gaji berhasil dihapus.');
    }
}
