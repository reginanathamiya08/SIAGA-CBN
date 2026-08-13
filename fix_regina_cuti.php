<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$u = App\Models\User::where('nama', 'LIKE', '%Regina%')->first();
if (!$u) {
    echo "Regina tidak ditemukan.\n";
    exit;
}

$k = $u->kuotaPerizinanTahunIni();
if (!$k) {
    echo "Kuota perizinan Regina tidak ditemukan.\n";
    exit;
}

$terpakai = App\Models\Perizinan::where('user_id', $u->id)
    ->where('status_approval', 'disetujui')
    ->whereIn('jenis_izin', ['cuti', 'izin_pribadi', 'sakit_no_surat'])
    ->sum('jumlah_hari');

$k->update([
    'terpakai' => $terpakai,
    'sisa' => $k->kuota_total - $terpakai
]);

echo "Berhasil update jatah Regina. Terpakai: $terpakai hari, Sisa: " . ($k->kuota_total - $terpakai) . " hari.\n";
