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
        Schema::create('liquidations', function (Blueprint $table) {
            $table->id();

            // Individual expense fields
            $table->decimal('allowance', 10, 2)->nullable();
            $table->decimal('manpower', 10, 2)->nullable();
            $table->decimal('hauling', 10, 2)->nullable();
            $table->decimal('right_of_way', 10, 2)->nullable();
            $table->decimal('roro_expense', 10, 2)->nullable();
            $table->decimal('cash_charge', 10, 2)->nullable();
            $table->string('gasoline_allowance_type')->nullable();
            $table->decimal('gasoline_cash_company', 10, 2)->nullable();
            $table->decimal('gasoline_allowance_cash', 10, 2)->nullable();
            $table->decimal('gasoline_allowance_card', 10, 2)->nullable();

            // RFID fields
            $table->string('rfid_autosweep_type')->nullable();
            $table->decimal('rfid_autosweep_amount', 10, 2)->nullable();
            $table->string('rfid_easytrip_type')->nullable();
            $table->decimal('rfid_easytrip_amount', 10, 2)->nullable();

            // Other people-related fields
            $table->string('prepared_by')->nullable();
            $table->string('noted_by')->nullable();
            $table->string('validated_by')->nullable();
            $table->string('collected_by')->nullable();
            $table->string('approved_by')->nullable();

            // Others (JSON format)
            $table->json('others')->nullable();
            $table->string('status')->nullable();
            $table->string('cvr_approval_id');
            $table->string('cvr_number')->nullable();
            $table->string('cvr_id')->nullable();
            $table->string('mtm')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liquidations');
    }
};
