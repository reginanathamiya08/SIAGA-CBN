<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('jenis_karyawan_id')->nullable()->after('email');
        });

        // Migrate data
        DB::table('users')->where('jenis_karyawan', 'tetap')->update(['jenis_karyawan_id' => 1]);
        DB::table('users')->where('jenis_karyawan', 'kontrak')->update(['jenis_karyawan_id' => 2]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('jenis_karyawan');
            $table->foreign('jenis_karyawan_id')->references('id')->on('jenis_karyawans')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['jenis_karyawan_id']);
            $table->enum('jenis_karyawan', ['tetap', 'kontrak'])->after('email')->nullable();
        });

        DB::table('users')->where('jenis_karyawan_id', 1)->update(['jenis_karyawan' => 'tetap']);
        DB::table('users')->where('jenis_karyawan_id', 2)->update(['jenis_karyawan' => 'kontrak']);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('jenis_karyawan_id');
        });
    }
};
