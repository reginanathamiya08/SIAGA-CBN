<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_absensi', function (Blueprint $table) {
            $table->string('ip_masuk',  50)->nullable()->after('long_masuk');
            $table->string('ip_pulang', 50)->nullable()->after('long_pulang');
        });
    }

    public function down(): void
    {
        Schema::table('detail_absensi', function (Blueprint $table) {
            $table->dropColumn(['ip_masuk', 'ip_pulang']);
        });
    }
};