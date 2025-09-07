<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chat_channels', function (Blueprint $table) {
            $table->id();
            $table->string('channel')->unique();
            $table->boolean('is_blocked')->default(false);
            $table->foreignId('blocked_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_channels');
    }
};
