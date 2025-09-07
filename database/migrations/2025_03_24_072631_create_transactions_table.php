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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('payment_type'); // Define accepted types in the model
            $table->foreignId('type_id')->nullable(); // Nullable for flexibility
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Foreign key
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending'); // pending, success, failed
            $table->text('response')->nullable();
            $table->string('expdate'); // Expiry date (MMYY)
            $table->string('cvv'); // CVV code
            $table->string('ccno'); // Credit card number
            $table->string('cred_type'); // Credit card type
            $table->timestamps();
        });

    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
