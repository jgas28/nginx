@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($approvers as $approver)
            <div class="bg-white shadow-md rounded-lg border border-blue-300">
                <div class="bg-blue-500 text-white px-4 py-2 rounded-t-lg">
                    <h2 class="text-lg font-semibold">{{ $approver->name }}</h2>
                </div>
                <div class="px-6 py-4 space-y-2">
                    <div>
                        <p class="text-gray-600 text-sm">Running Total</p>
                        <p class="text-xl font-bold text-green-600">
                            ₱ {{ number_format($runningTotalsByApprover[$approver->id] ?? 0, 2) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Uncollected Amount</p>
                        <p class="text-xl font-bold text-red-500">
                            ₱ {{ number_format($uncollectedByApprover[$approver->id] ?? 0, 2) }}
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
