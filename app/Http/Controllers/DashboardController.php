<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\RunningBalance;
use App\Models\Approver;
use App\Models\DeliveryRequest;
use App\Models\CashVoucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $user->load('roles');

        $roleIds = $user->roles->pluck('id')->toArray();

        // Flags for which data to load
        $needsBalanceData = count(array_intersect($roleIds, [37, 38, 39, 40, 41])) > 0;
        $PnLData = in_array(40, $roleIds) || in_array(41, $roleIds);

        $profits = [];
        $totalDeliveryRates = 0;  // Total Delivery Rate
        $totalAccessorialRates = 0;  // Total Accessorial Rate

        if ($PnLData) {
            // Load all delivery requests and their related data
            $deliveryRequests = DeliveryRequest::with([
                'lineItems',
            ])->get();

            // Calculate total delivery and accessorial rates
            foreach ($deliveryRequests as $dr) {
                // Sum up the total delivery rate for all requests
                $totalDeliveryRates += $dr->delivery_rate;

                // Sum up the accessorial rate for each line item
                foreach ($dr->lineItems as $item) {
                    $totalAccessorialRates += $item->accessorial_rate;
                }
            }
        }

        // Approver-related data (balance-related)
        $approvers = [];
        $runningTotalsByApprover = [];
        $uncollectedByApprover = [];

        if ($needsBalanceData) {
            $approvers = Approver::all();

            // Get the running total balance for each approver (for types: 1, 2, 3, 5, 8, 10)
            $runningTotalsByApprover = RunningBalance::whereIn('type', [1, 2, 3, 5, 8, 10])
                ->selectRaw('approver_id, SUM(amount) as total')
                ->groupBy('approver_id')
                ->pluck('total', 'approver_id');

            // Get the uncollected amounts for each approver (for types: 4, 5)
            $uncollectedByApprover = RunningBalance::whereIn('type', [4, 5])
                ->selectRaw('approver_id, SUM(amount) as total')
                ->groupBy('approver_id')
                ->pluck('total', 'approver_id')
                ->map(fn($amount) => abs($amount));
        }

        // Render views based on roles
        if (in_array(37, $roleIds)) {
            return view('dashboards.coordinator', compact('approvers', 'runningTotalsByApprover', 'uncollectedByApprover'));
        }

        if (in_array(38, $roleIds)) {
            return view('dashboards.admin', compact('approvers', 'runningTotalsByApprover', 'uncollectedByApprover'));
        }

        if (in_array(39, $roleIds)) {
            return view('dashboards.allocation', compact('approvers', 'runningTotalsByApprover', 'uncollectedByApprover'));
        }

        if (in_array(40, $roleIds)) {
            return view('dashboards.owner1', compact(
                'approvers', 
                'runningTotalsByApprover', 
                'uncollectedByApprover', 
                'profits',
                'totalDeliveryRates', // Pass the total delivery rates across all requests
                'totalAccessorialRates' // Pass the total accessorial rates across all line items
            ));
        }

        if (in_array(41, $roleIds)) {
            return view('dashboards.owner2', compact(
                'approvers', 
                'runningTotalsByApprover', 
                'uncollectedByApprover', 
                'profits',
                'totalDeliveryRates', // Pass the total delivery rates across all requests
                'totalAccessorialRates' // Pass the total accessorial rates across all line items
            ));
        }

        abort(403, 'Unauthorized dashboard access.');
    }
}
