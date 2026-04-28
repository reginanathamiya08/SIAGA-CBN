<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawan')->cascadeOnDelete();
            $table->foreignId('mitra_id')
                  ->nullable()
                  ->constrained('mitra')
                  ->nullOnDelete();
            $table->date('tanggal');
            $table->dateTime('waktu_masuk')->nullable();
            $table->dateTime('waktu_pulang')->nullable();
            $table->decimal('lat_masuk', 10, 7)->nullable();
            $table->decimal('long_masuk', 10, 7)->nullable();
            $table->decimal('lat_pulang', 10, 7)->nullable();
            $table->decimal('long_pulang', 10, 7)->nullable();
            $table->enum('status', [
                'hadir',
                'telat',
                'alfa',
                'izin',
                'sakit',
                'cuti',
                'dinas_luar',
            ])->default('hadir');
            // is_telat hanya relevan untuk karyawan tetap CBN (batas 08:15)
            // karyawan kontrak mengikuti ketentuan mitra masing-masing
            $table->boolean('is_telat')->default(false);
            $table->timestamps();

            $table->unique(['karyawan_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};