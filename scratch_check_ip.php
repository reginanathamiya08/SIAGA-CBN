<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mitraList = App\Models\Mitra::all();
echo "--- MITRA LIST ---\n";
foreach ($mitraList as $m) {
    echo "ID: {$m->id} | Nama: {$m->nama_mitra} | IP Public: {$m->ip_public}\n";
}
