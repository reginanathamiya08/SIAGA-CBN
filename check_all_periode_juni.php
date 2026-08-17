<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$periodes = App\Models\PeriodeGaji::orderBy('tanggal_mulai', 'desc')->get();
echo "ALL PERIODE GAJI IN DB:\n";
foreach ($periodes as $p) {
    $slipCount = App\Models\SlipGajiPeriode::where('periode_id', $p->id)->count();
    echo "ID: {$p->id} | Nama: {$p->nama_periode} | Mulai: {$p->tanggal_mulai?->toDateString()} | Selesai: {$p->tanggal_selesai?->toDateString()} | Total Slips: {$slipCount}\n";

    $slips = App\Models\SlipGajiPeriode::with('details')->where('periode_id', $p->id)->get();
    foreach ($slips as $s) {
        $uMakan = $s->getNominal('Uang Makan');
        $uTrans = $s->getNominal('Uang Transport');
        echo "   -> User: {$s->user_id} | Hadir: {$s->total_hadir} | Uang Makan: Rp {$uMakan} | Transport: Rp {$uTrans}\n";
    }
}
