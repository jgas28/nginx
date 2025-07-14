@extends('layouts.app')

@section('title', 'Delivery Request Report')

@section('content')

<!-- Filter Form -->
<form method="GET" action="{{ route('reports.dr') }}" class="mb-6">
    <div class="grid grid-cols-2 gap-4">
        <!-- MTM Filter -->
        <div>
            <label for="mtm" class="block text-sm font-medium text-gray-700">MTM:</label>
            <input type="text" id="mtm" name="mtm" value="{{ old('mtm', request('mtm')) }}"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>

        <!-- From Date Filter -->
        <div>
            <label for="date_from" class="block text-sm font-medium text-gray-700">From Date:</label>
            <input type="date" id="date_from" name="date_from" value="{{ old('date_from', request('date_from')) }}"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4">
        <!-- To Date Filter -->
        <div>
            <label for="date_to" class="block text-sm font-medium text-gray-700">To Date:</label>
            <input type="date" id="date_to" name="date_to" value="{{ old('date_to', request('date_to')) }}"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>

        <!-- Delivery Date Filter -->
        <div>
            <label for="delivery_date" class="block text-sm font-medium text-gray-700">Delivery Date:</label>
            <input type="date" id="delivery_date" name="delivery_date" value="{{ old('delivery_date', request('delivery_date')) }}"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4">
        <!-- Area Filter (Dropdown) -->
        <div>
            <label for="area" class="block text-sm font-medium text-gray-700">Area:</label>
            <select id="area" name="area" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <option value="">Select Area</option>
                @foreach($areas as $area)
                    <option value="{{ $area->area_code }}" {{ old('area', request('area')) == $area->area_code ? 'selected' : '' }}>
                        {{ $area->area_code }} - {{ $area->area_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Status Filter (Dropdown) -->
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Status:</label>
            <select id="status" name="status" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <option value="">Select Status</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->status_name }}" {{ old('status', request('status')) == $status->status_name ? 'selected' : '' }}>
                        {{ $status->status_name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Filter</button>

    </form>
    
    <!-- Excel Export Button -->
    <form method="GET" action="{{ route('reports.export') }}" class="mb-6">
        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
            Download Excel
        </button>
    </form>

    <!-- Table -->
    <table class="min-w-full bg-white border border-gray-300 rounded-md shadow-sm">
        <thead>
            <tr class="bg-gray-50">
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">MTM</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Booking Date</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Delivery Date</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Delivery Rate</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Accessorial Rate</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Area</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deliveryRequests as $request)
                <tr class="border-b">
                    <td class="px-6 py-4">{{ $request->mtm }}</td>
                    <td class="px-6 py-4">{{ $request->booking_date }}</td>
                    <td class="px-6 py-4">{{ $request->delivery_date }}</td>
                    <td class="px-6 py-4">{{ $request->delivery_rate }}</td>
                    <td class="px-6 py-4">{{ $request->total_accessorial_rate }}</td>
                    <td class="px-6 py-4">{{ $request->area->area_code }}</td>
                    <td class="px-6 py-4">{{ $request->deliveryStatus->status_name ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endsection
