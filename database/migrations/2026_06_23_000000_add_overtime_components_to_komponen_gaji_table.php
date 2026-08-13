<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $components = [
            [
                'id' => 'MKG-00011',
                'nama_komponen' => 'Upah Lembur',
                'tipe' => 'pendapatan',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 'MKG-00012',
                'nama_komponen' => 'Uang Makan Lembur',
                'tipe' => 'pendapatan',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        foreach ($components as $component) {
            DB::table('komponen_gaji')->updateOrInsert(
                ['id' => $component['id']],
                $component
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('komponen_gaji')->whereIn('id', ['MKG-00011', 'MKG-00012'])->delete();
    }
};
