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
        Schema::create('billings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('soa_id'); // Assuming soa_id is a foreign key, unsigned integer
            $table->string('soa_number'); // Assuming soa_number is a string
            $table->unsignedBigInteger('dr_id'); // Assuming dr_id is a foreign key, unsigned integer
            $table->unsignedBigInteger('company_id'); // Assuming company_id is a foreign key, unsigned integer
            $table->unsignedBigInteger('withholding_tax_id'); // Assuming withholding_tax_id is a foreign key, unsigned integer
            $table->string('billed_to'); // Assuming billed_to is a string (company or person name)
            $table->text('billing_address'); // Assuming billing_address is a text field
            $table->decimal('total_price', 10, 2); // Assuming total_price is a decimal (max 10 digits, 2 decimal points)
            $table->date('billing_date'); // Assuming billing_date is a date
            $table->string('status'); // Assuming status is a string
            $table->unsignedBigInteger('created_by'); // Assuming created_by is a foreign key, unsigned integer
            $table->timestamps(); // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
};
