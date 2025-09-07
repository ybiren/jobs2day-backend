<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Change the avg_rating column to accept decimals
            $table->decimal('avg_rating', 3, 1)->change();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Rollback the column change
            $table->tinyInteger('avg_rating')->change();
        });
    }
};
