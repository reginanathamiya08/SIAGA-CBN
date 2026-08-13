<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = Schema::hasTable('absensi') ? 'absensi' : (Schema::hasTable('detail_absensi') ? 'detail_absensi' : null);

        if ($tableName) {
            DB::statement("ALTER TABLE {$tableName} MODIFY COLUMN status ENUM('hadir', 'telat', 'alfa', 'izin', 'sakit', 'cuti', 'dinas_luar') NOT NULL DEFAULT 'hadir'");
        }
    }

    public function down(): void
    {
        $tableName = Schema::hasTable('absensi') ? 'absensi' : (Schema::hasTable('detail_absensi') ? 'detail_absensi' : null);

        if ($tableName) {
            DB::statement("ALTER TABLE {$tableName} MODIFY COLUMN status ENUM('hadir', 'telat', 'alfa', 'izin', 'sakit', 'cuti', 'dinas_luar') NOT NULL DEFAULT 'hadir'");
        }
    }
};
