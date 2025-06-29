@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        {{-- Loop through the approvers --}}
        @foreach ($approvers as $approver)
            <div class="bg-white shadow-md rounded-lg border border-blue-300">
                <div class="bg-blue-500 text-white px-4 py-2 rounded-t-lg">
                    <h2 class="text-lg font-semibold">{{ $approver->name }}</h2>
                </div>
                <div class="px-6 py-4 space-y-2">
                    {{-- Running Total --}}
                    <div>
                        <p class="text-gray-600 text-sm">Running Total</p>
                        <p class="text-xl font-bold text-green-600">
                            ₱ {{ number_format($runningTotalsByApprover[$approver->id] ?? 0, 2) }}
                        </p>
                    </div>

                    {{-- Uncollected Amount --}}
                    <div>
                        <p class="text-gray-600 text-sm">Uncollected Amount</p>
                        <p class="text-xl font-bold text-red-500">
                            ₱ {{ number_format($uncollectedByApprover[$approver->id] ?? 0, 2) }}
                        </p>
                    </div>

                    {{-- Total Collected --}}
                    <div>
                        <p class="text-gray-600 text-sm">Total Collected</p>
                        <p class="text-xl font-bold text-blue-600">
                            ₱ {{ number_format(($runningTotalsByApprover[$approver->id] ?? 0) - ($uncollectedByApprover[$approver->id] ?? 0), 2) }}
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Total Delivery Rate --}}
    <div class="bg-white shadow-md rounded-lg border border-teal-300 mt-6">
        <div class="bg-teal-500 text-white px-4 py-2 rounded-t-lg">
            <h2 class="text-lg font-semibold">Total Delivery Rate</h2>
        </div>
        <div class="px-6 py-4 space-y-2">
            <p class="text-gray-600 text-sm">Total Delivery Rate Across All Requests</p>
            <p class="text-xl font-bold text-teal-600">
                ₱ {{ number_format($totalDeliveryRates, 2) }}
            </p>
        </div>
    </div>

    {{-- Total Accessorial Rate --}}
    <div class="bg-white shadow-md rounded-lg border border-purple-300 mt-6">
        <div class="bg-purple-500 text-white px-4 py-2 rounded-t-lg">
            <h2 class="text-lg font-semibold">Total Accessorial Rate</h2>
        </div>
        <div class="px-6 py-4 space-y-2">
            <p class="text-gray-600 text-sm">Total Accessorial Rate Across All Line Items</p>
            <p class="text-xl font-bold text-purple-600">
                ₱ {{ number_format($totalAccessorialRates, 2) }}
            </p>
        </div>
    </div>
@endsection
