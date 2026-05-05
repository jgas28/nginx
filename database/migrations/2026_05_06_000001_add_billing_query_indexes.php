<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // delivery_request.delivery_status is used in whereIn for delivered-status filtering
        $drTable = Schema::hasTable('delivery_request') ? 'delivery_request' : 'delivery_requests';
        Schema::table($drTable, function (Blueprint $table) use ($drTable) {
            if (!$this->indexExists($drTable, 'idx_dr_delivery_status')) {
                $table->index('delivery_status', 'idx_dr_delivery_status');
            }
            // created_at used in ORDER BY on edit-soa list
            if (!$this->indexExists($drTable, 'idx_dr_created_at')) {
                $table->index('created_at', 'idx_dr_created_at');
            }
        });

        // soa_delivery_requests.delivery_request_id enables the scoped GROUP BY queries
        if (Schema::hasTable('soa_delivery_requests')) {
            Schema::table('soa_delivery_requests', function (Blueprint $table) {
                if (!$this->indexExists('soa_delivery_requests', 'idx_sodr_dr_id')) {
                    $table->index('delivery_request_id', 'idx_sodr_dr_id');
                }
                // (soa_id, delivery_request_id) covers the conflict-check query
                if (!$this->indexExists('soa_delivery_requests', 'idx_sodr_soa_dr')) {
                    $table->index(['soa_id', 'delivery_request_id'], 'idx_sodr_soa_dr');
                }
            });
        }

        // Refresh statistics so the optimiser uses the new indexes immediately
        \DB::statement("ANALYZE TABLE `{$drTable}`");
        if (Schema::hasTable('soa_delivery_requests')) {
            \DB::statement('ANALYZE TABLE `soa_delivery_requests`');
        }
    }

    public function down(): void
    {
        $drTable = Schema::hasTable('delivery_request') ? 'delivery_request' : 'delivery_requests';
        Schema::table($drTable, function (Blueprint $table) {
            $table->dropIndexIfExists('idx_dr_delivery_status');
            $table->dropIndexIfExists('idx_dr_created_at');
        });

        if (Schema::hasTable('soa_delivery_requests')) {
            Schema::table('soa_delivery_requests', function (Blueprint $table) {
                $table->dropIndexIfExists('idx_sodr_dr_id');
                $table->dropIndexIfExists('idx_sodr_soa_dr');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return collect(\DB::select("SHOW INDEX FROM `{$table}`"))
            ->contains('Key_name', $indexName);
    }
};
