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
        $dashboardView = $user->getAssignedDashboardView();

        if (!$dashboardView) {
            return redirect()->route('no.dashboard');
        }

        $needsBalanceData = $user->hasDashboardAccess();
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
                ->where('status', '!=', 0)
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

            // Match the running-balance module totals exactly.
            $runningTotalsByApprover = RunningBalance::selectRaw('approver_id, SUM(amount) as total')
                ->groupBy('approver_id')
                ->pluck('total', 'approver_id');

            $uncollectedByApprover = RunningBalance::whereIn('type', [4, 5, 12])
                ->selectRaw('approver_id, SUM(amount) as total')
                ->groupBy('approver_id')
                ->pluck('total', 'approver_id');
        }

        $totalPendingDeliveries = DeliveryRequest::where('status', '!=', 0)
            ->where('delivery_status', 1)
            ->count();

        $totalDelivered = DeliveryRequest::where('status', '!=', 0)
            ->whereDate('delivery_date', Carbon::today())
            ->count();

        $totalTruckAllocated = DeliveryRequest::where('delivery_status', 8)
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
                    'text'  => 'text-emerald-700',
                    'icon'  => 'fa-chart-line',
                ],
                [
                    'label'    => 'Admin Expense',
                    'value'    => $totals->admin_total ?? 0,
                    'color'    => 'bg-blue-500',
                    'text'     => 'text-blue-700',
                    'icon'     => 'fa-user-shield',
                    'children' => $totals->admin_breakdown ?? [],
                ],
                [
                    'label'    => 'RPM Expense',
                    'value'    => $totals->rpm_total ?? 0,
                    'color'    => 'bg-amber-500',
                    'text'     => 'text-amber-700',
                    'icon'     => 'fa-wrench',
                    'children' => $totals->rpm_breakdown ?? [],
                ],
                [
                    'label'    => 'Operations Expense',
                    'value'    => $totals->operation_total ?? 0,
                    'color'    => 'bg-orange-500',
                    'text'     => 'text-orange-700',
                    'icon'     => 'fa-gears',
                    'children' => $totals->operation_breakdown ?? [],
                ],
                [
                    'label'    => 'Accessorial Expense',
                    'value'    => $totals->accessorial_total ?? 0,
                    'color'    => 'bg-teal-500',
                    'text'     => 'text-teal-700',
                    'icon'     => 'fa-truck-ramp-box',
                    'children' => $totals->accessorial_breakdown ?? [],
                ],
            ];
        }

        return view($dashboardView, compact(
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

    private function calculateLiquidationTotals(string $startDate, string $endDate): array
    {
        $liquidations = Liquidation::with('cashVoucher')
            ->whereHas('cashVoucher', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->get();

        $adminBreakdown       = $this->makeEmptyLiquidationBreakdown();
        $rpmBreakdown         = $this->makeEmptyLiquidationBreakdown();
        $operationBreakdown   = $this->makeEmptyLiquidationBreakdown();
        $accessorialBreakdown = $this->makeEmptyLiquidationBreakdown();

        foreach ($liquidations as $liquidation) {
            $type = strtolower(trim(optional($liquidation->cashVoucher)->cvr_type ?? ''));

            if ($type === 'admin') {
                $this->accumulateLiquidationBreakdown($adminBreakdown, $liquidation);
            } elseif ($type === 'rpm') {
                $this->accumulateLiquidationBreakdown($rpmBreakdown, $liquidation);
            } elseif (in_array($type, ['accesorial', 'accessorial'], true)) {
                // note: 'accesorial' is the typo stored in the DB
                $this->accumulateLiquidationBreakdown($accessorialBreakdown, $liquidation);
            } else {
                $this->accumulateLiquidationBreakdown($operationBreakdown, $liquidation);
            }
        }

        $adminTotal       = $this->breakdownNumericTotal($adminBreakdown);
        $rpmTotal         = $this->breakdownNumericTotal($rpmBreakdown);
        $operationTotal   = $this->breakdownNumericTotal($operationBreakdown);
        $accessorialTotal = $this->breakdownNumericTotal($accessorialBreakdown);

        return [
            'admin_total'           => $adminTotal,
            'rpm_total'             => $rpmTotal,
            'admin_rpm_total'       => $adminTotal + $rpmTotal,
            'operation_total'       => $operationTotal,
            'accessorial_total'     => $accessorialTotal,
            'admin_breakdown'       => $this->formatLiquidationBreakdownItems($adminBreakdown, 'blue'),
            'rpm_breakdown'         => $this->formatLiquidationBreakdownItems($rpmBreakdown, 'amber'),
            'operation_breakdown'   => $this->formatLiquidationBreakdownItems($operationBreakdown, 'orange'),
            'accessorial_breakdown' => $this->formatLiquidationBreakdownItems($accessorialBreakdown, 'teal'),
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
                ->where('status', '!=', 0)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $deliveryIncome = $deliveryRequests->sum(fn ($item) => (float) $item->delivery_rate);
            $accessorialIncome = $deliveryRequests->sum(
                fn ($item) => $item->lineItems->sum(fn ($lineItem) => (float) $lineItem->accessorial_rate)
            );

            $income = $deliveryIncome + $accessorialIncome;
            $liquidationTotals = $this->calculateLiquidationTotals($startDate, $endDate);
            $expenses =
                ($liquidationTotals['admin_total'] ?? 0) +
                ($liquidationTotals['rpm_total'] ?? 0) +
                ($liquidationTotals['operation_total'] ?? 0) +
                ($liquidationTotals['accessorial_total'] ?? 0);

            $series[] = [
                'label' => $period->format('M Y'),
                'income' => round($income, 2),
                'expenses' => round($expenses, 2),
                'profit' => round($income - $expenses, 2),
            ];
        }

        return $series;
    }

    private function makeEmptyLiquidationBreakdown(): array
    {
        return [
            'gasoline'      => 0,
            'rfid'          => 0,
            'allowance'     => 0,
            'lodging'       => 0,
            'manpower'      => 0,
            'hauling'       => 0,
            'freight'       => 0,
            'right_of_way'  => 0,
            'roro_expense'  => 0,
            'cash_charge'   => 0,
            'others'        => 0,
            'others_items'  => [],
        ];
    }

    private function accumulateLiquidationBreakdown(array &$breakdown, Liquidation $liquidation): void
    {
        $breakdown['gasoline']     += collect($liquidation->gasoline ?? [])->sum('amount');
        $breakdown['rfid']         += collect($liquidation->rfid ?? [])->sum('amount');
        $breakdown['allowance']    += (float) $liquidation->allowance;
        $breakdown['lodging']      += (float) $liquidation->lodging;
        $breakdown['manpower']     += (float) $liquidation->manpower;
        $breakdown['hauling']      += (float) $liquidation->hauling;
        $breakdown['freight']      += (float) $liquidation->freight;
        $breakdown['right_of_way'] += (float) $liquidation->right_of_way;
        $breakdown['roro_expense'] += (float) $liquidation->roro_expense;
        $breakdown['cash_charge']  += (float) $liquidation->cash_charge;

        foreach (($liquidation->others ?? []) as $other) {
            $typeName = trim($other['type'] ?? $other['description'] ?? '');
            $typeName = $typeName !== '' ? ucfirst($typeName) : 'Miscellaneous';
            $amount   = (float) ($other['amount'] ?? 0);
            $breakdown['others'] += $amount;
            $breakdown['others_items'][$typeName] = ($breakdown['others_items'][$typeName] ?? 0) + $amount;
        }
    }

    private function breakdownNumericTotal(array $breakdown): float
    {
        return $breakdown['gasoline'] + $breakdown['rfid']
             + $breakdown['allowance'] + $breakdown['lodging']
             + $breakdown['manpower']
             + $breakdown['hauling'] + $breakdown['freight']
             + $breakdown['right_of_way'] + $breakdown['roro_expense']
             + $breakdown['cash_charge'] + $breakdown['others'];
    }

    private function formatLiquidationBreakdownItems(array $breakdown, string $tone): array
    {
        $toneMap = [
            'blue'   => ['color' => 'bg-blue-500',   'text' => 'text-blue-700'],
            'amber'  => ['color' => 'bg-amber-500',  'text' => 'text-amber-700'],
            'orange' => ['color' => 'bg-orange-500', 'text' => 'text-orange-700'],
            'teal'   => ['color' => 'bg-teal-500',   'text' => 'text-teal-700'],
        ];

        $fieldMap = [
            'gasoline'     => ['label' => 'Diesel / Fuel',  'icon' => 'fa-gas-pump'],
            'rfid'         => ['label' => 'Toll Fee',        'icon' => 'fa-road'],
            'allowance'    => ['label' => 'Allowance',       'icon' => 'fa-wallet'],
            'lodging'      => ['label' => 'Lodging',         'icon' => 'fa-bed'],
            'manpower'     => ['label' => 'Manpower',        'icon' => 'fa-users'],
            'hauling'      => ['label' => 'Hauling',         'icon' => 'fa-dolly'],
            'freight'      => ['label' => 'Freight',         'icon' => 'fa-truck-ramp-box'],
            'right_of_way' => ['label' => 'Right of Way',   'icon' => 'fa-signs-post'],
            'roro_expense' => ['label' => 'RoRo Expense',   'icon' => 'fa-ship'],
            'cash_charge'  => ['label' => 'Cash Charge',    'icon' => 'fa-money-bill-wave'],
            'others'       => ['label' => 'Others',         'icon' => 'fa-ellipsis'],
        ];

        $styles = $toneMap[$tone] ?? ['color' => 'bg-slate-500', 'text' => 'text-slate-700'];
        $othersItems = $breakdown['others_items'] ?? [];

        return collect($fieldMap)->map(function ($meta, $field) use ($breakdown, $styles, $othersItems) {
            $item = [
                'field' => $field,
                'label' => $meta['label'],
                'value' => $breakdown[$field] ?? 0,
                'color' => $styles['color'],
                'text'  => $styles['text'],
                'icon'  => $meta['icon'],
            ];
            if ($field === 'others' && !empty($othersItems)) {
                $item['children'] = collect($othersItems)->map(fn ($amt, $name) => [
                    'field' => 'others_sub',
                    'label' => $name,
                    'value' => $amt,
                    'color' => $styles['color'],
                    'text'  => $styles['text'],
                    'icon'  => 'fa-arrow-right',
                ])->values()->all();
            }
            return $item;
        })->values()->all();
    }
}
