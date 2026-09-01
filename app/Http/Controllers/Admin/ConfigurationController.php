<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Configuration;
use App\Models\KomponenGaji;
use App\Http\Controllers\Admin\KomponenGajiController;
use Illuminate\Http\Request;

class ConfigurationController extends Controller
{
    public function index()
    {
        $configs = Configuration::all()->groupBy('group');
        $masterComponents = KomponenGaji::orderBy('id', 'asc')->get();
        $protectedIds = KomponenGajiController::$protectedIds;

        return view('admin.configurations.index', compact('configs', 'masterComponents', 'protectedIds'));
    }

    public function update(Request $request)
    {
        $data = $request->except('_token', '_method');

        foreach ($data as $key => $value) {
            Configuration::setValue($key, $value, null, 'string', 'gaji');
        }

        // Sinkronisasi otomatis seluruh data karyawan jika kuota cuti tahunan diubah oleh admin
        if (isset($data['kuota_cuti_tahunan'])) {
            $kuotaList = \App\Models\KuotaPerizinan::where('tahun', now()->year)->get();
            foreach ($kuotaList as $k) {
                $k->syncWithApprovedLeaves();
            }
        }

        return back()->with('success', 'Konfigurasi sistem berhasil diperbarui dan disinkronkan ke seluruh data.');
    }
}
