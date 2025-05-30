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
        Schema::create('add_on_rates', function (Blueprint $table) {
            $table->id();
            $table->string('add_on_rate_type_code')->unique();  // Code for Add-On Rate
            $table->string('add_on_rate_type_name');  // Name for Add-On Rate
            $table->decimal('rate', 8, 2);  // Rate for Add-On (with precision of 2 decimal places)
            $table->decimal('percent_rate', 5, 2);  // Percent Rate for Add-On (with precision of 2 decimal places)
            $table->string('delivery_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('add_on_rates');
    }
};
