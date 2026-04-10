<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\RunningBalance;
use App\Models\Approver;
use App\Models\CashVoucher;
use App\Models\DeliveryRequest;
use App\Models\Liquidation;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $user->load('roles');

        $roleIds = $user->roles->pluck('id')->toArray();

        $needsBalanceData = count(array_intersect($roleIds, [37, 38, 39, 40, 41])) > 0;
        $PnLData = in_array(40, $roleIds) || in_array(41, $roleIds);

        $profits = [];
        $totalDeliveryRates = 0;
        $totalAccessorialRates = 0;
        $totals = null;

        // Get the selected month and date range
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;

        if ($PnLData) {
            // Check if month is selected, if yes, use that month, otherwise use the current month
            $month = Carbon::createFromFormat('Y-m', $selectedMonth);
            $startDate = $month->startOfMonth()->toDateString();
            $endDate = $month->endOfMonth()->toDateString();

            // Filter Delivery Requests by selected month or date range
            $deliveryRequests = DeliveryRequest::with('lineItems')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            foreach ($deliveryRequests as $dr) {
                $totalDeliveryRates += floatval($dr->delivery_rate);
                foreach ($dr->lineItems as $item) {
                    $totalAccessorialRates += floatval($item->accessorial_rate);
                }
            }

            // Sum admin/rpm vs operational expenses based on selected date range
            $liquidations = Liquidation::with('cashVoucher')
            ->whereHas('cashVoucher', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->get();

            $adminRpmTotal = 0;
            $operationTotal = 0;

            foreach ($liquidations as $l) {
                $total = 
                    floatval($l->allowance) +
                    floatval($l->manpower) +
                    floatval($l->hauling) +
                    floatval($l->right_of_way) +
                    floatval($l->roro_expense) +
                    floatval($l->cash_charge);

                // JSON field totals
                $total += collect($l->gasoline ?? [])->sum('amount');
                $total += collect($l->rfid ?? [])->sum('amount');
                $total += collect($l->others ?? [])->sum('amount');

                $type = optional($l->cashVoucher)->cvr_type;

                if (in_array($type, ['admin', 'rpm'])) {
                    $adminRpmTotal += $total;
                } else {
                    $operationTotal += $total;
                }
            }

            $totals = (object) [
                'admin_rpm_total' => $adminRpmTotal,
                'operation_total' => $operationTotal,
            ];
        }

        $approvers = [];
        $runningTotalsByApprover = [];
        $uncollectedByApprover = [];

        if ($needsBalanceData) {
            $approvers = Approver::all();

            // Monthly running balance for the selected range
            $runningTotalsByApprover = RunningBalance::selectRaw('approver_id, SUM(amount) as total')
                ->groupBy('approver_id')
                ->pluck('total', 'approver_id');

            // Monthly uncollected balance for the selected range
            $uncollectedByApprover = RunningBalance::whereIn('type', [4, 5])
                ->selectRaw('approver_id, SUM(amount) as total')
                ->groupBy('approver_id')
                ->pluck('total', 'approver_id')
                ->map(fn($amount) => abs($amount));
        }

        $totalPendingDeliveries = DeliveryRequest::where('status', '!=', 1)
            ->count();

        $totalDelivered = DeliveryRequest::where('status', 1)
            ->whereDate('created_at', Carbon::today())
            ->count();

        $totalTruckAllocated = DeliveryRequest::where('status', 8)
            ->count();

        $totalCVRapproval = CashVoucher::where('status', 1)
            ->count();

        $totalLiquidation = Liquidation::where('status', 4)
            ->count();

        // Role-based dashboard view rendering
        if (in_array(37, $roleIds)) {
            return view('dashboards.coordinator', compact(
                'approvers', 'runningTotalsByApprover', 'uncollectedByApprover'
            ));
        }

        if (in_array(38, $roleIds)) {
            return view('dashboards.admin', compact(
                'approvers', 'runningTotalsByApprover', 'uncollectedByApprover'
            ));
        }

        if (in_array(39, $roleIds)) {
            return view('dashboards.allocation', compact(
                'approvers', 'runningTotalsByApprover', 'uncollectedByApprover'
            ));
        }

        if (in_array(40, $roleIds)) {
            return view('dashboards.owner1', compact(
                'approvers',
                'runningTotalsByApprover',
                'uncollectedByApprover',
                'profits',
                'totalDeliveryRates',
                'totalAccessorialRates',
                'totals',
                'totalPendingDeliveries',
                'totalDelivered',
                'totalTruckAllocated',
                'totalLiquidation',
                'totalCVRapproval'
            ));
        }

        if (in_array(41, $roleIds)) {
            return view('dashboards.owner2', compact(
                'approvers',
                'runningTotalsByApprover',
                'uncollectedByApprover',
                'profits',
                'totalDeliveryRates',
                'totalAccessorialRates',
                'totals',
                'totalPendingDeliveries',
                'totalDelivered',
                'totalTruckAllocated'
            ));
        }

        abort(403, 'Unauthorized dashboard access.');
    }
}
