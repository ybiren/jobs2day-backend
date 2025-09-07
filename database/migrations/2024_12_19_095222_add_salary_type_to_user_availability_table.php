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
        Schema::table('user_availability', function (Blueprint $table) {
            $table->enum('salary_type', [1, 2, 3, 4])->nullable()->after('are_you_mobile'); // Adding the enum column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        {
            Schema::table('user_availability', function (Blueprint $table) {
                $table->dropColumn('salary_type'); // Removing the column if rollback is needed
            });
        }
    }
};
