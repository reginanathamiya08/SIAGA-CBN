<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_komponen_gaji', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('nama_komponen');
            $table->enum('tipe', ['pendapatan', 'potongan']);
            $table->timestamps();
        });

        // Seed default components
        $components = [
            ['id' => 'MKG-00001', 'nama_komponen' => 'Gaji Pokok', 'tipe' => 'pendapatan', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 'MKG-00002', 'nama_komponen' => 'Tunjangan Pangan', 'tipe' => 'pendapatan', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 'MKG-00003', 'nama_komponen' => 'Uang Makan', 'tipe' => 'pendapatan', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 'MKG-00004', 'nama_komponen' => 'Uang Transport', 'tipe' => 'pendapatan', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 'MKG-00005', 'nama_komponen' => 'Tunjangan Jamsostek', 'tipe' => 'pendapatan', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 'MKG-00006', 'nama_komponen' => 'Tunjangan Askes', 'tipe' => 'pendapatan', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 'MKG-00007', 'nama_komponen' => 'Potongan Telat', 'tipe' => 'potongan', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 'MKG-00008', 'nama_komponen' => 'Potongan Izin/Alfa', 'tipe' => 'potongan', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 'MKG-00009', 'nama_komponen' => 'Potongan BPJS Kesehatan', 'tipe' => 'potongan', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 'MKG-00010', 'nama_komponen' => 'Potongan BPJS Ketenagakerjaan', 'tipe' => 'potongan', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('master_komponen_gaji')->insert($components);
    }

    public function down(): void
    {
        Schema::dropIfExists('master_komponen_gaji');
    }
};
