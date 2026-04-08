<?php

namespace App\Exports;

use App\Models\Attendance;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendanceSummaryExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private array $filters = [])
    {
    }

    public function collection()
    {
        $query = Attendance::with('user')
            ->orderBy('date')
            ->orderBy('user_id');

        if (!empty($this->filters['month'])) {
            $monthDate = Carbon::createFromFormat('Y-m', $this->filters['month']);
            $query->whereYear('date', $monthDate->year)
                ->whereMonth('date', $monthDate->month);
        }

        if (!empty($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        } elseif (!empty($this->filters['employee'])) {
            $employee = $this->filters['employee'];
            $query->whereHas('user', function ($q) use ($employee) {
                $q->where('fname', 'like', '%' . $employee . '%')
                    ->orWhere('lname', 'like', '%' . $employee . '%')
                    ->orWhere('employee_code', 'like', '%' . $employee . '%');
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Employee Code',
            'Employee',
            'Position',
            'Status',
            'Time In',
            'Time Out',
            'Total Hours',
            'Remarks',
        ];
    }

    public function map($attendance): array
    {
        $totalHours = !is_null($attendance->total_hours)
            ? (float) $attendance->total_hours
            : ($attendance->status === 'Present' ? 8.0 : 0.0);

        return [
            optional($attendance->date)->format('Y-m-d'),
            $attendance->user->employee_code ?? 'N/A',
            trim(($attendance->user->fname ?? '') . ' ' . ($attendance->user->lname ?? '')),
            $attendance->user->position ?? 'N/A',
            $attendance->status,
            $attendance->time_in ? $attendance->time_in->format('H:i') : '-',
            $attendance->time_out ? $attendance->time_out->format('H:i') : '-',
            number_format($totalHours, 2),
            $attendance->remarks ?? '',
        ];
    }
}
