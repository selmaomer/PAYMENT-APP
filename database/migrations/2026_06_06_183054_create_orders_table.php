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
        Schema::create('orders', function (Blueprint $table) {
             $table->id();
             $table->string('order_number')->unique();
             $table->string('customer_name');
             $table->string('customer_email');
             $table->decimal('amount', 10, 2);
             $table->string('currency')->default('USD');
             $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
             $table->string('transaction_reference')->nullable();
             $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
