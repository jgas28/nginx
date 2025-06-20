<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\MonthlySeriesResetController;
use App\Http\Controllers\AccessorialTypeController;
use App\Http\Controllers\AddOnRateController;
use App\Http\Controllers\ApproverController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\WithholdingTaxController;
use App\Http\Controllers\TruckController;
use App\Http\Controllers\DeliveryStatusController;
use App\Http\Controllers\TruckTypeController;
use App\Http\Controllers\DeliveryTypeController;
use App\Http\Controllers\DistanceTypeController;
use App\Http\Controllers\FleetCardController;
use App\Http\Controllers\ExpenseTypeController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\CVR_Request_TypeController;
use App\Http\Controllers\DeliveryRequestTypeController;
use App\Http\Controllers\DeliveryRequestController;
use App\Http\Controllers\AllocationController;
use App\Http\Controllers\CashVoucherController;
use App\Http\Controllers\CoordinatorsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LiquidationController;
use App\Http\Controllers\RunningBalanceController;

// Redirect to dashboard or login
Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// Public Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/password/change', [PasswordController::class, 'showChangeForm'])->name('password.change');
    Route::post('/password/change', [PasswordController::class, 'change']);
    Route::resource('companies', CompanyController::class);
    Route::get('/monthly-series/reset', [MonthlySeriesResetController::class, 'index'])->name('monthly-series.reset.index');
    Route::post('/monthly-series/reset', [MonthlySeriesResetController::class, 'reset'])->name('monthly-series.reset'); 
    Route::resource('accessorialTypes', AccessorialTypeController::class);
    Route::resource('addOnRates', AddOnRateController::class);
    Route::resource('approvers', ApproverController::class);
    Route::resource('areas', AreaController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('taxes', WithholdingTaxController::class);
    Route::resource('trucks', TruckController::class);
    Route::resource('trucksTypes', TruckTypeController::class);
    Route::resource('deliveryStatus', DeliveryStatusController::class);
    Route::resource('deliveryTypes', DeliveryTypeController::class);
    Route::resource('distanceTypes', DistanceTypeController::class);
    Route::resource('fleetCards', FleetCardController::class);
    Route::resource('expenseTypes', ExpenseTypeController::class);
    Route::resource('warehouses', WarehouseController::class);
    Route::resource('regions', RegionController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('cvr_request_types', CVR_Request_TypeController::class);
    Route::resource('deliveryRequestType', DeliveryRequestTypeController::class);
    Route::resource('deliveryRequest', DeliveryRequestController::class);
    Route::resource('cashVoucherRequests', CashVoucherController::class);
    Route::resource('coordinators', CoordinatorsController::class)->parameters([
        'coordinators' => 'deliveryRequest'
    ]);
    Route::resource('admin', AdminController::class);
    Route::get('/running-balance', [RunningBalanceController::class, 'index'])->name('running_balance.index');
    Route::post('/running-balance/store', [RunningBalanceController::class, 'store'])->name('running_balance.store');

    Route::get('/liquidations/admin', [LiquidationController::class, 'indexAdmin'])
    ->name('liquidations.indexAdmin');
    // 🟢 Put this first
    Route::get('/liquidations/{id}/liquidate', [LiquidationController::class, 'liquidate'])
        ->name('liquidations.liquidate');
    Route::post('/liquidations/{id}/liquidate', [App\Http\Controllers\LiquidationController::class, 'storeSummary'])
    ->name('liquidations.storeSummary');

    Route::get('/liquidations/{id}/review', [LiquidationController::class, 'review'])->name('liquidations.review');
    Route::get('/liquidations/review-list', [LiquidationController::class, 'reviewList'])->name('liquidations.reviewList');
    Route::post('/liquidations/{id}/validate', [LiquidationController::class, 'validateLiquidation'])->name('liquidations.validate');
    Route::post('/liquidations/{id}/collect', [LiquidationController::class, 'collectedLiquidation'])->name('liquidations.collect');
    Route::get('/liquidations/{id}/validated', [LiquidationController::class, 'validate'])->name('liquidations.validated');
    Route::get('/liquidations/{id}/approval', [LiquidationController::class, 'approval'])->name('liquidations.approval');
    Route::get('/liquidations/validated-list', [LiquidationController::class, 'validatedList'])->name('liquidations.validatedList');
    Route::get('/liquidations/approval-list', [LiquidationController::class, 'approvalList'])->name('liquidations.approvalList');
    Route::post('/liquidations/{id}/approved', [LiquidationController::class, 'approvedLiquidation'])->name('liquidations.approved');

    Route::post('/running-balance/reimburse', [RunningBalanceController::class, 'storeReimbursement'])->name('running-balance.reimburse');
    Route::post('/running-balance/collected', [RunningBalanceController::class, 'storeCollected'])->name('running-balance.collected');

    // ⚠️ Put this after
    Route::resource('liquidations', LiquidationController::class);
    
    //others
    Route::get('/regions/by-area/{area}', [RegionController::class, 'getByArea']);
    Route::get('/deliveryRequest/splitView/{deliveryRequest}', [DeliveryRequestController::class, 'splitView'])->name('deliveryRequest.splitView');
    Route::get('/deliveryRequest/split/{id}', [DeliveryRequestController::class, 'showSplitForm'])->name('deliveryRequest.split.form');
    Route::post('/deliveryRequest/split/{id}', [DeliveryRequestController::class, 'performSplit'])->name('deliveryRequest.split.perform');

    Route::get('/allocations', [AllocationController::class, 'index'])
        ->name('allocations.index');
    Route::get('/allocations/allocate', [AllocationController::class, 'allocate'])
    ->name('allocations.allocate');
    Route::resource('allocations', AllocationController::class)->except(['show']);

    Route::post('/generate-multiple-cvr', [AllocationController::class, 'generateMultipleCvrNumbers'])->name('cvr.generateMultiple');

    Route::get('/coordinators/splitView/{deliveryRequest}', [CoordinatorsController::class, 'splitView'])->name('coordinators.splitView');
    Route::get('/coordinators/split/{id}', [CoordinatorsController::class, 'showSplitForm'])->name('coordinators.split.form');
    Route::post('/coordinators/split/{id}', [CoordinatorsController::class, 'performSplit'])->name('coordinators.split.perform');
    Route::get('/coordinators/{deliveryRequest}/edit-allocation', [CoordinatorsController::class, 'editAllocation'])
    ->name('coordinators.editAllocation');
    Route::put('/coordinators/{deliveryRequest}/update-allocation', [CoordinatorsController::class, 'updateAllocation'])
    ->name('coordinators.updateAllocation');
    Route::get('coordinators/{id}/request', [CoordinatorsController::class, 'request'])->name('coordinators.coordinators');
    Route::post('coordinators/store-pullout', [CoordinatorsController::class, 'storePullout'])->name('coordinators.storePullout');

    Route::get('cashVoucherRequests/{id}/request', [CashVoucherController::class, 'request'])->name('cashVoucherRequests.request');
    Route::get('/cash-voucher-accessorial', [CashVoucherController::class, 'accessorial'])->name('cashVoucherRequests.accessorial');
    Route::get('cashVoucherRequests/{id}/accessorialRequest', [CashVoucherController::class, 'accessorialRequest'])->name('cashVoucherRequests.accessorialRequest');
    Route::get('/cash-voucher-approval', [CashVoucherController::class, 'approval'])->name('cashVoucherRequests.approval');
    Route::get('cashVoucherRequests/{id}/approvalRequest', [CashVoucherController::class, 'approvalRequest'])->name('cashVoucherRequests.approvalRequest');
    Route::post('/cash-voucher/store-accessorial', [CashVoucherController::class, 'store_accessorial'])->name('cashVoucherRequests.store_accessorial');
    Route::get('/cash-voucher/edit/{id}', [CashVoucherController::class, 'editView'])->name('cashVoucherRequests.editView');
    Route::put('/cash-voucher/{id}/update', [CashVoucherController::class, 'cvrUpdate'])
    ->name('cashVoucherRequests.cvrUpdate');
    Route::get('/cvr/{id}/{cvr_number}', [CashVoucherController::class, 'showCustomCVR'])->name('cashVoucherRequests.showCustomCVR');
    Route::post('cashVoucherRequests/approvalRequestStore', [CashVoucherController::class, 'approvalRequestStore'])->name('cashVoucherRequests.approvalRequestStore');
    Route::post('/cash-voucher/reject', [CashVoucherController::class, 'reject'])->name('cashVoucherRequests.reject');
    Route::get('/cash-voucher/rejectView', [CashVoucherController::class, 'rejectView'])->name('cashVoucherRequests.rejectView');
    Route::get('/cash-voucher-requests/{id}/edit-cvr', [CashVoucherController::class, 'editCVR'])
        ->name('cashVoucherRequests.editCVR');
    Route::put('/cash-voucher-requests/{id}/update-cvr', [CashVoucherController::class, 'updateCVR'])
    ->name('cashVoucherRequests.updateCVR');

    //admin
    Route::post('/cvr/generate', [AdminController::class, 'generateCvrNumber'])->name('cvr.generate');
    Route::post('/admin/generate-cvr-number', [AdminController::class, 'generateCvrNumber'])->name('admin.generate-cvr-number');
    Route::get('/adminCV/approval', [AdminController::class, 'approvals'])->name('adminCV.approval');
    Route::put('/adminCV/{id}', [AdminController::class, 'update'])->name('adminCV.update');
    Route::get('/admin/{id}/viewPrint', [AdminController::class, 'viewPrint'])->name('admin.viewPrint');
    
    // Edit Approval View
    Route::get('/admin/approval/edit/{id}', [AdminController::class, 'editApproval'])->name('admin.editApproval');

    // Confirm/Release Request
    Route::get('/admin/approval-request/{id}', [AdminController::class, 'approvalRequest'])->name('admin.approvalRequest');
    Route::get('/admin/cashvoucher/print-preview/{id}', [AdminController::class, 'printPreview'])->name('admin.cashvoucher.printPreview');
    Route::post('/admin-update', [AdminController::class, 'StoreApprovalRequest'])->name('admin.storeRequest');


});

// Admin-only Routes (if needed separately)
Route::middleware(['auth', 'role:administrator'])->group(function () {
    Route::get('/administrator', function () {
        return view('dashboard'); // or a dedicated admin dashboard view
    })->name('administrator.dashboard');

    
});
