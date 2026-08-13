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
        Schema::rename('riwayat_penempatan', 'detail_riwayat_penempatan');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('detail_riwayat_penempatan', 'riwayat_penempatan');
    }
};
