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
        Schema::create('allocations', function (Blueprint $table) {
            $table->id();
            $table->string('dr_id');  // Foreign key referencing the "drivers" table (assuming it's named "drivers")
            $table->string('line_item_id');  // Foreign key referencing the "line_items" table
            $table->string('truck_id');  // Foreign key referencing the "trucks" table
            $table->decimal('amount', 10, 2);  // Amount (with precision of 2 decimal places)
            $table->string('fleet_card_id');  // Foreign key referencing the "fleet_cards" table
            $table->string('driver_id');  // Foreign key referencing the "drivers" table
            $table->boolean('helper');  // Boolean value for helper (true/false)
            $table->string('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allocations');
    }
};
