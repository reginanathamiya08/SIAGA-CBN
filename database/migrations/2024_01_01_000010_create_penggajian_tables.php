<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── KOMPONEN GAJI ──────────────────────────────────────────
        // Konfigurasi per karyawan, dibaca engine saat proses gaji.
        // uang_makan & uang_transport NULL untuk karyawan kontrak
        // divisi HC karena dibayar langsung oleh mitra.
        // ── KOMPONEN GAJI ──────────────────────────────────────────
        Schema::create('komponen_gaji', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('user_id', 20)->unique();
            $table->decimal('gaji_pokok', 12, 2)->default(0);
            $table->decimal('uang_makan', 10, 2)->nullable();
            $table->decimal('uang_transport', 10, 2)->nullable();
            $table->decimal('persen_bpjs_kes', 5, 2)->default(9.24);
            $table->decimal('persen_bpjs_tk', 5, 2)->default(5.00);
            $table->string('updated_by', 20)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });

        // ── PERIODE GAJI ───────────────────────────────────────────
        Schema::create('periode_gaji', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('nama_periode', 50);     // contoh: "Juli 2025"
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('status', ['draft', 'proses', 'final'])->default('draft');
            $table->timestamp('finalisasi_at')->nullable();
            $table->string('finalisasi_by', 20)->nullable();
            $table->timestamps();

            $table->foreign('finalisasi_by')->references('id')->on('users')->nullOnDelete();
        });

        // ── SLIP GAJI ──────────────────────────────────────────────
        Schema::create('detail_slip_gaji', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('user_id', 20);
            $table->string('periode_id', 20);
            $table->decimal('gaji_pokok', 12, 2)->default(0);
            $table->decimal('uang_makan', 10, 2)->default(0);
            $table->decimal('uang_transport', 10, 2)->default(0);
            $table->integer('total_hadir')->default(0);
            $table->integer('total_telat')->default(0);
            $table->integer('total_alfa')->default(0);
            $table->integer('total_izin')->default(0);
            $table->integer('total_cuti')->default(0);
            $table->decimal('potongan_telat', 10, 2)->default(0);   // khusus karyawan tetap CBN
            $table->decimal('potongan_izin', 10, 2)->default(0);
            $table->decimal('potongan_bpjs_kes', 10, 2)->default(0);
            $table->decimal('potongan_bpjs_tk', 10, 2)->default(0);
            $table->decimal('total_potongan', 10, 2)->default(0);
            $table->decimal('gaji_bersih', 12, 2)->default(0);
            $table->enum('status', ['draft', 'diterbitkan'])->default('draft');
            $table->timestamp('diterbitkan_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('periode_id')->references('id')->on('periode_gaji')->cascadeOnDelete();
            $table->unique(['user_id', 'periode_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_slip_gaji');
        Schema::dropIfExists('periode_gaji');
        Schema::dropIfExists('komponen_gaji');
    }
};