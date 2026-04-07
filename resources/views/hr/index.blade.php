@extends('layouts.app')

@section('title', 'Human Resource - Payroll Management')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Human Resource - Payroll</h1>
            <p class="text-gray-600 mt-2">Manage employee payroll, daily rates, and cutoff-based salary computation.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Total Payrolls</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalPayrolls }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-4">
                        <i class="fas fa-file-invoice-dollar text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Pending</p>
                        <p class="text-3xl font-bold text-orange-600 mt-2">{{ $pendingPayrolls }}</p>
                    </div>
                    <div class="bg-orange-100 rounded-full p-4">
                        <i class="fas fa-hourglass-half text-orange-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Approved</p>
                        <p class="text-3xl font-bold text-green-600 mt-2">{{ $approvedPayrolls }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-4">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Paid</p>
                        <p class="text-3xl font-bold text-blue-600 mt-2">{{ $paidPayrolls }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-4">
                        <i class="fas fa-money-bill-wave text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm font-medium">Total Gross Salary</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">P{{ number_format($totalGrossSalary, 2) }}</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm font-medium">Total Allowances</p>
                <p class="text-2xl font-bold text-green-600 mt-2">P{{ number_format($totalAllowances, 2) }}</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm font-medium">Total Deductions</p>
                <p class="text-2xl font-bold text-red-600 mt-2">P{{ number_format($totalDeductions, 2) }}</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm font-medium">Total Net Salary</p>
                <p class="text-2xl font-bold text-blue-600 mt-2">P{{ number_format($totalNetSalary, 2) }}</p>
            </div>
        </div>

        <div id="compensation-setup-container">
            @include('hr.partials.compensation-setup')
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Filter & Search</h2>
            
            <form method="GET" action="{{ route('hr.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Employee Name</label>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or code..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Status</option>
                            <option value="Pending" {{ $status === 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Approved" {{ $status === 'Approved' ? 'selected' : '' }}>Approved</option>
                            <option value="Paid" {{ $status === 'Paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                        <select name="month" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Months</option>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromFormat('m', $i)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium flex-1">
                            <i class="fas fa-search mr-2"></i>Search
                        </button>
                        <a href="{{ route('hr.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-medium">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                </div>
            </form>

            <div class="mt-4">
                <a href="{{ route('hr.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium">
                    <i class="fas fa-plus mr-2"></i>Create Payroll
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
                            <th class="px-6 py-3">Cutoff Period</th>
                            <th class="px-6 py-3">Days Worked</th>
                            <th class="px-6 py-3">Basis</th>
                            <th class="px-6 py-3">Gross Salary</th>
                            <th class="px-6 py-3">SSS</th>
                            <th class="px-6 py-3">PhilHealth</th>
                            <th class="px-6 py-3">Tax</th>
                            <th class="px-6 py-3">Allowance</th>
                            <th class="px-6 py-3">Deduction</th>
                            <th class="px-6 py-3">Net Salary</th>
                            <th class="px-6 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($payrollRecords as $payroll)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium">{{ $payroll->payroll_no }}</td>
                                <td class="px-6 py-4">{{ $payroll->user->fname }} {{ $payroll->user->lname }}</td>
                                <td class="px-6 py-4">{{ $payroll->cutoff_from->format('M d') }} - {{ $payroll->cutoff_to->format('M d, Y') }}</td>
                                <td class="px-6 py-4">{{ number_format($payroll->total_days_worked, 1) }}</td>
                                <td class="px-6 py-4">{{ $payroll->compensation_basis === 'monthly_fixed' ? 'Monthly' : 'Daily' }}</td>
                                <td class="px-6 py-4 font-medium">P{{ number_format($payroll->gross_salary, 2) }}</td>
                                <td class="px-6 py-4 text-red-600">P{{ number_format($payroll->sss_deduction ?? 0, 2) }}</td>
                                <td class="px-6 py-4 text-red-600">P{{ number_format($payroll->philhealth_deduction ?? 0, 2) }}</td>
                                <td class="px-6 py-4 text-red-600">P{{ number_format($payroll->tax_deduction ?? 0, 2) }}</td>
                                <td class="px-6 py-4 text-green-600">P{{ number_format($payroll->total_allowance, 2) }}</td>
                                <td class="px-6 py-4 text-red-600">P{{ number_format($payroll->total_deduction, 2) }}</td>
                                <td class="px-6 py-4 font-bold text-blue-600">P{{ number_format($payroll->net_salary, 2) }}</td>
                                <td class="px-6 py-4">
                                    @if($payroll->payroll_status === 'Pending')
                                        <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-xs font-semibold">Pending</span>
                                    @elseif($payroll->payroll_status === 'Approved')
                                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Approved</span>
                                    @elseif($payroll->payroll_status === 'Paid')
                                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">Paid</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-3xl mb-2"></i>
                                    <p>No payroll records found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $payrollRecords->links() }}
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('compensation-setup-container');

    if (!container) {
        return;
    }

    async function loadCompensationSection(url) {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html',
            },
        });

        if (!response.ok) {
            throw new Error('Unable to load compensation setup.');
        }

        const html = await response.text();
        container.innerHTML = html;
    }

    container.addEventListener('submit', async function (event) {
        const form = event.target.closest('[data-compensation-search-form]');

        if (!form) {
            return;
        }

        event.preventDefault();

        const url = new URL(form.action);
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);

        url.search = params.toString();

        try {
            await loadCompensationSection(url.toString());
        } catch (error) {
            window.location.href = url.toString();
        }
    });

    container.addEventListener('click', async function (event) {
        const link = event.target.closest('[data-compensation-pagination] a, [data-compensation-link]');

        if (!link) {
            return;
        }

        event.preventDefault();

        try {
            await loadCompensationSection(link.href);
        } catch (error) {
            window.location.href = link.href;
        }
    });
});
</script>
@endsection
