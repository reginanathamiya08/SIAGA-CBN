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
        Schema::table('perizinan', function (Blueprint $table) {
            $table->string('detail_slip_gaji_id')->nullable()->after('alasan_tolak');
            $table->foreign('detail_slip_gaji_id')->references('id')->on('detail_slip_gaji')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perizinan', function (Blueprint $table) {
            $table->dropForeign(['detail_slip_gaji_id']);
            $table->dropColumn('detail_slip_gaji_id');
        });
    }
};
