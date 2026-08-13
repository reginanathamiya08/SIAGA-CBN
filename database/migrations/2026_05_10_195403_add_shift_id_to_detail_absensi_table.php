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
        Schema::table('detail_absensi', function (Blueprint $table) {
            if (!Schema::hasColumn('detail_absensi', 'mitra_id')) {
                $table->string('mitra_id', 20)->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('detail_absensi', 'shift_id')) {
                $table->string('shift_id', 20)->nullable()->after('mitra_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_absensi', function (Blueprint $table) {
            $table->dropColumn(['mitra_id', 'shift_id']);
        });
    }
};
