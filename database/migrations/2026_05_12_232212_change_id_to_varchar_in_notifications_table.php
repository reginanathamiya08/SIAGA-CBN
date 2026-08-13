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
        Schema::table('notifications', function (Blueprint $table) {
            DB::statement('ALTER TABLE notifications MODIFY id VARCHAR(20) NOT NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            DB::statement('ALTER TABLE notifications MODIFY id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL');
        });
    }
};
