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
        Schema::table('detail_slip_gaji', function (Blueprint $table) {
            $table->string('komponen_id', 20)->nullable()->after('periode_id');
            
            $table->foreign('komponen_id')
                  ->references('id')
                  ->on('komponen_gaji')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_slip_gaji', function (Blueprint $table) {
            $table->dropForeign(['komponen_id']);
            $table->dropColumn('komponen_id');
        });
    }
};
