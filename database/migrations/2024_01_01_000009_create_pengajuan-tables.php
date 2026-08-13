<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── PERIZINAN ──────────────────────────────────────────────
        // jenis_izin menentukan aturan potongan:
        //   cuti           → potong uang makan 35rb/hari
        //   izin_pribadi   → potong kuota cuti
        //   sakit_surat    → tidak potong cuti, limit 12 hari
        //   sakit_no_surat → potong kuota cuti
        Schema::create('perizinan', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('user_id', 20);
            $table->enum('jenis_izin', [
                'cuti',
                'izin_pribadi',
                'sakit_surat',
                'sakit_no_surat',
            ]);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->integer('jumlah_hari');
            $table->text('keterangan')->nullable();
            $table->string('file_bukti', 255)->nullable();
            $table->enum('status_approval', [
                'menunggu',
                'disetujui',
                'ditolak',
            ])->default('menunggu');
            $table->string('approved_by', 20)
                  ->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('alasan_tolak')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });

        // ── LEMBUR ─────────────────────────────────────────────────
        Schema::create('lembur', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('user_id', 20);
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->decimal('total_jam', 4, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->enum('status_approval', [
                'menunggu',
                'disetujui',
                'ditolak',
            ])->default('menunggu');
            $table->string('approved_by', 20)
                  ->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('alasan_tolak')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });

        // ── DINAS LUAR ─────────────────────────────────────────────
        Schema::create('dinas_luar', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('user_id', 20);
            $table->string('tujuan', 200);
            $table->date('tanggal_berangkat');
            $table->date('tanggal_kembali');
            $table->string('file_surat_tugas', 255)->nullable();
            $table->enum('status_approval', [
                'menunggu',
                'disetujui',
                'ditolak',
            ])->default('menunggu');
            $table->string('approved_by', 20)
                  ->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('alasan_tolak')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dinas_luar');
        Schema::dropIfExists('lembur');
        Schema::dropIfExists('perizinan');
    }
};