<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The key fix: (status, created_at) lets MySQL satisfy the WHERE + ORDER BY from
        // a single index walk, eliminating the filesort on 1600+ rows.
        // Drop the separate status index first to avoid redundancy.
        Schema::table('delivery_request_line_items', function (Blueprint $table) {
            $table->dropIndex('idx_drli_status');
            $table->index(['status', 'created_at'], 'idx_drli_status_created');
        });

        // delivery_request: company_id and customer_id are used in every billing JOIN
        // but have no indexes — causes full scans on that table too.
        $drTable = Schema::hasTable('delivery_request') ? 'delivery_request' : 'delivery_requests';
        Schema::table($drTable, function (Blueprint $table) use ($drTable) {
            if (!$this->indexExists($drTable, 'idx_dr_company_id')) {
                $table->index('company_id', 'idx_dr_company_id');
            }
            if (!$this->indexExists($drTable, 'idx_dr_customer_id')) {
                $table->index('customer_id', 'idx_dr_customer_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('delivery_request_line_items', function (Blueprint $table) {
            $table->dropIndex('idx_drli_status_created');
            $table->index('status', 'idx_drli_status');
        });

        $drTable = Schema::hasTable('delivery_request') ? 'delivery_request' : 'delivery_requests';
        Schema::table($drTable, function (Blueprint $table) {
            $table->dropIndex('idx_dr_company_id');
            $table->dropIndex('idx_dr_customer_id');
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return collect(\DB::select("SHOW INDEX FROM `{$table}`"))
            ->contains('Key_name', $indexName);
    }
};
