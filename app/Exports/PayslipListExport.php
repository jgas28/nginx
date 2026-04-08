<?php

namespace App\Exports;

use App\Models\Payroll;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PayslipListExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private array $filters = [])
    {
    }

    public function collection()
    {
        $query = Payroll::with(['user', 'creator'])
            ->orderByDesc('cutoff_to')
            ->orderByDesc('created_at');

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($payrollQuery) use ($search) {
                $payrollQuery->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('fname', 'like', '%' . $search . '%')
                        ->orWhere('lname', 'like', '%' . $search . '%')
                        ->orWhere('employee_code', 'like', '%' . $search . '%');
                })->orWhere('payroll_no', 'like', '%' . $search . '%');
            });
        }

        if (!empty($this->filters['month'])) {
            $monthDate = Carbon::createFromFormat('Y-m', $this->filters['month']);
            $query->whereYear('cutoff_from', $monthDate->year)
                ->whereMonth('cutoff_from', $monthDate->month);
        }

        if (!empty($this->filters['status'])) {
            $query->where('payroll_status', $this->filters['status']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Payroll No.',
            'Employee Code',
            'Employee Name',
            'Position',
            'Cutoff From',
            'Cutoff To',
            'Gross Salary',
            'SSS',
            'PhilHealth',
            'Tax',
            'Allowance',
            'Total Deduction',
            'Net Salary',
            'Status',
            'Created By',
        ];
    }

    public function map($payroll): array
    {
        return [
            $payroll->payroll_no,
            $payroll->user->employee_code ?? 'N/A',
            trim(($payroll->user->fname ?? '') . ' ' . ($payroll->user->lname ?? '')),
            $payroll->user->position ?? 'N/A',
            optional($payroll->cutoff_from)->format('Y-m-d'),
            optional($payroll->cutoff_to)->format('Y-m-d'),
            number_format((float) $payroll->gross_salary, 2, '.', ''),
            number_format((float) ($payroll->sss_deduction ?? 0), 2, '.', ''),
            number_format((float) ($payroll->philhealth_deduction ?? 0), 2, '.', ''),
            number_format((float) ($payroll->tax_deduction ?? 0), 2, '.', ''),
            number_format((float) $payroll->total_allowance, 2, '.', ''),
            number_format((float) $payroll->total_deduction, 2, '.', ''),
            number_format((float) $payroll->net_salary, 2, '.', ''),
            $payroll->payroll_status,
            trim(($payroll->creator->fname ?? '') . ' ' . ($payroll->creator->lname ?? '')),
        ];
    }
}
