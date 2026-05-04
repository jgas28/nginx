<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('soa_delivery_requests', function (Blueprint $table) {
            $table->decimal('delivery_rate_amount', 15, 2)->default(0)->after('amount');
            $table->decimal('accessorial_rate_amount', 15, 2)->default(0)->after('delivery_rate_amount');
            $table->string('billing_type', 20)->default('both')->after('accessorial_rate_amount');
        });
    }

    public function down(): void
    {
        Schema::table('soa_delivery_requests', function (Blueprint $table) {
            $table->dropColumn(['delivery_rate_amount', 'accessorial_rate_amount', 'billing_type']);
        });
    }
};
