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
        // 1. Create a temporary column to hold the old IDs
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('temp_jns_id')->nullable();
        });

        // 2. Copy data
        DB::table('users')->update(['temp_jns_id' => DB::raw('jenis_karyawan_id')]);

        // 3. Drop foreign key and column
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['jenis_karyawan_id']);
            $table->dropColumn('jenis_karyawan_id');
        });

        // 4. Drop the jenis_karyawans table
        Schema::dropIfExists('jenis_karyawans');

        // 5. Recreate jenis_karyawans with string ID
        Schema::create('jenis_karyawans', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('nama_jenis');
            $table->timestamps();
        });

        // 6. Insert new data with custom IDs
        DB::table('jenis_karyawans')->insert([
            ['id' => 'JNS-00001', 'nama_jenis' => 'Tetap', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 'JNS-00002', 'nama_jenis' => 'Kontrak', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 7. Add jenis_karyawan_id as string(20) to users
        Schema::table('users', function (Blueprint $table) {
            $table->string('jenis_karyawan_id', 20)->nullable()->after('email');
        });

        // 8. Map data back from temp_jns_id
        DB::table('users')->where('temp_jns_id', 1)->update(['jenis_karyawan_id' => 'JNS-00001']);
        DB::table('users')->where('temp_jns_id', 2)->update(['jenis_karyawan_id' => 'JNS-00002']);

        // 9. Add foreign key back and drop temp_jns_id
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('jenis_karyawan_id')->references('id')->on('jenis_karyawans')->nullOnDelete();
            $table->dropColumn('temp_jns_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting this complex structural change would require complex logic 
        // to map strings back to integer IDs and recreate the original table structure.
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['jenis_karyawan_id']);
            $table->dropColumn('jenis_karyawan_id');
        });
        Schema::dropIfExists('jenis_karyawans');
    }
};
