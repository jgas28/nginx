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
        Schema::create('cvr_request_types', function (Blueprint $table) {
            $table->id();
            $table->string('request_code')->unique();  // Unique request code
            $table->string('request_type');  // Type of request (e.g., Expense, Salary)
            $table->string('group_type'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cvr_request_types');
    }
};
