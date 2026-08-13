<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('detail_penempatan', 'riwayat_penempatan');
        Schema::rename('detail_absensi', 'absensi');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('riwayat_penempatan', 'detail_penempatan');
        Schema::rename('absensi', 'detail_absensi');
    }
};
