<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$k = App\Models\User::where('nama', 'LIKE', '%Regina%')->with('role')->first();

if (!$k) {
    echo "User bernama Regina tidak ditemukan.\n";
    exit;
}

echo "Username: " . $k->username . "\n";
echo "Role Slug: " . ($k->role->slug ?? 'NULL') . "\n";
