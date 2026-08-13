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
        Schema::table('detail_gaji_komponen', function (Blueprint $table) {
            $table->dropForeign('detail_gaji_komponen_master_komponen_gaji_id_foreign');
        });

        Schema::table('detail_gaji_komponen', function (Blueprint $table) {
            $table->renameColumn('master_komponen_gaji_id', 'komponen_gaji_id');
        });

        Schema::table('detail_gaji_komponen', function (Blueprint $table) {
            $table->foreign('komponen_gaji_id')
                  ->references('id')
                  ->on('komponen_gaji')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_gaji_komponen', function (Blueprint $table) {
            $table->dropForeign(['komponen_gaji_id']);
        });

        Schema::table('detail_gaji_komponen', function (Blueprint $table) {
            $table->renameColumn('komponen_gaji_id', 'master_komponen_gaji_id');
        });

        Schema::table('detail_gaji_komponen', function (Blueprint $table) {
            $table->foreign('master_komponen_gaji_id')
                  ->references('id')
                  ->on('komponen_gaji')
                  ->cascadeOnDelete();
        });
    }
};
