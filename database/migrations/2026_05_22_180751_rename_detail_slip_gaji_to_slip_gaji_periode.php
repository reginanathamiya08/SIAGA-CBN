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
        // 1. Drop foreign key constraint on perizinan
        Schema::table('perizinan', function (Blueprint $table) {
            $table->dropForeign(['detail_slip_gaji_id']);
        });

        // 2. Rename the table
        Schema::rename('detail_slip_gaji', 'slip_gaji_periode');

        // 3. Rename the column in perizinan
        Schema::table('perizinan', function (Blueprint $table) {
            $table->renameColumn('detail_slip_gaji_id', 'slip_gaji_periode_id');
        });

        // 4. Re-add foreign key on perizinan pointing to slip_gaji_periode
        Schema::table('perizinan', function (Blueprint $table) {
            $table->foreign('slip_gaji_periode_id')
                  ->references('id')
                  ->on('slip_gaji_periode')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perizinan', function (Blueprint $table) {
            $table->dropForeign(['slip_gaji_periode_id']);
        });

        Schema::table('perizinan', function (Blueprint $table) {
            $table->renameColumn('slip_gaji_periode_id', 'detail_slip_gaji_id');
        });

        Schema::rename('slip_gaji_periode', 'detail_slip_gaji');

        Schema::table('perizinan', function (Blueprint $table) {
            $table->foreign('detail_slip_gaji_id')
                  ->references('id')
                  ->on('detail_slip_gaji')
                  ->onDelete('set null');
        });
    }
};
