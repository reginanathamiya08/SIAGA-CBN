<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'jenis_karyawan_id')) {
                // Drop foreign key if exists
                try {
                    $table->dropForeign(['jenis_karyawan_id']);
                } catch (\Exception $e) {
                    // Ignore if foreign key was not named default
                }
                $table->dropColumn('jenis_karyawan_id');
            }

            if (Schema::hasColumn('users', 'jenis_karyawan')) {
                $table->dropColumn('jenis_karyawan');
            }
        });

        Schema::dropIfExists('jenis_karyawans');
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('jenis_karyawan_id', 20)->nullable()->after('email');
        });
    }
};
