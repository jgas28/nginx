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
                    @php
                        $selectedEmployee = $employees->firstWhere('id', (int) request('user_id'));
                        $selectedEmployeeLabel = $selectedEmployee
                            ? trim($selectedEmployee->fname . ' ' . $selectedEmployee->lname) . ' - ' . $selectedEmployee->employee_code
                            : '';
                    @endphp
                    <label class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                    <div class="relative">
                        <input
                            type="text"
                            id="hr_employee_search"
                            value="{{ old('employee_search', $selectedEmployeeLabel) }}"
                            placeholder="Search employee name or code..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                            autocomplete="off"
                            required
                        >
                        <div
                            id="hr_employee_suggestions"
                            class="absolute z-20 mt-1 hidden max-h-64 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg"
                        >
                            @foreach($employees as $employee)
                                @php
                                    $employeeLabel = trim($employee->fname . ' ' . $employee->lname) . ' - ' . $employee->employee_code;
                                @endphp
                                <button
                                    type="button"
                                    class="hr-employee-suggestion flex w-full items-center justify-between px-4 py-3 text-left text-sm text-gray-700 hover:bg-blue-50"
                                    data-label="{{ $employeeLabel }}"
                                    data-user-id="{{ $employee->id }}"
                                >
                                    <span>
                                        {{ $employee->fname }} {{ $employee->lname }}
                                        <span class="text-xs text-gray-500">({{ $employee->employee_code }})</span>
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        @if(($employee->monthly_salary ?? 0) > 0)
                                            Monthly: P{{ number_format($employee->monthly_salary ?? 0, 2) }}
                                        @else
                                            Daily: P{{ number_format($employee->daily_rate ?? 0, 2) }}
                                        @endif
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <input type="hidden" id="hr_user_id" name="user_id" value="{{ old('user_id', request('user_id')) }}">
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

                {{-- ── Earnings ─────────────────────────────────── --}}
                <div class="md:col-span-2">
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-500 mb-3 mt-2 flex items-center gap-2">
                        <i class="fas fa-coins text-green-500"></i> Earnings
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Allowance</label>
                            <input type="number" step="0.01" min="0" name="total_allowance" value="{{ request('total_allowance', 0) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Incentives</label>
                            <input type="number" step="0.01" min="0" name="incentive_amount" value="{{ request('incentive_amount', 0) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                </div>

                {{-- ── Deductions ───────────────────────────────── --}}
                <div class="md:col-span-2">
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-500 mb-3 mt-2 flex items-center gap-2">
                        <i class="fas fa-minus-circle text-red-500"></i> Deductions <span class="text-gray-400 font-normal normal-case tracking-normal">(SSS &amp; PhilHealth are auto-calculated)</span>
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pag-ibig Contribution/Loan</label>
                            <input type="number" step="0.01" min="0" name="pagibig_deduction" value="{{ request('pagibig_deduction', 0) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cellphone Loan</label>
                            <input type="number" step="0.01" min="0" name="cellphone_loan" value="{{ request('cellphone_loan', 0) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gasul Fund / Cash Advance</label>
                            <input type="number" step="0.01" min="0" name="gasul_fund" value="{{ request('gasul_fund', 0) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Unreturn Budget for Delivery</label>
                            <input type="number" step="0.01" min="0" name="unreturn_budget" value="{{ request('unreturn_budget', 0) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cash Bond</label>
                            <input type="number" step="0.01" min="0" name="cash_bond" value="{{ request('cash_bond', 0) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Others</label>
                            <input type="number" step="0.01" min="0" name="other_deduction" value="{{ request('other_deduction', 0) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>
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
                    <input type="hidden" name="incentive_amount" value="{{ request('incentive_amount', 0) }}">
                    <input type="hidden" name="pagibig_deduction" value="{{ request('pagibig_deduction', 0) }}">
                    <input type="hidden" name="cellphone_loan" value="{{ request('cellphone_loan', 0) }}">
                    <input type="hidden" name="gasul_fund" value="{{ request('gasul_fund', 0) }}">
                    <input type="hidden" name="unreturn_budget" value="{{ request('unreturn_budget', 0) }}">
                    <input type="hidden" name="cash_bond" value="{{ request('cash_bond', 0) }}">
                    <input type="hidden" name="other_deduction" value="{{ request('other_deduction', 0) }}">

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
                    <input type="hidden" name="incentive_amount" value="{{ request('incentive_amount', 0) }}">
                    <input type="hidden" name="pagibig_deduction" value="{{ request('pagibig_deduction', 0) }}">
                    <input type="hidden" name="cellphone_loan" value="{{ request('cellphone_loan', 0) }}">
                    <input type="hidden" name="gasul_fund" value="{{ request('gasul_fund', 0) }}">
                    <input type="hidden" name="unreturn_budget" value="{{ request('unreturn_budget', 0) }}">
                    <input type="hidden" name="cash_bond" value="{{ request('cash_bond', 0) }}">
                    <input type="hidden" name="other_deduction" value="{{ request('other_deduction', 0) }}">
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
const hrEmployeeSearchInput = document.getElementById('hr_employee_search');
const hrEmployeeIdInput = document.getElementById('hr_user_id');
const hrEmployeeSuggestions = document.getElementById('hr_employee_suggestions');
const hrEmployeeSuggestionItems = Array.from(document.querySelectorAll('.hr-employee-suggestion'));

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

function hideHrEmployeeSuggestions() {
    hrEmployeeSuggestions?.classList.add('hidden');
}

function showHrEmployeeSuggestions() {
    if (!hrEmployeeSuggestions) {
        return;
    }

    hrEmployeeSuggestions.classList.remove('hidden');
}

function filterHrEmployeeSuggestions() {
    if (!hrEmployeeSearchInput || !hrEmployeeSuggestions) {
        return;
    }

    const keyword = hrEmployeeSearchInput.value.trim().toLowerCase();
    let visibleCount = 0;

    if (hrEmployeeIdInput) {
        hrEmployeeIdInput.value = '';
    }

    hrEmployeeSuggestionItems.forEach((item) => {
        const label = (item.dataset.label || '').toLowerCase();
        const shouldShow = keyword === '' || label.includes(keyword);
        item.classList.toggle('hidden', !shouldShow);

        if (shouldShow) {
            visibleCount += 1;
        }
    });

    if (visibleCount > 0) {
        showHrEmployeeSuggestions();
    } else {
        hideHrEmployeeSuggestions();
    }
}

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

hrEmployeeSearchInput?.addEventListener('focus', filterHrEmployeeSuggestions);
hrEmployeeSearchInput?.addEventListener('input', filterHrEmployeeSuggestions);

hrEmployeeSuggestionItems.forEach((item) => {
    item.addEventListener('click', function () {
        if (!hrEmployeeSearchInput) {
            return;
        }

        hrEmployeeSearchInput.value = item.dataset.label || '';
        if (hrEmployeeIdInput) {
            hrEmployeeIdInput.value = item.dataset.userId || '';
        }
        hideHrEmployeeSuggestions();
    });
});

document.addEventListener('click', function (event) {
    if (!hrEmployeeSearchInput || !hrEmployeeSuggestions) {
        return;
    }

    if (!hrEmployeeSearchInput.closest('.relative')?.contains(event.target)) {
        hideHrEmployeeSuggestions();
    }
});

updateCheckedDaysCount();
</script>
@endsection
