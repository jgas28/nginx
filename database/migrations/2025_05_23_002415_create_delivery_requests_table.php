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
        Schema::create('delivery_requests', function (Blueprint $table) {
            $table->id();
            $table->string('mtm');  // MTM (Monthly or similar value)
            $table->date('booking_date');  // Date of booking
            $table->date('delivery_date');  // Date of delivery
            $table->string('delivery_type');  // Type of delivery (e.g., Standard, Expedited)
            $table->decimal('delivery_rate', 10, 2);  // Delivery rate
            $table->string('company_id');  // Foreign key for company
            $table->string('project_name');  // Name of the associated project
            $table->string('region_id');  // Foreign key for region
            $table->string('status');  // Status of the delivery request (e.g., Pending, Delivered)
            $table->string('customer_id');  // Foreign key for customer
            $table->string('truck_type_id');  // Foreign key for truck type
            $table->string('area_id');  // Foreign key for area
            $table->string('expense_type_id');  // Foreign key for expense type
            $table->string('delivery_request_type');  // Type of the delivery request
            $table->string('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_requests');
    }
};
