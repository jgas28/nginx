@extends('layouts.app')

@section('content')
    <!-- <h1>Edit Delivery Request</h1> -->

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('coordinators.split.perform', $deliveryRequest) }}" method="POST">
        @csrf
        @method('POST')

        <div class="border bg-white p-4 rounded shadow-sm space-y-6">
            <!-- Row 1 -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- MTM Number -->
                <div>
                <label for="mtm" class="block text-sm font-medium text-gray-700 mb-1">MTM Number</label>
                <input type="text" name="mtm" id="mtm" required
                    value="{{ old('mtm', $deliveryRequest->mtm) }}"
                    class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('mtm') border-red-500 @enderror">
                @error('mtm')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                </div>

                <!-- Booking Date -->
                <div>
                <label for="booking_date" class="block text-sm font-medium text-gray-700 mb-1">Booking Date</label>
                <input type="date" name="booking_date" id="booking_date" readonly
                    value="{{ old('booking_date', $deliveryRequest->booking_date) }}"
                    class="block w-full rounded border border-gray-300 bg-gray-100 cursor-not-allowed px-3 py-2 focus:outline-none">
                @error('booking_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                </div>

                <!-- Delivery Date -->
                <div>
                <label for="delivery_date" class="block text-sm font-medium text-gray-700 mb-1">Delivery Date</label>
                <input type="date" name="delivery_date" id="delivery_date" readonly
                    value="{{ old('delivery_date', $deliveryRequest->delivery_date) }}"
                    class="block w-full rounded border border-gray-300 bg-gray-100 cursor-not-allowed px-3 py-2 focus:outline-none">
                @error('delivery_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                </div>
            </div>

            <!-- Row 2 -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <!-- Delivery Rate (2 cols) -->
                <div class="md:col-span-2">
                <label for="delivery_rate" class="block text-sm font-medium text-gray-700 mb-1">Delivery Rate</label>
                <input type="text" name="delivery_rate" id="delivery_rate" readonly
                    value="{{ old('delivery_rate', $deliveryRequest->delivery_rate) }}"
                    class="block w-full rounded border border-gray-300 bg-gray-100 cursor-not-allowed px-3 py-2 focus:outline-none">
                @error('delivery_rate')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                </div>

                <!-- Truck Type (2 cols) -->
                <div class="md:col-span-2">
                <label for="truck_type_id" class="block text-sm font-medium text-gray-700 mb-1">Truck Type</label>
                <select name="truck_type_id" id="truck_type_id" readonly
                    class="block w-full rounded border border-gray-300 bg-gray-100 cursor-not-allowed px-3 py-2 focus:outline-none">
                    <option value="">Select Truck Type</option>
                    @foreach($truckTypes as $truckType)
                    <option value="{{ $truckType->id }}" {{ $truckType->id == old('truck_type_id', $deliveryRequest->truck_type_id) ? 'selected' : '' }}>{{ $truckType->truck_code }}</option>
                    @endforeach
                </select>
                @error('region_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                </div>

                <!-- Company (2 cols) -->
                <div class="md:col-span-2">
                <label for="company_id" class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                <select name="company_id" id="company_id" readonly
                    class="block w-full rounded border border-gray-300 bg-gray-100 cursor-not-allowed px-3 py-2 focus:outline-none">
                    <option value="">Select Company</option>
                    @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ $company->id == old('company_id', $deliveryRequest->company_id) ? 'selected' : '' }}>{{ $company->company_name }}</option>
                    @endforeach
                </select>
                @error('company_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                </div>

                <!-- Expense Type (2 cols) -->
                <div class="md:col-span-2">
                <label for="expense_type_id" class="block text-sm font-medium text-gray-700 mb-1">Expense Type</label>
                <select name="expense_type_id" id="expense_type_id" readonly
                    class="block w-full rounded border border-gray-300 bg-gray-100 cursor-not-allowed px-3 py-2 focus:outline-none">
                    <option value="">Select Expense Type</option>
                    @foreach($expenseTypes as $expenseType)
                    <option value="{{ $expenseType->id }}" {{ $expenseType->id == old('expense_type_id', $deliveryRequest->expense_type_id) ? 'selected' : '' }}>{{ $expenseType->expense_code }}</option>
                    @endforeach
                </select>
                @error('expense_type_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                </div>

                <!-- Project Name (4 cols) -->
                <div class="md:col-span-4">
                <label for="project_name" class="block text-sm font-medium text-gray-700 mb-1">Project Name</label>
                <input type="text" name="project_name" id="project_name" readonly
                    value="{{ old('project_name', $deliveryRequest->project_name) }}"
                    class="block w-full rounded border border-gray-300 bg-gray-100 cursor-not-allowed px-3 py-2 focus:outline-none">
                @error('project_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                </div>
            </div>

            <!-- Row 3 -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <!-- Region (2 cols) -->
                <div class="md:col-span-2">
                <label for="area_id" class="block text-sm font-medium text-gray-700 mb-1">Region</label>
                <select name="area_id" id="area_id" readonly
                    class="block w-full rounded border border-gray-300 bg-gray-100 cursor-not-allowed px-3 py-2 focus:outline-none">
                    <option value="">Select Area</option>
                    @foreach($areas as $area)
                    <option value="{{ $area->id }}" {{ $area->id == old('area_id', $deliveryRequest->area_id) ? 'selected' : '' }}>{{ $area->area_code }}</option>
                    @endforeach
                </select>
                @error('area_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                </div>

                <!-- Province (2 cols) -->
                <div class="md:col-span-2">
                <label for="region_id" class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                <select name="region_id" id="region_id" readonly
                    class="block w-full rounded border border-gray-300 bg-gray-100 cursor-not-allowed px-3 py-2 focus:outline-none">
                    <option value="">Select Province</option>
                    @foreach($regions as $region)
                    <option value="{{ $region->id }}" {{ $region->id == old('region_id', $deliveryRequest->region_id) ? 'selected' : '' }}>{{ $region->province }}</option>
                    @endforeach
                </select>
                @error('region_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                </div>

                <!-- Customer (4 cols) -->
                <div class="md:col-span-4">
                <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                <select name="customer_id" id="customer_id" readonly
                    class="block w-full rounded border border-gray-300 bg-gray-100 cursor-not-allowed px-3 py-2 focus:outline-none">
                    <option value="">Select Company</option>
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ $customer->id == old('customer_id', $deliveryRequest->customer_id) ? 'selected' : '' }}>{{ $customer->name }}</option>
                    @endforeach
                </select>
                @error('customer_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                </div>

                <!-- Delivery Type (4 cols) -->
                <div class="md:col-span-4">
                <label for="delivery_type" class="block text-sm font-medium text-gray-700 mb-1">Delivery Type</label>
                <select name="delivery_type" id="delivery_type" readonly
                    class="block w-full rounded border border-gray-300 bg-gray-100 cursor-not-allowed px-3 py-2 focus:outline-none">
                    <option value="Regular" selected>Regular</option>
                </select>
                @error('delivery_type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                </div>
            </div>
            </div>


        <!-- Conditional Output for Delivery Type -->
        <div id="regular-fields" class="mt-3 p-4 border bg-white rounded shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                @foreach($deliveryLineItems as $index => $lineItem)
                    <input type="hidden" name="regular[{{ $index }}][id]" value="{{ $lineItem->id }}">
                    <!-- Warehouse (4 cols) -->
                    <div class="md:col-span-4">
                        <label for="regular_warehouse_id_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-1">Warehouse</label>
                        <select name="regular[{{ $index }}][warehouse_id]" id="regular_warehouse_id_{{ $index }}" readonly
                        class="block w-full rounded border border-gray-300 bg-gray-100 cursor-not-allowed px-3 py-2 focus:outline-none">
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ $warehouse->id == old('regular.' . $index . '.warehouse_id', $lineItem->warehouse_id) ? 'selected' : '' }}>
                            {{ $warehouse->warehouse_name }}
                            </option>
                        @endforeach
                            </select>
                        @error('regular.' . $index . '.warehouse_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Delivery Number (4 cols) -->
                    <div class="md:col-span-4">
                        <label for="regular_delivery_number_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-1">Delivery Number</label>
                        <input type="text" name="regular[{{ $index }}][delivery_number]" id="regular_delivery_number_{{ $index }}" readonly
                        value="{{ old('regular.' . $index . '.delivery_number', $lineItem->delivery_number) }}"
                        class="block w-full rounded border border-gray-300 bg-gray-100 cursor-not-allowed px-3 py-2 focus:outline-none">
                            @error('regular.' . $index . '.delivery_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                    </div>

                    <!-- Site Name (4 cols) -->
                    <div class="md:col-span-4">
                        <label for="regular_site_name_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-1">Site Name</label>
                        <input type="text" name="regular[{{ $index }}][site_name]" id="regular_site_name_{{ $index }}" readonly
                        value="{{ old('regular.' . $index . '.site_name', $lineItem->site_name) }}"
                        class="block w-full rounded border border-gray-300 bg-gray-100 cursor-not-allowed px-3 py-2 focus:outline-none">
                        @error('regular.' . $index . '.site_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Delivery Status (4 cols) -->
                    <div class="md:col-span-4">
                        <label for="regular_delivery_status_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-1">Delivery Status</label>
                        <select name="regular[{{ $index }}][delivery_status]" id="regular_delivery_status_{{ $index }}" readonly
                        class="block w-full rounded border border-gray-300 bg-gray-100 cursor-not-allowed px-3 py-2 focus:outline-none">
                        @foreach($deliveryStatuses as $deliveryStatus)
                            <option value="{{ $deliveryStatus->id }}" {{ $deliveryStatus->id == old('regular.' . $index . '.delivery_status', $lineItem->delivery_status) ? 'selected' : '' }}>
                                {{ $deliveryStatus->status_name }}
                            </option>
                        @endforeach
                        </select>
                            @error('regular.' . $index . '.delivery_status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                    </div>

                    <!-- Delivery Address (4 cols) -->
                    <div class="md:col-span-4">
                        <label for="regular_delivery_address_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
                        <textarea name="regular[{{ $index }}][delivery_address]" id="regular_delivery_address_{{ $index }}" readonly
                        class="block w-full rounded border border-gray-300 bg-gray-100 cursor-not-allowed px-3 py-2 focus:outline-none resize-none" rows="3">{{ old('regular.' . $index . '.delivery_address', trim($lineItem->delivery_address ?? '')) }}</textarea>
                        @error('regular.' . $index . '.delivery_address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                           @enderror
                    </div>
                        @endforeach
                    </div>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded my-4">
                    Create Delivery Request
                </button>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const mtmInput = document.getElementById('mtm');
        if (mtmInput) {
            let value = mtmInput.value.trim();

            // If it doesn't already end with -2, append it
            if (value && !value.endsWith('-1')) {
                // Remove any accidental -2s
                value = value.replace(/-1/g, '');
                mtmInput.value = value + '-1';
            }
        }
    });
</script>

@endsection
