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
        Schema::create('post_availability_specific_dates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->date('availability_date');
            $table->timestamps();

            // Foreign key relationship
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('post_availability_specific_dates');
    }
};
