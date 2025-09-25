@extends('layouts.app')

@section('content')
<div class="container mx-auto p-1">
    <h2 class="text-2xl font-bold mb-4">List of SOAs</h2>
    
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <table class="min-w-full bg-white border border-gray-300 shadow-md rounded-lg">
        <thead>
            <tr>
                <th class="px-4 py-2 border">SOA Number</th>
                <th class="px-4 py-2 border">SOA Type</th>
                <th class="px-4 py-2 border">Billed To</th>
                <th class="px-4 py-2 border">Billing Address</th>
                <th class="px-4 py-2 border">Total Price</th>
                <th class="px-4 py-2 border">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($soas as $soa)
            <tr>
                <td class="px-4 py-2 border">{{ $soa->soa_number }}</td>
                <td class="px-4 py-2 border">
                    @if($soa->billing_type == 1)
                        Regular
                    @elseif($soa->billing_type == 2)
                        Rates
                    @else
                        N/A
                    @endif
                </td>
                <td class="px-4 py-2 border">{{ $soa->billed_to }}</td>
                <td class="px-4 py-2 border">{{ $soa->billing_address }}</td>
                <td class="px-4 py-2 border">{{ number_format($soa->total_price, 2) }}</td>
                <td class="px-4 py-2 border">
                    <!-- Print Icon -->
                    <a href="{{ route('soa.print', ['soa' => $soa->id]) }}" target="_blank" class="text-blue-600 hover:text-blue-800 mr-4">
                        <i class="fas fa-print fa-lg"></i> <!-- Print Icon -->
                    </a>

                    <!-- Download Icon -->
                    <a href="" class="text-green-600 hover:text-blue-800 mr-4">
                        <i class="fas fa-download fa-lg"></i> <!-- Download Icon -->
                    </a>

                    <!-- Edit Icon -->
                    <a href="" class="text-yellow-600 hover:text-yellow-800">
                        <i class="fas fa-edit fa-lg"></i> <!-- Edit Icon -->
                    </a>
                </td>

            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- <div class="mt-6 text-left">
        <a href="" class="bg-green-600 text-white px-6 py-2 rounded-lg shadow-md hover:bg-green-700 transition-colors duration-300">
            Download SOAs as Excel
        </a>
    </div> -->
</div>
@endsection
