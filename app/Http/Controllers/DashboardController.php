<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\RunningBalance;
use App\Models\Approver;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $user->load('roles');

        $roleIds = $user->roles->pluck('id')->toArray();

        // Shared running balance data (only if needed by dashboard)
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

        // You can pass the balance data only to roles who need it:
        if (in_array(37, $roleIds)) {
            return view('dashboards.coordinator', compact('approvers', 'runningTotalsByApprover', 'uncollectedByApprover'));
        } elseif (in_array(38, $roleIds)) {
            return view('dashboards.admin', compact('approvers', 'runningTotalsByApprover', 'uncollectedByApprover'));
        } elseif (in_array(39, $roleIds)) {
            return view('dashboards.allocation');
        } elseif (in_array(40, $roleIds)) {
            return view('dashboards.owner1');
        } elseif (in_array(41, $roleIds)) {
            return view('dashboards.owner2');
        }

        abort(403, 'Unauthorized dashboard access.');
    }
}
