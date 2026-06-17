<?php

namespace Database\Seeders;

use App\Http\Controllers\AccessorialTypeController;
use App\Http\Controllers\AddOnRateController;
use App\Http\Controllers\AllocationController;
use App\Http\Controllers\ApproverController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\CashVoucherController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CoordinatorsController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CVR_Request_TypeController;
use App\Http\Controllers\DeliveryRequestController;
use App\Http\Controllers\DeliveryRequestTypeController;
use App\Http\Controllers\DeliveryStatusController;
use App\Http\Controllers\DeliveryTypeController;
use App\Http\Controllers\DistanceTypeController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ExpenseTypeController;
use App\Http\Controllers\FleetCardController;
use App\Http\Controllers\LiquidationController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TruckController;
use App\Http\Controllers\TruckTypeController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WithholdingTaxController;
use App\Models\Module;
use App\Models\Role;
use App\Models\RoleModulePermission;
use Illuminate\Database\Seeder;

class ModulePermissionSeeder extends Seeder
{
    /**
     * One entry per controller with a Route::resource(...) registration in routes/web.php.
     * AdminController is deliberately excluded -- its resourceful methods are really part of
     * the CV-admin-approval workflow, not plain settings CRUD. DashboardController and
     * DetailsController have no Route::resource() at all.
     *
     * Format: slug => [display name, controller FQCN].
     */
    private const MODULES = [
        'companies' => ['Companies', CompanyController::class],
        'accessorialTypes' => ['Accessorial Types', AccessorialTypeController::class],
        'addOnRates' => ['Add-On Rates', AddOnRateController::class],
        'approvers' => ['Approvers', ApproverController::class],
        'areas' => ['Areas', AreaController::class],
        'customers' => ['Customers', CustomerController::class],
        'taxes' => ['Withholding Taxes', WithholdingTaxController::class],
        'trucks' => ['Trucks', TruckController::class],
        'trucksTypes' => ['Truck Types', TruckTypeController::class],
        'deliveryStatus' => ['Delivery Status', DeliveryStatusController::class],
        'deliveryTypes' => ['Delivery Types', DeliveryTypeController::class],
        'distanceTypes' => ['Distance Types', DistanceTypeController::class],
        'fleetCards' => ['Fleet Cards', FleetCardController::class],
        'expenseTypes' => ['Expense Types', ExpenseTypeController::class],
        'warehouses' => ['Warehouses', WarehouseController::class],
        'regions' => ['Regions', RegionController::class],
        'suppliers' => ['Suppliers', SupplierController::class],
        'cvr_request_types' => ['CVR Request Types', CVR_Request_TypeController::class],
        'deliveryRequestType' => ['Delivery Request Type', DeliveryRequestTypeController::class],
        'deliveryRequest' => ['Delivery Request', DeliveryRequestController::class],
        'cashVoucherRequests' => ['Cash Voucher Requests', CashVoucherController::class],
        'employees' => ['Employees', EmployeeController::class],
        'coordinators' => ['Coordinators', CoordinatorsController::class],
        'liquidations' => ['Liquidations', LiquidationController::class],
        'allocations' => ['Allocations', AllocationController::class],
    ];

