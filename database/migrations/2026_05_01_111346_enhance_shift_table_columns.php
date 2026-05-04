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
        Schema::table('shift', function (Blueprint $table) {
            // Columns already exist as jam_mulai and jam_selesai based on current state
            
            // Add new columns
            $table->time('window_start')->nullable()->after('jam_selesai');
            $table->time('window_end')->nullable()->after('window_start');
            $table->integer('toleransi_menit')->default(0)->after('window_end');
            $table->boolean('is_lintas_hari')->default(false)->after('toleransi_menit');
            
            // Remove old column
            if (Schema::hasColumn('shift', 'batas_telat')) {
                $table->dropColumn('batas_telat');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift', function (Blueprint $table) {
            $table->dropColumn(['window_start', 'window_end', 'toleransi_menit', 'is_lintas_hari']);
            $table->time('batas_telat')->nullable();
        });
    }
};
