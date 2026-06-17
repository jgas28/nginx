<?php

use Database\Seeders\ModulePermissionSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        (new ModulePermissionSeeder())->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Mirrors the slugs seeded by ModulePermissionSeeder::MODULES. Deleting these module
        // rows cascades to their role_module_permissions rows (see the create_role_module_permissions_table migration).
        DB::table('modules')->whereIn('slug', [
            'companies', 'accessorialTypes', 'addOnRates', 'approvers', 'areas', 'customers',
            'taxes', 'trucks', 'trucksTypes', 'deliveryStatus', 'deliveryTypes', 'distanceTypes',
            'fleetCards', 'expenseTypes', 'warehouses', 'regions', 'suppliers', 'cvr_request_types',
            'deliveryRequestType', 'deliveryRequest', 'cashVoucherRequests', 'employees',
            'coordinators', 'liquidations', 'allocations',
        ])->delete();
    }
};
