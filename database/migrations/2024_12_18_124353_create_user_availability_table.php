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
        Schema::create('user_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');  // Nullable foreign key to users table
            $table->date('available_at')->nullable();  // Nullable available_at date
            $table->decimal('expected_min_salary', 10, 2)->nullable();  // Nullable expected_min_salary
            $table->decimal('expected_max_salary', 10, 2)->nullable();  // Nullable expected_max_salary
            $table->enum('are_you_mobile', ['yes', 'no', 'flexible'])->nullable();  // Nullable enum field
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_availability');
    }
};
