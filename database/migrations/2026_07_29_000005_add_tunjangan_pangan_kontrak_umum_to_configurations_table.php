<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('configurations')->updateOrInsert(
            ['key' => 'tunjangan_pangan_kontrak_umum'],
            [
                'key' => 'tunjangan_pangan_kontrak_umum',
                'label' => 'Tunjangan Pangan (Kontrak Umum)',
                'value' => '805000',
                'type' => 'number',
                'group' => 'gaji',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }

    public function down(): void
    {
        DB::table('configurations')->where('key', 'tunjangan_pangan_kontrak_umum')->delete();
    }
};
