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
        Schema::create('post_availability_days', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->enum('monday', [0, 1])->default(0);
            $table->enum('tuesday', [0, 1])->default(0);
            $table->enum('wednesday', [0, 1])->default(0);
            $table->enum('thursday', [0, 1])->default(0);
            $table->enum('friday', [0, 1])->default(0);
            $table->enum('saturday', [0, 1])->default(0);
            $table->enum('sunday', [0, 1])->default(0);
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
        Schema::dropIfExists('post_availability_days');
    }
};
