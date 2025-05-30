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
            $table->string('add_on_rate_type_code'); 
            $table->string('add_on_rate_type_name');
            $table->string('rate');
            $table->string('percent_rate');
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
