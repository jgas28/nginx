<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // companies.company_name and customers.name are ORDER BY targets — no indexes exist
        Schema::table('companies', function (Blueprint $table) {
            $table->index('company_name', 'idx_companies_company_name');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->index('name', 'idx_customers_name');
        });

        // Update table statistics so MySQL's cost estimator picks better query plans
        \DB::statement('ANALYZE TABLE delivery_request_line_items');
        \DB::statement('ANALYZE TABLE companies');
        \DB::statement('ANALYZE TABLE customers');
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropIndex('idx_companies_company_name');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('idx_customers_name');
        });
    }
};
