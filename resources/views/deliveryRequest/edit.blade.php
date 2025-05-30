@extends('layouts.app')

@section('content')
    <!-- <h1>Edit Delivery Request</h1> -->

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('deliveryRequest.update', $deliveryRequest) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="border bg-white p-4 space-y-6">
            <!-- First row -->
            <div class="flex flex-wrap -mx-2">
                <div class="w-full md:w-4/12 px-2 mb-4 md:mb-0">
                <label for="mtm" class="block text-sm font-medium text-gray-700 mb-1">MTM Number</label>
                <input type="text" name="mtm" id="mtm" required
                        value="{{ old('mtm', $deliveryRequest->mtm) }}"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm
                                focus:ring-blue-500 focus:border-blue-500">
                @error('mtm')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
                </div>

                <div class="w-full md:w-4/12 px-2 mb-4 md:mb-0">
                <label for="booking_date" class="block text-sm font-medium text-gray-700 mb-1">Booking Date</label>
                <input type="date" name="booking_date" id="booking_date" required
                        value="{{ old('booking_date', $deliveryRequest->booking_date) }}"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm
                                focus:ring-blue-500 focus:border-blue-500">
                @error('booking_date')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
                </div>

                <div class="w-full md:w-4/12 px-2">
                <label for="delivery_date" class="block text-sm font-medium text-gray-700 mb-1">Delivery Date</label>
                <input type="date" name="delivery_date" id="delivery_date" required
                        value="{{ old('delivery_date', $deliveryRequest->delivery_date) }}"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm
                                focus:ring-blue-500 focus:border-blue-500">
                @error('delivery_date')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
                </div>
            </div>

            <!-- Second row -->
            <div class="flex flex-wrap -mx-2">
                <div class="w-full md:w-2/12 px-2 mb-4 md:mb-0">
                <label for="delivery_rate" class="block text-sm font-medium text-gray-700 mb-1">Delivery Rate</label>
                <input type="text" name="delivery_rate" id="delivery_rate" required
                        value="{{ old('delivery_rate', $deliveryRequest->delivery_rate) }}"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm
                                focus:ring-blue-500 focus:border-blue-500">
                @error('delivery_rate')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
                </div>

                <div class="w-full md:w-2/12 px-2 mb-4 md:mb-0">
                <label for="truck_type_id" class="block text-sm font-medium text-gray-700 mb-1">Truck Type</label>
                <select name="truck_type_id" id="truck_type_id" required
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm
                                focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select Truck Type</option>
                    @foreach($truckTypes as $truckType)
                    <option value="{{ $truckType->id }}" {{ $truckType->id == old('truck_type_id', $deliveryRequest->truck_type_id) ? 'selected' : '' }}>
                        {{ $truckType->truck_code }}
                    </option>
                    @endforeach
                </select>
                @error('truck_type_id')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
                </div>

                <div class="w-full md:w-2/12 px-2 mb-4 md:mb-0">
                <label for="company_id" class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                <select name="company_id" id="company_id" required
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm
                                focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select Company</option>
                    @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ $company->id == old('company_id', $deliveryRequest->company_id) ? 'selected' : '' }}>
                        {{ $company->company_name }}
                    </option>
                    @endforeach
                </select>
                @error('company_id')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
                </div>

                <div class="w-full md:w-2/12 px-2 mb-4 md:mb-0">
                <label for="expense_type_id" class="block text-sm font-medium text-gray-700 mb-1">Expense Type</label>
                <select name="expense_type_id" id="expense_type_id" required
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm
                                focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select Expense Type</option>
                    @foreach($expenseTypes as $expenseType)
                    <option value="{{ $expenseType->id }}" {{ $expenseType->id == old('expense_type_id', $deliveryRequest->expense_type_id) ? 'selected' : '' }}>
                        {{ $expenseType->expense_code }}
                    </option>
                    @endforeach
                </select>
                @error('expense_type_id')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
                </div>

                <div class="w-full md:w-4/12 px-2">
                <label for="project_name" class="block text-sm font-medium text-gray-700 mb-1">Project Name</label>
                <input type="text" name="project_name" id="project_name" required
                        value="{{ old('project_name', $deliveryRequest->project_name) }}"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm
                                focus:ring-blue-500 focus:border-blue-500">
                @error('project_name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
                </div>
            </div>

            <!-- Third row -->
            <div class="flex flex-wrap -mx-2">
                <div class="w-full md:w-2/12 px-2 mb-4 md:mb-0">
                <label for="area_id" class="block text-sm font-medium text-gray-700 mb-1">Region</label>
                <select name="area_id" id="area_id" required
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm
                                focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select Region</option>
                    @foreach($areas as $area)
                    <option value="{{ $area->id }}" {{ $area->id == old('area_id', $deliveryRequest->area_id) ? 'selected' : '' }}>
                        {{ $area->area_code }}
                    </option>
                    @endforeach
                </select>
                @error('area_id')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
                </div>

                <div class="w-full md:w-2/12 px-2 mb-4 md:mb-0">
                <label for="region_id" class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                <select name="region_id" id="region_id" required
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm
                                focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select Province</option>
                    @foreach($regions as $region)
                    <option value="{{ $region->id }}" {{ $region->id == old('region_id', $deliveryRequest->region_id) ? 'selected' : '' }}>
                        {{ $region->province }}
                    </option>
                    @endforeach
                </select>
                @error('region_id')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
                </div>

                <div class="w-full md:w-4/12 px-2 mb-4 md:mb-0">
                <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                <select name="customer_id" id="customer_id" required
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm
                                focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select Customer</option>
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ $customer->id == old('customer_id', $deliveryRequest->customer_id) ? 'selected' : '' }}>
                        {{ $customer->name }}
                    </option>
                    @endforeach
                </select>
                @error('customer_id')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
                </div>

                <div class="w-full md:w-4/12 px-2">
                <label for="delivery_type" class="block text-sm font-medium text-gray-700 mb-1">Delivery Type</label>
                <select name="delivery_type" id="delivery_type" required
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm
                                focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select Delivery Type</option>
                    @foreach($deliveryTypes as $deliveryType)
                    <option value="{{ $deliveryType->delivery_type_name }}" {{ $deliveryType->delivery_type_name == old('delivery_type', $deliveryRequest->delivery_type) ? 'selected' : '' }}>
                        {{ $deliveryType->delivery_type_name }}
                    </option>
                    @endforeach
                </select>
                @error('delivery_type')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
                </div>
            </div>
            </div>


        <!-- Conditional Output for Delivery Type -->
        @if(old('delivery_type', $deliveryRequest->delivery_type) === 'Regular')
            @if($deliveryLineItems->isNotEmpty())
                <div id="regular-fields" class="mt-3 p-4 border bg-white">
                    <div class="flex flex-wrap -mx-2">
                        @foreach($deliveryLineItems as $index => $lineItem)
                        <!-- Hidden ID -->
                        <input type="hidden" name="regular[{{ $index }}][id]" value="{{ $lineItem->id }}">

                        <!-- Warehouse -->
                        <div class="w-full md:w-2/12 px-2 mb-4">
                            <label for="regular_warehouse_id_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-1">Warehouse</label>
                            <select name="regular[{{ $index }}][warehouse_id]" id="regular_warehouse_id_{{ $index }}" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm
                                        focus:ring-blue-500 focus:border-blue-500">
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" 
                                {{ $warehouse->id == old('regular.' . $index . '.warehouse_id', $lineItem->warehouse_id) ? 'selected' : '' }}>
                                {{ $warehouse->warehouse_name }}
                                </option>
                            @endforeach
                            </select>
                            @error('regular.' . $index . '.warehouse_id')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Delivery Number -->
                        <div class="w-full md:w-4/12 px-2 mb-4">
                            <label for="regular_delivery_number_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-1">Delivery Number</label>
                            <input type="text" name="regular[{{ $index }}][delivery_number]" id="regular_delivery_number_{{ $index }}" 
                                value="{{ old('regular.' . $index . '.delivery_number', $lineItem->delivery_number) }}"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm
                                        focus:ring-blue-500 focus:border-blue-500">
                            @error('regular.' . $index . '.delivery_number')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Accessorial Type -->
                        <div class="w-full md:w-2/12 px-2 mb-4">
                            <label for="regular_accessorial_type_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-1">Accessorial Type</label>
                            <select name="regular[{{ $index }}][accessorial_type]" id="regular_accessorial_type_{{ $index }}" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm
                                        focus:ring-blue-500 focus:border-blue-500">
                            <option value="" {{ old('regular.' . $index . '.accessorial_type', $lineItem->accessorial_type) == '' ? 'selected' : '' }}>
                                Select an Accessorial Type
                            </option>
                            @foreach($accessorialTypes as $accessorialType)
                                <option value="{{ $accessorialType->id }}" 
                                {{ old('regular.' . $index . '.accessorial_type', $lineItem->accessorial_type) == $accessorialType->id ? 'selected' : '' }}>
                                {{ $accessorialType->accessorial_types_code }}
                                </option>
                            @endforeach
                            </select>
                            @error('regular.' . $index . '.accessorial_type')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Accessorial Rate -->
                        <div class="w-full md:w-2/12 px-2 mb-4">
                            <label for="regular_accessorial_rate_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-1">Accessorial Rate</label>
                            <input type="text" name="regular[{{ $index }}][accessorial_rate]" id="regular_accessorial_rate_{{ $index }}" 
                                value="{{ old('regular.' . $index . '.accessorial_rate', $lineItem->accessorial_rate) }}"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm
                                        focus:ring-blue-500 focus:border-blue-500">
                            @error('regular.' . $index . '.accessorial_rate')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Site Name -->
                        <div class="w-full md:w-4/12 px-2 mb-4">
                            <label for="regular_site_name_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-1">Site Name</label>
                            <input type="text" name="regular[{{ $index }}][site_name]" id="regular_site_name_{{ $index }}" 
                                value="{{ old('regular.' . $index . '.site_name', $lineItem->site_name) }}"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm
                                        focus:ring-blue-500 focus:border-blue-500">
                            @error('regular.' . $index . '.site_name')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Delivery Status -->
                        <div class="w-full md:w-4/12 px-2 mb-4">
                            <label for="regular_delivery_status_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-1">Delivery Status</label>
                            <select name="regular[{{ $index }}][delivery_status]" id="regular_delivery_status_{{ $index }}" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm
                                        focus:ring-blue-500 focus:border-blue-500">
                            @foreach($deliveryStatuses as $deliveryStatus)
                                <option value="{{ $deliveryStatus->id }}" 
                                {{ $deliveryStatus->id == old('regular.' . $index . '.delivery_status', $lineItem->delivery_status) ? 'selected' : '' }}>
                                {{ $deliveryStatus->status_name }}
                                </option>
                            @endforeach
                            </select>
                            @error('regular.' . $index . '.delivery_status')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Delivery Address -->
                        <div class="w-full md:w-4/12 px-2 mb-4">
                            <label for="regular_delivery_address_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
                            <textarea name="regular[{{ $index }}][delivery_address]" id="regular_delivery_address_{{ $index }}" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm
                                            focus:ring-blue-500 focus:border-blue-500 resize-none" rows="3">{{ old('regular.' . $index . '.delivery_address', trim($lineItem->delivery_address ?? '')) }}</textarea>
                            @error('regular.' . $index . '.delivery_address')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        @endforeach
                    </div>
                    </div>
            @else
                <div class="mt-3 text-gray-500 text-sm">No data available.</div>
            @endif

        <!-- Multi Drop Fields -->
        @elseif(old('delivery_type', $deliveryRequest->delivery_type) === 'Multi-Drop')
        <!-- Multi-Drop Fields (Initially Hidden) -->
        @if($deliveryLineItems->isNotEmpty())
            <div id="multi-drop-fields" >
                <div id="multi-drop-items">
                    <!-- Display Warehouse, Add-on Rate Fields outside the loop (only once) -->
                    <div class="multi-drop-row">
                        <div class="border border-gray-300 bg-white p-4 mt-3 rounded">
                            <div class="flex flex-wrap -mx-2">
                                <!-- Warehouse -->
                                <div class="w-full md:w-1/3 px-2 mb-4">
                                    <label for="multi_drop_0_warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">
                                        Warehouse
                                    </label>
                                    <select name="multi_drop[0][warehouse_id]" id="multi_drop_0_warehouse_id" class="w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}"
                                                {{ $deliveryLineItems->first()->warehouse_id == $warehouse->id ? 'selected' : '' }}>
                                                {{ $warehouse->warehouse_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error("multi_drop.0.warehouse_id")
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Add-on Rate -->
                                <div class="w-full md:w-1/3 px-2 mb-4">
                                    <label for="multi_drop_0_add_on_rate" class="block text-sm font-medium text-gray-700 mb-1">
                                        Add-on Rate
                                    </label>
                                    <select name="multi_drop[0][add_on_rate]" id="multi_drop_0_add_on_rate" class="w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        @foreach($AddOnRates_multiDrops as $AddOnRates_multiDrop)
                                            <option value="{{ $AddOnRates_multiDrop->id }}"
                                                {{ $deliveryLineItems->first()->add_on_rate == $AddOnRates_multiDrop->id ? 'selected' : '' }}>
                                                {{ $AddOnRates_multiDrop->add_on_rate_type_code }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error("multi_drop.0.add_on_rate")
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Add Button -->
                                <div class="w-full md:w-1/6 px-2 flex items-end mb-4">
                                    <button type="button" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded add-more-drop">
                                        Add
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="multi-drop-fields" class="">
                        <div id="multi-drop-items">
                            <!-- Loop through deliveryLineItems for the dynamic fields -->
                            @php
                            $totalItems = $deliveryLineItems->count();
                            @endphp
                            <input type="hidden" name="multi_drop_count" value="{{ $totalItems }}">
                            @foreach($deliveryLineItems as $index => $lineItem)
                            <input type="hidden" name="multi_drop[{{ $index }}][id]" value="{{ $lineItem->id }}">
                                <div class="multi-drop-row" id="multi-drop-row-{{ $index }}">
                                    <div class="border border-gray-300 bg-white p-4 mt-3 rounded">
                                        <div class="flex flex-wrap -mx-2">
                                            <!-- Site Name Field -->
                                            <div class="w-full md:w-1/6 px-2 mb-4">
                                                <label for="site_name_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-1">Site Name</label>
                                                <input type="text" name="multi_drop[{{ $index }}][site_name]" id="site_name_{{ $index }}" 
                                                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                                    value="{{ old("multi_drop.{$index}.site_name", $lineItem->site_name) }}">
                                                @error("multi_drop.{$index}.site_name")
                                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <!-- Delivery Number Field -->
                                            <div class="w-full md:w-1/4 px-2 mb-4">
                                                <label for="delivery_number_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-1">Delivery Number</label>
                                                <input type="text" name="multi_drop[{{ $index }}][delivery_number]" id="delivery_number_{{ $index }}" 
                                                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                                    value="{{ old("multi_drop.{$index}.delivery_number", $lineItem->delivery_number) }}">
                                                @error("multi_drop.{$index}.delivery_number")
                                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <!-- Delivery Address Field -->
                                            <div class="w-full md:w-1/4 px-2 mb-4">
                                                <label for="delivery_address_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
                                                <textarea name="multi_drop[{{ $index }}][delivery_address]" id="delivery_address_{{ $index }}" 
                                                    class="w-full border border-gray-300 rounded px-3 py-2 resize-y focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old("multi_drop.{$index}.delivery_address", $lineItem->delivery_address) }}</textarea>
                                                @error("multi_drop.{$index}.delivery_address")
                                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <!-- Delivery Status Field -->
                                            <div class="w-full md:w-1/6 px-2 mb-4">
                                                <label for="delivery_status_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-1">Delivery Status</label>
                                                <select name="multi_drop[{{ $index }}][delivery_status]" id="delivery_status_{{ $index }}" 
                                                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    <option value="" {{ old('multi_drop.' . $index . '.delivery_status', $lineItem->delivery_status) == '' ? 'selected' : '' }}>
                                                        Select a Delivery Status
                                                    </option>
                                                    @foreach($deliveryStatuses as $deliveryStatus)
                                                        <option value="{{ $deliveryStatus->id }}" 
                                                            {{ old('multi_drop.' . $index . '.delivery_status', $lineItem->delivery_status) == $deliveryStatus->id ? 'selected' : '' }}>
                                                            {{ $deliveryStatus->status_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('multi_drop.' . $index . '.delivery_status')
                                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <!-- Accessorial Type -->
                                            <div class="w-full md:w-1/12 px-2 mb-4">
                                                <label for="accessorial_type_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-1">Accessorial Type</label>
                                                <select name="multi_drop[{{ $index }}][accessorial_type]" id="accessorial_type_{{ $index }}" 
                                                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    <option value="" {{ old('multi_drop.' . $index . '.accessorial_type', $lineItem->accessorial_type) == '' ? 'selected' : '' }}>
                                                        Select an Accessorial Type
                                                    </option>
                                                    @foreach($accessorialTypes as $accessorialType)
                                                        <option value="{{ $accessorialType->id }}" 
                                                            {{ old('multi_drop.' . $index . '.accessorial_type', $lineItem->accessorial_type) == $accessorialType->id ? 'selected' : '' }}>
                                                            {{ $accessorialType->accessorial_types_code }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('multi_drop.' . $index . '.accessorial_type')
                                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <!-- Accessorial Rate -->
                                            <div class="w-full md:w-1/12 px-2 mb-4">
                                                <label for="accessorial_rate_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-1">Accessorial Rate</label>
                                                <input type="text" name="multi_drop[{{ $index }}][accessorial_rate]" id="accessorial_rate_{{ $index }}" 
                                                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                                    value="{{ old('multi_drop.' . $index . '.accessorial_rate', $lineItem->accessorial_rate) }}">
                                                @error('multi_drop.' . $index . '.accessorial_rate')
                                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        @endforeach
                    </div>
                </div>
            </div>

    @else
        <div class="mt-3 text-gray-500 text-sm">No data available.</div>
    @endif

    @elseif(old('delivery_type', $deliveryRequest->delivery_type) === 'Multi Pick-Up')
        @if($deliveryLineItems->isNotEmpty())
            <div id="multi-pickup-fields">
                <div id="multi-pickup-items">
                    <!-- Multi-Pickup Row -->
                    <div class="multi-pickup-row">
                        <div class="mt-2 p-4 border border-gray-300 bg-white rounded">
                            <div class="flex flex-wrap -mx-2">
                                <div class="w-full md:w-1/3 px-2 mb-4">
                                    <label for="multi_pickup_0_site_name" class="block text-sm font-medium text-gray-700 mb-1">Site Name</label>
                                    <input type="text" name="multi_pickup[0][site_name]" id="multi_pickup_0_site_name" 
                                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                        value="{{ old('multi_pickup.0.site_name', $deliveryLineItems->first()->site_name) }}">
                                    @error("multi_pickup.0.site_name")
                                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="w-full md:w-1/3 px-2 mb-4">
                                    <label for="multi_pickup_0_delivery_address" class="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
                                    <textarea name="multi_pickup[0][delivery_address]" id="multi_pickup_0_delivery_address" 
                                        class="w-full border border-gray-300 rounded px-3 py-2 resize-y focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                        style="height: 120px;">{{ old('multi_pickup.0.delivery_address', $deliveryLineItems->first()->delivery_address) }}</textarea>
                                    @error("multi_pickup.0.delivery_address")
                                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="w-full md:w-1/6 px-2 mb-4">
                                    <label for="multi_pickup_0_add_on_rate" class="block text-sm font-medium text-gray-700 mb-1">Add-on Rate</label>
                                    <select name="multi_pickup[0][add_on_rate]" id="multi_pickup_0_add_on_rate" 
                                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        @foreach($AddOnRates_multiPickUps as $AddOnRates_multiPickUp)
                                            <option value="{{ $AddOnRates_multiPickUp->id }}" 
                                                @if(old('multi_pickup.0.add_on_rate', $deliveryLineItems->first()->add_on_rate_id) == $AddOnRates_multiPickUp->id) selected @endif>
                                                {{ $AddOnRates_multiPickUp->add_on_rate_type_code }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error("multi_pickup.0.add_on_rate")
                                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="w-full md:w-1/12 px-2 flex items-start justify-center mt-10">
                                    <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded add-more-pickup">
                                        Add More
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Loop through each line item -->
                        @php
                            $totalItems = $deliveryLineItems->count();
                        @endphp
                        <input type="hidden" name="multi_pickup_count" value="{{ $totalItems }}">
                        @foreach($deliveryLineItems as $index => $lineItem)
                       
                        <input type="hidden" name="multi_pickup[{{ $index }}][id]" value="{{ $lineItem->id }}">
                            <div class="mt-2 p-4 border bg-white rounded shadow-sm">
                                <div class="grid grid-cols-12 gap-4 items-end">
                                    <!-- Warehouse -->
                                    <div class="col-span-12 md:col-span-2">
                                        <label for="multi_pickup_{{ $index }}_warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">Warehouse</label>
                                        <select name="multi_pickup[{{ $index }}][warehouse_id]" id="multi_pickup_{{ $index }}_warehouse_id" 
                                            class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('multi_pickup.' . $index . '.warehouse_id') border-red-500 @enderror">
                                            @foreach($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}" 
                                                    @if(old("multi_pickup.{$index}.warehouse_id", $lineItem->warehouse_id) == $warehouse->id) selected @endif>
                                                    {{ $warehouse->warehouse_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("multi_pickup.{$index}.warehouse_id")
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Delivery Number -->
                                    <div class="col-span-12 md:col-span-4">
                                        <label for="multi_pickup_{{ $index }}_delivery_number" class="block text-sm font-medium text-gray-700 mb-1">Delivery Number</label>
                                        <input type="text" name="multi_pickup[{{ $index }}][delivery_number]" id="multi_pickup_{{ $index }}_delivery_number" 
                                            value="{{ old("multi_pickup.{$index}.delivery_number", $lineItem->delivery_number) }}" 
                                            class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('multi_pickup.' . $index . '.delivery_number') border-red-500 @enderror">
                                        @error("multi_pickup.{$index}.delivery_number")
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Delivery Status -->
                                    <div class="col-span-12 md:col-span-1">
                                        <label for="delivery_status_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-1">Delivery Status</label>
                                        <select name="multi_pickup[{{ $index }}][delivery_status]" id="delivery_status_{{ $index }}" 
                                            class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('multi_pickup.' . $index . '.delivery_status') border-red-500 @enderror">
                                            <option value="" {{ old('multi_pickup.' . $index . '.delivery_status', $lineItem->delivery_status) == '' ? 'selected' : '' }}>Select a Delivery Status</option>
                                            @foreach($deliveryStatuses as $deliveryStatus)
                                                <option value="{{ $deliveryStatus->id }}" {{ old('multi_pickup.' . $index . '.delivery_status', $lineItem->delivery_status) == $deliveryStatus->id ? 'selected' : '' }}>
                                                    {{ $deliveryStatus->status_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('multi_pickup.' . $index . '.delivery_status')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Accessorial Type -->
                                    <div class="col-span-12 md:col-span-2">
                                        <label for="accessorial_type_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-1">Accessorial Type</label>
                                        <select name="multi_pickup[{{ $index }}][accessorial_type]" id="accessorial_type_{{ $index }}" 
                                            class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('multi_pickup.' . $index . '.accessorial_type') border-red-500 @enderror">
                                            <option value="" {{ old('multi_pickup.' . $index . '.accessorial_type', $lineItem->accessorial_type) == '' ? 'selected' : '' }}>Select an Accessorial Type</option>
                                            @foreach($accessorialTypes as $accessorialType)
                                                <option value="{{ $accessorialType->id }}" {{ old('multi_pickup.' . $index . '.accessorial_type', $lineItem->accessorial_type) == $accessorialType->id ? 'selected' : '' }}>
                                                    {{ $accessorialType->accessorial_types_code }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('multi_pickup.' . $index . '.accessorial_type')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Accessorial Rate -->
                                    <div class="col-span-12 md:col-span-1">
                                        <label for="accessorial_rate_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-1">Accessorial Rate</label>
                                        <input type="text" name="multi_pickup[{{ $index }}][accessorial_rate]" id="accessorial_rate_{{ $index }}" 
                                            value="{{ old('multi_pickup.' . $index . '.accessorial_rate', $lineItem->accessorial_rate) }}" 
                                            class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('multi_pickup.' . $index . '.accessorial_rate') border-red-500 @enderror">
                                        @error('multi_pickup.' . $index . '.accessorial_rate')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="mt-3 text-gray-500 text-sm">No data available.</div>
        @endif
    @endif

        <button type="submit" class="btn btn-primary my-4">Update Delivery Request</button>
    </form>
    
    <script>
        let multiDropIndex, currentIndex;

        document.addEventListener('DOMContentLoaded', function () {
            // Get initial values from hidden inputs
            var MultiDropIndexCount = document.querySelector('input[name="multi_drop_count"]');
            var MultiPickUpIndexCount = document.querySelector('input[name="multi_pickup_count"]');

            // Initialize global variables if values exist
            if (MultiDropIndexCount !== null && MultiDropIndexCount.value) {
                MultiDropIndexCount = Number(MultiDropIndexCount.value);
                multiDropIndex = MultiDropIndexCount ;
            }

            if (MultiPickUpIndexCount !== null && MultiPickUpIndexCount.value) {
                MultiPickUpIndexCount = Number(MultiPickUpIndexCount.value);
                currentIndex = MultiPickUpIndexCount; // Set currentIndex from hidden field
            }

            // Log the initial values to confirm
            console.log('multiDropIndex: ', multiDropIndex);
            console.log('currentIndex: ', currentIndex);

            // Use event delegation to handle clicks on both buttons dynamically
            document.body.addEventListener('click', function (event) {
                // console.log('Clicked element:', event.target); // Log the clicked element

                // Check if the clicked button has the class 'add-more-drop' (for multi-drop)
                if (event.target && event.target.classList.contains('add-more-drop')) {
                    console.log("Add More Drop button clicked");

                    // Logic for Multi Drop functionality
                    // For example, add a new row for multi-drop fields
                    const newRow = document.createElement('div');
                    newRow.classList.add('multi-drop-row');
                    newRow.id = `multi-drop-row-${multiDropIndex}`;

                    newRow.innerHTML = `
                        <div class="mt-2 p-4 border bg-white rounded shadow-sm">
                            <div class="grid grid-cols-12 gap-4 items-end">
                                <!-- Site Name -->
                                <div class="col-span-12 md:col-span-1">
                                <label for="site_name_${multiDropIndex}" class="block text-sm font-medium text-gray-700 mb-1">Site Name</label>
                                <input type="text" name="multi_drop[${multiDropIndex}][site_name]" id="site_name_${multiDropIndex}" 
                                    class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('multi_drop.${multiDropIndex}.site_name') border-red-500 @enderror">
                                @error('multi_drop.${multiDropIndex}.site_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                </div>

                                <!-- Delivery Number -->
                                <div class="col-span-12 md:col-span-3">
                                <label for="delivery_number_${multiDropIndex}" class="block text-sm font-medium text-gray-700 mb-1">Delivery Number</label>
                                <input type="text" name="multi_drop[${multiDropIndex}][delivery_number]" id="delivery_number_${multiDropIndex}" 
                                    class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('multi_drop.${multiDropIndex}.delivery_number') border-red-500 @enderror">
                                @error('multi_drop.${multiDropIndex}.delivery_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                </div>

                                <!-- Delivery Address -->
                                <div class="col-span-12 md:col-span-3">
                                <label for="delivery_address_${multiDropIndex}" class="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
                                <textarea name="multi_drop[${multiDropIndex}][delivery_address]" id="delivery_address_${multiDropIndex}" 
                                    class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('multi_drop.${multiDropIndex}.delivery_address') border-red-500 @enderror"></textarea>
                                @error('multi_drop.${multiDropIndex}.delivery_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                </div>

                                <!-- Delivery Status -->
                                <div class="col-span-12 md:col-span-2">
                                <label for="delivery_status_${multiDropIndex}" class="block text-sm font-medium text-gray-700 mb-1">Delivery Status</label>
                                <select name="multi_drop[${multiDropIndex}][delivery_status]" id="delivery_status_${multiDropIndex}" 
                                    class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('multi_drop.${multiDropIndex}.delivery_status') border-red-500 @enderror">
                                    @foreach($deliveryStatuses as $deliveryStatus)
                                    <option value="{{ $deliveryStatus->id }}">{{ $deliveryStatus->status_name }}</option>
                                    @endforeach
                                </select>
                                @error('multi_drop.${multiDropIndex}.delivery_status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                </div>

                                <!-- Accessorial Type -->
                                <div class="col-span-12 md:col-span-1">
                                <label for="accessorial_type_${multiDropIndex}" class="block text-sm font-medium text-gray-700 mb-1">Accessorial Type</label>
                                <select name="multi_drop[${multiDropIndex}][accessorial_type]" id="accessorial_type_${multiDropIndex}" 
                                    class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('multi_drop.${multiDropIndex}.accessorial_type') border-red-500 @enderror">
                                    <option value="">Select an Accessorial Type</option>
                                    @foreach($accessorialTypes as $accessorialType)
                                    <option value="{{ $accessorialType->id }}">{{ $accessorialType->accessorial_types_code }}</option>
                                    @endforeach
                                </select>
                                @error('multi_drop.${multiDropIndex}.accessorial_type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                </div>

                                <!-- Accessorial Rate -->
                                <div class="col-span-12 md:col-span-1">
                                <label for="accessorial_rate_${multiDropIndex}" class="block text-sm font-medium text-gray-700 mb-1">Accessorial Rate</label>
                                <input type="text" name="multi_drop[${multiDropIndex}][accessorial_rate]" id="accessorial_rate_${multiDropIndex}" 
                                    class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('multi_drop.${multiDropIndex}.accessorial_rate') border-red-500 @enderror">
                                @error('multi_drop.${multiDropIndex}.accessorial_rate')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                </div>

                                <!-- Delete Button -->
                                <div class="col-span-12 md:col-span-1 flex justify-center items-center">
                                <button type="button" class="bg-red-600 text-white px-3 py-2 rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500" 
                                    onclick="deleteDropRow(${multiDropIndex})">Delete</button>
                                </div>
                            </div>
                            </div>

                    `;

                    // Append the new row for multi-drop fields
                    document.getElementById('multi-drop-items').appendChild(newRow);

                    // Increment the index for the next multi-drop row
                    multiDropIndex++;

                    // Update the hidden input for multi-drop count if necessary
                    document.querySelector('input[name="multi_drop_count"]').value = multiDropIndex;

                    console.log('multiDropIndex: ', multiDropIndex); // Log to confirm increment
                }

                // Check if the clicked button has the class 'add-more-pickup' (for multi-pickup)
                else if (event.target && event.target.classList.contains('add-more-pickup')) {
                    console.log("Add More Pickup button clicked");

                    // Logic for Multi Pickup functionality
                    // For example, add a new row for multi-pickup fields
                    const newRow = document.createElement('div');
                    newRow.classList.add('multi-pickup-row');
                    newRow.id = `multi-pickup-row-${currentIndex}`;

                    newRow.innerHTML = `
                        <div class="mt-2 p-4 border bg-white rounded shadow-sm">
                            <div class="grid grid-cols-12 gap-4 items-end">
                                <!-- Warehouse (2 columns) -->
                                <div class="col-span-12 md:col-span-2">
                                <label for="multi_pickup_${currentIndex}_warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">Warehouse</label>
                                <select name="multi_pickup[${currentIndex}][warehouse_id]" id="multi_pickup_${currentIndex}_warehouse_id" 
                                    class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('multi_pickup.${currentIndex}.warehouse_id') border-red-500 @enderror">
                                    @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->warehouse_name }}</option>
                                    @endforeach
                                </select>
                                @error('multi_pickup.${currentIndex}.warehouse_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                </div>

                                <!-- Delivery Number (4 columns) -->
                                <div class="col-span-12 md:col-span-4">
                                <label for="multi_pickup_${currentIndex}_delivery_number" class="block text-sm font-medium text-gray-700 mb-1">Delivery Number</label>
                                <input type="text" name="multi_pickup[${currentIndex}][delivery_number]" id="multi_pickup_${currentIndex}_delivery_number"
                                    class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('multi_pickup.${currentIndex}.delivery_number') border-red-500 @enderror">
                                @error('multi_pickup.${currentIndex}.delivery_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                </div>

                                <!-- Delivery Status (1 column) -->
                                <div class="col-span-12 md:col-span-1">
                                <label for="multi_pickup_${currentIndex}_delivery_status" class="block text-sm font-medium text-gray-700 mb-1">Delivery Status</label>
                                <select name="multi_pickup[${currentIndex}][delivery_status]" id="multi_pickup_${currentIndex}_delivery_status" 
                                    class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('multi_pickup.${currentIndex}.delivery_status') border-red-500 @enderror">
                                    @foreach($deliveryStatuses as $deliveryStatus)
                                    <option value="{{ $deliveryStatus->id }}">{{ $deliveryStatus->status_name }}</option>
                                    @endforeach
                                </select>
                                @error('multi_pickup.${currentIndex}.delivery_status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                </div>

                                <!-- Accessorial Type (1 column) -->
                                <div class="col-span-12 md:col-span-1">
                                <label for="multi_pickup_${currentIndex}_accessorial_type" class="block text-sm font-medium text-gray-700 mb-1">Accessorial Type</label>
                                <select name="multi_pickup[${currentIndex}][accessorial_type]" id="multi_pickup_${currentIndex}_accessorial_type" 
                                    class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('multi_pickup.${currentIndex}.accessorial_type') border-red-500 @enderror">
                                    <option value="">Select an Accessorial Type</option>
                                    @foreach($accessorialTypes as $accessorialType)
                                    <option value="{{ $accessorialType->id }}">{{ $accessorialType->accessorial_types_code }}</option>
                                    @endforeach
                                </select>
                                @error('multi_pickup.${currentIndex}.accessorial_type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                </div>

                                <!-- Accessorial Rate (1 column) -->
                                <div class="col-span-12 md:col-span-1">
                                <label for="multi_pickup_${currentIndex}_accessorial_rate" class="block text-sm font-medium text-gray-700 mb-1">Accessorial Rate</label>
                                <input type="text" name="multi_pickup[${currentIndex}][accessorial_rate]" id="multi_pickup_${currentIndex}_accessorial_rate" 
                                    class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('multi_pickup.${currentIndex}.accessorial_rate') border-red-500 @enderror">
                                @error('multi_pickup.${currentIndex}.accessorial_rate')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                </div>

                                <!-- Delete Button (1 column) -->
                                <div class="col-span-12 md:col-span-1 flex justify-center items-center">
                                <button type="button" class="bg-red-600 text-white px-3 py-2 rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500" 
                                    onclick="deleteRow(${currentIndex})">Delete</button>
                                </div>
                            </div>
                            </div>

                    `;

                    // Append the new row for multi-pickup fields
                    document.getElementById('multi-pickup-items').appendChild(newRow);

                    // Increment the index for the next multi-pickup row
                    currentIndex++;

                    // Update the hidden input for multi-pickup count if necessary
                    document.querySelector('input[name="multi_pickup_count"]').value = currentIndex;

                    console.log('currentIndex: ', currentIndex); // Log to confirm increment
                }
            });
        });

     // Function to delete a multi-drop row and decrement indices
        function deleteDropRow(index) {
            const row = document.getElementById(`multi-drop-row-${index}`);
            row.remove();

            // Decrement the index for subsequent rows
            for (let i = index + 1; i < multiDropIndex; i++) {
                const rowToShift = document.getElementById(`multi-drop-row-${i}`);
                rowToShift.id = `multi-drop-row-${i - 1}`; // Shift the ID of the row
                // Update the name and ID attributes for all form fields
                rowToShift.querySelectorAll('[name]').forEach(field => {
                    const name = field.name;
                    const newName = name.replace(`[${i}]`, `[${i - 1}]`);
                    field.name = newName;
                    field.id = newName;
                });
            }

            // Decrement the multiDropIndex as we have removed a row
            multiDropIndex--;
        }
    </script>


@endsection
