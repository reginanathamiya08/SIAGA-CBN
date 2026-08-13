<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen_karyawan', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('user_id', 20);
            
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->enum('jenis_dokumen', [
                'KTA',
                'SIM',
                'ijazah',
                'sertifikat',
                'lainnya',
            ]);
            $table->string('file_path', 255);
            $table->timestamp('uploaded_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_karyawan');
    }
};