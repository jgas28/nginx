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
        Schema::create('soa_delivery_line_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('soa_id');
            $table->unsignedBigInteger('delivery_request_line_item_id');
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['soa_id', 'delivery_request_line_item_id'], 'unique_soa_line_item');
        });

        // Add foreign key constraints
        Schema::table('soa_delivery_line_items', function (Blueprint $table) {
            $table->foreign('soa_id', 'fk_soa_delivery_line_items_soa')
                  ->references('id')
                  ->on('soas')
                  ->onDelete('cascade');

            $table->foreign('delivery_request_line_item_id', 'fk_soa_delivery_line_items_item')
                  ->references('id')
                  ->on('delivery_request_line_items')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('soa_delivery_line_items', function (Blueprint $table) {
            $table->dropForeign('fk_soa_delivery_line_items_soa');
            $table->dropForeign('fk_soa_delivery_line_items_item');
        });

        Schema::dropIfExists('soa_delivery_line_items');
    }
};
