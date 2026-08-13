<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('configurations')->updateOrInsert(
            ['key' => 'extra_fooding_satpam'],
            [
                'key' => 'extra_fooding_satpam',
                'label' => 'Uang Saku Satpam (Extra Fooding)',
                'value' => '100000',
                'type' => 'number',
                'group' => 'penggajian',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }

    public function down(): void
    {
        DB::table('configurations')->where('key', 'extra_fooding_satpam')->delete();
    }
};
