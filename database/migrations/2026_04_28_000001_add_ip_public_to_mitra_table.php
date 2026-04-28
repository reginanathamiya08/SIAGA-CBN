<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mitra', function (Blueprint $table) {
            // IP Public jaringan kantor mitra — WAJIB diisi admin
            // Validasi dilakukan di server saat karyawan melakukan absensi
            $table->string('ip_public', 50)->after('radius_meter');
        });
    }

    public function down(): void
    {
        Schema::table('mitra', function (Blueprint $table) {
            $table->dropColumn('ip_public');
        });
    }
};