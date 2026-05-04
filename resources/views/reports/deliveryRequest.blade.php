@extends('layouts.app')

@section('title', 'Delivery Request Report')

@section('content')

<!-- Filter Form -->
<form method="GET" action="{{ route('reports.dr') }}" class="mb-6" id="filterForm">
    <div class="grid grid-cols-2 gap-4">
        <!-- Include the filter fields here (same as your filter form) -->
        <input type="hidden" name="mtm" value="{{ request('mtm') }}">
        <input type="hidden" name="date_from" value="{{ request('date_from') }}">
        <input type="hidden" name="date_to" value="{{ request('date_to') }}">
        <input type="hidden" name="area" value="{{ request('area') }}">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <input type="hidden" name="customer_id" value="{{ request('customer_id') }}">
        <input type="hidden" name="company_id" value="{{ request('company_id') }}">
        <!-- MTM Filter -->
        <div>
            <label for="mtm" class="block text-sm font-medium text-gray-700">MTM:</label>
            <input type="text" id="mtm" name="mtm" value="{{ old('mtm', request('mtm')) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
        </div>

        <!-- From Date Filter -->
        <div>
            <label for="date_from" class="block text-sm font-medium text-gray-700">From Date:</label>
            <input type="date" id="date_from" name="date_from" value="{{ old('date_from', request('date_from')) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4">
        <!-- To Date Filter -->
        <div>
            <label for="date_to" class="block text-sm font-medium text-gray-700">To Date:</label>
            <input type="date" id="date_to" name="date_to" value="{{ old('date_to', request('date_to')) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
        </div>

        <!-- Delivery Date Filter -->
        <div>
            <label for="delivery_date" class="block text-sm font-medium text-gray-700">Delivery Date:</label>
            <input type="date" id="delivery_date" name="delivery_date" value="{{ old('delivery_date', request('delivery_date')) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4">
        <!-- Area Filter (Dropdown) -->
        <div>
            <label for="area" class="block text-sm font-medium text-gray-700">Area:</label>
            <select id="area" name="area" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
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
            <select id="status" name="status" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                <option value="">Select Status</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->status_name }}" {{ old('status', request('status')) == $status->status_name ? 'selected' : '' }}>
                        {{ $status->status_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Customer Filter (Dropdown) -->
        <div>
            <label for="customer_id" class="block text-sm font-medium text-gray-700">Customer:</label>
            <select id="customer_id" name="customer_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                <option value="">Select Customer</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ old('customer_id', request('customer_id')) == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="company_id" class="block text-sm font-medium text-gray-700">Company:</label>
            <select id="company_id" name="company_id"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                <option value="">Select Company</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}"
                        {{ old('company_id', request('company_id')) == $company->id ? 'selected' : '' }}>
                        {{ $company->company_name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Filter</button>
    <button type="button" id="resetFilters" class="px-4 py-2 bg-gray-400 text-white rounded-md hover:bg-gray-500">Reset Filters</button>
    <!-- Export button as a submit -->
    <button type="submit" formaction="{{ route('reports.export') }}" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Download Excel</button>    
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

<script>
    document.getElementById('resetFilters').addEventListener('click', function() {
        // Clear all the filter fields
        document.getElementById('filterForm').reset();
        
        // Manually reset the URL without query parameters using location.replace()
        // This ensures the page is loaded without any filter parameters
        location.replace("{{ route('reports.dr') }}"); // Redirect to the base URL (without filters)
    });
</script>
@endsection

