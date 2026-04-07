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
        Schema::create('payroll', function (Blueprint $table) {
            $table->id();
            $table->string('payroll_no')->unique();
            $table->bigInteger('user_id')->unsigned();
            $table->date('cutoff_from');
            $table->date('cutoff_to');
            $table->decimal('total_days_worked', 10, 2)->default(0);
            $table->decimal('total_hours_worked', 10, 2)->default(0);
            $table->integer('total_late_minutes')->default(0);
            $table->integer('total_undertime_minutes')->default(0);
            $table->decimal('total_overtime_hours', 10, 2)->default(0);
            $table->decimal('gross_salary', 10, 2)->default(0);
            $table->decimal('total_allowance', 10, 2)->default(0);
            $table->decimal('total_deduction', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2)->default(0);
            $table->string('payroll_status')->default('Pending');
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->timestamps();
            
            // Comment out foreign keys for now as there may be type incompatibility
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll');
    }
};
