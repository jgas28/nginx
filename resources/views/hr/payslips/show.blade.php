@extends('layouts.app')

@section('title', 'Payslip - ' . $payslip['payroll']->payroll_no)

@section('content')
@php
    $emp        = $payslip['employee'];
    $pr         = $payslip['payroll'];
    $earnings   = $payslip['earnings'];
    $deductions = $payslip['deductions'];

    $totalEarnings   = collect($earnings)->sum('amount');
    $totalDeductions = (float) $pr->total_deduction;
    $netPay          = (float) $pr->net_salary;
    $cutoffLabel     = \Carbon\Carbon::parse($pr->cutoff_from)->format('M d') . '–' . \Carbon\Carbon::parse($pr->cutoff_to)->format('d, Y');
    $fullName        = trim(($emp->fname ?? '') . ' ' . ($emp->lname ?? ''));
    $maxRows         = max(count($earnings), count($deductions));
@endphp

<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-4xl mx-auto px-4">

        {{-- Actions --}}
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Payslip</h1>
                <p class="text-gray-500 text-sm mt-1">{{ $pr->payroll_no }} · {{ strtoupper($cutoffLabel) }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('hr.payslips.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-arrow-left mr-1"></i>Back
                </a>
                <a href="{{ route('hr.payslips.pdf', $pr) }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-file-pdf mr-1"></i>Download PDF
                </a>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════ --}}
        {{-- SALARY ACKNOWLEDGEMENT                        --}}
        {{-- ══════════════════════════════════════════════ --}}
        <div class="bg-white rounded-xl shadow-lg p-8 mb-6 print:shadow-none">
            <h2 class="text-center text-xl font-bold uppercase tracking-widest mb-6">Salary Acknowledgement</h2>

            <p class="text-base font-semibold text-gray-900">{{ $fullName }}</p>
            <p class="text-sm text-gray-700 mt-1">
                for the period covering <strong>{{ strtoupper($cutoffLabel) }}</strong>.&nbsp;&nbsp;&nbsp;&nbsp;The said amount was received on
            </p>
            <div class="flex items-baseline gap-8 mt-2 mb-4">
                <span class="font-bold text-gray-900">{{ now()->format('M d, Y') }}</span>
                <span class="font-bold text-gray-900 text-lg">₱{{ number_format($netPay, 2) }}</span>
            </div>
            <p class="text-sm text-blue-800 mb-6">This receipt confirms that I have received the salary in full for the mentioned period.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-3 gap-x-8 text-sm">
                <div>
                    <p class="font-semibold text-gray-700 mb-2">Received by:</p>
                    @foreach(['Name', 'Ref No.', 'Signature', 'Position', 'Date'] as $field)
                    <div class="flex items-end gap-2 mb-2">
                        <span class="text-gray-600 w-20 shrink-0">{{ $field }}:</span>
                        <span class="flex-1 border-b border-gray-400">&nbsp;</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════ --}}
        {{-- PAYSLIP                                       --}}
        {{-- ══════════════════════════════════════════════ --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">

            {{-- Payslip header --}}
            <div class="border-b-2 border-gray-800 text-center py-3 px-4">
                <p class="font-bold text-base">Payslip</p>
                <p class="font-bold text-sm">For the Period of {{ strtoupper($cutoffLabel) }}</p>
            </div>

            {{-- Employee info --}}
            <div class="grid grid-cols-2 divide-x divide-gray-300 border-b border-gray-300 text-sm">
                <div class="flex divide-x divide-gray-300">
                    <span class="px-3 py-2 font-bold bg-gray-50 w-44 shrink-0">Payslip for the Period:</span>
                    <span class="px-3 py-2 flex-1">{{ strtoupper($cutoffLabel) }}</span>
                </div>
                <div class="flex divide-x divide-gray-300">
                    <span class="px-3 py-2 font-bold bg-gray-50 w-36 shrink-0">Employee name:</span>
                    <span class="px-3 py-2 flex-1">{{ $fullName }}</span>
                </div>
            </div>
            <div class="grid grid-cols-2 divide-x divide-gray-300 border-b border-gray-300 text-sm">
                <div class="flex divide-x divide-gray-300">
                    <span class="px-3 py-2 font-bold bg-gray-50 w-44 shrink-0">Designation:</span>
                    <span class="px-3 py-2 flex-1">{{ $emp->position ?? 'N/A' }}</span>
                </div>
                <div class="flex divide-x divide-gray-300">
                    <span class="px-3 py-2 font-bold bg-gray-50 w-36 shrink-0">Employee ID No.:</span>
                    <span class="px-3 py-2 flex-1">{{ $emp->employee_code ?? 'N/A' }}</span>
                </div>
            </div>

            {{-- Earnings / Deductions table --}}
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="border-b border-gray-300 bg-gray-50">
                        <th class="px-4 py-2 text-center border-r border-gray-300 w-1/4">Earnings</th>
                        <th class="px-4 py-2 text-center border-r border-gray-300 w-1/6">Amount</th>
                        <th class="px-4 py-2 text-center border-r border-gray-300 w-2/5">Deductions</th>
                        <th class="px-4 py-2 text-center w-1/6">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @for($i = 0; $i < $maxRows; $i++)
                        @php
                            $e = $earnings[$i]  ?? null;
                            $d = $deductions[$i] ?? null;
                        @endphp
                        <tr class="border-b border-gray-100 hover:bg-gray-50/50">
                            <td class="px-4 py-2 border-r border-gray-200 text-gray-700">{{ $e ? $e['label'] . ':' : '' }}</td>
                            <td class="px-4 py-2 border-r border-gray-200 text-right font-medium text-gray-800">
                                {{ ($e && $e['amount'] > 0) ? '₱'.number_format($e['amount'], 2) : '' }}
                            </td>
                            <td class="px-4 py-2 border-r border-gray-200 text-gray-700">{{ $d ? $d['label'] . ':' : '' }}</td>
                            <td class="px-4 py-2 text-right font-medium text-gray-800">
                                {{ ($d && $d['amount'] > 0) ? '₱'.number_format($d['amount'], 2) : '' }}
                            </td>
                        </tr>
                    @endfor

                    {{-- Total row --}}
                    <tr class="border-t-2 border-gray-400 bg-gray-50 font-bold">
                        <td class="px-4 py-2.5 border-r border-gray-300">Total Earnings:</td>
                        <td class="px-4 py-2.5 border-r border-gray-300 text-right text-blue-700">₱{{ number_format($totalEarnings, 2) }}</td>
                        <td class="px-4 py-2.5 border-r border-gray-300 text-right">Total Deductions:</td>
                        <td class="px-4 py-2.5 text-right text-red-600">₱{{ number_format($totalDeductions, 2) }}</td>
                    </tr>
                    {{-- Net Pay --}}
                    <tr class="font-bold bg-gray-50">
                        <td class="px-4 py-2.5 border-r border-gray-300 border-t border-gray-200" colspan="2"></td>
                        <td class="px-4 py-2.5 border-r border-gray-300 border-t border-gray-200 text-right text-red-700">Net Pay:</td>
                        <td class="px-4 py-2.5 border-t border-gray-200 text-right text-red-700 text-base">₱{{ number_format($netPay, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            {{-- Net pay amount line --}}
            <div class="flex justify-end px-6 py-3 border-t border-gray-200 bg-gray-50">
                <div class="text-right">
                    <span class="text-xs text-gray-500 mr-3">Amount</span>
                    <span class="font-bold text-gray-900 border-b-2 border-gray-700 px-4">₱{{ number_format($netPay, 2) }}</span>
                </div>
            </div>

            {{-- Salary Paid --}}
            <div class="flex items-start gap-6 px-6 py-4 border-t border-gray-200 text-sm">
                <span class="font-bold text-gray-800 w-28 shrink-0 pt-1">Salary Paid</span>
                <div class="flex flex-col gap-2">
                    <div class="flex items-center gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <span class="inline-block w-4 h-4 border border-gray-500 rounded-sm"></span> CASH
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <span class="inline-block w-4 h-4 border border-gray-500 rounded-sm"></span> Bank Transfer
                        </label>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <span class="inline-block w-4 h-4 border border-gray-500 rounded-sm"></span>
                        Others; pls specify <span class="border-b border-gray-400 w-40 inline-block">&nbsp;</span>
                    </label>
                </div>
            </div>

            {{-- Signatures --}}
            <div class="grid grid-cols-2 divide-x divide-gray-300 border-t border-gray-300">
                <div class="px-8 py-6 text-center text-sm">
                    <p class="font-bold text-gray-800">Employer Signature</p>
                    <div class="border-t border-gray-600 mt-8 mx-8 pt-1"></div>
                </div>
                <div class="px-8 py-6 text-center text-sm">
                    <p class="font-bold text-gray-800">Employee Signature</p>
                    <div class="border-t border-gray-600 mt-8 mx-8 pt-1"></div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="border-t border-gray-300 px-6 py-3 bg-gray-50 text-center text-xs text-gray-700">
                Full details of your pay for this covered period are given above. Please check carefully and any
                questions concerning the accuracy of this statement should be taken up with the office
            </div>
        </div>

    </div>
</div>
@endsection
