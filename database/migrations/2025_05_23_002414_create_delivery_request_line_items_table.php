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
        Schema::create('delivery_request_line_items', function (Blueprint $table) {
            $table->id();
            $table->string('mtm');
          
            $table->json('warehouse_id')->nullable(); // Storing multiple warehouse IDs as JSON
            $table->json('site_name')->nullable(); // Storing multiple site names as JSON
            $table->json('delivery_number')->nullable(); // Storing multiple delivery numbers as JSON
            $table->string('truck_id')->nullable();
            $table->string('status')->nullable(); // E.g., Pending, Delivered, etc.
            $table->string('delivery_status')->nullable(); // E.g., On Time, Delayed, etc.
            $table->json('delivery_address')->nullable(); // Storing multiple delivery addresses as JSON
            $table->string('distance_type')->nullable(); // E.g., Estimated, Actual, etc.
            $table->json('add_on_rate')->nullable(); // Decimal for additional rate
            $table->json('accessorial_type')->nullable(); 
            $table->json('accessorial_rate')->nullable(); 

            $table->string('dr_id');  // Foreign key for DR (Delivery Request)
            $table->string('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_request_line_items');
    }
};
