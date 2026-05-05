<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('soas', function (Blueprint $table) {
            $table->decimal('vat_amount', 15, 2)->default(0)->after('adjustment_remarks');
            $table->decimal('withholding_tax_rate', 5, 2)->nullable()->after('vat_amount');
            $table->decimal('withholding_tax_amount', 15, 2)->default(0)->after('withholding_tax_rate');
        });
    }

    public function down(): void
    {
        Schema::table('soas', function (Blueprint $table) {
            $table->dropColumn(['vat_amount', 'withholding_tax_rate', 'withholding_tax_amount']);
        });
    }
};
