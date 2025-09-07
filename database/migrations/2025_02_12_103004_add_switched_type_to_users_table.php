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
            $table->renameColumn('is_onboarding', 'is_onboarding_person');
            $table->enum('is_onboarding_business', ['0', '1'])->nullable()->after('is_onboarding_person');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('is_onboarding_person', 'is_onboarding');
            $table->dropColumn('is_onboarding_business');
        });
    }

};
