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
        Schema::table('users', function (Blueprint $table) {
            // Modify the auth_type column to include 'email'
            $table->enum('auth_type', ['facebook', 'apple', 'phone', 'google', 'email'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert the enum to its original values
            $table->enum('auth_type', ['facebook', 'apple', 'phone', 'google'])->change();
        });
    }
};
