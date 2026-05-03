<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('soas', function (Blueprint $table) {
            $table->decimal('subtotal_amount', 15, 2)->default(0)->after('total_amount');
            $table->string('discount_type', 20)->nullable()->after('subtotal_amount');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('discount_type');
            $table->text('discount_remarks')->nullable()->after('discount_amount');
            $table->decimal('adjustment_amount', 15, 2)->default(0)->after('discount_remarks');
            $table->text('adjustment_remarks')->nullable()->after('adjustment_amount');
        });
    }

    public function down(): void
    {
        Schema::table('soas', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal_amount',
                'discount_type',
                'discount_amount',
                'discount_remarks',
                'adjustment_amount',
                'adjustment_remarks',
            ]);
        });
    }
};
