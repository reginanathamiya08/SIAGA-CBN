<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ubah relasi password_reset_tokens dari 1-1 menjadi 1-M.
     *
     * Sebelum: email sebagai PRIMARY KEY → 1 user hanya bisa punya 1 token.
     * Sesudah: id sebagai PRIMARY KEY, email sebagai FK biasa → 1 user bisa punya banyak token.
     */
    public function up(): void
    {
        // 1. Hapus FK dan PK lama terlebih dahulu
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            // Drop foreign key yang ditambahkan di migration sebelumnya
            $table->dropForeign(['email']);
            // Drop primary key dari email
            $table->dropPrimary(['email']);
        });

        // 2. Tambah kolom id sebagai PK baru, ubah email jadi indexed FK biasa
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            // Tambah id auto-increment sebagai primary key baru
            $table->id()->first();
            // Index email agar query tetap cepat
            $table->index('email');
            // Re-add foreign key dengan referensi ke users.email
            $table->foreign('email')
                  ->references('email')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Kembalikan ke struktur asal (1-1): email sebagai PK.
     */
    public function down(): void
    {
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->dropForeign(['email']);
            $table->dropIndex(['email']);
            $table->dropColumn('id');
        });

        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->primary('email');
            $table->foreign('email')
                  ->references('email')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
};
