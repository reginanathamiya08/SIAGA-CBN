<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $user_id) {
            $user_id->id();
            $user_id->string('user_id'); // refers to karyawan id
            $user_id->string('title');
            $user_id->text('message');
            $user_id->string('type')->default('info'); // info, success, warning, danger
            $user_id->boolean('is_read')->default(false);
            $user_id->string('link')->nullable(); // link to detail page
            $user_id->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
