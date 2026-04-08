@extends('layouts.app')

@section('title', 'Payslips')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Payslips</h1>
                <p class="text-gray-600 mt-2">View employee payslips with detailed Philippine payroll deductions.</p>
            </div>
            <a href="{{ route('hr.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>Back to HR
            </a>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <form method="GET" action="{{ route('hr.payslips.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4" id="payslip-filter-form">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                    <div class="relative">
                        <input
                            type="text"
                            id="payslip_employee_search"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Search employee name, code, or payroll no..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                            autocomplete="off"
                        >
                        <div
                            id="payslip_employee_suggestions"
                            class="absolute z-20 mt-1 hidden max-h-64 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg"
                        >
                            @foreach($employees as $employee)
                                @php
                                    $employeeLabel = trim($employee->fname . ' ' . $employee->lname) . ' - ' . $employee->employee_code;
                                @endphp
                                <button
                                    type="button"
                                    class="payslip-employee-suggestion flex w-full items-center justify-between px-4 py-3 text-left text-sm text-gray-700 hover:bg-blue-50"
                                    data-label="{{ $employeeLabel }}"
                                    data-user-id="{{ $employee->id }}"
                                >
                                    <span>{{ $employee->fname }} {{ $employee->lname }}</span>
                                    <span class="text-xs text-gray-500">{{ $employee->employee_code }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <input type="hidden" id="payslip_employee_id" name="user_id" value="{{ $employeeId }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                    <input type="month" name="month" value="{{ $month }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">All Status</option>
                        <option value="Pending" {{ $status === 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Approved" {{ $status === 'Approved' ? 'selected' : '' }}>Approved</option>
                        <option value="Paid" {{ $status === 'Paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium flex-1">
                        <i class="fas fa-search mr-2"></i>Search
                    </button>
                    <a href="{{ route('hr.payslips.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-medium">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>

            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('hr.payslips.export.excel', request()->only(['search', 'month', 'status', 'user_id'])) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg font-medium">
                    <i class="fas fa-file-excel mr-2"></i>Export Excel
                </a>
                <a href="{{ route('hr.payslips.export.pdf', request()->only(['search', 'month', 'status', 'user_id'])) }}" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium">
                    <i class="fas fa-file-pdf mr-2"></i>Download PDF List
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="bg-gray-100 text-gray-900 font-semibold">
                        <tr>
                            <th class="px-6 py-3">Payroll No.</th>
                            <th class="px-6 py-3">Employee</th>
                            <th class="px-6 py-3">Cutoff</th>
                            <th class="px-6 py-3">Gross</th>
                            <th class="px-6 py-3">Total Deductions</th>
                            <th class="px-6 py-3">Net Salary</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($payslips as $payslip)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium">{{ $payslip->payroll_no }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $payslip->user->fname }} {{ $payslip->user->lname }}</div>
                                    <div class="text-xs text-gray-500">{{ $payslip->user->employee_code }}</div>
                                </td>
                                <td class="px-6 py-4">{{ $payslip->cutoff_from->format('M d, Y') }} - {{ $payslip->cutoff_to->format('M d, Y') }}</td>
                                <td class="px-6 py-4">P{{ number_format($payslip->gross_salary, 2) }}</td>
                                <td class="px-6 py-4 text-red-600">P{{ number_format($payslip->total_deduction, 2) }}</td>
                                <td class="px-6 py-4 font-bold text-blue-600">P{{ number_format($payslip->net_salary, 2) }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                                        {{ $payslip->payroll_status === 'Pending' ? 'bg-orange-100 text-orange-800' : '' }}
                                        {{ $payslip->payroll_status === 'Approved' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $payslip->payroll_status === 'Paid' ? 'bg-blue-100 text-blue-800' : '' }}">
                                        {{ $payslip->payroll_status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2">
                                        <a href="{{ route('hr.payslips.show', $payslip) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-xs font-medium">View Payslip</a>
                                        <a href="{{ route('hr.payslips.pdf', $payslip) }}" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg text-xs font-medium">PDF</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-gray-500">
                                    @if($employeeId)
                                        No payslips found for the selected employee and filters.
                                    @else
                                        No payslips found for the selected filters.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $payslips->links() }}
            </div>
        </div>
    </div>
</div>

<script>
const payslipFilterForm = document.getElementById('payslip-filter-form');
const payslipEmployeeSearchInput = document.getElementById('payslip_employee_search');
const payslipEmployeeIdInput = document.getElementById('payslip_employee_id');
const payslipEmployeeSuggestions = document.getElementById('payslip_employee_suggestions');
const payslipEmployeeSuggestionItems = Array.from(document.querySelectorAll('.payslip-employee-suggestion'));

function hidePayslipEmployeeSuggestions() {
    payslipEmployeeSuggestions?.classList.add('hidden');
}

function showPayslipEmployeeSuggestions() {
    if (!payslipEmployeeSuggestions) {
        return;
    }

    payslipEmployeeSuggestions.classList.remove('hidden');
}

function filterPayslipEmployeeSuggestions() {
    if (!payslipEmployeeSearchInput || !payslipEmployeeSuggestions) {
        return;
    }

    const keyword = payslipEmployeeSearchInput.value.trim().toLowerCase();
    let visibleCount = 0;

    if (payslipEmployeeIdInput) {
        payslipEmployeeIdInput.value = '';
    }

    payslipEmployeeSuggestionItems.forEach((item) => {
        const label = (item.dataset.label || '').toLowerCase();
        const shouldShow = keyword === '' || label.includes(keyword);
        item.classList.toggle('hidden', !shouldShow);

        if (shouldShow) {
            visibleCount += 1;
        }
    });

    if (visibleCount > 0) {
        showPayslipEmployeeSuggestions();
    } else {
        hidePayslipEmployeeSuggestions();
    }
}

payslipEmployeeSearchInput?.addEventListener('focus', filterPayslipEmployeeSuggestions);
payslipEmployeeSearchInput?.addEventListener('input', filterPayslipEmployeeSuggestions);

payslipEmployeeSuggestionItems.forEach((item) => {
    item.addEventListener('click', function () {
        if (!payslipEmployeeSearchInput) {
            return;
        }

        payslipEmployeeSearchInput.value = item.dataset.label || '';
        if (payslipEmployeeIdInput) {
            payslipEmployeeIdInput.value = item.dataset.userId || '';
        }
        hidePayslipEmployeeSuggestions();
        payslipFilterForm?.submit();
    });
});

document.addEventListener('click', function (event) {
    if (!payslipEmployeeSearchInput || !payslipEmployeeSuggestions) {
        return;
    }

    if (!payslipEmployeeSearchInput.closest('.relative')?.contains(event.target)) {
        hidePayslipEmployeeSuggestions();
    }
});
</script>
@endsection
