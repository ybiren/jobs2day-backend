<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->enum('type', [0, 1]); // 0: review to person, 1: review to job
            $table->unsignedBigInteger('user_id'); // reviewer ID
            $table->unsignedBigInteger('post_id')->nullable(); // post ID (for job reviews)
            $table->unsignedBigInteger('reviewed_user_id')->nullable(); // reviewed user ID (for person reviews)
            $table->unsignedTinyInteger('stars'); // value should be 1–5, validated in application
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->foreign('reviewed_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('reviews');
    }
};
