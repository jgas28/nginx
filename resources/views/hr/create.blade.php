@extends('layouts.app')

@section('title', 'Create Payroll')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-5xl mx-auto px-4">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Create Payroll</h1>
                <p class="text-gray-600 mt-2">Attendance between the selected cutoff dates will compute the payroll automatically.</p>
            </div>
            <a href="{{ route('hr.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>Back to HR
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Cutoff Guide</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                <div class="p-4 bg-blue-50 rounded-lg">
                    <p class="font-semibold text-gray-900">10th Cutoff</p>
                    <p>Use from the 26th of the previous month up to the 10th of the current month.</p>
                </div>
                <div class="p-4 bg-green-50 rounded-lg">
                    <p class="font-semibold text-gray-900">25th Cutoff</p>
                    <p>Use from the 11th up to the 25th of the current month.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <form method="GET" action="{{ route('hr.create') }}" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                    <select name="user_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                        <option value="">Select Employee</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ request('user_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->fname }} {{ $employee->lname }} - {{ $employee->employee_code }}
                                @if(($employee->monthly_salary ?? 0) > 0)
                                    (Monthly: P{{ number_format($employee->monthly_salary ?? 0, 2) }})
                                @else
                                    (Daily: P{{ number_format($employee->daily_rate ?? 0, 2) }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quick Cutoff</label>
                    <select id="quick_cutoff" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">Manual Date Selection</option>
                        <option value="10">10th Cutoff</option>
                        <option value="15">15th Cutoff</option>
                        <option value="25">25th Cutoff</option>
                        <option value="30">30th / End of Month</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cutoff From</label>
                    <input type="date" id="cutoff_from" name="cutoff_from" value="{{ request('cutoff_from') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cutoff To</label>
                    <input type="date" id="cutoff_to" name="cutoff_to" value="{{ request('cutoff_to') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Allowance</label>
                    <input type="number" step="0.01" min="0" name="total_allowance" value="{{ request('total_allowance', 0) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Deduction</label>
                    <input type="number" step="0.01" min="0" name="total_deduction" value="{{ request('total_deduction', 0) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>

                <div class="md:col-span-2">
                    @if($preview)
                        @foreach($preview['selected_attendance_dates'] as $selectedAttendanceDate)
                            <input type="hidden" name="selected_attendance_dates[]" value="{{ $selectedAttendanceDate }}">
                        @endforeach
                    @endif
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                        <i class="fas fa-calculator mr-2"></i>Preview Payroll
                    </button>
                </div>
            </form>
        </div>

        @if($preview)
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Payroll Preview</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                    <div>Employee: <strong>{{ $preview['employee']->fname }} {{ $preview['employee']->lname }}</strong></div>
                    <div>Compensation Basis: <strong>{{ $preview['compensation_basis'] === 'monthly_fixed' ? 'Monthly Fixed Salary' : 'Daily Rate' }}</strong></div>
                    <div>Daily Rate: <strong>P{{ number_format($preview['daily_rate'], 2) }}</strong></div>
                    <div>Monthly Salary: <strong>P{{ number_format($preview['monthly_salary'], 2) }}</strong></div>
                    <div>Cutoff: <strong>{{ \Carbon\Carbon::parse($preview['cutoff_from'])->format('M d, Y') }} - {{ \Carbon\Carbon::parse($preview['cutoff_to'])->format('M d, Y') }}</strong></div>
                    <div>Days Worked: <strong>{{ number_format($preview['total_days_worked'], 0) }}</strong></div>
                    <div>Total Hours Worked: <strong>{{ number_format($preview['total_hours_worked'], 2) }}</strong></div>
                    <div>Gross Salary: <strong>P{{ number_format($preview['gross_salary'], 2) }}</strong></div>
                    <div>SSS Deduction: <strong>P{{ number_format($preview['sss_deduction'], 2) }}</strong></div>
                    <div>PhilHealth Deduction: <strong>P{{ number_format($preview['philhealth_deduction'], 2) }}</strong></div>
                    <div>Tax Deduction: <strong>P{{ number_format($preview['tax_deduction'], 2) }}</strong></div>
                    <div>Allowance: <strong>P{{ number_format($preview['total_allowance'], 2) }}</strong></div>
                    <div>Manual Deduction: <strong>P{{ number_format($preview['manual_deduction'], 2) }}</strong></div>
                    <div>Total Deduction: <strong>P{{ number_format($preview['total_deduction'], 2) }}</strong></div>
                    <div class="md:col-span-2 text-lg">Net Salary: <strong class="text-blue-600">P{{ number_format($preview['net_salary'], 2) }}</strong></div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Attendance Dates To Count</h2>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ $preview['employee']->fname }} {{ $preview['employee']->lname }}'s present and late dates are checked by default.
                            Absent dates stay unchecked and cannot be counted.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-sm font-medium text-blue-700">
                            Checked Days: <span id="checked-days-count" class="ml-1">{{ number_format($preview['total_days_worked'], 0) }}</span>
                        </span>
                        <button type="button" id="check-worked-dates" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-check-square mr-2"></i>Check Worked Dates
                        </button>
                        <button type="button" id="uncheck-all-dates" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-square mr-2"></i>Uncheck All
                        </button>
                    </div>
                </div>

                <form method="GET" action="{{ route('hr.create') }}">
                    <input type="hidden" name="user_id" value="{{ request('user_id') }}">
                    <input type="hidden" name="cutoff_from" value="{{ request('cutoff_from') }}">
                    <input type="hidden" name="cutoff_to" value="{{ request('cutoff_to') }}">
                    <input type="hidden" name="total_allowance" value="{{ request('total_allowance', 0) }}">
                    <input type="hidden" name="total_deduction" value="{{ request('total_deduction', 0) }}">

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600">
                            <thead class="bg-gray-100 text-gray-900 font-semibold">
                                <tr>
                                    <th class="px-4 py-3">Include</th>
                                    <th class="px-4 py-3">Date</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Time In</th>
                                    <th class="px-4 py-3">Time Out</th>
                                    <th class="px-4 py-3">Hours</th>
                                    <th class="px-4 py-3">Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($preview['attendance_records'] as $record)
                                    @php
                                        $recordDate = $record->date->format('Y-m-d');
                                        $isWorkedStatus = in_array($record->status, ['Present', 'Late'], true);
                                        $isChecked = in_array($recordDate, $preview['selected_attendance_dates'], true);
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3">
                                            <input
                                                type="checkbox"
                                                class="attendance-date-checkbox"
                                                name="selected_attendance_dates[]"
                                                value="{{ $recordDate }}"
                                                data-worked-status="{{ $isWorkedStatus ? '1' : '0' }}"
                                                {{ $isChecked ? 'checked' : '' }}
                                                {{ !$isWorkedStatus ? 'disabled' : '' }}
                                            >
                                        </td>
                                        <td class="px-4 py-3">{{ $record->date->format('M d, Y') }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold
                                                {{ $record->status === 'Present' ? 'bg-green-100 text-green-700' : '' }}
                                                {{ $record->status === 'Late' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                                {{ $record->status === 'Absent' ? 'bg-red-100 text-red-700' : '' }}
                                                {{ !in_array($record->status, ['Present', 'Late', 'Absent'], true) ? 'bg-gray-100 text-gray-700' : '' }}">
                                                {{ $record->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">{{ $record->time_in ? $record->time_in->format('H:i') : '-' }}</td>
                                        <td class="px-4 py-3">{{ $record->time_out ? $record->time_out->format('H:i') : '-' }}</td>
                                        <td class="px-4 py-3">{{ $record->total_hours ? number_format($record->total_hours, 2) : '-' }}</td>
                                        <td class="px-4 py-3">{{ $record->remarks ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">No attendance records found for this cutoff.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                            <i class="fas fa-sync-alt mr-2"></i>Recompute Payroll
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <form method="POST" action="{{ route('hr.store') }}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ request('user_id') }}">
                    <input type="hidden" name="cutoff_from" value="{{ request('cutoff_from') }}">
                    <input type="hidden" name="cutoff_to" value="{{ request('cutoff_to') }}">
                    <input type="hidden" name="total_allowance" value="{{ request('total_allowance', 0) }}">
                    <input type="hidden" name="total_deduction" value="{{ request('total_deduction', 0) }}">
                    @foreach($preview['selected_attendance_dates'] as $selectedAttendanceDate)
                        <input type="hidden" name="selected_attendance_dates[]" value="{{ $selectedAttendanceDate }}">
                    @endforeach

                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">
                        <i class="fas fa-save mr-2"></i>Create Payroll
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>

<script>
document.getElementById('quick_cutoff')?.addEventListener('change', function () {
    const value = this.value;
    const fromInput = document.getElementById('cutoff_from');
    const toInput = document.getElementById('cutoff_to');
    const today = new Date();
    const year = today.getFullYear();
    const month = today.getMonth();

    if (value === '10') {
        const from = new Date(year, month - 1, 26);
        const to = new Date(year, month, 10);
        fromInput.value = from.toISOString().split('T')[0];
        toInput.value = to.toISOString().split('T')[0];
    }

    if (value === '25') {
        const from = new Date(year, month, 11);
        const to = new Date(year, month, 25);
        fromInput.value = from.toISOString().split('T')[0];
        toInput.value = to.toISOString().split('T')[0];
    }

    if (value === '15') {
        const from = new Date(year, month, 1);
        const to = new Date(year, month, 15);
        fromInput.value = from.toISOString().split('T')[0];
        toInput.value = to.toISOString().split('T')[0];
    }

    if (value === '30') {
        const from = new Date(year, month, 16);
        const to = new Date(year, month + 1, 0);
        fromInput.value = from.toISOString().split('T')[0];
        toInput.value = to.toISOString().split('T')[0];
    }
});

const attendanceDateCheckboxes = Array.from(document.querySelectorAll('.attendance-date-checkbox'));
const checkedDaysCount = document.getElementById('checked-days-count');
const checkWorkedDatesButton = document.getElementById('check-worked-dates');
const uncheckAllDatesButton = document.getElementById('uncheck-all-dates');

function updateCheckedDaysCount() {
    if (!checkedDaysCount) {
        return;
    }

    const checkedCount = attendanceDateCheckboxes.filter((checkbox) => checkbox.checked).length;
    checkedDaysCount.textContent = checkedCount;
}

attendanceDateCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener('change', updateCheckedDaysCount);
});

checkWorkedDatesButton?.addEventListener('click', function () {
    attendanceDateCheckboxes.forEach((checkbox) => {
        checkbox.checked = checkbox.dataset.workedStatus === '1';
    });

    updateCheckedDaysCount();
});

uncheckAllDatesButton?.addEventListener('click', function () {
    attendanceDateCheckboxes.forEach((checkbox) => {
        checkbox.checked = false;
    });

    updateCheckedDaysCount();
});

updateCheckedDaysCount();
</script>
@endsection
