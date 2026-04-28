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
        Schema::create('komponen_gaji', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->unique()->constrained('karyawan')->cascadeOnDelete();
            $table->decimal('gaji_pokok', 12, 2)->default(0);
            $table->decimal('uang_makan', 10, 2)->nullable();
            $table->decimal('uang_transport', 10, 2)->nullable();
            $table->decimal('persen_bpjs_kes', 5, 2)->default(9.24);
            $table->decimal('persen_bpjs_tk', 5, 2)->default(5.00);
            $table->foreignId('updated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();
        });

        // ── PERIODE GAJI ───────────────────────────────────────────
        // Mendefinisikan rentang periode penggajian.
        // status: draft → proses → final
        Schema::create('periode_gaji', function (Blueprint $table) {
            $table->id();
            $table->string('nama_periode', 50);     // contoh: "Juli 2025"
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('status', ['draft', 'proses', 'final'])->default('draft');
            $table->timestamp('finalisasi_at')->nullable();
            $table->foreignId('finalisasi_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();
        });

        // ── SLIP GAJI ──────────────────────────────────────────────
        // Snapshot hasil hitung gaji. Nilai tidak berubah meski
        // komponen_gaji diedit kemudian (audit trail).
        // potongan_telat hanya terisi untuk karyawan tetap CBN.
        Schema::create('slip_gaji', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawan')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('periode_gaji')->cascadeOnDelete();
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

            $table->unique(['karyawan_id', 'periode_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slip_gaji');
        Schema::dropIfExists('periode_gaji');
        Schema::dropIfExists('komponen_gaji');
    }
};