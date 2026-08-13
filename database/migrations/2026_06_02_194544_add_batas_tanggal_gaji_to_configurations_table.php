<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Configuration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Configuration::updateOrCreate(
            ['key' => 'batas_tanggal_gaji'],
            [
                'label' => 'Batas Minimal Tanggal Proses Gaji (Bulan Berjalan)',
                'value' => '25',
                'type'  => 'number',
                'group' => 'gaji',
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Configuration::where('key', 'batas_tanggal_gaji')->delete();
    }
};
