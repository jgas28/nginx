@extends('layouts.app')

@section('content')
    <style>
        #delivery-request-create-form input[type="text"],
        #delivery-request-create-form input[type="date"],
        #delivery-request-create-form input[type="number"],
        #delivery-request-create-form select {
            min-height: 48px;
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }

        #delivery-request-create-form .searchable-select-source + [data-searchable-select-wrapper] > button {
            min-height: 48px;
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        #delivery-request-create-form .searchable-select-source + [data-searchable-select-wrapper] > button > span:first-child {
            height: 2rem;
            width: 2rem;
        }

        #delivery-request-create-form .text-red-600,
        #delivery-request-create-form .text-red-500 {
            pointer-events: none;
        }
    </style>

    <form action="{{ route('deliveryRequest.store') }}" method="POST" id="delivery-request-create-form">
        @csrf
        <div class="border bg-white p-4 rounded shadow-sm">
            <!-- Row 1 -->
            <div class="flex flex-wrap -mx-2">
                <div class="w-full md:w-1/3 px-2 mb-4">
                    <label for="mtm" class="block text-sm font-medium text-gray-700 mb-1">MTM Number</label>
                    <input type="text" name="mtm" id="mtm" required
                        class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @error('mtm')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
                @php
                    $today = \Carbon\Carbon::today()->toDateString();
                @endphp

                <div class="w-full md:w-1/3 px-2 mb-4">
                    <label for="booking_date" class="block text-sm font-medium text-gray-700 mb-1">Booking Date</label>
                    <input type="date" name="booking_date" id="booking_date" required
                        value="{{ old('booking_date', $today) }}"
                        class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @error('booking_date')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="w-full md:w-1/3 px-2 mb-4">
                    <label for="delivery_date" class="block text-sm font-medium text-gray-700 mb-1">Delivery Date</label>
                    <input type="date" name="delivery_date" id="delivery_date" required
                        value="{{ old('delivery_date', $today) }}"
                        class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @error('delivery_date')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Row 2 -->
            <div class="flex flex-wrap -mx-2">
               <div class="w-full md:w-1/6 px-2 mb-4">
                    <label for="delivery_rate" class="block text-sm font-medium text-gray-700 mb-1">Delivery Rate</label>
                    <input 
                        type="number" 
                        name="delivery_rate" 
                        id="delivery_rate" 
                        step="0.01" 
                        required
                        class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    >
                    @error('delivery_rate')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="w-full md:w-1/6 px-2 mb-4">
                    <label for="truck_type_id" class="block text-sm font-medium text-gray-700 mb-1">Truck Type</label>
                    <select name="truck_type_id" id="truck_type_id" required
                            class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Select Truck Type</option>
                        @foreach($truckTypes as $truckType)
                            <option value="{{ $truckType->id }}">{{ $truckType->truck_code }}</option>
                        @endforeach
                    </select>
                    @error('truck_type_id')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="w-full md:w-1/6 px-2 mb-4">
                    <label for="company_id" class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                    <select name="company_id" id="company_id" required
                            class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Select Company</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                        @endforeach
                    </select>
                    @error('company_id')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="w-full md:w-1/6 px-2 mb-4">
                    <label for="expense_type_id" class="block text-sm font-medium text-gray-700 mb-1">Expense Type</label>
                    <select name="expense_type_id" id="expense_type_id" required
                            class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Select Expense Type</option>
                        @foreach($expenseTypes as $expenseType)
                            <option value="{{ $expenseType->id }}">{{ $expenseType->expense_code }}</option>
                        @endforeach
                    </select>
                    @error('expense_type_id')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="w-full md:w-1/3 px-2 mb-4">
                    <label for="project_name" class="block text-sm font-medium text-gray-700 mb-1">Project Name</label>
                    <input type="text" name="project_name" id="project_name" required
                        class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @error('project_name')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Row 3 -->
            <div class="flex flex-wrap -mx-2">
                <div class="w-full md:w-1/6 px-2 mb-4">
                    <label for="area_id" class="block text-sm font-medium text-gray-700 mb-1">Region</label>
                    <select name="area_id" id="area_id" required
                            class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Select Province</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}">{{ $area->area_code }}</option>
                        @endforeach
                    </select>
                    @error('area_id')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="w-full md:w-1/6 px-2 mb-4">
                    <label for="region_id" class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                    <select name="region_id" id="region_id" required
                            class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Select Province</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->id }}">{{ $region->province }}</option>
                        @endforeach
                    </select>
                    @error('region_id')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="w-full md:w-1/6 px-2 mb-4">
                    <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                    <select name="customer_id" id="customer_id" required
                            class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Select Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" data-type="{{ $customer->name }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                    @error('customer_id')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="w-full md:w-1/6 px-2 mb-4">
                    <label for="delivery_status" class="block text-sm font-medium text-gray-700 mb-1">Delivery Status</label>
                    <select name="delivery_status" id="delivery_status" required
                            class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Select Status</option>
                        @foreach($deliveryStatuses as $deliveryStatus)
                            <option value="{{ $deliveryStatus->id }}" data-type="{{ $deliveryStatus->status_name }}">{{ $deliveryStatus->status_name }}</option>
                        @endforeach
                    </select>
                    @error('customer_id')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="w-full md:w-1/3 px-2 mb-4">
                    <label for="delivery_type" class="block text-sm font-medium text-gray-700 mb-1">Delivery Type</label>
                    <select name="delivery_type" id="delivery_type" required
                            class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Select Delivery Type</option>
                        @foreach($deliveryTypes as $deliveryType)
                            <option value="{{ $deliveryType->delivery_type_name }}" data-type="{{ $deliveryType->delivery_type_name }}">{{ $deliveryType->delivery_type_name }}</option>
                        @endforeach
                    </select>
                    @error('delivery_type')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>


        <!-- Regular Fields (Initially Hidden) -->
        <div id="regular-fields" class="hidden mt-4 p-4 border border-gray-200 bg-white rounded shadow-sm">
            <div class="flex flex-wrap -mx-2">
                <!-- Warehouse -->
                <div class="w-full md:w-1/3 px-2 mb-4">
                    <label for="regular_warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">Warehouse</label>
                    <select name="regular[0][warehouse_id]" id="regular_warehouse_id"
                        class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->warehouse_name }}</option>
                        @endforeach
                    </select>
                    @error('regular.0.warehouse_id')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Delivery Number -->
                <div class="w-full md:w-1/3 px-2 mb-4">
                    <label for="regular_delivery_number" class="block text-sm font-medium text-gray-700 mb-1">Delivery Number</label>
                    <input type="text" name="regular[0][delivery_number]" id="regular_delivery_number"
                        class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @error('regular.0.delivery_number')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Site Name -->
                <div class="w-full md:w-1/3 px-2 mb-4">
                    <label for="regular_site_name" class="block text-sm font-medium text-gray-700 mb-1">Site Name</label>
                    <input type="text" name="regular[0][site_name]" id="regular_site_name"
                        class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @error('regular.0.site_name')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Delivery Address -->
                <div class="w-full md:w-2/3 px-2 mb-4">
                    <label for="regular_delivery_address" class="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
                    <textarea name="regular[0][delivery_address]" id="regular_delivery_address" rows="3"
                        class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                    @error('regular.0.delivery_address')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Multi-Drop Fields (Initially Hidden) -->
        <div id="multi-drop-fields" class="hidden mt-6 p-4 border border-gray-200 bg-white rounded shadow-sm">
            <div class="w-full">
                <div id="multi-drop-items">
                    <!-- Top Row: Warehouse + Add-on Rate + Add Button -->
                    <div class="flex flex-wrap -mx-2 mb-4">
                        <div class="w-full md:w-1/3 px-2">
                            <label for="multi_drop_0_warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">Warehouse</label>
                            <select name="multi_drop[0][warehouse_id]" id="multi_drop_0_warehouse_id"
                                class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->warehouse_name }}</option>
                                @endforeach
                            </select>
                            @error('multi_drop.0.warehouse_id')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="w-full md:w-1/3 px-2">
                            <label for="add_on_rate_0" class="block text-sm font-medium text-gray-700 mb-1">Add-on Rate</label>
                            <select name="multi_drop[0][add_on_rate]" id="add_on_rate_0"
                                class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                @foreach($AddOnRates_multiDrops as $AddOnRates_multiDrop)
                                    <option value="{{ $AddOnRates_multiDrop->id }}">{{ $AddOnRates_multiDrop->add_on_rate_type_code }}</option>
                                @endforeach
                            </select>
                            @error('multi_drop.0.add_on_rate')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="w-full md:w-1/3 px-2 flex items-end">
                            <button type="button"
                                class="add-more-drop inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition">
                                Add
                            </button>
                        </div>
                    </div>

                    <!-- Multi-Drop Row 0 -->
                    <div class="multi-drop-row mb-4" id="multi-drop-row-0">
                        <div class="flex flex-wrap -mx-2">
                            <div class="w-full md:w-1/6 px-2 mb-4">
                                <label for="site_name_0" class="block text-sm font-medium text-gray-700 mb-1">Site Name</label>
                                <input type="text" name="multi_drop[0][site_name]" id="site_name_0"
                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                @error('multi_drop.0.site_name')
                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="w-full md:w-1/4 px-2 mb-4">
                                <label for="delivery_number_0" class="block text-sm font-medium text-gray-700 mb-1">Delivery Number</label>
                                <input type="text" name="multi_drop[0][delivery_number]" id="delivery_number_0"
                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                @error('multi_drop.0.delivery_number')
                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="w-full md:w-1/4 px-2 mb-4">
                                <label for="delivery_address_0" class="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
                                <textarea name="multi_drop[0][delivery_address]" id="delivery_address_0" rows="2"
                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"></textarea>
                                @error('multi_drop.0.delivery_address')
                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Multi-Drop Row 1 -->
                    <div class="multi-drop-row mb-4" id="multi-drop-row-1">
                        <div class="flex flex-wrap -mx-2">
                            <div class="w-full md:w-1/6 px-2 mb-4">
                                <label for="site_name_1" class="block text-sm font-medium text-gray-700 mb-1">Site Name</label>
                                <input type="text" name="multi_drop[1][site_name]" id="multi_site_name_1"
                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                @error('multi_drop.1.site_name')
                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="w-full md:w-1/4 px-2 mb-4">
                                <label for="delivery_number_1" class="block text-sm font-medium text-gray-700 mb-1">Delivery Number</label>
                                <input type="text" name="multi_drop[1][delivery_number]" id="delivery_number_1"
                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                @error('multi_drop.1.delivery_number')
                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="w-full md:w-1/4 px-2 mb-4">
                                <label for="delivery_address_1" class="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
                                <textarea name="multi_drop[1][delivery_address]" id="delivery_address_1" rows="2"
                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"></textarea>
                                @error('multi_drop.1.delivery_address')
                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Multi Pick-Up Fields (Initially Hidden) -->
        <div id="multi-pickup-fields" class="hidden mt-6 p-4 border border-gray-200 bg-white rounded shadow-sm">
            <div id="multi-pickup-items">
                <!-- Multi Pickup Row 0 -->
                <div class="multi-pickup-row mb-6" id="multi-pickup-row-0">
                    <div class="flex flex-wrap -mx-2 mb-4">
                        <div class="w-full md:w-1/3 px-2">
                            <label for="site_name_0" class="block text-sm font-medium text-gray-700 mb-1">Site Name</label>
                            <input type="text" name="multi_pickup[0][site_name]" id="site_name_0"
                                class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            @error('multi_pickup.0.site_name')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="w-full md:w-1/3 px-2">
                            <label for="delivery_address_0" class="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
                            <textarea name="multi_pickup[0][delivery_address]" id="delivery_address_0" rows="2"
                                class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"></textarea>
                            @error('multi_pickup.0.delivery_address')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="w-full md:w-1/4 px-2">
                            <label for="add_on_rate_0" class="block text-sm font-medium text-gray-700 mb-1">Add-on Rate</label>
                            <select name="multi_pickup[0][add_on_rate]" id="add_on_rate_0"
                                class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                @foreach($AddOnRates_multiPickUps as $AddOnRates_multiPickUp)
                                    <option value="{{ $AddOnRates_multiPickUp->id }}">{{ $AddOnRates_multiPickUp->add_on_rate_type_code }}</option>
                                @endforeach
                            </select>
                            @error('multi_pickup.0.add_on_rate')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="w-full md:w-1/12 px-2 flex items-end">
                            <button type="button"
                                class="add-more-pickup inline-flex items-center px-3 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition">
                                Add More
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-wrap -mx-2">
                        <div class="w-full md:w-1/6 px-2 mb-4">
                            <label for="multi_pickup_0_warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">Warehouse</label>
                            <select name="multi_pickup[0][warehouse_id]" id="multi_pickup_0_warehouse_id"
                                class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->warehouse_name }}</option>
                                @endforeach
                            </select>
                            @error('multi_pickup.0.warehouse_id')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="w-full md:w-1/2 px-2 mb-4">
                            <label for="multi_pickup_0_delivery_number" class="block text-sm font-medium text-gray-700 mb-1">Delivery Number</label>
                            <input type="text" name="multi_pickup[0][delivery_number]" id="multi_pickup_0_delivery_number"
                                class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            @error('multi_pickup.0.delivery_number')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Multi Pickup Row 1 -->
                <div class="multi-pickup-row mb-6" id="multi-pickup-row-1">
                    <div class="flex flex-wrap -mx-2">
                        <div class="w-full md:w-1/6 px-2 mb-4">
                            <label for="multi_pickup_1_warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">Warehouse</label>
                            <select name="multi_pickup[1][warehouse_id]" id="multi_pickup_1_warehouse_id"
                                class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->warehouse_name }}</option>
                                @endforeach
                            </select>
                            @error('multi_pickup.1.warehouse')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="w-full md:w-1/2 px-2 mb-4">
                            <label for="multi_pickup_1_delivery_number" class="block text-sm font-medium text-gray-700 mb-1">Delivery Number</label>
                            <input type="text" name="multi_pickup[1][delivery_number]" id="multi_pickup_1_delivery_number"
                                class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            @error('multi_pickup.1.delivery_number')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="mt-4 float-right inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            Create Delivery Request
        </button>
    </form>

    <style>
        .searchable-select-source {
            position: absolute;
            left: -9999px;
            opacity: 0;
            pointer-events: none;
        }

        .searchable-select-panel::-webkit-scrollbar {
            width: 6px;
        }

        .searchable-select-panel::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
    </style>

    <script>
        let currentIndex = 2;
        let multiDropIndex = 2;
        const form = document.getElementById('delivery-request-create-form');
        const regularFields = document.getElementById('regular-fields');
        const multiDropFields = document.getElementById('multi-drop-fields');
        const multiPickupFields = document.getElementById('multi-pickup-fields');
        const deliveryTypeSelect = document.getElementById('delivery_type');
        const areaSelect = document.getElementById('area_id');
        const regionSelect = document.getElementById('region_id');
        const warehouseTemplateHtml = document.getElementById('multi_pickup_0_warehouse_id')?.innerHTML ?? '';

        function getSelectIcon(select) {
            const lookup = `${select.id} ${select.name}`.toLowerCase();
            if (lookup.includes('company')) return 'fa-building';
            if (lookup.includes('customer')) return 'fa-users';
            if (lookup.includes('truck_type')) return 'fa-truck-ramp-box';
            if (lookup.includes('expense_type')) return 'fa-file-invoice-dollar';
            if (lookup.includes('area_id')) return 'fa-map';
            if (lookup.includes('region_id')) return 'fa-location-dot';
            if (lookup.includes('warehouse')) return 'fa-warehouse';
            if (lookup.includes('delivery_status')) return 'fa-signal';
            if (lookup.includes('delivery_type')) return 'fa-shapes';
            if (lookup.includes('add_on_rate')) return 'fa-tags';
            return 'fa-circle-chevron-down';
        }

        function closeAllSearchableSelects(except = null) {
            form.querySelectorAll('[data-searchable-select-wrapper]').forEach((wrapper) => {
                if (wrapper === except) return;
                wrapper.dataset.open = 'false';
                wrapper.querySelector('[data-searchable-select-panel]')?.classList.add('hidden');
            });
        }

        function updateSearchableSelectLabel(select) {
            if (!select._searchableSelect) return;
            const selectedOption = select.options[select.selectedIndex];
            const placeholder = select.dataset.placeholder || select.querySelector('option[value=""]')?.text || 'Select an option';
            select._searchableSelect.label.textContent = selectedOption && selectedOption.value !== '' ? selectedOption.textContent.trim() : placeholder;
        }

        function renderSearchableSelectOptions(select, term = '') {
            if (!select._searchableSelect) return;
            const { list, emptyState, searchInput } = select._searchableSelect;
            const normalizedTerm = term.trim().toLowerCase();
            const options = Array.from(select.options);
            list.innerHTML = '';
            let visibleCount = 0;

            options.forEach((option) => {
                if (!option.value && normalizedTerm) return;
                if (normalizedTerm && !option.textContent.toLowerCase().includes(normalizedTerm)) return;
                visibleCount += 1;
                const button = document.createElement('button');
                button.type = 'button';
                button.className = `flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm transition ${option.selected ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-100'}`;
                button.innerHTML = `
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full ${option.selected ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500'}">
                        <i class="fas ${getSelectIcon(select)} text-xs"></i>
                    </span>
                    <span class="min-w-0 flex-1 truncate">${option.textContent.trim()}</span>
                    ${option.selected ? '<i class="fas fa-check text-xs text-indigo-500"></i>' : ''}
                `;
                button.addEventListener('click', () => {
                    select.value = option.value;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    updateSearchableSelectLabel(select);
                    renderSearchableSelectOptions(select, searchInput.value);
                    closeAllSearchableSelects();
                });
                list.appendChild(button);
            });

            emptyState.classList.toggle('hidden', visibleCount > 0);
        }

        function refreshSearchableSelect(select) {
            if (!select) return;
            if (!select._searchableSelect) {
                enhanceSearchableSelect(select);
                return;
            }
            updateSearchableSelectLabel(select);
            renderSearchableSelectOptions(select, select._searchableSelect.searchInput.value);
        }

        function enhanceSearchableSelect(select) {
            if (!select || select.dataset.searchableReady === 'true') {
                refreshSearchableSelect(select);
                return;
            }

            select.dataset.searchableReady = 'true';
            select.classList.add('searchable-select-source');

            const wrapper = document.createElement('div');
            wrapper.dataset.searchableSelectWrapper = 'true';
            wrapper.dataset.open = 'false';
            wrapper.className = 'relative mt-1';
            wrapper.innerHTML = `
                <button type="button" class="flex w-full items-center gap-3 rounded-xl border border-slate-300 bg-white px-3 py-3 text-left text-sm text-slate-700 shadow-sm transition hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                        <i class="fas ${getSelectIcon(select)} text-sm"></i>
                    </span>
                    <span class="min-w-0 flex-1 truncate" data-searchable-select-label></span>
                    <span class="text-slate-400"><i class="fas fa-chevron-down text-xs"></i></span>
                </button>
                <div data-searchable-select-panel class="searchable-select-panel absolute left-0 right-0 z-30 mt-2 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/10">
                    <div class="border-b border-slate-200 p-3">
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <i class="fas fa-magnifying-glass text-xs"></i>
                            </span>
                            <input type="text" data-searchable-select-input placeholder="Search option..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-indigo-300 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                        </div>
                    </div>
                    <div data-searchable-select-list class="max-h-56 overflow-y-auto p-2"></div>
                    <div data-searchable-select-empty class="hidden px-4 py-3 text-sm text-slate-500">No matching options found.</div>
                </div>
            `;

            select.insertAdjacentElement('afterend', wrapper);
            const trigger = wrapper.querySelector('button');
            const panel = wrapper.querySelector('[data-searchable-select-panel]');
            const searchInput = wrapper.querySelector('[data-searchable-select-input]');
            const list = wrapper.querySelector('[data-searchable-select-list]');
            const emptyState = wrapper.querySelector('[data-searchable-select-empty]');
            const label = wrapper.querySelector('[data-searchable-select-label]');
            const linkedLabels = select.id
                ? form.querySelectorAll(`label[for="${select.id}"]`)
                : [];

            select._searchableSelect = { wrapper, panel, searchInput, list, emptyState, label };
            select.tabIndex = -1;

            linkedLabels.forEach((linkedLabel) => {
                linkedLabel.addEventListener('click', (event) => {
                    event.preventDefault();
                    trigger.click();
                });
            });

            trigger.addEventListener('click', () => {
                const shouldOpen = wrapper.dataset.open !== 'true';
                closeAllSearchableSelects(wrapper);
                wrapper.dataset.open = shouldOpen ? 'true' : 'false';
                panel.classList.toggle('hidden', !shouldOpen);
                if (shouldOpen) {
                    searchInput.value = '';
                    renderSearchableSelectOptions(select);
                    setTimeout(() => searchInput.focus(), 0);
                }
            });

            searchInput.addEventListener('input', () => {
                renderSearchableSelectOptions(select, searchInput.value);
            });

            select.addEventListener('change', () => {
                updateSearchableSelectLabel(select);
                renderSearchableSelectOptions(select, searchInput.value);
            });

            updateSearchableSelectLabel(select);
            renderSearchableSelectOptions(select);
        }

        function initializeSearchableSelects(root = form) {
            root.querySelectorAll('select').forEach((select) => enhanceSearchableSelect(select));
        }

        function clearFormFields(sectionId) {
            const section = document.getElementById(sectionId);
            if (!section) return;
            section.querySelectorAll('input, select, textarea').forEach((input) => {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    input.checked = false;
                } else if (input.tagName === 'SELECT') {
                    input.selectedIndex = 0;
                    refreshSearchableSelect(input);
                } else {
                    input.value = '';
                }
            });
        }

        function handleDeliveryTypeChange(resetHiddenSections = true) {
            const deliveryType = deliveryTypeSelect.options[deliveryTypeSelect.selectedIndex]?.getAttribute('data-type') || '';
            [regularFields, multiDropFields, multiPickupFields].forEach((section) => section.classList.add('hidden'));
            if (resetHiddenSections) {
                clearFormFields('regular-fields');
                clearFormFields('multi-drop-fields');
                clearFormFields('multi-pickup-fields');
            }
            if (deliveryType === 'Regular') regularFields.classList.remove('hidden');
            if (deliveryType === 'Multi-Drop') multiDropFields.classList.remove('hidden');
            if (deliveryType === 'Multi Pick-Up') multiPickupFields.classList.remove('hidden');
        }

        function deleteRow(index) {
            document.getElementById(`multi-pickup-row-${index}`)?.remove();
            for (let i = index + 1; i < currentIndex; i += 1) {
                const rowToShift = document.getElementById(`multi-pickup-row-${i}`);
                if (!rowToShift) continue;
                rowToShift.id = `multi-pickup-row-${i - 1}`;
                rowToShift.querySelectorAll('[name]').forEach((field) => {
                    field.name = field.name.replace(`[${i}]`, `[${i - 1}]`);
                    if (field.id) field.id = field.id.replace(`_${i}_`, `_${i - 1}_`);
                });
            }
            currentIndex -= 1;
            initializeSearchableSelects(document.getElementById('multi-pickup-items'));
        }

        function deleteDropRow(index) {
            document.getElementById(`multi-drop-row-${index}`)?.remove();
            for (let i = index + 1; i < multiDropIndex; i += 1) {
                const rowToShift = document.getElementById(`multi-drop-row-${i}`);
                if (!rowToShift) continue;
                rowToShift.id = `multi-drop-row-${i - 1}`;
                rowToShift.querySelectorAll('[name]').forEach((field) => {
                    field.name = field.name.replace(`[${i}]`, `[${i - 1}]`);
                    if (field.id) field.id = field.id.replace(`_${i}`, `_${i - 1}`);
                });
            }
            multiDropIndex -= 1;
        }

        window.deleteRow = deleteRow;
        window.deleteDropRow = deleteDropRow;
        window.refreshSearchableSelect = refreshSearchableSelect;

        document.addEventListener('click', (event) => {
            if (!event.target.closest('[data-searchable-select-wrapper]')) closeAllSearchableSelects();
        });

        deliveryTypeSelect.addEventListener('change', () => handleDeliveryTypeChange(true));

        document.querySelector('.add-more-pickup').addEventListener('click', function() {
            const newRow = document.createElement('div');
            newRow.classList.add('multi-pickup-row');
            newRow.id = `multi-pickup-row-${currentIndex}`;
            newRow.innerHTML = `
                <div class="flex flex-wrap -mx-2 mb-4">
                    <div class="w-full md:w-1/6 px-2 mb-4 md:mb-0">
                        <label for="multi_pickup_${currentIndex}_warehouse_id" class="block text-sm font-medium text-gray-700">Warehouse</label>
                        <select name="multi_pickup[${currentIndex}][warehouse_id]" id="multi_pickup_${currentIndex}_warehouse_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            ${warehouseTemplateHtml}
                        </select>
                        <div class="text-red-600 text-sm mt-1 hidden" id="error_multi_pickup_${currentIndex}_warehouse_id"></div>
                    </div>
                    <div class="w-full md:w-1/3 px-2 mb-4 md:mb-0">
                        <label for="multi_pickup_${currentIndex}_delivery_number" class="block text-sm font-medium text-gray-700">Delivery Number</label>
                        <input type="text" name="multi_pickup[${currentIndex}][delivery_number]" id="multi_pickup_${currentIndex}_delivery_number" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <div class="text-red-600 text-sm mt-1 hidden" id="error_multi_pickup_${currentIndex}_delivery_number"></div>
                    </div>
                    <div class="w-full md:w-1/12 px-2 flex items-end justify-center">
                        <button type="button" class="inline-flex items-center gap-2 rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700" onclick="deleteRow(${currentIndex})">
                            <i class="fas fa-trash text-xs"></i>
                            Delete
                        </button>
                    </div>
                </div>
            `;
            const secondRow = document.getElementById('multi-pickup-row-1');
            document.getElementById('multi-pickup-items').insertBefore(newRow, secondRow.nextSibling);
            initializeSearchableSelects(newRow);
            currentIndex++;
        });

        document.querySelector('.add-more-drop').addEventListener('click', function() {
            const newRow = document.createElement('div');
            newRow.classList.add('row');
            newRow.id = `multi-drop-row-${multiDropIndex}`;
            newRow.innerHTML = `
                <div class="flex flex-wrap -mx-2 mb-4">
                    <div class="w-full md:w-2/12 px-2 mb-4 md:mb-0">
                        <label for="site_name_${multiDropIndex}" class="block text-sm font-medium text-gray-700">Site Name</label>
                        <input type="text" name="multi_drop[${multiDropIndex}][site_name]" id="site_name_${multiDropIndex}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <div class="text-red-600 text-sm mt-1 hidden" id="error_multi_drop_${multiDropIndex}_site_name"></div>
                    </div>
                    <div class="w-full md:w-3/12 px-2 mb-4 md:mb-0">
                        <label for="delivery_number_${multiDropIndex}" class="block text-sm font-medium text-gray-700">Delivery Number</label>
                        <input type="text" name="multi_drop[${multiDropIndex}][delivery_number]" id="delivery_number_${multiDropIndex}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <div class="text-red-600 text-sm mt-1 hidden" id="error_multi_drop_${multiDropIndex}_delivery_number"></div>
                    </div>
                    <div class="w-full md:w-3/12 px-2 mb-4 md:mb-0">
                        <label for="delivery_address_${multiDropIndex}" class="block text-sm font-medium text-gray-700">Delivery Address</label>
                        <textarea name="multi_drop[${multiDropIndex}][delivery_address]" id="delivery_address_${multiDropIndex}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                        <div class="text-red-600 text-sm mt-1 hidden" id="error_multi_drop_${multiDropIndex}_delivery_address"></div>
                    </div>
                    <div class="w-full md:w-1/12 px-2 flex items-center justify-center">
                        <button type="button" class="inline-flex items-center gap-2 rounded-md bg-red-600 px-3 py-2 text-white hover:bg-red-700" onclick="deleteDropRow(${multiDropIndex})">
                            <i class="fas fa-trash text-xs"></i>
                            Delete
                        </button>
                    </div>
                </div>
            `;
            const secondRow = document.getElementById('multi-drop-row-1');
            document.getElementById('multi-drop-items').insertBefore(newRow, secondRow.nextSibling);
            multiDropIndex++;
        });

        areaSelect.addEventListener('change', function () {
            const areaId = this.value;
            if (!areaId) {
                regionSelect.disabled = false;
                regionSelect.innerHTML = '<option value="">Select Province</option>';
                refreshSearchableSelect(regionSelect);
                return;
            }

            regionSelect.disabled = true;
            regionSelect.innerHTML = '<option value="">Loading provinces...</option>';
            refreshSearchableSelect(regionSelect);

            fetch(`/regions/by-area/${areaId}`)
                .then(response => response.json())
                .then(data => {
                    regionSelect.disabled = false;
                    regionSelect.innerHTML = '<option value="">Select Province</option>';
                    data.forEach(region => {
                        const option = document.createElement('option');
                        option.value = region.id;
                        option.textContent = region.province;
                        regionSelect.appendChild(option);
                    });
                    refreshSearchableSelect(regionSelect);
                })
                .catch(() => {
                    regionSelect.disabled = false;
                    regionSelect.innerHTML = '<option value="">Select Province</option>';
                    refreshSearchableSelect(regionSelect);
                    alert('Unable to fetch regions.');
                });
        });

        initializeSearchableSelects();
        handleDeliveryTypeChange(false);
    </script>
@endsection
