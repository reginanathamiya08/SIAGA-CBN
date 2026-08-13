<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_gaji_komponen', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('slip_gaji_periode_id');
            $table->string('master_komponen_gaji_id');
            $table->decimal('nominal', 15, 2);
            $table->timestamps();

            $table->foreign('slip_gaji_periode_id')
                  ->references('id')
                  ->on('slip_gaji_periode')
                  ->onDelete('cascade');

            $table->foreign('master_komponen_gaji_id')
                  ->references('id')
                  ->on('master_komponen_gaji')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_gaji_komponen');
    }
};
