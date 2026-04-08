@extends('layouts.app')

@section('title', 'Payslip Details')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-6xl mx-auto px-4">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Payslip Details</h1>
                <p class="text-gray-600 mt-2">Employee salary breakdown with SSS, PhilHealth, withholding tax, and other deductions.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('hr.payslips.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
                <a href="{{ route('hr.payslips.pdf', $payslip['payroll']) }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-file-pdf mr-2"></i>Download PDF
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $payslip['employee']->fname }} {{ $payslip['employee']->lname }}</h2>
                    <p class="text-gray-600 mt-1">{{ $payslip['employee']->employee_code }} | {{ $payslip['employee']->position ?? 'N/A' }}</p>
                    <p class="text-gray-600">TIN: {{ $payslip['employee']->tin_no ?: 'N/A' }}</p>
                    <p class="text-gray-600">SSS: {{ $payslip['employee']->sss_no ?: 'N/A' }}</p>
                    <p class="text-gray-600">PhilHealth: {{ $payslip['employee']->philhealth_no ?: 'N/A' }}</p>
                </div>
                <div class="text-sm text-gray-700 space-y-1">
                    <div>Payroll No: <strong>{{ $payslip['payroll']->payroll_no }}</strong></div>
                    <div>Cutoff: <strong>{{ $payslip['payroll']->cutoff_from->format('M d, Y') }} - {{ $payslip['payroll']->cutoff_to->format('M d, Y') }}</strong></div>
                    <div>Status: <strong>{{ $payslip['payroll']->payroll_status }}</strong></div>
                    <div>Created By: <strong>{{ $payslip['payroll']->creator->fname ?? '' }} {{ $payslip['payroll']->creator->lname ?? '' }}</strong></div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Earnings</h2>
                <div class="space-y-3">
                    @foreach($payslip['earnings'] as $earning)
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <span class="text-gray-700">{{ $earning['label'] }}</span>
                            <span class="font-semibold text-gray-900">P{{ number_format($earning['amount'], 2) }}</span>
                        </div>
                    @endforeach
                    <div class="flex items-center justify-between pt-2 text-lg font-bold text-blue-700">
                        <span>Total Earnings</span>
                        <span>P{{ number_format($payslip['payroll']->gross_salary + $payslip['payroll']->total_allowance, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Deductions</h2>
                <div class="space-y-3">
                    @foreach($payslip['deductions'] as $deduction)
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <span class="text-gray-700">{{ $deduction['label'] }}</span>
                            <span class="font-semibold text-red-600">P{{ number_format($deduction['amount'], 2) }}</span>
                        </div>
                    @endforeach
                    <div class="flex items-center justify-between pt-2 text-lg font-bold text-red-600">
                        <span>Total Deductions</span>
                        <span>P{{ number_format($payslip['payroll']->total_deduction, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                <div class="rounded-lg bg-blue-50 px-4 py-4">
                    <div class="text-gray-600">Days Worked</div>
                    <div class="text-2xl font-bold text-blue-700">{{ number_format($payslip['payroll']->total_days_worked, 0) }}</div>
                </div>
                <div class="rounded-lg bg-indigo-50 px-4 py-4">
                    <div class="text-gray-600">Hours Worked</div>
                    <div class="text-2xl font-bold text-indigo-700">{{ number_format($payslip['payroll']->total_hours_worked, 2) }}</div>
                </div>
                <div class="rounded-lg bg-emerald-50 px-4 py-4">
                    <div class="text-gray-600">Compensation Basis</div>
                    <div class="text-2xl font-bold text-emerald-700">{{ $payslip['payroll']->compensation_basis === 'monthly_fixed' ? 'Monthly' : 'Daily' }}</div>
                </div>
                <div class="rounded-lg bg-green-50 px-4 py-4">
                    <div class="text-gray-600">Net Pay</div>
                    <div class="text-2xl font-bold text-green-700">P{{ number_format($payslip['payroll']->net_salary, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Attendance Breakdown</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="bg-gray-100 text-gray-900 font-semibold">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Time In</th>
                            <th class="px-4 py-3">Time Out</th>
                            <th class="px-4 py-3">Hours</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($payslip['attendance_records'] as $record)
                            @php
                                $timeIn = $record->time_in ? $record->time_in->format('h:i A') : ($record->status === 'Present' ? '08:00 AM' : '-');
                                $timeOut = $record->time_out ? $record->time_out->format('h:i A') : ($record->status === 'Present' ? '05:00 PM' : '-');
                                $hours = !is_null($record->total_hours) ? number_format($record->total_hours, 2) : ($record->status === 'Present' ? '8.00' : '0.00');
                            @endphp
                            <tr>
                                <td class="px-4 py-3">{{ $record->date->format('M d, Y') }}</td>
                                <td class="px-4 py-3">{{ $record->status }}</td>
                                <td class="px-4 py-3">{{ $timeIn }}</td>
                                <td class="px-4 py-3">{{ $timeOut }}</td>
                                <td class="px-4 py-3">{{ $hours }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">No attendance records found for this payslip cutoff.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
