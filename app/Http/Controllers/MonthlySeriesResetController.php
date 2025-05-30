<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MonthlySeriesNumber;
use App\Models\Company;
use Carbon\Carbon;

class MonthlySeriesResetController extends Controller
{
    public function index()
    {
        $series = MonthlySeriesNumber::with('company')->get();

        return view('monthly_series_reset.index', compact('series'));
    }

    // Handle the reset action
    public function reset()
    {
        MonthlySeriesNumber::query()->update(['series_number' => 0]);

        return redirect()->back()->with('success', 'All series numbers have been reset to 0.');
    }
}


