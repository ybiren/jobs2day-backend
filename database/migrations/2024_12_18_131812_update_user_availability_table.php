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
        Schema::table('user_availability', function (Blueprint $table) {
            $table->string('available_at')->change(); // Change to varchar
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_availability', function (Blueprint $table) {
            $table->date('available_at')->change(); // Revert back to date if needed
        });
    }
};
