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
        Schema::table('job_applications', function (Blueprint $table) {
            // Add nullable document_1 and document_2 columns to the job_applications table
            $table->string('document_1')->nullable();
            $table->string('document_2')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            // Remove the columns in case of rollback
            $table->dropColumn(['document_1', 'document_2']);
        });
    }
};
