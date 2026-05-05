<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // delivery_request_line_items has no indexes — dr_id and status are hit on every billing query
        Schema::table('delivery_request_line_items', function (Blueprint $table) {
            $table->index('dr_id', 'idx_drli_dr_id');
            $table->index('status', 'idx_drli_status');
            $table->index(['dr_id', 'status'], 'idx_drli_dr_id_status');
        });

        // soa_delivery_requests: index on soa_id speeds up getAttachedDeliveryRequests lookups
        if (Schema::hasTable('soa_delivery_requests')) {
            Schema::table('soa_delivery_requests', function (Blueprint $table) {
                if (!$this->indexExists('soa_delivery_requests', 'idx_sodr_soa_id')) {
                    $table->index('soa_id', 'idx_sodr_soa_id');
                }
            });
        }

        // soas: created_at is used in ORDER BY for index listing
        Schema::table('soas', function (Blueprint $table) {
            $table->index('created_at', 'idx_soas_created_at');
            $table->index('customer_id', 'idx_soas_customer_id');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_request_line_items', function (Blueprint $table) {
            $table->dropIndex('idx_drli_dr_id');
            $table->dropIndex('idx_drli_status');
            $table->dropIndex('idx_drli_dr_id_status');
        });

        if (Schema::hasTable('soa_delivery_requests')) {
            Schema::table('soa_delivery_requests', function (Blueprint $table) {
                $table->dropIndex('idx_sodr_soa_id');
            });
        }

        Schema::table('soas', function (Blueprint $table) {
            $table->dropIndex('idx_soas_created_at');
            $table->dropIndex('idx_soas_customer_id');
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return collect(\DB::select("SHOW INDEX FROM `{$table}`"))
            ->contains('Key_name', $indexName);
    }
};
