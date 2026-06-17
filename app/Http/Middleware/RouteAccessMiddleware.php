<?php

namespace App\Http\Middleware;

use App\Http\Controllers\AccessorialTypeController;
use App\Http\Controllers\AddOnRateController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AllocationController;
use App\Http\Controllers\ApproverController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CashVoucherController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CoordinatorsController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CVR_Request_TypeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DetailsController;
use App\Http\Controllers\DeliveryRequestController;
use App\Http\Controllers\DeliveryRequestTypeController;
use App\Http\Controllers\DeliveryStatusController;
use App\Http\Controllers\DeliveryTypeController;
use App\Http\Controllers\DistanceTypeController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ExpenseTypeController;
use App\Http\Controllers\FleetCardController;
use App\Http\Controllers\HRController;
use App\Http\Controllers\LiquidationController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\MonthlySeriesResetController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\UserPermissionController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RunningBalanceController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TruckController;
use App\Http\Controllers\TruckTypeController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WithholdingTaxController;
use App\Models\Module;
use App\Models\RoleModulePermission;
use App\Models\User;
use App\Models\UserModulePermission;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RouteAccessMiddleware
{
    private const SUPER_ADMIN_ROLE_IDS = [1, 2];

    /**
     * Resourceful CRUD methods governed by the per-module role_module_permissions table.
     * `show` is deliberately excluded -- some controllers (e.g. AllocationController) gate it
     * with a role list distinct from `index`, which a single coarse "view" action can't
     * represent without widening access. `show` always falls through to the legacy arrays below.
     */
    private const CRUD_METHOD_ACTION_MAP = [
        'index' => 'view',
        'create' => 'create',
        'store' => 'create',
        'edit' => 'edit',
        'update' => 'edit',
        'destroy' => 'delete',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        $userOverride = $this->resolveUserOverride($request, $user);

        if ($userOverride !== null) {
            return $userOverride ? $next($request) : abort(403, 'Unauthorized');
        }

        $allowedRoleIds = $this->resolveAllowedRoleIds($request);

        if ($allowedRoleIds === []) {
            return $next($request);
        }

        if ($allowedRoleIds !== null && $user->hasAnyRoleId($allowedRoleIds)) {
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }

    /**
     * Per-employee permission override, checked before the role-based resolution below.
     * Unlike role-based access (an allow-list that can only ever grant), an override is
     * authoritative when present -- it can both grant beyond the employee's role(s) and
     * restrict below them. Returns null when no override applies, meaning the caller should
     * fall through to the existing role-based resolution unchanged.
     */
    private function resolveUserOverride(Request $request, User $user): ?bool
    {
        if ($user->isAdmin()) {
            return null;
        }

        [$controller, $method] = $this->routeControllerMethod($request) ?? [null, null];

        if ($controller === null || !array_key_exists($method, self::CRUD_METHOD_ACTION_MAP)) {
            return null;
        }

        $moduleId = $this->moduleMap()[$controller] ?? null;

        if ($moduleId === null) {
            return null;
        }

        $override = UserModulePermission::where('user_id', $user->id)
            ->where('module_id', $moduleId)
            ->first();

        if (!$override) {
            return null;
        }

        $action = self::CRUD_METHOD_ACTION_MAP[$method];

        return (bool) $override->{"can_{$action}"};
    }

    private function resolveAllowedRoleIds(Request $request): ?array
    {
        [$controller, $method] = $this->routeControllerMethod($request) ?? [null, null];

        if ($controller === null) {
            return null;
        }

        if ($this->shouldAllowAllAuthenticated($controller, $method)) {
            return [];
        }

        if (array_key_exists($method, self::CRUD_METHOD_ACTION_MAP)) {
            $moduleRoleIds = $this->resolveModuleRoleIds($controller, self::CRUD_METHOD_ACTION_MAP[$method]);

            if ($moduleRoleIds !== null) {
                return $this->withSuperAdmins($moduleRoleIds);
            }
        }

        foreach ($this->controllerMethodRules()[$controller] ?? [] as $rule) {
            if ($this->matchesMethod($method, $rule['methods'])) {
                return $this->withSuperAdmins($rule['roles']);
            }
        }

        $controllerRules = $this->controllerRules();

        if (array_key_exists($controller, $controllerRules)) {
            return $this->withSuperAdmins($controllerRules[$controller]);
        }

        return self::SUPER_ADMIN_ROLE_IDS;
    }

    /**
     * Shared route parsing used by both resolveUserOverride() and resolveAllowedRoleIds().
     * Returns [controllerFqcn, method] or null when the route has no controller action.
     */
    private function routeControllerMethod(Request $request): ?array
    {
        $route = $request->route();

        if (!$route) {
            return null;
        }

        $action = (string) $route->getActionName();

        if ($action === '' || $action === 'Closure' || !str_contains($action, '@')) {
            return null;
        }

        [$controller, $method] = explode('@', $action, 2);

        return [ltrim($controller, '\\'), $method];
    }

    private function shouldAllowAllAuthenticated(string $controller, string $method): bool
    {
        return match ($controller) {
            PasswordController::class => true,
            RegionController::class => $method === 'getByArea',
            default => false,
        };
    }

    private function matchesMethod(string $method, array $methods): bool
    {
        return in_array('*', $methods, true) || in_array($method, $methods, true);
    }

    private function withSuperAdmins(array $roleIds): array
    {
        return array_values(array_unique([
            ...self::SUPER_ADMIN_ROLE_IDS,
            ...$roleIds,
        ]));
    }

    /**
     * Resolves the allowed role ids for a CRUD action via the module permission table.
     * Returns null when the controller has no Module row -- meaning it isn't governed by
     * this system at all, and the caller should fall through to the legacy arrays.
     */
    private function resolveModuleRoleIds(string $controller, string $action): ?array
    {
        $moduleId = $this->moduleMap()[$controller] ?? null;

        if ($moduleId === null) {
            return null;
        }

        return RoleModulePermission::where('module_id', $moduleId)
            ->where("can_{$action}", true)
            ->pluck('role_id')
            ->all();
    }

    /**
     * Controller FQCN => module id, cached indefinitely. Modules only change via migrations,
     * not at runtime, so there is no invalidation path -- bust manually (cache:clear) if needed.
     */
    private function moduleMap(): array
    {
        return Cache::rememberForever('modules.controller_map', function () {
            return Module::query()->pluck('id', 'controller')->all();
        });
    }

    private function controllerRules(): array
    {
        return [
            AuthController::class => [3, 4],
            CompanyController::class => [3],
            AreaController::class => [3],
            CustomerController::class => [3],
            AccessorialTypeController::class => [3],
            AddOnRateController::class => [3],
            DeliveryStatusController::class => [3],
            DeliveryTypeController::class => [3],
            DistanceTypeController::class => [3],
            WarehouseController::class => [3],
            DeliveryRequestTypeController::class => [3],
            MonthlySeriesResetController::class => [3],
            ApproverController::class => [3],
            WithholdingTaxController::class => [3],
            ExpenseTypeController::class => [3],
            CVR_Request_TypeController::class => [3],
            SupplierController::class => [3],
            TruckTypeController::class => [3],
            FleetCardController::class => [3],
            EmployeeController::class => [3, 4],
            TruckController::class => [3, 5],
            CashVoucherController::class => [12, 13, 14, 15, 16, 17, 18],
            AdminController::class => [12, 13, 14, 15, 16, 17, 18],
            DashboardController::class => [37, 38, 39, 40, 41],
            DetailsController::class => [40, 41],
            LiquidationController::class => [20, 21, 22, 23, 24, 25, 26, 27],
            PermissionController::class => [1, 2],
            AuditLogController::class => [1, 2],
            UserPermissionController::class => [1, 2],
        ];
    }

    private function controllerMethodRules(): array
    {
        return [
            DeliveryRequestController::class => [
                ['methods' => ['create', 'store'], 'roles' => [6]],
                ['methods' => ['*'], 'roles' => [7]],
            ],
            CoordinatorsController::class => [
                ['methods' => ['create', 'store'], 'roles' => [10]],
                ['methods' => ['*'], 'roles' => [11]],
            ],
            AllocationController::class => [
                ['methods' => ['index', 'allocate', 'store', 'create', 'edit', 'update', 'destroy', 'generateMultipleCvrNumbers'], 'roles' => [8]],
                ['methods' => ['DRList', 'show'], 'roles' => [9]],
            ],
            ReportsController::class => [
                ['methods' => ['deliveryRequestReport', 'export'], 'roles' => [11]],
                ['methods' => ['cashVoucherReport', 'AdminExport', 'rpmCashVoucherReport', 'RPMexport'], 'roles' => [12, 13, 14, 15, 16, 17, 18]],
            ],
            RunningBalanceController::class => [
                ['methods' => ['editRefund', 'updateRefund', 'editReturn', 'updateReturn'], 'roles' => [43, 58]], // 46 is the new refund/return role
                ['methods' => ['index', 'store', 'storeReimbursement', 'storeUncollected', 'print', 'printRefund', 'printReturn'], 'roles' => [43, 58]],
                ['methods' => ['storeCollected', 'collectedFunds'], 'roles' => [43, 45]],
                ['methods' => ['adminFunds', 'storeReimbursementAdmin', 'storeCollectedAdmin'], 'roles' => [44]],
                ['methods' => ['davaoFunds'], 'roles' => [45]],
                ['methods' => ['exportExcel', 'exportPdf'], 'roles' => [43, 44, 45, 58]],
            ],
            BillingController::class => [
                ['methods' => ['dashboard'], 'roles' => [49]],
                ['methods' => ['createSOAForm', 'createSOAFormAccessorial', 'createSOA', 'createAccessorialSOA'], 'roles' => [50]],
                ['methods' => ['index', 'indexAccessorial', 'exportExcel', 'exportHuawei', 'showSoa', 'editSOAForm', 'updateSOA', 'markAsPaid', 'destroySOA', 'print', 'downloadPdf'], 'roles' => [51]],
            ],
            AttendanceController::class => [
                ['methods' => ['index'], 'roles' => [55]],
                ['methods' => ['create', 'store'], 'roles' => [56]],
                ['methods' => ['summary', 'summaryExcel', 'summaryPdf'], 'roles' => [57]],
            ],
            HRController::class => [
                ['methods' => ['index', 'updateDailyRates'], 'roles' => [52]],
                ['methods' => ['create', 'store'], 'roles' => [53]],
                ['methods' => ['payslips', 'exportPayslipsExcel', 'exportPayslipsPdf', 'showPayslip', 'downloadPayslipPdf'], 'roles' => [54]],
            ],
        ];
    }
}
