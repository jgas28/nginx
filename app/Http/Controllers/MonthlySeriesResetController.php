<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MonthlySeriesNumber;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MonthlySeriesResetController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = MonthlySeriesNumber::query()->with('company');

        if ($request->boolean('datatable') || $request->ajax()) {
            return $this->datatableResponse($request, clone $baseQuery);
        }

        $series = (clone $baseQuery)->get();
        $companies = Company::query()
            ->orderBy('company_name')
            ->get(['id', 'company_code', 'company_name']);

        $activeSeriesCount = $series->where('series_number', '>', 0)->count();
        $zeroSeriesCount = $series->where('series_number', '<=', 0)->count();
        $highestSeries = $series->max('series_number') ?? 0;
        $currentMonthLabel = Carbon::now()->format('F Y');

        $chartSeries = $series
            ->sortByDesc('series_number')
            ->take(6)
            ->map(function (MonthlySeriesNumber $record) {
                $company = $record->company;
                $fallbackName = 'Company #' . $record->company_id;

                return [
                    'label' => $company?->company_code ?: ($company?->company_name ?: $fallbackName),
                    'name' => $company?->company_name ?: $fallbackName,
                    'value' => (int) $record->series_number,
                ];
            })
            ->values();

        return view('monthly_series_reset.index', compact(
            'series',
            'companies',
            'activeSeriesCount',
            'zeroSeriesCount',
            'highestSeries',
            'currentMonthLabel',
            'chartSeries'
        ));
    }

    // Handle the reset action
    public function reset()
    {
        MonthlySeriesNumber::query()->update(['series_number' => 0]);

        return redirect()->back()->with('success', 'All series numbers have been reset to 0.');
    }

    protected function datatableResponse(Request $request, $query)
    {
        $draw = (int) $request->input('draw', 1);
        $start = max((int) $request->input('start', 0), 0);
        $length = (int) $request->input('length', 10);
        $length = $length > 0 ? min($length, 100) : 10;

        $companyId = trim((string) $request->input('company_id', ''));
        $searchValue = trim((string) data_get($request->input('search', []), 'value', ''));
        $orderColumnIndex = (int) data_get($request->input('order', []), '0.column', 0);
        $orderDirection = strtolower((string) data_get($request->input('order', []), '0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($companyId !== '') {
            $query->where('company_id', $companyId);
        }

        $recordsTotal = (clone $query)->count();

        if ($searchValue !== '') {
            $query->where(function ($builder) use ($searchValue) {
                $builder
                    ->where('month', 'like', '%' . $searchValue . '%')
                    ->orWhere('series_number', 'like', '%' . $searchValue . '%')
                    ->orWhereHas('company', function ($companyQuery) use ($searchValue) {
                        $companyQuery
                            ->where('company_code', 'like', '%' . $searchValue . '%')
                            ->orWhere('company_name', 'like', '%' . $searchValue . '%')
                            ->orWhere('company_location', 'like', '%' . $searchValue . '%');
                    });
            });
        }

        $recordsFiltered = (clone $query)->count();

        $orderableColumns = [
            0 => 'company_code',
            1 => 'company_name',
            2 => 'company_location',
            3 => 'month',
            4 => 'series_number',
            5 => 'updated_at',
        ];

        $orderColumn = $orderableColumns[$orderColumnIndex] ?? 'company_name';

        $query
            ->leftJoin('companies', 'monthly_series_numbers.company_id', '=', 'companies.id')
            ->select('monthly_series_numbers.*')
            ->orderBy(
                DB::raw(match ($orderColumn) {
                    'company_code' => 'companies.company_code',
                    'company_name' => 'companies.company_name',
                    'company_location' => 'companies.company_location',
                    default => 'monthly_series_numbers.' . $orderColumn,
                }),
                $orderDirection
            );

        $rows = $query
            ->skip($start)
            ->take($length)
            ->get()
            ->map(function (MonthlySeriesNumber $record) {
                return [
                    'company_code' => $record->company->company_code ?? 'N/A',
                    'company_name' => $record->company->company_name ?? 'N/A',
                    'company_location' => $record->company->company_location ?? 'N/A',
                    'month' => $record->month ?: 'N/A',
                    'series_number' => (int) $record->series_number,
                    'updated_at' => optional($record->updated_at)->format('M d, Y h:i A') ?? 'N/A',
                ];
            });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ]);
    }
}

