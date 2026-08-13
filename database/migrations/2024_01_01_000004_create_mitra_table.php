<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mitra', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('nama_mitra', 150);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->integer('radius_meter')->default(100);
            $table->string('mitra_induk_id', 20)
                  ->nullable();
            
            $table->foreign('mitra_induk_id')
                  ->references('id')
                  ->on('mitra')
                  ->nullOnDelete();

            $table->boolean('is_cabang')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mitra');
    }
};