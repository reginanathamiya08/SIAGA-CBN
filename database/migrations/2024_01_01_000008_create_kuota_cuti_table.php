<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kuota_cuti', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('user_id', 20);
            
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->year('tahun');
            $table->integer('kuota_total')->default(12);
            $table->integer('terpakai')->default(0);
            $table->integer('sisa')->default(12);
            $table->timestamps();

            $table->unique(['user_id', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kuota_cuti');
    }
};