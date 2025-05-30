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
        Schema::create('cash_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('cvr_type');  // Type of the cash voucher
            $table->string('cvr_number')->unique();  // Unique number for the cash voucher
            $table->decimal('amount', 10, 2);  // Amount for the voucher
            $table->string('line_item_id');  // Foreign key for line item
            $table->string('request_type');  // Type of request
            $table->string('requestor');  // Requestor name
            $table->string('mtm');  // MTM (Monthly or similar value)
            $table->string('driver');  // Driver associated with voucher
            $table->string('fleet_card');  // Fleet card reference
            $table->text('helpers');  // Helpers information (e.g., helpers involved in the voucher)
            $table->string('status');  // Voucher status
            $table->string('withholding_tax_id');  // Withholding tax ID
            $table->string('voucher_type');  // Type of voucher
            $table->text('remarks')->nullable();  // Remarks field
            $table->decimal('tax_based_amount', 10, 2);  // Amount based on tax calculations
            $table->string('company_id');  // Foreign key to companies
            $table->string('expense_type_id');  // Expense type ID
            $table->string('supplier_id');  // Supplier ID
            $table->text('description')->nullable();  // Description for the cash voucher
            $table->text('amount_details');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_vouchers');
    }
};
