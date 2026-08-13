<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_slip_gaji', function (Blueprint $table) {
            $table->decimal('gaji_pangan', 12, 2)->default(0)->after('gaji_pokok');
            $table->decimal('tunjangan_jamsostek', 12, 2)->default(0)->after('uang_transport');
            $table->decimal('tunjangan_askes', 12, 2)->default(0)->after('tunjangan_jamsostek');
            $table->decimal('tunjangan_pph21', 12, 2)->default(0)->after('tunjangan_askes');
            $table->decimal('pendapatan_lainnya', 12, 2)->default(0)->after('tunjangan_pph21');
            $table->decimal('potongan_pph21', 12, 2)->default(0)->after('potongan_bpjs_tk');
            $table->decimal('potongan_pinjaman', 12, 2)->default(0)->after('potongan_pph21');
            $table->decimal('potongan_lainnya', 12, 2)->default(0)->after('potongan_pinjaman');
        });
    }

    public function down(): void
    {
        Schema::table('detail_slip_gaji', function (Blueprint $table) {
            $table->dropColumn([
                'gaji_pangan',
                'tunjangan_jamsostek',
                'tunjangan_askes',
                'tunjangan_pph21',
                'pendapatan_lainnya',
                'potongan_pph21',
                'potongan_pinjaman',
                'potongan_lainnya'
            ]);
        });
    }
};
