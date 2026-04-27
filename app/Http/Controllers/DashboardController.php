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

        $month = Carbon::createFromFormat('Y-m', $selectedMonth);

        if ($PnLData) {
            // Check if month is selected, if yes, use that month, otherwise use the current month
            $startDate = $month->copy()->startOfMonth()->toDateString();
            $endDate = $month->copy()->endOfMonth()->toDateString();

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

            $totals = (object) $this->calculateLiquidationTotals($startDate, $endDate);
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

        $analyticsSeries = [];
        $analyticsMax = 1;
        $activityMix = [];
        $expenseMix = [];

        if ($PnLData) {
            $analyticsSeries = $this->buildMonthlyAnalyticsSeries($month);
            $analyticsMax = max(
                1,
                collect($analyticsSeries)->max(fn ($item) => max(
                    $item['income'] ?? 0,
                    $item['expenses'] ?? 0,
                    $item['profit'] ?? 0
                )) ?? 1
            );

            $activityMix = [
                [
                    'label' => 'Delivered Today',
                    'value' => $totalDelivered ?? 0,
                    'color' => 'bg-sky-500',
                    'text' => 'text-sky-700',
                ],
                [
                    'label' => 'Pending Deliveries',
                    'value' => $totalPendingDeliveries ?? 0,
                    'color' => 'bg-rose-500',
                    'text' => 'text-rose-700',
                ],
                [
                    'label' => 'Truck Allocated',
                    'value' => $totalTruckAllocated ?? 0,
                    'color' => 'bg-violet-500',
                    'text' => 'text-violet-700',
                ],
                [
                    'label' => 'Liquidations',
                    'value' => $totalLiquidation ?? 0,
                    'color' => 'bg-teal-500',
                    'text' => 'text-teal-700',
                ],
                [
                    'label' => 'CVR Approvals',
                    'value' => $totalCVRapproval ?? 0,
                    'color' => 'bg-fuchsia-500',
                    'text' => 'text-fuchsia-700',
                ],
            ];

            $expenseMix = [
                [
                    'label' => 'Income',
                    'value' => ($totalDeliveryRates + $totalAccessorialRates),
                    'color' => 'bg-emerald-500',
                    'text' => 'text-emerald-700',
                ],
                [
                    'label' => 'Admin / RPM Expense',
                    'value' => $totals->admin_rpm_total ?? 0,
                    'color' => 'bg-amber-500',
                    'text' => 'text-amber-700',
                ],
                [
                    'label' => 'Operational Expense',
                    'value' => $totals->operation_total ?? 0,
                    'color' => 'bg-orange-500',
                    'text' => 'text-orange-700',
                ],
            ];
        }

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
                'totalCVRapproval',
                'analyticsSeries',
                'analyticsMax',
                'activityMix',
                'expenseMix'
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
                'totalTruckAllocated',
                'totalLiquidation',
                'totalCVRapproval',
                'analyticsSeries',
                'analyticsMax',
                'activityMix',
                'expenseMix'
            ));
        }

        abort(403, 'Unauthorized dashboard access.');
    }

    private function calculateLiquidationTotals(string $startDate, string $endDate): array
    {
        $liquidations = Liquidation::with('cashVoucher')
            ->whereHas('cashVoucher', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->get();

        $adminRpmTotal = 0;
        $operationTotal = 0;

        foreach ($liquidations as $liquidation) {
            $total = $this->sumLiquidationAmount($liquidation);
            $type = optional($liquidation->cashVoucher)->cvr_type;

            if (in_array($type, ['admin', 'rpm'])) {
                $adminRpmTotal += $total;
            } else {
                $operationTotal += $total;
            }
        }

        return [
            'admin_rpm_total' => $adminRpmTotal,
            'operation_total' => $operationTotal,
        ];
    }

    private function buildMonthlyAnalyticsSeries(Carbon $selectedMonth): array
    {
        $series = [];

        for ($offset = 5; $offset >= 0; $offset--) {
            $period = $selectedMonth->copy()->subMonths($offset);
            $startDate = $period->copy()->startOfMonth()->toDateString();
            $endDate = $period->copy()->endOfMonth()->toDateString();

            $deliveryRequests = DeliveryRequest::with('lineItems')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $deliveryIncome = $deliveryRequests->sum(fn ($item) => (float) $item->delivery_rate);
            $accessorialIncome = $deliveryRequests->sum(
                fn ($item) => $item->lineItems->sum(fn ($lineItem) => (float) $lineItem->accessorial_rate)
            );

            $income = $deliveryIncome + $accessorialIncome;
            $liquidationTotals = $this->calculateLiquidationTotals($startDate, $endDate);
            $expenses = ($liquidationTotals['admin_rpm_total'] ?? 0) + ($liquidationTotals['operation_total'] ?? 0);

            $series[] = [
                'label' => $period->format('M Y'),
                'income' => round($income, 2),
                'expenses' => round($expenses, 2),
                'profit' => round($income - $expenses, 2),
            ];
        }

        return $series;
    }

    private function sumLiquidationAmount(Liquidation $liquidation): float
    {
        $total =
            (float) $liquidation->allowance +
            (float) $liquidation->manpower +
            (float) $liquidation->hauling +
            (float) $liquidation->right_of_way +
            (float) $liquidation->roro_expense +
            (float) $liquidation->cash_charge;

        $total += collect($liquidation->gasoline ?? [])->sum('amount');
        $total += collect($liquidation->rfid ?? [])->sum('amount');
        $total += collect($liquidation->others ?? [])->sum('amount');

        return $total;
    }
}
