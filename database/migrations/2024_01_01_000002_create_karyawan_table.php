<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('karyawan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->unique()
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->string('nama', 100);

            // ── Jenis karyawan ───────────────────────────────────────────
            // tetap   : karyawan internal CBN (4 orang)
            // kontrak : karyawan alih daya yang ditempatkan di mitra
            $table->enum('jenis_karyawan', ['tetap', 'kontrak']);

            // ── Divisi ───────────────────────────────────────────────────
            // Karyawan tetap: keuangan | koordinator_cs | adm_umum
            // Karyawan kontrak: HC | umum
            $table->enum('divisi', [
                // Divisi karyawan tetap CBN
                'keuangan',
                'koordinator_cs',
                'adm_umum',
                // Divisi karyawan kontrak
                'HC',
                'umum',
            ])->nullable();

            // ── Jabatan ──────────────────────────────────────────────────
            // Karyawan tetap: Staff Keuangan | Koordinator CS | Staff Adm & Umum
            // Karyawan kontrak divisi HC:
            //   Satpam, Sopir, Marketing, Pramusaji, Pramubakti,
            //   Call Centre, Card Center, E-Channel, Juru Parkir,
            //   Teknisi, Monitoring ATM dan Jaringan, PPI
            // Karyawan kontrak divisi Umum:
            //   CS, CS ATM, Ekspedisi
            $table->enum('jabatan', [
                // Karyawan tetap
                'Staff Keuangan',
                'Koordinator CS',
                'Staff Administrasi & Umum',
                // Kontrak - HC
                'Satpam',
                'Sopir',
                'Marketing',
                'Pramusaji',
                'Pramubakti',
                'Call Centre',
                'Card Center',
                'E-Channel',
                'Juru Parkir',
                'Teknisi',
                'Monitoring ATM dan Jaringan',
                'PPI',
                // Kontrak - Umum
                'CS',
                'CS ATM',
                'Ekspedisi',
            ])->nullable();

            $table->date('tanggal_masuk');
            $table->string('no_hp', 20)->nullable();

            // ── Flag gaji di atas UMR ────────────────────────────────────
            // true untuk: Marketing, Call Centre, Card Center,
            //             Teknisi, Monitoring ATM dan Jaringan, PPI
            // false untuk jabatan lainnya (UMR tahun berjalan)
            $table->boolean('gaji_atas_umr')->default(false);

            // ── Flag bersifat shift ──────────────────────────────────────
            // true untuk: Satpam, Card Center, Monitoring ATM dan Jaringan
            $table->boolean('is_shift')->default(false);

            // ── Flag uang makan dibayar mitra ────────────────────────────
            // true  → divisi HC (uang makan dibayar oleh mitra/perusahaan mitra)
            // false → divisi Umum atau karyawan tetap (dibayar CBN)
            $table->boolean('uang_makan_by_mitra')->default(false);

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('karyawan');
    }
};