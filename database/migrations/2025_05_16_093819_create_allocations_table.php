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
            $table->unsignedBigInteger('dr_id');
            $table->unsignedBigInteger('line_item_id');
            $table->unsignedBigInteger('truck_id');
            $table->decimal('amount', 10, 2);
            $table->unsignedBigInteger('fleet_card_id');
            $table->unsignedBigInteger('driver_id');
            $table->json('helper')->nullable();
            $table->unsignedBigInteger('created_by');
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
