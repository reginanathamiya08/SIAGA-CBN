<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('komponen_gaji')->updateOrInsert(
            ['id' => 'MKG-00013'],
            [
                'id' => 'MKG-00013',
                'nama_komponen' => 'Pendapatan Lainnya',
                'tipe' => 'pendapatan',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }

    public function down(): void
    {
        DB::table('komponen_gaji')->where('id', 'MKG-00013')->delete();
    }
};