    /**
     * Hand-resolved from RouteAccessMiddleware's controllerRules()/controllerMethodRules(),
     * by walking each controller's rule array in order and taking the first rule that matches
     * each CRUD method -- exactly mirroring matchesMethod()'s early-return resolution (`show`
     * is intentionally excluded from this whole system, see RouteAccessMiddleware).
     *
     * Format: slug => [role_id => [can_view, can_create, can_edit, can_delete]].
     * Any slug not yet listed here resolves to "no rows" (today's SUPER_ADMIN_ROLE_IDS-only
     * fallback for controllers absent from both legacy arrays, e.g. RegionController).
     */
    private const PERMISSIONS = [
        // Flat controllerRules() entries: one role list with blanket full-CRUD access today.
        'companies' => [3 => [true, true, true, true]],
        'accessorialTypes' => [3 => [true, true, true, true]],
        'addOnRates' => [3 => [true, true, true, true]],
        'approvers' => [3 => [true, true, true, true]],
        'areas' => [3 => [true, true, true, true]],
        'customers' => [3 => [true, true, true, true]],
        'taxes' => [3 => [true, true, true, true]],
        'trucks' => [3 => [true, true, true, true], 5 => [true, true, true, true]],
        'trucksTypes' => [3 => [true, true, true, true]],
        'deliveryStatus' => [3 => [true, true, true, true]],
        'deliveryTypes' => [3 => [true, true, true, true]],
        'distanceTypes' => [3 => [true, true, true, true]],
        'fleetCards' => [3 => [true, true, true, true]],
        'expenseTypes' => [3 => [true, true, true, true]],
        'warehouses' => [3 => [true, true, true, true]],
        // RegionController is absent from both legacy arrays -- no role-specific rows.
        'suppliers' => [3 => [true, true, true, true]],
        'cvr_request_types' => [3 => [true, true, true, true]],
        'deliveryRequestType' => [3 => [true, true, true, true]],
        // controllerMethodRules(): ['create','store']=>[6] matches first for create/store;
        // ['*']=>[7] wins for every other method (index/edit/update/destroy).
        'deliveryRequest' => [
            6 => [false, true, false, false],
            7 => [true, false, true, true],
        ],
        'cashVoucherRequests' => [
            12 => [true, true, true, true],
            13 => [true, true, true, true],
            14 => [true, true, true, true],
            15 => [true, true, true, true],
            16 => [true, true, true, true],
            17 => [true, true, true, true],
            18 => [true, true, true, true],
        ],
        'employees' => [3 => [true, true, true, true], 4 => [true, true, true, true]],
        // Same create/store-vs-wildcard split as DeliveryRequestController, roles 10 and 11.
        'coordinators' => [
            10 => [false, true, false, false],
            11 => [true, false, true, true],
        ],
        'liquidations' => [
            20 => [true, true, true, true],
            21 => [true, true, true, true],
            22 => [true, true, true, true],
            23 => [true, true, true, true],
            24 => [true, true, true, true],
            25 => [true, true, true, true],
            26 => [true, true, true, true],
            27 => [true, true, true, true],
        ],
        // controllerMethodRules(): rule1 (role 8) covers index/store/create/edit/update/destroy
        // (plus allocate/generateMultipleCvrNumbers, not CRUD methods). Rule2 (role 9, DRList/show)
        // never resolves any of our 6 mapped methods since `show` is excluded -- role 9 gets no rows.
        'allocations' => [8 => [true, true, true, true]],
    ];

    private const SUPER_ADMIN_ROLE_IDS = [1, 2];

    public function run(): void
    {
        $existingRoleIds = Role::pluck('id')->all();

        foreach (self::MODULES as $slug => [$name, $controller]) {
            $module = Module::updateOrCreate(
                ['controller' => $controller],
                ['name' => $name, 'slug' => $slug]
            );

            foreach (self::PERMISSIONS[$slug] ?? [] as $roleId => [$view, $create, $edit, $delete]) {
                if (!in_array($roleId, $existingRoleIds, true)) {
                    continue;
                }

                RoleModulePermission::updateOrCreate(
                    ['role_id' => $roleId, 'module_id' => $module->id],
                    ['can_view' => $view, 'can_create' => $create, 'can_edit' => $edit, 'can_delete' => $delete]
                );
            }

            foreach (self::SUPER_ADMIN_ROLE_IDS as $roleId) {
                if (!in_array($roleId, $existingRoleIds, true)) {
                    continue;
                }

                RoleModulePermission::updateOrCreate(
                    ['role_id' => $roleId, 'module_id' => $module->id],
                    ['can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => true]
                );
            }
        }
    }
}
