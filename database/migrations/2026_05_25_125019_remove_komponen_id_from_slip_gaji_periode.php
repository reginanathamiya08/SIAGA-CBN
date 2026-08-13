<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus kolom komponen_id dari slip_gaji_periode.
     *
     * Alasan:
     * - Relasi slip_gaji_periode dengan komponen_gaji sebenarnya M-M
     * - Tabel pivot-nya adalah detail_gaji_komponen
     * - Kolom komponen_id di sini redundan dan membingungkan ERD
     * - Rincian komponen sudah ada di tabel detail_gaji_komponen
     */
    public function up(): void
    {
        Schema::table('slip_gaji_periode', function (Blueprint $table) {
            $table->dropForeign('detail_slip_gaji_komponen_id_foreign');
            $table->dropColumn('komponen_id');
        });
    }

    public function down(): void
    {
        Schema::table('slip_gaji_periode', function (Blueprint $table) {
            $table->string('komponen_id', 20)->nullable()->after('periode_id');
            $table->foreign('komponen_id', 'detail_slip_gaji_komponen_id_foreign')
                  ->references('id')
                  ->on('komponen_gaji')
                  ->nullOnDelete();
        });
    }
};
