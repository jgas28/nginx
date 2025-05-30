@extends('layouts.app')

@section('content')
    <form action="{{ route('coordinators.store') }}" method="POST">
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
                    <input type="text" name="delivery_rate" id="delivery_rate" required
                        class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
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
                <div class="w-full md:w-1/3 px-2 mb-4">
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

                <!-- Delivery Status -->
                <div class="w-full md:w-1/3 px-2 mb-4">
                    <label for="regular_delivery_status" class="block text-sm font-medium text-gray-700 mb-1">Delivery Status</label>
                    <select name="regular[0][delivery_status]" id="regular_delivery_status"
                        class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @foreach($deliveryStatuses as $deliveryStatus)
                            <option value="{{ $deliveryStatus->id }}">{{ $deliveryStatus->status_name }}</option>
                        @endforeach
                    </select>
                    @error('regular.0.delivery_status')
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

                            <div class="w-full md:w-1/4 px-2 mb-4">
                                <label for="delivery_status_0" class="block text-sm font-medium text-gray-700 mb-1">Delivery Status</label>
                                <select name="multi_drop[0][delivery_status]" id="delivery_status_0"
                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    @foreach($deliveryStatuses as $deliveryStatus)
                                        <option value="{{ $deliveryStatus->id }}">{{ $deliveryStatus->status_name }}</option>
                                    @endforeach
                                </select>
                                @error('multi_drop.0.delivery_status')
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

                            <div class="w-full md:w-1/4 px-2 mb-4">
                                <label for="delivery_status_1" class="block text-sm font-medium text-gray-700 mb-1">Delivery Status</label>
                                <select name="multi_drop[1][delivery_status]" id="delivery_status_1"
                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    @foreach($deliveryStatuses as $deliveryStatus)
                                        <option value="{{ $deliveryStatus->id }}">{{ $deliveryStatus->status_name }}</option>
                                    @endforeach
                                </select>
                                @error('multi_drop.1.delivery_status')
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

                        <div class="w-full md:w-1/4 px-2 mb-4">
                            <label for="multi_pickup_0_delivery_status" class="block text-sm font-medium text-gray-700 mb-1">Delivery Status</label>
                            <select name="multi_pickup[0][delivery_status]" id="multi_pickup_0_delivery_status"
                                class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                @foreach($deliveryStatuses as $deliveryStatus)
                                    <option value="{{ $deliveryStatus->id }}">{{ $deliveryStatus->status_name }}</option>
                                @endforeach
                            </select>
                            @error('multi_pickup.0.delivery_status')
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

                        <div class="w-full md:w-1/4 px-2 mb-4">
                            <label for="multi_pickup_1_delivery_status" class="block text-sm font-medium text-gray-700 mb-1">Delivery Status</label>
                            <select name="multi_pickup[1][delivery_status]" id="multi_pickup_1_delivery_status"
                                class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                @foreach($deliveryStatuses as $deliveryStatus)
                                    <option value="{{ $deliveryStatus->id }}">{{ $deliveryStatus->status_name }}</option>
                                @endforeach
                            </select>
                            @error('multi_pickup.1.delivery_status')
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

    <script>
        let currentIndex = 2;
        let multiDropIndex = 2;

        // JavaScript code to handle dynamic field visibility and adding new items
        document.getElementById('delivery_type').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const deliveryType = selectedOption.getAttribute('data-type');
            
            console.log('Selected Delivery Type:', deliveryType); // Log the selected type for debugging
            
            // Hide all additional fields
            document.getElementById('regular-fields').style.display = 'none';
            document.getElementById('multi-drop-fields').style.display = 'none';
            document.getElementById('multi-pickup-fields').style.display = 'none';

            clearFormFields('regular-fields');
            clearFormFields('multi-drop-fields');
            clearFormFields('multi-pickup-fields');
            
            // Show fields based on selected delivery type
            if (deliveryType === 'Regular') {
                document.getElementById('regular-fields').style.display = 'block';
            } 
            else if (deliveryType === 'Multi-Drop') {
                document.getElementById('multi-drop-fields').style.display = 'block';
            } 
            else if (deliveryType === 'Multi Pick-Up') {
                document.getElementById('multi-pickup-fields').style.display = 'block';
            }
        });

        function clearFormFields(sectionId) {
            const section = document.getElementById(sectionId);
            if (section) {
                const inputs = section.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    if (input.type === 'checkbox' || input.type === 'radio') {
                        input.checked = false;
                    } else {
                        input.value = '';
                    }
                });
            }
        }

        // Function to handle adding more fields for multi-pickup
        document.querySelector('.add-more-pickup').addEventListener('click', function() {
            const newRow = document.createElement('div');
            newRow.classList.add('multi-pickup-row');
            newRow.id = `multi-pickup-row-${currentIndex}`;

            newRow.innerHTML = `
                <div class="flex flex-wrap -mx-2 mb-4">
                    <div class="w-full md:w-1/6 px-2 mb-4 md:mb-0">
                        <label for="multi_pickup_${currentIndex}_warehouse_id" class="block text-sm font-medium text-gray-700">Warehouse</label>
                        <select name="multi_pickup[${currentIndex}][warehouse_id]" id="multi_pickup_${currentIndex}_warehouse_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <!-- JS-rendered options -->
                        </select>
                        <!-- Error placeholder -->
                        <div class="text-red-600 text-sm mt-1 hidden" id="error_multi_pickup_${currentIndex}_warehouse_id"></div>
                    </div>

                    <div class="w-full md:w-1/3 px-2 mb-4 md:mb-0">
                        <label for="multi_pickup_${currentIndex}_delivery_number" class="block text-sm font-medium text-gray-700">Delivery Number</label>
                        <input type="text" name="multi_pickup[${currentIndex}][delivery_number]" id="multi_pickup_${currentIndex}_delivery_number" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <div class="text-red-600 text-sm mt-1 hidden" id="error_multi_pickup_${currentIndex}_delivery_number"></div>
                    </div>

                    <div class="w-full md:w-1/4 px-2 mb-4 md:mb-0">
                        <label for="multi_pickup_${currentIndex}_delivery_status" class="block text-sm font-medium text-gray-700">Delivery Status</label>
                        <select name="multi_pickup[${currentIndex}][delivery_status]" id="multi_pickup_${currentIndex}_delivery_status" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <!-- JS-rendered options -->
                        </select>
                        <div class="text-red-600 text-sm mt-1 hidden" id="error_multi_pickup_${currentIndex}_delivery_status"></div>
                    </div>

                    <div class="w-full md:w-1/12 px-2 flex items-end justify-center">
                        <button type="button" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700" onclick="deleteRow(${currentIndex})">Delete</button>
                    </div>
                </div>
            `;

            // Get the reference to the second row element (multi-pickup-row-1)
            const secondRow = document.getElementById('multi-pickup-row-1');
            
            // Insert the new row after the second row
            document.getElementById('multi-pickup-items').insertBefore(newRow, secondRow.nextSibling);

            // Increment the index for the next row
            currentIndex++;
        });

        // Function to delete a row and decrement indices
        function deleteRow(index) {
            const row = document.getElementById(`multi-pickup-row-${index}`);
            row.remove();

            // Decrement the index for subsequent rows
            for (let i = index + 1; i < currentIndex; i++) {
                const rowToShift = document.getElementById(`multi-pickup-row-${i}`);
                rowToShift.id = `multi-pickup-row-${i - 1}`; // Shift the ID of the row
                // Update the name and ID attributes for all form fields
                rowToShift.querySelectorAll('[name]').forEach(field => {
                    const name = field.name;
                    const newName = name.replace(`[${i}]`, `[${i - 1}]`);
                    field.name = newName;
                    field.id = newName;
                });
            }

            // Decrement the currentIndex as we have removed a row
            currentIndex--;
        }

        // JavaScript code to handle dynamic field visibility and adding new items
        document.getElementById('delivery_type').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const deliveryType = selectedOption.getAttribute('data-type');
            
            console.log('Selected Delivery Type:', deliveryType); // Log the selected type for debugging
            
            // Hide all additional fields
            document.getElementById('regular-fields').style.display = 'none';
            document.getElementById('multi-drop-fields').style.display = 'none';
            document.getElementById('multi-pickup-fields').style.display = 'none';
            
            // Show fields based on selected delivery type
            if (deliveryType === 'Regular') {
                document.getElementById('regular-fields').style.display = 'block';
            } 
            else if (deliveryType === 'Multi-Drop') {
                document.getElementById('multi-drop-fields').style.display = 'block';
            } 
            else if (deliveryType === 'Multi Pick-Up') {
                document.getElementById('multi-pickup-fields').style.display = 'block';
            }
        });

        // Function to handle adding more fields for multi-drop
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

                    <div class="w-full md:w-3/12 px-2 mb-4 md:mb-0">
                        <label for="delivery_status_${multiDropIndex}" class="block text-sm font-medium text-gray-700">Delivery Status</label>
                        <select name="multi_drop[${multiDropIndex}][delivery_status]" id="delivery_status_${multiDropIndex}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <!-- Delivery status options rendered by server or JS -->
                        </select>
                        <div class="text-red-600 text-sm mt-1 hidden" id="error_multi_drop_${multiDropIndex}_delivery_status"></div>
                    </div>

                    <div class="w-full md:w-1/12 px-2 flex items-center justify-center">
                        <button type="button" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded" onclick="deleteDropRow(${multiDropIndex})">Delete</button>
                    </div>
                    </div>
            `;

            // Get the reference to the second row element (multi-drop-row-1)
            const secondRow = document.getElementById('multi-drop-row-1');
            
            // Insert the new row after the second row
            document.getElementById('multi-drop-items').insertBefore(newRow, secondRow.nextSibling);


            // Increment the index for the next row
            multiDropIndex++;
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

        document.getElementById('area_id').addEventListener('change', function () {
            const areaId = this.value;
            const regionSelect = document.getElementById('region_id');

            if (areaId) {
                fetch(`/regions/by-area/${areaId}`)
                    .then(response => response.json())
                    .then(data => {
                        regionSelect.innerHTML = '<option value="">Select Province</option>';
                        data.forEach(region => {
                            const option = document.createElement('option');
                            option.value = region.id;
                            option.text = region.province;
                            regionSelect.appendChild(option);
                        });
                    })
                    .catch(() => alert('Unable to fetch regions.'));
            } else {
                regionSelect.innerHTML = '<option value="">Select Province</option>';
            }
        });


    </script>
@endsection
