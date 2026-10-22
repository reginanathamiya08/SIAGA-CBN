<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Memastikan kolom status pada tabel absensi mendukung seluruh status
 * (hadir, telat, alfa, izin, sakit, cuti, dinas_luar).
 * Dibutuhkan untuk fitur Import Excel Data Absensi Historis.
 */
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
        // Tidak di-rollback agar data izin/sakit/cuti/dinas_luar tidak hilang.
    }
};
