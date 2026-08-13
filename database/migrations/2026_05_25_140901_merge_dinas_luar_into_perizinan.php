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
        // 1. Insert Dinas Luar into jenis_perizinan
        DB::table('jenis_perizinan')->insert([
            'id' => 'JNS-00005',
            'slug' => 'dinas_luar',
            'nama_jenis' => 'Dinas Luar Kota',
            'memotong_kuota' => false,
            'memotong_uang_makan' => false,
            'wajib_upload_bukti' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Drop dinas_luar table
        Schema::dropIfExists('dinas_luar');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Recreate dinas_luar table
        Schema::create('dinas_luar', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('user_id', 20);
            $table->string('tujuan', 200);
            $table->date('tanggal_berangkat');
            $table->date('tanggal_kembali');
            $table->string('file_surat_tugas', 255)->nullable();
            $table->enum('status_approval', [
                'menunggu',
                'disetujui',
                'ditolak',
            ])->default('menunggu');
            $table->string('approved_by', 20)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('alasan_tolak')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });

        // 2. Delete Dinas Luar row from jenis_perizinan
        DB::table('jenis_perizinan')->where('id', 'JNS-00005')->delete();
    }
};
