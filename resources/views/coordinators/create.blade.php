@extends('layouts.app')

@section('content')
<style>
    #coordinator-create-form {
        --coordinator-field-height: 3.25rem;
        --coordinator-field-radius: 0.9rem;
        --coordinator-field-font-size: 0.95rem;
        --coordinator-field-padding-y: 0.75rem;
        --coordinator-field-padding-x: 0.9rem;
        --coordinator-label-font-size: 0.95rem;
        --coordinator-label-color: #334155;
    }

    #coordinator-create-form select.searchable-select-source {
        position: absolute;
        left: -9999px;
        opacity: 0;
        pointer-events: none;
    }

    #coordinator-create-form .searchable-select-panel::-webkit-scrollbar {
        width: 6px;
    }

    #coordinator-create-form .searchable-select-panel::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 9999px;
    }

    #coordinator-create-form [data-searchable-select-wrapper] {
        position: relative;
        z-index: 1;
    }

    #coordinator-create-form [data-searchable-select-wrapper][data-open="true"] {
        z-index: 90;
    }

    #coordinator-create-form label {
        margin-bottom: 0.5rem;
        display: block;
        font-size: var(--coordinator-label-font-size);
        font-weight: 600;
        color: var(--coordinator-label-color);
    }

    #coordinator-create-form input[type="text"],
    #coordinator-create-form input[type="date"],
    #coordinator-create-form input[type="number"],
    #coordinator-create-form input[type="email"],
    #coordinator-create-form input[type="time"],
    #coordinator-create-form select,
    #coordinator-create-form textarea {
        min-height: var(--coordinator-field-height);
        width: 100%;
        border-radius: var(--coordinator-field-radius);
        border: 1px solid #cbd5e1;
        background: #fff;
        padding: var(--coordinator-field-padding-y) var(--coordinator-field-padding-x);
        font-size: var(--coordinator-field-font-size);
        line-height: 1.45;
        color: #1e293b;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
    }

    #coordinator-create-form textarea {
        min-height: 6.5rem;
    }

    #coordinator-create-form .searchable-select-trigger {
        min-height: var(--coordinator-field-height);
        border-radius: var(--coordinator-field-radius);
        padding: var(--coordinator-field-padding-y) var(--coordinator-field-padding-x);
        font-size: var(--coordinator-field-font-size);
        line-height: 1.45;
    }

    #coordinator-create-form .searchable-select-trigger-icon {
        height: 2rem;
        width: 2rem;
        border-radius: 9999px;
    }

    #coordinator-create-form .searchable-select-panel input {
        min-height: 2.75rem;
        border-radius: 0.85rem;
        font-size: var(--coordinator-field-font-size);
    }

    #coordinator-create-form .searchable-select-option {
        border-radius: 0.85rem;
        padding: 0.6rem 0.75rem;
        font-size: var(--coordinator-field-font-size);
        line-height: 1.4;
    }

    #coordinator-create-form .searchable-select-option-icon {
        height: 1.9rem;
        width: 1.9rem;
        border-radius: 9999px;
    }

    #coordinator-create-form .text-red-600.text-sm.mt-1,
    #coordinator-create-form .text-red-600.text-sm {
        font-size: 0.875rem !important;
        line-height: 1.4;
    }

    #coordinator-create-form input:focus,
    #coordinator-create-form select:focus,
    #coordinator-create-form textarea:focus {
        border-color: #4f46e5;
        outline: none;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12);
    }

    #coordinator-create-form .section-card {
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: 1.75rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    #coordinator-create-form .pill-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        min-height: 3rem;
        border-radius: 1rem;
        padding: 0.75rem 1.1rem;
        font-size: 0.95rem;
        font-weight: 600;
        transition: all .2s ease;
    }

    #coordinator-create-form #regular-fields,
    #coordinator-create-form #multi-drop-fields,
    #coordinator-create-form #multi-pickup-fields {
        border: 1px solid #e2e8f0 !important;
        background: #ffffff !important;
        border-radius: 1.75rem !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        padding: 1.5rem !important;
    }
</style>

