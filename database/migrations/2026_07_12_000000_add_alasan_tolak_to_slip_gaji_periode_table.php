<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slip_gaji_periode', function (Blueprint $table) {
            $table->text('alasan_tolak')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('slip_gaji_periode', function (Blueprint $table) {
            $table->dropColumn('alasan_tolak');
        });
    }
};
