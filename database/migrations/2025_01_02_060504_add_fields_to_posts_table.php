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
        Schema::table('posts', function (Blueprint $table) {
            $table->decimal('fixed_salary', 10, 2)->nullable()->after('subdomain');
            $table->enum('availability', [0, 1])->nullable()->after('fixed_salary');
            $table->decimal('latitude', 10, 8)->nullable()->after('availability');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->string('coordinates')->nullable()->after('longitude'); // Save full location string if needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['fixed_salary', 'availability', 'latitude', 'longitude', 'coordinates']);
        });
    }
};
