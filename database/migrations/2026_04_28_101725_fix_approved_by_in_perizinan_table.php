<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus foreign key dulu jika ada
        Schema::table('perizinan', function (Blueprint $table) {
            try {
                $table->dropForeign(['approved_by']);
            } catch (\Exception $e) {
                // Abaikan jika foreign key tidak ada
            }
        });

        // Ubah tipe kolom ke unsignedBigInteger
        DB::statement('ALTER TABLE perizinan MODIFY approved_by BIGINT UNSIGNED NULL');

        // Tambah foreign key kembali
        Schema::table('perizinan', function (Blueprint $table) {
            $table->foreign('approved_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('perizinan', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
        });
        DB::statement('ALTER TABLE perizinan MODIFY approved_by VARCHAR(50) NULL');
    }
};