<div class="mx-auto max-w-7xl space-y-6 py-8">
    <div class="section-card px-6 py-6 sm:px-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-violet-50 px-4 py-2 text-sm font-semibold text-violet-700 ring-1 ring-violet-100">
                    <i class="fas fa-route text-sm"></i>
                    Coordinator Request
                </div>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900">Create Delivery Request</h1>
                <p class="mt-2 max-w-3xl text-base leading-7 text-slate-500">
                    Build a delivery request with cleaner sections, searchable pickers, and aligned fields for regular, multi-drop, or multi pick-up workflows.
                </p>
            </div>
            <a href="{{ route('coordinators.index') }}"
               class="inline-flex h-12 items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-6 text-base font-semibold text-slate-700 shadow-sm transition hover:border-slate-400 hover:text-slate-900">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                    <i class="fas fa-arrow-left text-xs"></i>
                </span>
                Back to Requests
            </a>
        </div>
    </div>

    <form action="{{ route('coordinators.store') }}" method="POST" id="coordinator-create-form" class="space-y-6" novalidate>
        @csrf
        <div class="section-card p-6">
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
                                required class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
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
                                required class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
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
                                    required class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                @error('multi_drop.0.site_name')
                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="w-full md:w-1/4 px-2 mb-4">
                                <label for="delivery_number_0" class="block text-sm font-medium text-gray-700 mb-1">Delivery Number</label>
                                <input type="text" name="multi_drop[0][delivery_number]" id="delivery_number_0"
                                    required class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                @error('multi_drop.0.delivery_number')
                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="w-full md:w-1/4 px-2 mb-4">
                                <label for="delivery_address_0" class="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
                                <textarea name="multi_drop[0][delivery_address]" id="delivery_address_0" rows="2"
                                    required class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"></textarea>
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
                                    required class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                @error('multi_drop.1.site_name')
                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="w-full md:w-1/4 px-2 mb-4">
                                <label for="delivery_number_1" class="block text-sm font-medium text-gray-700 mb-1">Delivery Number</label>
                                <input type="text" name="multi_drop[1][delivery_number]" id="delivery_number_1"
                                    required class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                @error('multi_drop.1.delivery_number')
                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="w-full md:w-1/4 px-2 mb-4">
                                <label for="delivery_address_1" class="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
                                <textarea name="multi_drop[1][delivery_address]" id="delivery_address_1" rows="2"
                                    required class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"></textarea>
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
                                required class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            @error('multi_pickup.0.site_name')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="w-full md:w-1/3 px-2">
                            <label for="delivery_address_0" class="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
                            <textarea name="multi_pickup[0][delivery_address]" id="delivery_address_0" rows="2"
                                required class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"></textarea>
                            @error('multi_pickup.0.delivery_address')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="w-full md:w-1/4 px-2">
                            <label for="add_on_rate_0" class="block text-sm font-medium text-gray-700 mb-1">Add-on Rate</label>
                            <select name="multi_pickup[0][add_on_rate]" id="add_on_rate_0"
                                required class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
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
                                required class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
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
                                required class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
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
                                required class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
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
                                required class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            @error('multi_pickup.1.delivery_number')
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <div class="section-card p-6">
        <div class="flex flex-col gap-4 border-t border-slate-200 pt-4 md:flex-row md:items-center md:justify-between">
            <p class="text-base text-slate-500">Double-check the delivery type and line-item details before creating the request.</p>
            <div class="flex flex-wrap items-center justify-end gap-3">
                    <a href="{{ route('coordinators.index') }}"
                       class="inline-flex h-12 min-w-[160px] items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-5 text-base font-semibold text-slate-600 shadow-sm transition hover:border-slate-400 hover:text-slate-900">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                            <i class="fas fa-xmark text-xs"></i>
                        </span>
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex h-12 min-w-[240px] items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-6 text-base font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/15">
                            <i class="fas fa-floppy-disk text-xs"></i>
                        </span>
                        Create Delivery Request
                    </button>
            </div>
        </div>
    </div>
    </form>

    <div id="coordinator-create-validation-modal" class="fixed inset-0 z-[120] hidden items-center justify-center bg-slate-900/50 p-4">
        <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl">
            <h2 class="text-xl font-bold text-slate-900">Missing Required Fields</h2>
            <p class="mt-2 text-sm text-slate-500">Please complete the required fields before saving this delivery request.</p>
            <ul id="coordinator-create-validation-list" class="mt-4 list-disc space-y-1 pl-5 text-sm text-rose-600"></ul>
            <div class="mt-6 flex justify-end">
                <button type="button" id="coordinator-create-validation-close" class="inline-flex h-11 items-center justify-center rounded-2xl bg-indigo-600 px-5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

    <script>
        let currentIndex = 2;
        let multiDropIndex = 2;
        const form = document.getElementById('coordinator-create-form');
        const multiPickupWarehouseOptions = document.getElementById('multi_pickup_0_warehouse_id')?.innerHTML ?? '';
        const validationModal = document.getElementById('coordinator-create-validation-modal');
        const validationList = document.getElementById('coordinator-create-validation-list');

        function closeAllSearchableSelects() {
            document.querySelectorAll('#coordinator-create-form [data-searchable-select-wrapper]').forEach((wrapper) => {
                wrapper.dataset.open = 'false';
                wrapper.querySelector('[data-searchable-select-panel]')?.classList.add('hidden');
            });
        }

        function showValidationModal(messages) {
            if (!validationModal || !validationList) {
                return;
            }

            validationList.innerHTML = '';
            messages.forEach((message) => {
                const item = document.createElement('li');
                item.textContent = message;
                validationList.appendChild(item);
            });

            validationModal.classList.remove('hidden');
            validationModal.classList.add('flex');
        }

        function hideValidationModal() {
            if (!validationModal) {
                return;
            }

            validationModal.classList.add('hidden');
            validationModal.classList.remove('flex');
        }

        function getFieldLabel(field) {
            const fieldId = field.getAttribute('id');
            const label = fieldId ? form.querySelector(`label[for="${fieldId}"]`) : null;

            return (label?.textContent || field.name || 'Field').replace(/\s+/g, ' ').trim();
        }

        function getVisibleRequiredFields() {
            return Array.from(form.querySelectorAll('[required]')).filter((field) => {
                if (field.disabled) {
                    return false;
                }

                const section = field.closest('#regular-fields, #multi-drop-fields, #multi-pickup-fields');

                if (!section) {
                    return true;
                }

                return section.style.display !== 'none' && !section.classList.contains('hidden');
            });
        }

        function validateCoordinatorCreateForm() {
            const invalidFields = getVisibleRequiredFields().filter((field) => !field.value || `${field.value}`.trim() === '');

            if (invalidFields.length === 0) {
                return true;
            }

            const uniqueMessages = [...new Set(invalidFields.map((field) => `${getFieldLabel(field)} is required.`))];
            showValidationModal(uniqueMessages);
            invalidFields[0].focus();
            return false;
        }

        function isEmptyMultiDropRow(row) {
            const siteName = row.querySelector('input[name*="[site_name]"]')?.value?.trim() || '';
            const deliveryNumber = row.querySelector('input[name*="[delivery_number]"]')?.value?.trim() || '';
            const deliveryAddress = row.querySelector('textarea[name*="[delivery_address]"]')?.value?.trim() || '';

            return !siteName && !deliveryNumber && !deliveryAddress;
        }

        function isEmptyMultiPickupRow(row) {
            const deliveryNumber = row.querySelector('input[name*="[delivery_number]"]')?.value?.trim() || '';
            return !deliveryNumber;
        }

        function syncCoordinatorSectionState() {
            ['regular-fields', 'multi-drop-fields', 'multi-pickup-fields'].forEach((sectionId) => {
                const section = document.getElementById(sectionId);
                if (!section) {
                    return;
                }

                const isVisible = section.style.display !== 'none' && !section.classList.contains('hidden');
                section.querySelectorAll('input, select, textarea').forEach((field) => {
                    field.disabled = !isVisible;
                });
            });
        }

        function pruneEmptyCoordinatorRows() {
            document.querySelectorAll('#multi-drop-items .multi-drop-row').forEach((row, index) => {
                if (index === 0) {
                    return;
                }

                if (isEmptyMultiDropRow(row)) {
                    row.remove();
                }
            });

            document.querySelectorAll('#multi-pickup-items .multi-pickup-row').forEach((row, index) => {
                if (index === 0) {
                    return;
                }

                if (isEmptyMultiPickupRow(row)) {
                    row.remove();
                }
            });
        }

        function mountSearchableSelect(select, config = {}) {
            if (!select || select.dataset.searchableMounted === 'true') {
                return;
            }

            select.dataset.searchableMounted = 'true';
            select.classList.add('searchable-select-source');

            const placeholder = config.placeholder || select.options[0]?.textContent?.trim() || 'Select option';
            const icon = config.icon || 'fa-circle-dot';

            const wrapper = document.createElement('div');
            wrapper.dataset.searchableSelectWrapper = 'true';
            wrapper.dataset.open = 'false';
            wrapper.className = 'relative';
            wrapper.innerHTML = `
                <button type="button" class="searchable-select-trigger flex w-full items-center gap-3 border border-slate-300 bg-white text-left text-slate-700 shadow-sm transition hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <span class="searchable-select-trigger-icon inline-flex shrink-0 items-center justify-center bg-indigo-50 text-indigo-600">
                        <i class="fas ${icon} text-sm"></i>
                    </span>
                    <span class="min-w-0 flex-1 truncate" data-searchable-select-label></span>
                    <span class="text-slate-400"><i class="fas fa-chevron-down text-xs"></i></span>
                </button>
                <div data-searchable-select-panel class="searchable-select-panel absolute left-0 right-0 z-[95] mt-2 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/10">
                    <div class="border-b border-slate-200 p-3">
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <i class="fas fa-magnifying-glass text-xs"></i>
                            </span>
                            <input type="text" data-searchable-select-input placeholder="Search option..." class="w-full border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-slate-700 outline-none transition focus:border-indigo-300 focus:bg-white focus:ring-2 focus:ring-indigo-100">
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

            function updateLabel() {
                const selectedOption = select.options[select.selectedIndex];
                label.textContent = selectedOption && selectedOption.value !== '' ? selectedOption.textContent.trim() : placeholder;
            }

            function renderOptions(term = '') {
                const normalizedTerm = term.trim().toLowerCase();
                list.innerHTML = '';
                let visibleCount = 0;

                Array.from(select.options).forEach((option) => {
                    if (!option.value && normalizedTerm) {
                        return;
                    }

                    if (normalizedTerm && !option.textContent.toLowerCase().includes(normalizedTerm)) {
                        return;
                    }

                    visibleCount += 1;
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = `searchable-select-option flex w-full items-center gap-3 text-left transition ${option.selected ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-100'}`;
                    button.innerHTML = `
                        <span class="searchable-select-option-icon inline-flex shrink-0 items-center justify-center ${option.selected ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500'}">
                            <i class="fas ${icon} text-xs"></i>
                        </span>
                        <span class="min-w-0 flex-1 truncate">${option.textContent.trim()}</span>
                        ${option.selected ? '<i class="fas fa-check text-xs text-indigo-500"></i>' : ''}
                    `;

                    button.addEventListener('click', () => {
                        select.value = option.value;
                        updateLabel();
                        renderOptions(searchInput.value);
                        panel.classList.add('hidden');
                        wrapper.dataset.open = 'false';
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    });

                    list.appendChild(button);
                });

                emptyState.classList.toggle('hidden', visibleCount !== 0);
            }

            trigger.addEventListener('click', () => {
                const shouldOpen = wrapper.dataset.open !== 'true';
                closeAllSearchableSelects();
                wrapper.dataset.open = shouldOpen ? 'true' : 'false';
                panel.classList.toggle('hidden', !shouldOpen);

                if (shouldOpen) {
                    searchInput.value = '';
                    renderOptions();
                    setTimeout(() => searchInput.focus(), 0);
                }
            });

            searchInput.addEventListener('input', () => renderOptions(searchInput.value));
            updateLabel();
            renderOptions();
        }

        function mountCoordinatorSearchableSelects(root = document) {
            const configs = {
                truck_type_id: { placeholder: 'Select Truck Type', icon: 'fa-truck-ramp-box' },
                company_id: { placeholder: 'Select Company', icon: 'fa-building' },
                expense_type_id: { placeholder: 'Select Expense Type', icon: 'fa-receipt' },
                area_id: { placeholder: 'Select Region', icon: 'fa-map-location-dot' },
                region_id: { placeholder: 'Select Province', icon: 'fa-map-pin' },
                customer_id: { placeholder: 'Select Customer', icon: 'fa-user-group' },
                delivery_status: { placeholder: 'Select Status', icon: 'fa-truck-fast' },
                delivery_type: { placeholder: 'Select Delivery Type', icon: 'fa-route' },
                regular_warehouse_id: { placeholder: 'Select Warehouse', icon: 'fa-warehouse' },
                multi_drop_0_warehouse_id: { placeholder: 'Select Warehouse', icon: 'fa-warehouse' },
                add_on_rate_0: { placeholder: 'Select Add-on Rate', icon: 'fa-plus-circle' },
                multi_pickup_0_warehouse_id: { placeholder: 'Select Warehouse', icon: 'fa-warehouse' },
                multi_pickup_1_warehouse_id: { placeholder: 'Select Warehouse', icon: 'fa-warehouse' },
            };

            Object.entries(configs).forEach(([id, config]) => {
                const select = root.querySelector(`#${id}`);
                if (select) {
                    mountSearchableSelect(select, config);
                }
            });

            root.querySelectorAll('select[id^="multi_pickup_"][id$="_warehouse_id"]').forEach((select) => {
                mountSearchableSelect(select, { placeholder: 'Select Warehouse', icon: 'fa-warehouse' });
            });

            root.querySelectorAll('select[id^="multi_drop_"][id$="_warehouse_id"]').forEach((select) => {
                mountSearchableSelect(select, { placeholder: 'Select Warehouse', icon: 'fa-warehouse' });
            });

            root.querySelectorAll('select[id^="add_on_rate_"]').forEach((select) => {
                mountSearchableSelect(select, { placeholder: 'Select Add-on Rate', icon: 'fa-plus-circle' });
            });
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
            document.getElementById('regular-fields').classList.add('hidden');
            document.getElementById('multi-drop-fields').classList.add('hidden');
            document.getElementById('multi-pickup-fields').classList.add('hidden');

            clearFormFields('regular-fields');
            clearFormFields('multi-drop-fields');
            clearFormFields('multi-pickup-fields');
            
            // Show fields based on selected delivery type
            if (deliveryType === 'Regular') {
                document.getElementById('regular-fields').style.display = 'block';
                document.getElementById('regular-fields').classList.remove('hidden');
            } 
            else if (deliveryType === 'Multi-Drop') {
                document.getElementById('multi-drop-fields').style.display = 'block';
                document.getElementById('multi-drop-fields').classList.remove('hidden');
            } 
            else if (deliveryType === 'Multi Pick-Up') {
                document.getElementById('multi-pickup-fields').style.display = 'block';
                document.getElementById('multi-pickup-fields').classList.remove('hidden');
            }

            syncCoordinatorSectionState();
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
                        <select name="multi_pickup[${currentIndex}][warehouse_id]" id="multi_pickup_${currentIndex}_warehouse_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            ${multiPickupWarehouseOptions}
                        </select>
                        <!-- Error placeholder -->
                        <div class="text-red-600 text-sm mt-1 hidden" id="error_multi_pickup_${currentIndex}_warehouse_id"></div>
                    </div>

                    <div class="w-full md:w-1/3 px-2 mb-4 md:mb-0">
                        <label for="multi_pickup_${currentIndex}_delivery_number" class="block text-sm font-medium text-gray-700">Delivery Number</label>
                        <input type="text" name="multi_pickup[${currentIndex}][delivery_number]" id="multi_pickup_${currentIndex}_delivery_number" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <div class="text-red-600 text-sm mt-1 hidden" id="error_multi_pickup_${currentIndex}_delivery_number"></div>
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
            mountCoordinatorSearchableSelects(newRow);

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
                        <input type="text" name="multi_drop[${multiDropIndex}][site_name]" id="site_name_${multiDropIndex}" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <div class="text-red-600 text-sm mt-1 hidden" id="error_multi_drop_${multiDropIndex}_site_name"></div>
                    </div>

                    <div class="w-full md:w-3/12 px-2 mb-4 md:mb-0">
                        <label for="delivery_number_${multiDropIndex}" class="block text-sm font-medium text-gray-700">Delivery Number</label>
                        <input type="text" name="multi_drop[${multiDropIndex}][delivery_number]" id="delivery_number_${multiDropIndex}" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <div class="text-red-600 text-sm mt-1 hidden" id="error_multi_drop_${multiDropIndex}_delivery_number"></div>
                    </div>

                    <div class="w-full md:w-3/12 px-2 mb-4 md:mb-0">
                        <label for="delivery_address_${multiDropIndex}" class="block text-sm font-medium text-gray-700">Delivery Address</label>
                        <textarea name="multi_drop[${multiDropIndex}][delivery_address]" id="delivery_address_${multiDropIndex}" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                        <div class="text-red-600 text-sm mt-1 hidden" id="error_multi_drop_${multiDropIndex}_delivery_address"></div>
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
                regionSelect.disabled = true;
                regionSelect.innerHTML = '<option value="">Loading provinces...</option>';
                regionSelect.dataset.searchableMounted = 'false';
                regionSelect.nextElementSibling?.remove();
                mountSearchableSelect(regionSelect, { placeholder: 'Loading provinces...', icon: 'fa-map-pin' });

                fetch(`/regions/by-area/${areaId}`)
                    .then(response => response.json())
                    .then(data => {
                        regionSelect.disabled = false;
                        regionSelect.innerHTML = '<option value="">Select Province</option>';
                        data.forEach(region => {
                            const option = document.createElement('option');
                            option.value = region.id;
                            option.text = region.province;
                            regionSelect.appendChild(option);
                        });
                        regionSelect.dataset.searchableMounted = 'false';
                        regionSelect.nextElementSibling?.remove();
                        mountSearchableSelect(regionSelect, { placeholder: 'Select Province', icon: 'fa-map-pin' });
                    })
                    .catch(() => {
                        regionSelect.disabled = false;
                        regionSelect.innerHTML = '<option value="">Select Province</option>';
                        regionSelect.dataset.searchableMounted = 'false';
                        regionSelect.nextElementSibling?.remove();
                        mountSearchableSelect(regionSelect, { placeholder: 'Select Province', icon: 'fa-map-pin' });
                        alert('Unable to fetch regions.');
                    });
            } else {
                regionSelect.disabled = false;
                regionSelect.innerHTML = '<option value="">Select Province</option>';
                regionSelect.dataset.searchableMounted = 'false';
                regionSelect.nextElementSibling?.remove();
                mountSearchableSelect(regionSelect, { placeholder: 'Select Province', icon: 'fa-map-pin' });
            }
        });

        mountCoordinatorSearchableSelects();
        syncCoordinatorSectionState();

        const dynamicObserver = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) {
                        mountCoordinatorSearchableSelects(node);
                    }
                });
            });
        });

        ['multi-drop-items', 'multi-pickup-items', 'regular-fields'].forEach((id) => {
            const target = document.getElementById(id);
            if (target) {
                dynamicObserver.observe(target, { childList: true, subtree: true });
            }
        });

        document.addEventListener('click', (event) => {
            if (!event.target.closest('[data-searchable-select-wrapper]')) {
                closeAllSearchableSelects();
            }
        });

        document.getElementById('coordinator-create-validation-close')?.addEventListener('click', hideValidationModal);
        validationModal?.addEventListener('click', (event) => {
            if (event.target === validationModal) {
                hideValidationModal();
            }
        });

        form.addEventListener('submit', (event) => {
            pruneEmptyCoordinatorRows();
            syncCoordinatorSectionState();

            if (!validateCoordinatorCreateForm()) {
                event.preventDefault();
            }
        });

    </script>
@endsection
