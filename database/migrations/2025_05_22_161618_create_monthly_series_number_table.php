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
        Schema::create('monthly_series_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('company_id'); // Assuming company_id references the 'companies' table
            $table->string('month');  // Month in string format (e.g., 'January', 'February', 'March')
            $table->integer('series_number')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_series_numbers');
    }
};
