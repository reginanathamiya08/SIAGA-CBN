<?php

namespace Database\Seeders;

use App\Models\Configuration;
use Illuminate\Database\Seeder;

class ConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            [
                'key'   => 'umr_tahun_ini',
                'label' => 'UMR Sumatera Barat Tahun Berjalan',
                'value' => '2994031',
                'type'  => 'number',
                'group' => 'gaji',
            ],
            [
                'key'   => 'uang_makan_default',
                'label' => 'Uang Makan Harian (Standar)',
                'value' => '35000',
                'type'  => 'number',
                'group' => 'gaji',
            ],
            [
                'key'   => 'uang_transport_default',
                'label' => 'Uang Transport Harian (Standar)',
                'value' => '45000',
                'type'  => 'number',
                'group' => 'gaji',
            ],
            [
                'key'   => 'persen_bpjs_kes',
                'label' => 'Persentase BPJS Kesehatan',
                'value' => '9.24',
                'type'  => 'percent',
                'group' => 'gaji',
            ],
            [
                'key'   => 'persen_bpjs_tk',
                'label' => 'Persentase BPJS Ketenagakerjaan',
                'value' => '5.00',
                'type'  => 'percent',
                'group' => 'gaji',
            ],
            [
                'key'   => 'batas_tanggal_gaji',
                'label' => 'Batas Minimal Tanggal Proses Gaji (Bulan Berjalan)',
                'value' => '25',
                'type'  => 'number',
                'group' => 'gaji',
            ],
            [
                'key'   => 'kuota_cuti_tahunan',
                'label' => 'Kuota Cuti Tahunan Karyawan Baru',
                'value' => '12',
                'type'  => 'number',
                'group' => 'cuti',
            ],
        ];

        foreach ($configs as $config) {
            Configuration::updateOrCreate(['key' => $config['key']], $config);
        }
    }
}
