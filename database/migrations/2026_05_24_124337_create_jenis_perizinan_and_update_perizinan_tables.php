<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create jenis_perizinan table
        Schema::create('jenis_perizinan', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('slug', 50)->unique();
            $table->string('nama_jenis', 100);
            $table->boolean('memotong_kuota')->default(false);
            $table->boolean('memotong_uang_makan')->default(false);
            $table->boolean('wajib_upload_bukti')->default(false);
            $table->timestamps();
        });

        // Seed default kinds
        DB::table('jenis_perizinan')->insert([
            [
                'id' => 'JNS-00001',
                'slug' => 'cuti',
                'nama_jenis' => 'Cuti Tahunan',
                'memotong_kuota' => true,
                'memotong_uang_makan' => true,
                'wajib_upload_bukti' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'JNS-00002',
                'slug' => 'izin_pribadi',
                'nama_jenis' => 'Izin Pribadi',
                'memotong_kuota' => true,
                'memotong_uang_makan' => false,
                'wajib_upload_bukti' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'JNS-00003',
                'slug' => 'sakit_surat',
                'nama_jenis' => 'Sakit (Dengan Surat Dokter)',
                'memotong_kuota' => false,
                'memotong_uang_makan' => false,
                'wajib_upload_bukti' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'JNS-00004',
                'slug' => 'sakit_no_surat',
                'nama_jenis' => 'Sakit (Tanpa Surat Dokter)',
                'memotong_kuota' => true,
                'memotong_uang_makan' => false,
                'wajib_upload_bukti' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 2. Rename perizinan to detail_perizinan
        // Note: drop foreign keys first
        Schema::table('perizinan', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['approved_by']);
            if (Schema::hasColumn('perizinan', 'slip_gaji_periode_id')) {
                $table->dropForeign(['slip_gaji_periode_id']);
            }
        });

        Schema::rename('perizinan', 'detail_perizinan');

        // 3. Alter detail_perizinan table
        Schema::table('detail_perizinan', function (Blueprint $table) {
            $table->string('jenis_perizinan_id', 20)->nullable()->after('id');
            $table->string('kuota_perizinan_id', 20)->nullable()->after('jenis_perizinan_id');
        });

        // 4. Migrate existing data
        $rows = DB::table('detail_perizinan')->get();
        foreach ($rows as $row) {
            $jenisId = match ($row->jenis_izin) {
                'cuti' => 'JNS-00001',
                'izin_pribadi' => 'JNS-00002',
                'sakit_surat' => 'JNS-00003',
                'sakit_no_surat' => 'JNS-00004',
                default => 'JNS-00001',
            };

            $year = date('Y', strtotime($row->tanggal_mulai));
            $kuota = DB::table('kuota_perizinan')
                ->where('user_id', $row->user_id)
                ->where('tahun', $year)
                ->first();

            if (!$kuota) {
                $kuotaId = 'CUT-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
                $configVal = DB::table('configurations')->where('key', 'kuota_cuti_tahunan')->value('value') ?? 12;
                DB::table('kuota_perizinan')->insert([
                    'id' => $kuotaId,
                    'user_id' => $row->user_id,
                    'tahun' => $year,
                    'kuota_total' => $configVal,
                    'terpakai' => 0,
                    'sisa' => $configVal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $kuotaId = $kuota->id;
            }

            DB::table('detail_perizinan')
                ->where('id', $row->id)
                ->update([
                    'jenis_perizinan_id' => $jenisId,
                    'kuota_perizinan_id' => $kuotaId,
                ]);
        }

        // 5. Make new columns non-nullable and drop obsolete columns
        Schema::table('detail_perizinan', function (Blueprint $table) {
            $table->string('jenis_perizinan_id', 20)->nullable(false)->change();
            $table->string('kuota_perizinan_id', 20)->nullable(false)->change();
            $table->dropColumn('jenis_izin');
            $table->dropColumn('user_id');
        });

        // 6. Re-add foreign keys with new naming convention
        Schema::table('detail_perizinan', function (Blueprint $table) {
            $table->foreign('jenis_perizinan_id')
                  ->references('id')
                  ->on('jenis_perizinan')
                  ->cascadeOnDelete();
            
            $table->foreign('kuota_perizinan_id')
                  ->references('id')
                  ->on('kuota_perizinan')
                  ->cascadeOnDelete();

            $table->foreign('approved_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();

            $table->foreign('slip_gaji_periode_id')
                  ->references('id')
                  ->on('slip_gaji_periode')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Drop foreign keys on detail_perizinan
        Schema::table('detail_perizinan', function (Blueprint $table) {
            $table->dropForeign(['jenis_perizinan_id']);
            $table->dropForeign(['kuota_perizinan_id']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['slip_gaji_periode_id']);
        });

        // 2. Add obsolete columns back as nullable
        Schema::table('detail_perizinan', function (Blueprint $table) {
            $table->string('user_id', 20)->nullable()->after('id');
            $table->enum('jenis_izin', ['cuti', 'izin_pribadi', 'sakit_surat', 'sakit_no_surat'])->nullable()->after('user_id');
        });

        // 3. Rollback data
        $rows = DB::table('detail_perizinan')->get();
        foreach ($rows as $row) {
            $userId = DB::table('kuota_perizinan')->where('id', $row->kuota_perizinan_id)->value('user_id');
            $jenisIzin = match ($row->jenis_perizinan_id) {
                'JNS-00001' => 'cuti',
                'JNS-00002' => 'izin_pribadi',
                'JNS-00003' => 'sakit_surat',
                'JNS-00004' => 'sakit_no_surat',
                default => 'cuti',
            };

            DB::table('detail_perizinan')
                ->where('id', $row->id)
                ->update([
                    'user_id' => $userId,
                    'jenis_izin' => $jenisIzin,
                ]);
        }

        // 4. Make them non-nullable
        Schema::table('detail_perizinan', function (Blueprint $table) {
            $table->string('user_id', 20)->nullable(false)->change();
            $table->enum('jenis_izin', ['cuti', 'izin_pribadi', 'sakit_surat', 'sakit_no_surat'])->nullable(false)->change();
            $table->dropColumn('jenis_perizinan_id');
            $table->dropColumn('kuota_perizinan_id');
        });

        // 5. Rename table back
        Schema::rename('detail_perizinan', 'perizinan');

        // 6. Restore original foreign keys
        Schema::table('perizinan', function (Blueprint $table) {
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
            
            $table->foreign('approved_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();

            $table->foreign('slip_gaji_periode_id')
                  ->references('id')
                  ->on('slip_gaji_periode')
                  ->nullOnDelete();
        });

        // 7. Drop jenis_perizinan table
        Schema::dropIfExists('jenis_perizinan');
    }
};
