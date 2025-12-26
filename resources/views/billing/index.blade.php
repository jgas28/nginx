@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-semibold text-gray-800 mb-6">Created Billings</h1>
        
        @if ($billings->isEmpty())
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4">
                <p class="font-semibold">No billings found.</p>
            </div>
        @else
            <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                <table class="min-w-full table-auto text-sm">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600">
                            <th class="px-4 py-2 text-left">SOA Number</th>
                            <th class="px-4 py-2 text-left">Company</th>
                            <th class="px-4 py-2 text-left">Amount</th>
                            <th class="px-4 py-2 text-left">Billed To</th>
                            <th class="px-4 py-2 text-left">Billing Date</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($billings as $billing)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3">{{ $billing->soa_number }}</td>
                                <td class="px-4 py-3">{{ $billing->company->company_name }}</td> <!-- Assuming you have a relationship to Company -->
                                <td class="px-4 py-3">{{ number_format($billing->total_price, 2) }}</td>
                                <td class="px-4 py-3">{{ $billing->billed_to }}</td>
                                <td class="px-4 py-3">{{ $billing->billing_date }}</td>
                                <td class="px-4 py-3">
                                    @switch($billing->status)
                                        @case(1)
                                            Draft
                                            @break
                                        @case(2)
                                            Submitted
                                            @break
                                        @case(3)
                                            Waiting for Payment
                                            @break
                                        @case(4)
                                            Received Payment
                                            @break
                                        @default
                                            Unknown Status
                                    @endswitch
                                </td>
                                <td class="px-4 py-3 space-x-2">
                                    <a href="#" class="text-blue-600 hover:text-blue-800 focus:outline-none">View</a>
                                    <a href="{{ route('billing.edit', $billing->id) }}" class="text-yellow-600 hover:text-yellow-800 focus:outline-none">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
