<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('role_id', 20); // FK ke tabel roles
            $table->string('username', 50)->unique();
            $table->string('password');
            $table->string('nama', 100);
            $table->string('email', 150)->unique()->nullable();
            
            $table->enum('jenis_karyawan', ['tetap', 'kontrak']);
            $table->string('divisi', 50)->nullable();
            $table->string('jabatan', 100)->nullable();
            $table->date('tanggal_masuk')->nullable();
            $table->string('no_hp', 20)->nullable();
            
            $table->boolean('gaji_atas_umr')->default(false);
            $table->boolean('is_shift')->default(false);
            $table->boolean('uang_makan_by_mitra')->default(false);
            
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });

        // Tabel password_reset_tokens (bawaan Laravel, tetap diperlukan)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};