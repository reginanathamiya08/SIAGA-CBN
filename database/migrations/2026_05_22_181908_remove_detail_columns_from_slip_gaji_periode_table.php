<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slip_gaji_periode', function (Blueprint $table) {
            $table->dropColumn([
                'gaji_pokok',
                'gaji_pangan',
                'uang_makan',
                'uang_transport',
                'tunjangan_jamsostek',
                'tunjangan_askes',
                'tunjangan_pph21',
                'pendapatan_lainnya',
                'potongan_telat',
                'potongan_izin',
                'potongan_bpjs_kes',
                'potongan_bpjs_tk',
                'potongan_pph21',
                'potongan_pinjaman',
                'potongan_lainnya'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('slip_gaji_periode', function (Blueprint $table) {
            $table->decimal('gaji_pokok', 15, 2)->default(0)->after('komponen_id');
            $table->decimal('gaji_pangan', 15, 2)->default(0)->after('gaji_pokok');
            $table->decimal('uang_makan', 15, 2)->default(0)->after('gaji_pangan');
            $table->decimal('uang_transport', 15, 2)->default(0)->after('uang_makan');
            $table->decimal('tunjangan_jamsostek', 15, 2)->default(0)->after('uang_transport');
            $table->decimal('tunjangan_askes', 15, 2)->default(0)->after('tunjangan_jamsostek');
            $table->decimal('tunjangan_pph21', 15, 2)->default(0)->after('tunjangan_askes');
            $table->decimal('pendapatan_lainnya', 15, 2)->default(0)->after('tunjangan_pph21');
            $table->decimal('potongan_telat', 15, 2)->default(0)->after('total_cuti');
            $table->decimal('potongan_izin', 15, 2)->default(0)->after('potongan_telat');
            $table->decimal('potongan_bpjs_kes', 15, 2)->default(0)->after('potongan_izin');
            $table->decimal('potongan_bpjs_tk', 15, 2)->default(0)->after('potongan_bpjs_kes');
            $table->decimal('potongan_pph21', 15, 2)->default(0)->after('potongan_bpjs_tk');
            $table->decimal('potongan_pinjaman', 15, 2)->default(0)->after('potongan_pph21');
            $table->decimal('potongan_lainnya', 15, 2)->default(0)->after('potongan_pinjaman');
        });
    }
};
