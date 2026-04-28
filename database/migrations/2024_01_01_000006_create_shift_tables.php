<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift', function (Blueprint $table) {
            $table->id();
            $table->string('nama_shift', 50);
            $table->time('jam_masuk');
            $table->time('jam_keluar');
            $table->time('batas_telat');
            $table->timestamps();
        });

        Schema::create('jadwal_shift', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawan')->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shift')->cascadeOnDelete();
            $table->date('tanggal');
            $table->timestamps();

            $table->unique(['karyawan_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_shift');
        Schema::dropIfExists('shift');
    }
};