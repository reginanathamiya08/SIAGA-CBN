<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('nama_shift', 50);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->time('batas_telat');
            $table->timestamps();
        });

        Schema::create('detail_jadwal_shift', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('user_id', 20);
            $table->string('shift_id', 20);
            $table->date('tanggal');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('shift_id')->references('id')->on('shift')->cascadeOnDelete();
            $table->unique(['user_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_jadwal_shift');
        Schema::dropIfExists('shift');
    }
};