<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\KuotaPerizinan;

$count = 0;
KuotaPerizinan::where('tahun', 2026)->get()->each(function($k) use (&$count) {
    $k->update([
        'kuota_total' => 12,
        'sisa' => 12 - $k->terpakai
    ]);
    $count++;
});

echo "Berhasil sinkronisasi $count karyawan ke 12 hari.\n";
