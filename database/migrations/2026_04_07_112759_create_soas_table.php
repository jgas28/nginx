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
        Schema::create('soas', function (Blueprint $table) {
            $table->id();
            $table->string('soa_number')->unique();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->date('billing_period_from');
            $table->date('billing_period_to');
            $table->date('statement_date');
            $table->date('due_date')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('outstanding_amount', 15, 2)->default(0);
            $table->enum('status', ['draft', 'pending', 'approved', 'paid', 'overdue'])->default('draft');
            $table->text('notes')->nullable();
            $table->json('delivery_request_ids')->nullable();
            $table->unsignedInteger('created_by');
            $table->timestamps();

            $table->index(['company_id', 'status'], 'idx_company_status');
            $table->index(['billing_period_from', 'billing_period_to'], 'idx_billing_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soas');
    }
};
