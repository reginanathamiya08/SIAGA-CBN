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
        Schema::create('jenis_karyawans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jenis');
            $table->timestamps();
        });

        // Insert default data
        DB::table('jenis_karyawans')->insert([
            ['id' => 1, 'nama_jenis' => 'Tetap', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nama_jenis' => 'Kontrak', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_karyawans');
    }
};
