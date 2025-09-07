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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable(); // For latitude with precision 10, scale 7
            $table->decimal('longitude', 10, 7)->nullable(); // For longitude with precision 10, scale 7
            $table->string('coordinates')->nullable(); // For storing coordinates in string format
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'coordinates']);
        });
    }
};
