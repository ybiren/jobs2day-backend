<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove 'name' field
            $table->dropColumn('name');

            // Add new fields
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('city')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->date('dob')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('auth_id')->nullable();
            $table->enum('auth_type', ['facebook', 'apple', 'phone', 'google'])->nullable();
            $table->string('otp')->nullable();
            $table->text('description')->nullable();

            // Make 'email' and 'password' nullable
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove added columns
            $table->dropColumn([
                'first_name',
                'last_name',
                'city',
                'gender',
                'dob',
                'profile_image',
                'auth_id',
                'auth_type',
                'otp',
                'description'
            ]);

            // Add 'name' column back
            $table->string('name')->nullable();

            // Revert 'email' and 'password' back to not nullable
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
        });
    }
}
