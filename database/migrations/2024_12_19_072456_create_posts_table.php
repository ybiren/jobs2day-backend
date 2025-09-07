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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('user_type', ['business', 'person']);
            $table->string('job_role')->nullable();
            $table->string('place')->nullable();
            $table->string('field')->nullable();
            $table->string('subdomain')->nullable();
            $table->decimal('min_offered_salary', 10, 2)->nullable();
            $table->decimal('max_offered_salary', 10, 2)->nullable();
            $table->enum('transport', ['self_transport', 'public_transport', 'company_transport'])->nullable();
            $table->text('job_description')->nullable();
            $table->string('document')->nullable(); // File path
            $table->enum('status', [0, 1])->default(1); // Default is active (1)
            $table->timestamps();

            // Add foreign key relationship
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
