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
        Schema::create('cvr_approvals', function (Blueprint $table) {
            $table->id();
            $table->string('payment_type');  // Type of payment (e.g., Cash, Credit)
            $table->string('payment_name');  // Name of the payment method
            $table->string('reference_number');  // Reference number for the payment
            $table->decimal('amount', 10, 2);  // Amount for the payment
            $table->string('receiver');  // Name of the receiver
            $table->string('source');  // Source of the payment (e.g., Bank, Customer)
            $table->string('charge');  // Charges applied to the payment
            $table->string('cvr_number')->unique();  // Unique number for the CVR (Cash Voucher Request)
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cvr_approvals');
    }
};
