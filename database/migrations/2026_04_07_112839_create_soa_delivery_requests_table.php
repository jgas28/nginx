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
        Schema::create('soa_delivery_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('soa_id');
            $table->unsignedInteger('delivery_request_id');
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['soa_id', 'delivery_request_id'], 'unique_soa_delivery');
        });

        // Add foreign key constraints
        Schema::table('soa_delivery_requests', function (Blueprint $table) {
            $table->foreign('soa_id', 'fk_soa_delivery_soa')
                  ->references('id')
                  ->on('soas')
                  ->onDelete('cascade');

            $table->foreign('delivery_request_id', 'fk_soa_delivery_request')
                  ->references('id')
                  ->on('delivery_request')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('soa_delivery_requests', function (Blueprint $table) {
            $table->dropForeign('fk_soa_delivery_soa');
            $table->dropForeign('fk_soa_delivery_request');
        });

        Schema::dropIfExists('soa_delivery_requests');
    }
};
