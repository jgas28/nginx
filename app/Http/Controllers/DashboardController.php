<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $user->load('roles');

        $roleIds = $user->roles->pluck('id')->toArray();

        if (in_array(37, $roleIds)) {
            return view('dashboards.coordinator');
        } elseif (in_array(38, $roleIds)) {
            return view('dashboards.admin');
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
