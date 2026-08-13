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
        Schema::table('detail_perizinan', function (Blueprint $table) {
            // Ubah tipe status_approval dari enum ke string agar fleksibel
            $table->string('status_approval', 50)->change();
            
            // Kolom untuk rekan kerja pengganti
            $table->string('rekan_kerja_id', 20)->nullable()->after('kuota_perizinan_id');
            $table->string('status_rekan', 50)->nullable()->after('status_approval');
            $table->timestamp('rekan_approved_at')->nullable()->after('status_rekan');

            $table->foreign('rekan_kerja_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_perizinan', function (Blueprint $table) {
            $table->dropForeign(['rekan_kerja_id']);
            $table->dropColumn(['rekan_kerja_id', 'status_rekan', 'rekan_approved_at']);
            
            // Kembalikan ke enum awal
            $table->enum('status_approval', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu')->change();
        });
    }
};
