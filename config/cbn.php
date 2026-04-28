<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Koordinat & Radius Kantor CBN (untuk absensi karyawan tetap)
    |--------------------------------------------------------------------------
    */
    'kantor_lat'    => env('CBN_KANTOR_LAT', -0.9492),
    'kantor_lon'    => env('CBN_KANTOR_LON', 100.3543),
    'kantor_radius' => env('CBN_KANTOR_RADIUS', 100),

    /*
    |--------------------------------------------------------------------------
    | Aturan Keterlambatan Karyawan Tetap CBN
    |--------------------------------------------------------------------------
    */
    'batas_telat' => env('CBN_BATAS_TELAT', '08:15:00'),

    /*
    |--------------------------------------------------------------------------
    | Tunjangan Harian Karyawan Tetap CBN
    | Jika telat/alfa: KEDUA tunjangan dipotong (total Rp 80.000/hari)
    |--------------------------------------------------------------------------
    */
    'uang_makan_harian'     => env('CBN_UANG_MAKAN', 35000),
    'uang_transport_harian' => env('CBN_UANG_TRANSPORT', 45000),

    /*
    |--------------------------------------------------------------------------
    | Potongan Cuti (karyawan mengajukan cuti = uang makan dipotong 35rb/hari)
    |--------------------------------------------------------------------------
    */
    'potongan_cuti_per_hari' => 35000,

    /*
    |--------------------------------------------------------------------------
    | UMR Sumatera Barat Tahun Berjalan
    | Update setiap tahun sesuai Pergub terbaru
    |--------------------------------------------------------------------------
    */
    'umr_tahun_ini' => env('CBN_UMR', 2994031),  // UMR Sumbar 2025

    /*
    |--------------------------------------------------------------------------
    | Kuota Cuti Tahunan
    |--------------------------------------------------------------------------
    */
    'kuota_cuti_tahunan' => 12,

    /*
    |--------------------------------------------------------------------------
    | Default BPJS (sesuai regulasi)
    | Bisa diubah secara massal dari menu Komponen Gaji
    |--------------------------------------------------------------------------
    */
    'default_bpjs_kes' => 9.24,
    'default_bpjs_tk'  => 5.00,

];