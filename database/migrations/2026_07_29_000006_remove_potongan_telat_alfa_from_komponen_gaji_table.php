<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('komponen_gaji')->whereIn('id', ['MKG-00007', 'MKG-00008'])->delete();
    }

    public function down(): void
    {
        DB::table('komponen_gaji')->insertOrIgnore([
            ['id' => 'MKG-00007', 'nama_komponen' => 'Potongan Telat', 'tipe' => 'potongan', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 'MKG-00008', 'nama_komponen' => 'Potongan Izin/Alfa', 'tipe' => 'potongan', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
};
