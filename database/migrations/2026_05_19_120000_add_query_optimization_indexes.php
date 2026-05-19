<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfMissing('allocations', ['dr_id', 'trip_type', 'sequence'], 'idx_alloc_dr_trip_seq');
        $this->addIndexIfMissing('allocations', ['dr_id', 'trip_type', 'created_at'], 'idx_alloc_dr_trip_created');

        $this->addIndexIfMissing('cash_vouchers', ['dr_id', 'cvr_type', 'sequence'], 'idx_cv_dr_type_sequence');
        $this->addIndexIfMissing('cash_vouchers', ['status', 'cvr_type', 'created_at'], 'idx_cv_status_type_created');

        $this->addIndexIfMissing('cvr_approvals', ['cvr_id'], 'idx_cvr_approvals_cvr_id');

        $this->addIndexIfMissing('monthly_series_numbers', ['company_id', 'month'], 'idx_msn_company_month');

        $this->addIndexIfMissing('delivery_request', ['mtm'], 'idx_dr_mtm');
        $this->addIndexIfMissing('delivery_request_line_items', ['mtm', 'status'], 'idx_drli_mtm_status');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('delivery_request_line_items', 'idx_drli_mtm_status');
        $this->dropIndexIfExists('delivery_request', 'idx_dr_mtm');

        $this->dropIndexIfExists('monthly_series_numbers', 'idx_msn_company_month');

        $this->dropIndexIfExists('cvr_approvals', 'idx_cvr_approvals_cvr_id');

        $this->dropIndexIfExists('cash_vouchers', 'idx_cv_status_type_created');
        $this->dropIndexIfExists('cash_vouchers', 'idx_cv_dr_type_sequence');

        $this->dropIndexIfExists('allocations', 'idx_alloc_dr_trip_created');
        $this->dropIndexIfExists('allocations', 'idx_alloc_dr_trip_seq');
    }

    private function addIndexIfMissing(string $table, array $columns, string $indexName): void
    {
        if (!Schema::hasTable($table) || $this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($columns, $indexName) {
            $tableBlueprint->index($columns, $indexName);
        });
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (!Schema::hasTable($table) || !$this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($indexName) {
            $tableBlueprint->dropIndex($indexName);
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select('SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?', [$indexName]);

        return !empty($result);
    }
};
