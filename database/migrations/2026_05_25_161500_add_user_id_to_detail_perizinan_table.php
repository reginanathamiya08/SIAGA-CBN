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
        Schema::table('detail_perizinan', function (Blueprint $table) {
            $table->string('user_id', 20)->nullable()->after('id');
        });

        // Populate existing data
        $rows = DB::table('detail_perizinan')->get();
        foreach ($rows as $row) {
            $userId = DB::table('kuota_perizinan')->where('id', $row->kuota_perizinan_id)->value('user_id');
            if ($userId) {
                DB::table('detail_perizinan')->where('id', $row->id)->update(['user_id' => $userId]);
            }
        }

        // Make it non-nullable and add foreign key
        Schema::table('detail_perizinan', function (Blueprint $table) {
            $table->string('user_id', 20)->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_perizinan', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
