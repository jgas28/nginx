<?php

namespace App\Http\Controllers;

use App\Models\RunningBalance;
use App\Models\Company;
use App\Models\User;
use App\Models\Approver;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;

class RunningBalanceController extends Controller
{
    public function index(Request $request)
    {
        // Base query with relationships
        $query = RunningBalance::with(['approver', 'employee', 'creator', 'suppliers']);
        $search = trim((string) $request->input('search'));
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50], true) ? $perPage : 10;

        // Apply date filter if provided
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        // Filter by approver if provided
        if ($request->filled('approver_id')) {
            $query->where('approver_id', $request->approver_id);
        }

        if ($request->filled('adjustment_type')) {
            $query->where('adjustment_type', $request->adjustment_type);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('cvr_number', 'like', "%{$search}%")
                    ->orWhereHas('approver', function ($approver) use ($search) {
                        $approver->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('employee', function ($employee) use ($search) {
                        $employee->whereRaw("CONCAT(COALESCE(fname, ''), ' ', COALESCE(lname, '')) like ?", ["%{$search}%"]);
                    })
                    ->orWhereHas('creator', function ($creator) use ($search) {
                        $creator->whereRaw("CONCAT(COALESCE(fname, ''), ' ', COALESCE(lname, '')) like ?", ["%{$search}%"]);
                    })
                    ->orWhereHas('suppliers', function ($supplier) use ($search) {
                        $supplier->where('supplier_name', 'like', "%{$search}%");
                    });
            });
        }

        // Sorting
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        if (!in_array($sort, ['created_at', 'amount', 'type', 'adjustment_type'], true)) {
            $sort = 'created_at';
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $summaryQuery = clone $query;
        $visibleCount = (clone $summaryQuery)->count();
        $visibleAmount = (clone $summaryQuery)->sum('amount');
        $inCount = (clone $summaryQuery)->where('adjustment_type', 'In')->count();
        $outCount = (clone $summaryQuery)->where('adjustment_type', 'Out')->count();

        $balances = $query->orderBy($sort, $direction)
            ->paginate($perPage)
            ->appends($request->query());

        $approvers = Approver::all();
        $employees = User::where('status', '!=', 0)->get();
        $suppliers = Supplier::all();

        $excludedApproverIds = [564,565,801,802,803,1532,1651,1652,1654,1655,1656,1660,1661,1662,1663,1664,1665,1666,1969,1972,1975];

        // ✅ Include transfer (type 10) in running balance
        $runningTotalsByApprover = RunningBalance::selectRaw('approver_id, SUM(amount) as total')
            // whereIn('type', [1, 2, 3, 5, 8, 9, 10, 11, 12])
            // ->whereNotIn('id', $excludedApproverIds)
            // ->
            ->groupBy('approver_id')
            ->pluck('total', 'approver_id');

        // ✅ Salary deductions only (type 5)
        $salaryDeductions = RunningBalance::where('type', 5)
            ->selectRaw('approver_id, SUM(amount) as total')
            ->groupBy('approver_id')
            ->pluck('total', 'approver_id');

        // ✅ Uncollected + deductions (type 4 + 5) for display (absolute value)
        $uncollectedByApprover = RunningBalance::whereIn('type', [4, 5, 12]) // ✅ include 12 if applicable
        ->selectRaw('approver_id, SUM(amount) as total')
        ->groupBy('approver_id')
        ->pluck('total', 'approver_id');

        if ($request->ajax()) {
            return response()->json([
                'html' => view('running_balance.partials.index-table', compact(
                    'balances',
                    'approvers',
                    'runningTotalsByApprover',
                    'uncollectedByApprover',
                    'visibleCount',
                    'visibleAmount',
                    'inCount',
                    'outCount'
                ))->render(),
            ]);
        }

        return view('running_balance.index', compact(
            'balances',
            'approvers',
            'employees',
            'runningTotalsByApprover',
            'salaryDeductions',
            'uncollectedByApprover',
            'suppliers',
            'visibleCount',
            'visibleAmount',
            'inCount',
            'outCount'
        ));
    }

    // For Approver ID 2 (Laguna)
    public function adminFunds(Request $request)
    {
        $request->merge(['approver_id' => 2]);
        return $this->filteredFunds($request, 'adminFunds');
    }

    // For Approver ID 3 (Davao)
    public function davaoFunds(Request $request)
    {
        $request->merge(['approver_id' => 3]);
        return $this->filteredFunds($request, 'davaoFunds');
    }

    public function collectedFunds(Request $request)
    {
        $query = RunningBalance::with(['approver', 'employee', 'creator', 'suppliers'])
            ->where('type', 2);

        $search = trim((string) $request->input('search'));
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50], true) ? $perPage : 10;

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        if ($request->filled('adjustment_type')) {
            $query->where('adjustment_type', $request->adjustment_type);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('cvr_number', 'like', "%{$search}%")
                    ->orWhereHas('approver', fn($a) => $a->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('employee', fn($e) => $e->whereRaw("CONCAT(COALESCE(fname,''),' ',COALESCE(lname,'')) like ?", ["%{$search}%"]))
                    ->orWhereHas('creator', fn($c) => $c->whereRaw("CONCAT(COALESCE(fname,''),' ',COALESCE(lname,'')) like ?", ["%{$search}%"]))
                    ->orWhereHas('suppliers', fn($s) => $s->where('supplier_name', 'like', "%{$search}%"));
            });
        }

        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        if (!in_array($sort, ['created_at', 'amount', 'type', 'adjustment_type'], true)) {
            $sort = 'created_at';
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $summaryQuery = clone $query;
        $visibleCount = (clone $summaryQuery)->count();
        $visibleAmount = (clone $summaryQuery)->sum('amount');
        $inCount = (clone $summaryQuery)->where('adjustment_type', 'In')->count();
        $outCount = (clone $summaryQuery)->where('adjustment_type', 'Out')->count();

        $balances = $query->orderBy($sort, $direction)->paginate($perPage)->appends($request->query());

        $approvers = Approver::all();
        $employees = User::where('status', '!=', 0)->get();

        $runningTotalsByApprover = [];
        $uncollectedByApprover = [];
        $salaryDeductions = [];
        $approverId = null;
        $locationLabel = 'Collected';

        if ($request->ajax()) {
            return response()->json([
                'html' => view('running_balance.partials.funds-table', compact(
                    'balances', 'approverId', 'locationLabel'
                ))->render(),
                'summary' => [
                    'visible_count' => $visibleCount,
                    'visible_amount' => $visibleAmount,
                    'in_count' => $inCount,
                    'out_count' => $outCount,
                    'running_total' => 0,
                    'uncollected_total' => 0,
                ],
            ]);
        }

        return view('running_balance.collectedFunds', compact(
            'balances', 'approvers', 'employees',
            'runningTotalsByApprover', 'salaryDeductions', 'uncollectedByApprover',
            'approverId', 'locationLabel', 'visibleCount', 'visibleAmount', 'inCount', 'outCount'
        ));
    }

    // Shared logic
    protected function filteredFunds(Request $request, $viewName)
    {
        // Determine the correct approver_id from the route logic
        $approverId = $request->approver_id;

        $query = RunningBalance::with(['approver', 'employee', 'creator', 'suppliers'])
            ->where('approver_id', $approverId); // Always filter by fixed approver

        $search = trim((string) $request->input('search'));
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50], true) ? $perPage : 10;

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        if ($request->filled('adjustment_type')) {
            $query->where('adjustment_type', $request->adjustment_type);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('cvr_number', 'like', "%{$search}%")
                    ->orWhereHas('approver', function ($approver) use ($search) {
                        $approver->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('employee', function ($employee) use ($search) {
                        $employee->whereRaw("CONCAT(COALESCE(fname, ''), ' ', COALESCE(lname, '')) like ?", ["%{$search}%"]);
                    })
                    ->orWhereHas('creator', function ($creator) use ($search) {
                        $creator->whereRaw("CONCAT(COALESCE(fname, ''), ' ', COALESCE(lname, '')) like ?", ["%{$search}%"]);
                    })
                    ->orWhereHas('suppliers', function ($supplier) use ($search) {
                        $supplier->where('supplier_name', 'like', "%{$search}%");
                    });
            });
        }

        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        if (!in_array($sort, ['created_at', 'amount', 'type', 'adjustment_type'], true)) {
            $sort = 'created_at';
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $summaryQuery = clone $query;
        $visibleCount = (clone $summaryQuery)->count();
        $visibleAmount = (clone $summaryQuery)->sum('amount');
        $inCount = (clone $summaryQuery)->where('adjustment_type', 'In')->count();
        $outCount = (clone $summaryQuery)->where('adjustment_type', 'Out')->count();

        $balances = $query->orderBy($sort, $direction)
            ->paginate($perPage)
            ->appends($request->query());

        $approvers = Approver::all();
        $employees = User::where('status', '!=', 0)->get();

        $runningTotalsByApprover = RunningBalance::where('approver_id', $approverId)
            // ->whereIn('type', [1, 2, 3, 5, 8, 9, 10, 11, 12])
            ->selectRaw('approver_id, SUM(amount) as total')
            ->groupBy('approver_id')
            ->pluck('total', 'approver_id');

        $salaryDeductions = RunningBalance::where('approver_id', $approverId)
            ->where('type', 5)
            ->selectRaw('approver_id, SUM(amount) as total')
            ->groupBy('approver_id')
            ->pluck('total', 'approver_id');

        $uncollectedByApprover = RunningBalance::where('approver_id', $approverId)
            ->whereIn('type', [4, 5, 12])
            ->selectRaw('approver_id, SUM(amount) as total')
            ->groupBy('approver_id')
            ->pluck('total', 'approver_id');

        $locationLabel = $viewName === 'davaoFunds' ? 'Davao' : 'Laguna';
        $partialView = 'running_balance.partials.funds-table';

        if ($request->ajax()) {
            return response()->json([
                'html' => view($partialView, compact(
                    'balances',
                    'approverId',
                    'locationLabel'
                ))->render(),
                'summary' => [
                    'visible_count' => $visibleCount,
                    'visible_amount' => $visibleAmount,
                    'in_count' => $inCount,
                    'out_count' => $outCount,
                    'running_total' => $runningTotalsByApprover[$approverId] ?? 0,
                    'uncollected_total' => $uncollectedByApprover[$approverId] ?? 0,
                ],
            ]);
        }

        return view("running_balance.{$viewName}", compact(
            'balances',
            'approvers',
            'employees',
            'runningTotalsByApprover',
            'salaryDeductions', 
            'uncollectedByApprover',
            'approverId',
            'locationLabel',
            'visibleCount',
            'visibleAmount',
            'inCount',
            'outCount'
        ));
    }
    
    private function getExportRecords(Request $request): array
    {
        $query = RunningBalance::with(['approver', 'employee', 'creator', 'suppliers']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        if ($request->filled('approver_id')) {
            $query->where('approver_id', $request->approver_id);
        }

        if ($request->filled('adjustment_type')) {
            $query->where('adjustment_type', $request->adjustment_type);
        }

        $allowedSorts = ['created_at', 'amount', 'type', 'adjustment_type'];
        $sort      = in_array($request->get('sort'), $allowedSorts) ? $request->get('sort') : 'created_at';
        $direction = $request->get('direction') === 'asc' ? 'asc' : 'desc';

        $records = $query->orderBy($sort, $direction)->get();

        $locationLabel = 'All Source Funds';
        if ($request->filled('approver_id')) {
            $approver = Approver::find($request->approver_id);
            $locationLabel = $approver ? $approver->name : 'Filtered';
        }

        $dateFrom = $request->get('start_date') ?: 'All Dates';
        $dateTo   = $request->get('end_date')   ?: now()->format('Y-m-d');

        return compact('records', 'locationLabel', 'dateFrom', 'dateTo');
    }

    private function getTypeLabel(int $type): string
    {
        return match ($type) {
            1  => 'Top-up',
            2  => 'Collected',
            3  => 'Refund',
            4  => 'Uncollected Funds',
            5  => 'Salary Deduction',
            6  => 'Liquidated Amount',
            7  => 'Transfer',
            8  => 'Release Approved Amount',
            10 => 'Transfer',
            11 => 'Adjustment',
            12 => 'Adjustment for Uncollected',
            default => 'Reimbursement',
        };
    }

    public function exportExcel(Request $request)
    {
        ['records' => $records, 'locationLabel' => $locationLabel, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo] = $this->getExportRecords($request);

        $slug     = preg_replace('/[^a-z0-9]+/', '-', strtolower($locationLabel));
        $filename = 'running-balance-' . $slug . '-' . now()->format('Y-m-d') . '.csv';

        $callback = function () use ($records, $locationLabel, $dateFrom, $dateTo) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Running Balance Report - ' . $locationLabel]);
            fputcsv($out, ['Period: ' . $dateFrom . ' to ' . $dateTo]);
            fputcsv($out, ['Generated: ' . now()->format('M d, Y h:i A')]);
            fputcsv($out, []);
            fputcsv($out, ['Date', 'CVR Number', 'Type', 'Movement', 'Description', 'Employee / Supplier', 'Source', 'Created By', 'Amount']);

            foreach ($records as $record) {
                $party = '';
                if ($record->employee) {
                    $party = trim(($record->employee->fname ?? '') . ' ' . ($record->employee->lname ?? ''));
                } elseif ($record->suppliers) {
                    $party = $record->suppliers->supplier_name ?? '';
                }

                fputcsv($out, [
                    optional($record->created_at)->format('M d, Y'),
                    $record->cvr_number ?? '',
                    $this->getTypeLabel((int) $record->type),
                    $record->adjustment_type ?? '',
                    $record->description ?? '',
                    $party,
                    optional($record->approver)->name ?? '',
                    trim((optional($record->creator)->fname ?? '') . ' ' . (optional($record->creator)->lname ?? '')),
                    number_format((float) $record->amount, 2),
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportPdf(Request $request)
    {
        ['records' => $records, 'locationLabel' => $locationLabel, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo] = $this->getExportRecords($request);

        $slug     = preg_replace('/[^a-z0-9]+/', '-', strtolower($locationLabel));
        $filename = 'running-balance-' . $slug . '-' . now()->format('Y-m-d') . '.pdf';

        $pdf = Pdf::loadView('running_balance.export-pdf', compact('records', 'locationLabel', 'dateFrom', 'dateTo'))
            ->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'type'             => 'required|in:1,2,3,4,5,6,8,9,10,11,12',
            'approver_id'      => 'required|exists:cvr_approver,id',
            'amount' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) use ($request) {
                    $type = (int) $request->input('type');

                    if (in_array($type, [11, 12])) {
                        if ((float)$value === 0.0) {
                            $fail('The amount cannot be zero for adjustment types.');
                        }
                    } else {
                        if ((float)$value < 0.01) {
                            $fail('The amount must be at least 0.01.');
                        }
                    }
                },
            ],
            'description'      => 'nullable|string',
            'employee_id'      => 'nullable|exists:users,id',
            'supplier_id'      => 'nullable|exists:suppliers,id',
            'from_approver_id' => 'required_if:type,10|nullable|exists:cvr_approver,id',
        ]);

        $party = $this->resolvePartySelection($request);

        $amount = $request->amount;

        if (in_array($request->type, [3, 4, 8])) {
            $amount = -abs($amount); // Always negative
        } elseif (in_array($request->type, [11, 12])) {
            $amount = $amount; // Manual +/- allowed
        }

        // Determine adjustment type
        $adjustmentType = $amount > 0 ? 'In' : 'Out';

        // ✅ Handle Transfer separately
        if ($request->type == 10) {
            // Transfer out (deduction from source)
            RunningBalance::create([
                'approver_id' => $request->from_approver_id,
                'type'        => 10,
                'amount'      => -$request->amount,
                'description' => 'Transfer to ' . optional(Approver::find($request->approver_id))->name .
                                ($request->description ? ' - ' . $request->description : ''),
                'employee_id' => $party['employee_id'],
                'supplier_id' => $party['supplier_id'],
                'created_by'  => $user->id,
                'adjustment_type' => 'Out',
            ]);

            // Transfer in (credit to destination)
            RunningBalance::create([
                'approver_id' => $request->approver_id,
                'type'        => 10,
                'amount'      => $request->amount,
                'description' => 'Transfer from ' . optional(Approver::find($request->from_approver_id))->name .
                                ($request->description ? ' - ' . $request->description : ''),
                'employee_id' => $party['employee_id'],
                'supplier_id' => $party['supplier_id'],
                'created_by'  => $user->id,
                'adjustment_type' => 'In',
            ]);
        } else {
            $description = $request->description;

            if ($request->type == 9) {
                $description = 'Admin Adjustment: ' . $description;
            } elseif ($request->type == 11) {
                $description = 'Admin Adjustment: ' . $description;
            } elseif ($request->type == 12) {
                $description = 'Admin Adjustment for Uncollected: ' . $description;
            }

            // All other types
            RunningBalance::create([
                'approver_id' => $request->approver_id,
                'type'        => $request->type,
                'amount'      => $amount,
                'description' => $description,
                'employee_id' => $party['employee_id'],
                'supplier_id' => $party['supplier_id'],
                'created_by'  => $user->id,
                'adjustment_type' => $adjustmentType,
            ]);
        }

        return redirect()->route('running_balance.index')->with('success', 'Transaction recorded successfully.');
    }


    public function storeReimbursement(Request $request)
    {
        $liquidation_id = $request->liquidation_id;
        $validated = $request->validate([
            'employee_id' => 'nullable|exists:users,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'approver_id' => 'required|exists:cvr_approver,id',
            'created_by' => 'required|exists:users,id',
            'cvr_number' => 'required|string',
        ]);

        $party = $this->resolvePartySelection($request, true);
        $validated['employee_id'] = $party['employee_id'];
        $validated['supplier_id'] = $party['supplier_id'];
        $validated['type'] = 3;
        $validated['amount'] = -abs($validated['amount']);
        $validated['adjustment_type'] = 'Out';

        // Check for duplicate based on a unique combination
        $existing = RunningBalance::where([
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'approver_id' => $validated['approver_id'],
            'created_by' => $validated['created_by'],
            'cvr_number' => $validated['cvr_number'],
            'adjustment_type' => $validated['adjustment_type'],
            'type' => 3,
        ]);

        $this->applyPartyMatch($existing, $validated['employee_id'], $validated['supplier_id']);
        $existing = $existing->first();

        if ($existing) {     
            return redirect()->route('liquidations.review', $liquidation_id)
                ->with('info', 'Reimbursement already submitted.');
        }

        $reimbursement = RunningBalance::create($validated);

        return redirect()->route('liquidations.review', $liquidation_id);
    }

    public function storeReimbursementAdmin(Request $request)
    {
        $liquidation_id = $request->liquidation_id;
        $validated = $request->validate([
            'employee_id' => 'nullable|exists:users,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'approver_id' => 'required|exists:cvr_approver,id',
            'created_by' => 'required|exists:users,id',
            'cvr_number' => 'required|string',
        ]);

        $party = $this->resolvePartySelection($request, true);
        $validated['employee_id'] = $party['employee_id'];
        $validated['supplier_id'] = $party['supplier_id'];
        $validated['type'] = 3;
        $validated['amount'] = -abs($validated['amount']);
        $validated['adjustment_type'] = 'Out';

        // Check for duplicate based on a unique combination
        $existing = RunningBalance::where([
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'approver_id' => $validated['approver_id'],
            'created_by' => $validated['created_by'],
            'cvr_number' => $validated['cvr_number'],
            'adjustment_type' => $validated['adjustment_type'],
            'type' => 3,
        ]);

        $this->applyPartyMatch($existing, $validated['employee_id'], $validated['supplier_id']);
        $existing = $existing->first();

        if ($existing) {     
            return redirect()->route('liquidations.approval', $liquidation_id)
                ->with('info', 'Reimbursement already submitted.');
        }

        $reimbursement = RunningBalance::create($validated);

        return redirect()->route('liquidations.approval', $liquidation_id);
    }



    public function print($id)
    {
        $reimbursement = RunningBalance::with(['employee', 'approver', 'suppliers'])->findOrFail($id);

        return view('reimbursements.print', compact('reimbursement'));
    }

    public function printRefund($id)
    {
        $reimbursement = RunningBalance::with(['employee', 'approver', 'suppliers'])->findOrFail($id);

        return view('refunds.print', compact('reimbursement'));
    }

    public function printReturn($id)
    {
        $reimbursement = RunningBalance::with(['employee', 'approver', 'suppliers'])->findOrFail($id);

        return view('returns.print', compact('reimbursement'));
    }

    // public function storeCollected(Request $request)
    // {
    //     $liquidation_id = $request->liquidation_id;
    //     $validated = $request->validate([
    //         'employee_id' => 'required|exists:users,id',
    //         'amount_collected' => 'required|numeric|min:0',
    //         'amount_uncollected' => 'required|numeric|min:0',
    //         'description' => 'required|string',
    //         'description1' => 'required|string',
    //         'approver_id' => 'required|exists:cvr_approver,id',
    //         'created_by' => 'required|exists:users,id',
    //         'cvr_number' => 'required|string',
    //     ]);

    //     // 1. Returned Cash Record
    //     $reimbursement=RunningBalance::create([
    //         'employee_id' => $validated['employee_id'],
    //         'amount' => abs($validated['amount_collected']), // positive value
    //         'description' => $validated['description'],
    //         'approver_id' => $validated['approver_id'],
    //         'created_by' => $validated['created_by'],
    //         'cvr_number' => $validated['cvr_number'],
    //         'adjustment_type' => 'In',
    //         'type' => 2,
    //     ]);

    //     RunningBalance::create([
    //         'employee_id' => $validated['employee_id'],
    //         'amount' => -abs($validated['amount_uncollected']), // negative value
    //         'description' => $validated['description1'],
    //         'approver_id' => $validated['approver_id'],
    //         'created_by' => $validated['created_by'],
    //         'cvr_number' => $validated['cvr_number'],
    //         'adjustment_type' => 'Float',
    //         'type' => 4,
    //     ]);
    // }


    public function storeCollected(Request $request)
    {
        // dd($request->all());
        $liquidation_id = $request->liquidation_id;
        $user = Auth::user();
        // Validate the collected form data
        $validated = $request->validate([
            'amount_collected' => 'required|numeric|min:0', // Amount collected
            'description_collected' => 'required|string', // Description for collected
            'employee_id' => 'nullable|exists:users,id', // Employee ID for collected amount
            'supplier_id' => 'nullable|exists:suppliers,id',
            'approver_id_collected' => 'required|exists:cvr_approver,id', // Approver ID
            'cvr_number_collected' => 'required|string', // CVR Number
        ]);

        $party = $this->resolvePartySelection($request, true);

        // Create a new reimbursement entry for the collected amount
        RunningBalance::create([
            'employee_id' => $party['employee_id'],
            'supplier_id' => $party['supplier_id'],
            'amount' => abs($validated['amount_collected']), // Positive value for collected amount
            'description' => $validated['description_collected'],
            'approver_id' => $validated['approver_id_collected'],
            'created_by' => $user->id,
            'cvr_number' => $validated['cvr_number_collected'],
            'type' => 2,  // Assuming type '2' represents collected
            'adjustment_type' => 'In',  // Type of adjustment
        ]);

        // dd($validated);

        return redirect()->route('liquidations.validated', $liquidation_id)
                        ->with('success', 'Collected amount saved successfully!');
    }

    public function storeUncollected(Request $request)
    {
        // dd($request->all());
        $liquidation_id = $request->liquidation_id;
        $user = Auth::user();
        // Validate the uncollected form data
        $validated = $request->validate([
            'amount_uncollected' => 'required|numeric|min:0', // Amount uncollected (readonly in the form)
            'employee_id_uncollected' => 'required|array', // Employee IDs (array for multiple)
            'deduction_amount_uncollected' => 'required|array', // Deduction amounts (array for multiple)
            'employee_id_uncollected.*' => 'exists:users,id', // Ensure each employee exists
            'deduction_amount_uncollected.*' => 'numeric|min:0', // Ensure each deduction is a valid number
            'approver_id_uncollected' => 'required|exists:cvr_approver,id', // Approver ID
            'cvr_number_uncollected' => 'required|string', // CVR Number
            'description_uncollected' => 'required|string',
        ]);

        // Store uncollected amounts for each employee
        $employeeIds = $validated['employee_id_uncollected'];
        $deductionAmounts = $validated['deduction_amount_uncollected'];

        // Loop through each employee ID and its corresponding deduction amount
        foreach ($employeeIds as $index => $employeeId) {
            $deductionAmount = $deductionAmounts[$index];

            // Create a record in the RunningBalance table for each employee
            RunningBalance::create([
                'employee_id' => $employeeId,
                'amount' => -abs($deductionAmount), // Negative value for uncollected amount
                'description' => $validated['description_uncollected'], // Use description for uncollected
                'approver_id' => $validated['approver_id_uncollected'],
                'created_by' =>  $user->id,
                'cvr_number' => $validated['cvr_number_uncollected'],
                'type' => 4,  // Assuming type '4' represents uncollected
                'adjustment_type' => 'Float',  // Type of adjustment for uncollected
            ]);
        }

        // Redirect with success message
        return redirect()->route('liquidations.validated', $liquidation_id)
                        ->with('success', 'Uncollected amounts and deductions saved successfully!');
    }

    public function storeCollectedAdmin(Request $request)
    {
        $liquidation_id = $request->liquidation_id;
        $validated = $request->validate([
            'employee_id' => 'required|exists:users,id', 
            'amount_collected' => 'required|numeric|min:0',
            'amount_uncollected' => 'required|numeric|min:0',
            'description' => 'required|string',
            'description1' => 'required|string',
            'approver_id' => 'required|exists:cvr_approver,id',
            'created_by' => 'required|exists:users,id',
            'cvr_number' => 'required|string',
        ]);

        // 1. Returned Cash Record
        $reimbursement=RunningBalance::create([
            'employee_id' => $validated['employee_id'],
            'amount' => abs($validated['amount_collected']), // positive value
            'description' => $validated['description'],
            'approver_id' => $validated['approver_id'],
            'created_by' => $validated['created_by'],
            'cvr_number' => $validated['cvr_number'],
            'type' => 2,
            'adjustment_type' => 'In',
        ]);

        // 2. Uncollected Cash Record
        RunningBalance::create([
            'employee_id' => $validated['employee_id'],
            'amount' => -abs($validated['amount_uncollected']), // negative value
            'description' => $validated['description1'],
            'approver_id' => $validated['approver_id'],
            'created_by' => $validated['created_by'],
            'cvr_number' => $validated['cvr_number'],
            'type' => 4,
            'adjustment_type' => 'Float',
        ]);

         return redirect()->route('liquidations.approval', $liquidation_id);
    }

     // Edit Refund
    public function editRefund($id)
    {
        $refund = RunningBalance::findOrFail($id);
        return response()->json($refund);
    }

    // Update Refund
    public function updateRefund(Request $request, $id)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric',
        ]);

        $refund = RunningBalance::findOrFail($id);
        $refund->update($request->only('description', 'amount'));

        $liquidation_id = $request->input('liquidation_id');
        return redirect()->route('liquidations.approval', ['id' => $liquidation_id])
                 ->with('success', 'Refund updated successfully.');
    }

    // Edit Return
    public function editReturn($id)
    {
        $return = RunningBalance::findOrFail($id);
        return response()->json($return);
    }

    // Update Return
    public function updateReturn(Request $request, $id)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric',
        ]);

        $return = RunningBalance::findOrFail($id);
        $return->update($request->only('description', 'amount'));
       
        $liquidation_id = $request->input('liquidation_id');

        return redirect()->route('liquidations.approval', ['id' => $liquidation_id])
                 ->with('success', 'Return updated successfully.');
    }

    public function storeAdminAdjustment(Request $request)
    {
        $request->validate([
            'approver_id' => 'required|exists:cvr_approver,id',
            'employee_id' => 'nullable|exists:users,id',
            'amount' => 'required|numeric|not_in:0', // Allow both positive and negative
            'description' => 'required|string|max:255',
        ]);

        RunningBalance::create([
            'approver_id' => $request->approver_id,
            'employee_id' => $request->employee_id,
            'amount' => $request->amount,
            'description' => 'Admin Adjustment: ' . $request->description,
            'type' => 9, // Admin adjustment type
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('running_balance.index')->with('success', 'Admin adjustment recorded.');
    }

    public function storeUncollectedAdjustment(Request $request)
    {
        $request->validate([
            'approver_id' => 'required|exists:cvr_approver,id',
            'employee_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|not_in:0',
            'description' => 'required|string|max:255',
        ]);

        $adjustedAmount = $request->amount; // ✅ Keep raw amount (+ or -)

        RunningBalance::create([
            'approver_id' => $request->approver_id,
            'employee_id' => $request->employee_id,
            'amount' => $adjustedAmount,
            'description' => 'Uncollected Adjustment: ' . $request->description,
            'type' => 11,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('running_balance.index')->with('success', 'Uncollected adjustment recorded.');
    }

    private function resolvePartySelection(Request $request, bool $required = false): array
    {
        $employeeId = $request->filled('employee_id') ? (int) $request->input('employee_id') : null;
        $supplierId = $request->filled('supplier_id') ? (int) $request->input('supplier_id') : null;

        if ($employeeId && $supplierId) {
            throw ValidationException::withMessages([
                'party_type' => 'Select only one party: employee or supplier.',
            ]);
        }

        if ($required && !$employeeId && !$supplierId) {
            throw ValidationException::withMessages([
                'party_type' => 'Select an employee or supplier.',
            ]);
        }

        return [
            'employee_id' => $employeeId,
            'supplier_id' => $supplierId,
        ];
    }

    private function applyPartyMatch($query, ?int $employeeId, ?int $supplierId)
    {
        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        } else {
            $query->whereNull('employee_id');
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        } else {
            $query->whereNull('supplier_id');
        }

        return $query;
    }

}
