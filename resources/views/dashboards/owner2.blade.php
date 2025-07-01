@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <!-- Filter Form -->
    <form method="GET" action="{{ route('dashboard') }}" class="mb-4 flex justify-between items-center">
        <!-- Month Filter -->
        <div class="flex items-center space-x-4">
            <label for="month" class="font-semibold text-lg">Select Month</label>
            <input type="month" id="month" name="month" value="{{ request('month', now()->format('Y-m')) }}" class="p-2 border rounded">
        </div>

        <!-- Date Range Filter -->
        <div class="flex items-center space-x-4">
            <label for="start_date" class="font-semibold text-lg">Start Date</label>
            <input type="date" id="start_date" name="start_date" value="{{ request('start_date', now()->startOfMonth()->toDateString()) }}" class="p-2 border rounded">
            
            <label for="end_date" class="font-semibold text-lg">End Date</label>
            <input type="date" id="end_date" name="end_date" value="{{ request('end_date', now()->toDateString()) }}" class="p-2 border rounded">
        </div>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Filter</button>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
        {{-- Loop through the approvers --}}
        @foreach ($approvers as $approver)
            <div class="bg-white shadow-lg rounded-xl border border-gray-200 overflow-hidden flex flex-col">
                <div class="bg-gradient-to-r from-blue-500 to-blue-700 text-white px-6 py-4">
                    <h2 class="text-xl font-semibold">{{ $approver->name }}</h2>
                </div>
                <div class="px-6 py-4 flex-grow">
                    {{-- Running Total --}}
                    <div class="mb-4">
                        <p class="text-2xl font-semibold text-green-600">
                            ₱ {{ number_format($runningTotalsByApprover[$approver->id] ?? 0, 2) }}
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
        {{-- Profit Card --}}
        <div class="bg-white shadow-lg rounded-xl border border-gray-200 overflow-hidden flex flex-col">
            <div class="bg-gradient-to-r from-green-500 to-green-700 text-white px-6 py-4">
                <h2 class="text-xl font-semibold">Total Profit</h2>
            </div>
            <div class="px-6 py-4 flex-grow">
                {{-- Total Profit (Delivery Rate - Accessorial Rate) --}}
                <div class="flex justify-between">
                    <p class="text-xl font-semibold text-green-600">
                        ₱ {{ number_format($totalDeliveryRates + $totalAccessorialRates, 2) }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Total Admin/RPM --}}
        <div class="bg-white shadow-lg rounded-xl border border-gray-200 overflow-hidden flex flex-col">
            <div class="bg-gradient-to-r from-yellow-500 to-yellow-700 text-white px-6 py-4">
                <h2 class="text-xl font-semibold">Admin Expenses</h2>
            </div>
            <div class="px-6 py-4 flex-grow">
                <p class="text-xl font-semibold text-yellow-600">
                    ₱ {{ number_format($totals->admin_rpm_total ?? 0, 2) }}
                </p>
            </div>
        </div>

        {{-- Total Operational Expenses --}}
        <div class="bg-white shadow-lg rounded-xl border border-gray-200 overflow-hidden flex flex-col">
            <div class="bg-gradient-to-r from-orange-500 to-orange-700 text-white px-6 py-4">
                <h2 class="text-xl font-semibold">Operational Expenses</h2>
            </div>
            <div class="px-6 py-4 flex-grow">
                <p class="text-xl font-semibold text-orange-600">
                    ₱ {{ number_format($totals->operation_total ?? 0, 2) }}
                </p>
            </div>
        </div>

        <div class="bg-white shadow-lg rounded-xl border border-gray-200 overflow-hidden flex flex-col">
            <div class="bg-gradient-to-r from-blue-500 to-blue-700 text-white px-6 py-4">
                <h2 class="text-xl font-semibold">Pending Deliveries</h2>
            </div>
            <div class="px-6 py-4 flex-grow">
                <p class="text-2xl font-semibold text-blue-600">
                    {{ $totalPendingDeliveries }}
                </p>
            </div>
        </div>

        <div class="bg-white shadow-lg rounded-xl border border-gray-200 overflow-hidden flex flex-col">
            <div class="bg-gradient-to-r from-green-500 to-green-700 text-white px-6 py-4">
                <h2 class="text-xl font-semibold">Delivered Deliveries</h2>
            </div>
            <div class="px-6 py-4 flex-grow">
                <p class="text-2xl font-semibold text-green-600">
                    {{ $totalDelivered }}
                </p>
            </div>
        </div>

        <div class="bg-white shadow-lg rounded-xl border border-gray-200 overflow-hidden flex flex-col">
            <div class="bg-gradient-to-r from-orange-500 to-orange-700 text-white px-6 py-4">
                <h2 class="text-xl font-semibold">Truck Allocated Deliveries</h2>
            </div>
            <div class="px-6 py-4 flex-grow">
                <p class="text-2xl font-semibold text-orange-600">
                    {{ $totalTruckAllocated }}
                </p>
            </div>
        </div>
    </div>
@endsection
