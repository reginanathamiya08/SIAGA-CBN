<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add user_id to detail_gaji_komponen table
        Schema::table('detail_gaji_komponen', function (Blueprint $table) {
            $table->string('user_id', 20)->nullable()->after('id');
        });

        // 2. Populate user_id for existing records from slip_gaji_periode
        $details = DB::table('detail_gaji_komponen')->get();
        foreach ($details as $detail) {
            $slip = DB::table('slip_gaji_periode')->where('id', $detail->slip_gaji_periode_id)->first();
            if ($slip) {
                DB::table('detail_gaji_komponen')
                    ->where('id', $detail->id)
                    ->update(['user_id' => $slip->user_id]);
            }
        }

        // 3. Make user_id NOT NULL and add foreign key reference
        // First delete any dangling details that have no user_id (if any)
        DB::table('detail_gaji_komponen')->whereNull('user_id')->delete();

        Schema::table('detail_gaji_komponen', function (Blueprint $table) {
            $table->string('user_id', 20)->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        // 4. Drop foreign key constraint on slip_gaji_periode_id first, then make it nullable, then recreate it
        Schema::table('detail_gaji_komponen', function (Blueprint $table) {
            $table->dropForeign(['slip_gaji_periode_id']);
        });

        Schema::table('detail_gaji_komponen', function (Blueprint $table) {
            $table->string('slip_gaji_periode_id', 20)->nullable()->change();
            $table->foreign('slip_gaji_periode_id')->references('id')->on('slip_gaji_periode')->cascadeOnDelete();
        });

        // 5. Migrate existing settings from komponen_gaji_karyawan to detail_gaji_komponen
        if (Schema::hasTable('komponen_gaji_karyawan')) {
            $settings = DB::table('komponen_gaji_karyawan')->get();
            foreach ($settings as $setting) {
                $components = [
                    'MKG-00001' => $setting->gaji_pokok,
                    'MKG-00003' => $setting->uang_makan,
                    'MKG-00004' => $setting->uang_transport,
                    'MKG-00009' => $setting->persen_bpjs_kes,
                    'MKG-00010' => $setting->persen_bpjs_tk,
                ];

                foreach ($components as $compId => $val) {
                    if ($val !== null && $val !== '') {
                        $id = 'DET' . strtoupper(Str::random(17));
                        DB::table('detail_gaji_komponen')->insert([
                            'id' => $id,
                            'user_id' => $setting->user_id,
                            'slip_gaji_periode_id' => null,
                            'komponen_gaji_id' => $compId,
                            'nominal' => $val,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // 6. Drop the komponen_gaji_karyawan table
            Schema::dropIfExists('komponen_gaji_karyawan');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse required for this one-way restructuring
    }
};
