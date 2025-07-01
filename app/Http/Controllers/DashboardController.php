<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\RunningBalance;
use App\Models\Approver;
use App\Models\DeliveryRequest;
use App\Models\Liquidation;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $user->load('roles');

        $roleIds = $user->roles->pluck('id')->toArray();

        $needsBalanceData = count(array_intersect($roleIds, [37, 38, 39, 40, 41])) > 0;
        $PnLData = in_array(40, $roleIds) || in_array(41, $roleIds);

        $profits = [];
        $totalDeliveryRates = 0;
        $totalAccessorialRates = 0;
        $totals = null;

        if ($PnLData) {
            // Sum all delivery & accessorial rates
            $deliveryRequests = DeliveryRequest::with('lineItems')->get();

            foreach ($deliveryRequests as $dr) {
                $totalDeliveryRates += floatval($dr->delivery_rate);
                foreach ($dr->lineItems as $item) {
                    $totalAccessorialRates += floatval($item->accessorial_rate);
                }
            }

            // Sum admin/rpm vs operational expenses using Laravel logic
            $liquidations = Liquidation::with('cashVoucher')->get();

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

            $runningTotalsByApprover = RunningBalance::whereIn('type', [1, 2, 3, 5, 8, 10])
                ->selectRaw('approver_id, SUM(amount) as total')
                ->groupBy('approver_id')
                ->pluck('total', 'approver_id');

            $uncollectedByApprover = RunningBalance::whereIn('type', [4, 5])
                ->selectRaw('approver_id, SUM(amount) as total')
                ->groupBy('approver_id')
                ->pluck('total', 'approver_id')
                ->map(fn($amount) => abs($amount));
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
                'totals'
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
                'totals'
            ));
        }

        abort(403, 'Unauthorized dashboard access.');
    }
}
