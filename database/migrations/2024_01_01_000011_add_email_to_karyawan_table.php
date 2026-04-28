<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            // Tambah kolom email setelah no_hp
            // Email digunakan untuk menerima notifikasi approval izin, lembur, dinas luar
            $table->string('email')->nullable()->unique()->after('no_hp');
        });
    }

    public function down(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};