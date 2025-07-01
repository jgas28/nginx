@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
        {{-- Loop through the approvers --}}
        @foreach ($approvers as $approver)
            <div class="bg-white shadow-lg rounded-xl border border-blue-200 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-blue-700 text-white px-6 py-4">
                    <h2 class="text-xl font-semibold">{{ $approver->name }}</h2>
                </div>
                <div class="px-6 py-4 space-y-4">
                    {{-- Running Total --}}
                    <div>
                        <p class="text-2xl font-semibold text-green-600">
                            ₱ {{ number_format($runningTotalsByApprover[$approver->id] ?? 0, 2) }}
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex flex-wrap gap-6 justify-between mt-8">
        {{-- Profit Card --}}
        <div class="bg-white shadow-lg rounded-xl border border-green-200 w-full sm:w-1/2 lg:w-1/4">
            <div class="bg-gradient-to-r from-green-500 to-green-700 text-white px-6 py-4">
                <h2 class="text-xl font-semibold">Total Profit</h2>
            </div>
            <div class="px-6 py-4 space-y-4">
                {{-- Total Profit (Delivery Rate - Accessorial Rate) --}}
                <div class="flex justify-between">
                    <p class="text-xl font-semibold text-green-600">
                        ₱ {{ number_format($totalDeliveryRates - $totalAccessorialRates, 2) }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Total Admin/RPM --}}
        <div class="bg-white shadow-lg rounded-xl border border-yellow-200 w-full sm:w-1/2 lg:w-1/4">
            <div class="bg-gradient-to-r from-yellow-500 to-yellow-700 text-white px-6 py-4">
                <h2 class="text-xl font-semibold">Admin Expenses</h2>
            </div>
            <div class="px-6 py-4 space-y-4">
                <p class="text-xl font-semibold text-yellow-600">
                    ₱ {{ number_format($totals->admin_rpm_total ?? 0, 2) }}
                </p>
            </div>
        </div>

        {{-- Total Operational Expenses --}}
        <div class="bg-white shadow-lg rounded-xl border border-orange-200 w-full sm:w-1/2 lg:w-1/4">
            <div class="bg-gradient-to-r from-orange-500 to-orange-700 text-white px-6 py-4">
                <h2 class="text-xl font-semibold">Operational Expenses</h2>
            </div>
            <div class="px-6 py-4 space-y-4">
                <p class="text-xl font-semibold text-orange-600">
                    ₱ {{ number_format($totals->operation_total ?? 0, 2) }}
                </p>
            </div>
        </div>
    </div>
@endsection
