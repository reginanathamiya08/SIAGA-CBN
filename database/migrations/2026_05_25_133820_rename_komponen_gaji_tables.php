<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rename tables:
     * - komponen_gaji        → komponen_gaji_karyawan
     * - master_komponen_gaji → komponen_gaji
     */
    public function up(): void
    {
        // Step 1: Drop FK di detail_gaji_komponen yg referensi master_komponen_gaji
        Schema::table('detail_gaji_komponen', function (Blueprint $table) {
            $table->dropForeign(['master_komponen_gaji_id']);
        });

        // Step 2: Rename komponen_gaji → komponen_gaji_karyawan
        Schema::rename('komponen_gaji', 'komponen_gaji_karyawan');

        // Step 3: Rename master_komponen_gaji → komponen_gaji
        Schema::rename('master_komponen_gaji', 'komponen_gaji');

        // Step 4: Re-add FK di detail_gaji_komponen → sekarang referensi komponen_gaji (ex master)
        Schema::table('detail_gaji_komponen', function (Blueprint $table) {
            $table->foreign('master_komponen_gaji_id')
                  ->references('id')
                  ->on('komponen_gaji')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('detail_gaji_komponen', function (Blueprint $table) {
            $table->dropForeign(['master_komponen_gaji_id']);
        });

        Schema::rename('komponen_gaji', 'master_komponen_gaji');
        Schema::rename('komponen_gaji_karyawan', 'komponen_gaji');

        Schema::table('detail_gaji_komponen', function (Blueprint $table) {
            $table->foreign('master_komponen_gaji_id')
                  ->references('id')
                  ->on('master_komponen_gaji')
                  ->cascadeOnDelete();
        });
    }
};
