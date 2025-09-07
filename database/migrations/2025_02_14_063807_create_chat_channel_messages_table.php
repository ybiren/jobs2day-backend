<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chat_channel_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_channel_id')->constrained('chat_channels')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->string('file')->nullable();
            $table->boolean('is_seen')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_channel_messages');
    }
};
