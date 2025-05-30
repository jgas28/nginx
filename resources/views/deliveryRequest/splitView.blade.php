@extends('layouts.app')

@section('content')
    <!-- <h1>Edit Delivery Request</h1> -->

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

        <!-- Multi Drop Fields -->
        @if(old('delivery_type', $deliveryRequest->delivery_type) === 'Multi-Drop')
        <!-- Multi-Drop Fields (Initially Hidden) -->
        @if($deliveryLineItems->isNotEmpty())
            <div id="multi-drop-fields" >
                <div id="multi-drop-items">
                    <!-- Display Warehouse, Add-on Rate Fields outside the loop (only once) -->
                    <div class="multi-drop-row">
                
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
                                    <div class="border bg-white p-2 mt-3">
                                        <div class="flex flex-wrap -mx-2">
                                            <!-- Site Name Field -->
                                            <div class="w-full md:w-1/6 px-2 mb-4">
                                                <label for="site_name_{{ $index }}" class="block text-sm font-medium text-gray-700">Site Name</label>
                                                <input type="text" name="multi_drop[{{ $index }}][site_name]" id="site_name_{{ $index }}"
                                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring focus:ring-blue-200"
                                                    value="{{ old("multi_drop.{$index}.site_name", $lineItem->site_name) }}">
                                                @error("multi_drop.{$index}.site_name")
                                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <!-- Delivery Number Field -->
                                            <div class="w-full md:w-1/4 px-2 mb-4">
                                                <label for="delivery_number_{{ $index }}" class="block text-sm font-medium text-gray-700">Delivery Number</label>
                                                <input type="text" name="multi_drop[{{ $index }}][delivery_number]" id="delivery_number_{{ $index }}"
                                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring focus:ring-blue-200"
                                                    value="{{ old("multi_drop.{$index}.delivery_number", $lineItem->delivery_number) }}">
                                                @error("multi_drop.{$index}.delivery_number")
                                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <!-- Delivery Address Field -->
                                            <div class="w-full md:w-1/4 px-2 mb-4">
                                                <label for="delivery_address_{{ $index }}" class="block text-sm font-medium text-gray-700">Delivery Address</label>
                                                <textarea name="multi_drop[{{ $index }}][delivery_address]" id="delivery_address_{{ $index }}"
                                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring focus:ring-blue-200">{{ old("multi_drop.{$index}.delivery_address", $lineItem->delivery_address) }}</textarea>
                                                @error("multi_drop.{$index}.delivery_address")
                                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <!-- Delivery Status Field -->
                                            <div class="w-full md:w-1/6 px-2 mb-4">
                                                <label for="delivery_status_{{ $index }}" class="block text-sm font-medium text-gray-700">Delivery Status</label>
                                                <select name="multi_drop[{{ $index }}][delivery_status]" id="delivery_status_{{ $index }}"
                                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring focus:ring-blue-200">
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
                                                <label for="accessorial_type_{{ $index }}" class="block text-sm font-medium text-gray-700">Accessorial Type</label>
                                                <select name="multi_drop[{{ $index }}][accessorial_type]" id="accessorial_type_{{ $index }}"
                                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring focus:ring-blue-200">
                                                    <option value="" {{ old('regular.' . $index . '.accessorial_type', $lineItem->accessorial_type) == '' ? 'selected' : '' }}>
                                                        Select an Accessorial Type
                                                    </option>
                                                    @foreach($accessorialTypes as $accessorialType)
                                                        <option value="{{ $accessorialType->id }}"
                                                            {{ (old('multi_drop.' . $index . '.accessorial_type', $lineItem->accessorial_type) == $accessorialType->id) ? 'selected' : '' }}>
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
                                                <label for="accessorial_rate_{{ $index }}" class="block text-sm font-medium text-gray-700">Accessorial Rate</label>
                                                <input type="text" name="multi_drop[{{ $index }}][accessorial_rate]" id="accessorial_rate_{{ $index }}"
                                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring focus:ring-blue-200"
                                                    value="{{ old('multi_drop.' . $index . '.accessorial_rate', $lineItem->accessorial_rate) }}">
                                                @error('multi_drop.' . $index . '.accessorial_rate')
                                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <input type="hidden" name="multi_drop[{{ $index }}][id]" value="{{ $lineItem->id }}">

                                            <div class="w-full md:w-1/12 px-2 mb-4 flex items-end">
                                                <a href="{{ route('deliveryRequest.split.form', ['id' => $lineItem->id]) }}?request_id={{ $deliveryRequest->id }}"
                                                class="inline-block text-sm px-3 py-1 border border-blue-500 text-blue-500 rounded hover:bg-blue-500 hover:text-white transition">
                                                    Split
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="mt-3">No Data</div>
    @endif

    @elseif(old('delivery_type', $deliveryRequest->delivery_type) === 'Multi Pick-Up')
        @if($deliveryLineItems->isNotEmpty())
            <div id="multi-pickup-fields">
                <div id="multi-pickup-items">
                    <!-- Multi-Pickup Row -->
                    <div class="multi-pickup-row">
                        <div class="mt-2 p-2 border bg-white">
                            <div class="flex flex-wrap -mx-2">
                                <!-- Site Name -->
                                <div class="w-full md:w-1/3 px-2 mb-4">
                                    <label for="multi_pickup_0_site_name" class="block text-sm font-medium text-gray-700">Site Name</label>
                                    <input type="text" name="multi_pickup[0][site_name]" id="multi_pickup_0_site_name"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring focus:ring-blue-200"
                                        value="{{ old('multi_pickup.0.site_name', $deliveryLineItems->first()->site_name) }}">
                                    @error("multi_pickup.0.site_name")
                                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Delivery Address -->
                                <div class="w-full md:w-1/3 px-2 mb-4">
                                    <label for="multi_pickup_0_delivery_address" class="block text-sm font-medium text-gray-700">Delivery Address</label>
                                    <textarea name="multi_pickup[0][delivery_address]" id="multi_pickup_0_delivery_address"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring focus:ring-blue-200"
                                            style="height: 120px;">{{ old('multi_pickup.0.delivery_address', $deliveryLineItems->first()->delivery_address) }}</textarea>
                                    @error("multi_pickup.0.delivery_address")
                                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Add-on Rate -->
                                <div class="w-full md:w-1/6 px-2 mb-4">
                                    <label for="multi_pickup_0_add_on_rate" class="block text-sm font-medium text-gray-700">Add-on Rate</label>
                                    <select name="multi_pickup[0][add_on_rate]" id="multi_pickup_0_add_on_rate"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring focus:ring-blue-200">
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
                            </div>
                        </div>

                        <!-- Loop through each line item -->
                        @php
                            $totalItems = $deliveryLineItems->count();
                        @endphp
                        <input type="hidden" name="multi_pickup_count" value="{{ $totalItems }}">
                        @foreach($deliveryLineItems as $index => $lineItem)
                       
                        <input type="hidden" name="multi_pickup[{{ $index }}][id]" value="{{ $lineItem->id }}">
                            <div class="mt-2 p-2 border bg-white">
                                <div class="flex flex-wrap -mx-2">
                                    <!-- Warehouse -->
                                    <div class="w-full md:w-1/6 px-2 mb-4">
                                        <label for="multi_pickup_{{ $index }}_warehouse_id" class="block text-sm font-medium text-gray-700">Warehouse</label>
                                        <select name="multi_pickup[{{ $index }}][warehouse_id]" id="multi_pickup_{{ $index }}_warehouse_id"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring focus:ring-blue-200">
                                            @foreach($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}"
                                                    @if(old("multi_pickup.{$index}.warehouse_id", $lineItem->warehouse_id) == $warehouse->id) selected @endif>
                                                    {{ $warehouse->warehouse_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("multi_pickup.{$index}.warehouse_id")
                                            <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Delivery Number -->
                                    <div class="w-full md:w-1/3 px-2 mb-4">
                                        <label for="multi_pickup_{{ $index }}_delivery_number" class="block text-sm font-medium text-gray-700">Delivery Number</label>
                                        <input type="text" name="multi_pickup[{{ $index }}][delivery_number]" id="multi_pickup_{{ $index }}_delivery_number"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring focus:ring-blue-200"
                                            value="{{ old("multi_pickup.{$index}.delivery_number", $lineItem->delivery_number) }}">
                                        @error("multi_pickup.{$index}.delivery_number")
                                            <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Delivery Status -->
                                    <div class="w-full md:w-1/12 px-2 mb-4">
                                        <label for="delivery_status_{{ $index }}" class="block text-sm font-medium text-gray-700">Delivery Status</label>
                                        <select name="multi_pickup[{{ $index }}][delivery_status]" id="delivery_status_{{ $index }}"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring focus:ring-blue-200">
                                            <option value="" {{ old('multi_pickup.' . $index . '.delivery_status', $lineItem->delivery_status) == '' ? 'selected' : '' }}>
                                                Select a Delivery Status
                                            </option>
                                            @foreach($deliveryStatuses as $deliveryStatus)
                                                <option value="{{ $deliveryStatus->id }}"
                                                    {{ old('multi_pickup.' . $index . '.delivery_status', $lineItem->delivery_status) == $deliveryStatus->id ? 'selected' : '' }}>
                                                    {{ $deliveryStatus->status_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('multi_pickup.' . $index . '.delivery_status')
                                            <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Accessorial Type -->
                                    <div class="w-full md:w-1/6 px-2 mb-4">
                                        <label for="accessorial_type_{{ $index }}" class="block text-sm font-medium text-gray-700">Accessorial Type</label>
                                        <select name="multi_pickup[{{ $index }}][accessorial_type]" id="accessorial_type_{{ $index }}"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring focus:ring-blue-200">
                                            <option value="" {{ old('multi_pickup.' . $index . '.accessorial_type', $lineItem->accessorial_type) == '' ? 'selected' : '' }}>
                                                Select an Accessorial Type
                                            </option>
                                            @foreach($accessorialTypes as $accessorialType)
                                                <option value="{{ $accessorialType->id }}"
                                                    {{ (old('multi_pickup.' . $index . '.accessorial_type', $lineItem->accessorial_type) == $accessorialType->id) ? 'selected' : '' }}>
                                                    {{ $accessorialType->accessorial_types_code }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('multi_pickup.' . $index . '.accessorial_type')
                                            <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Accessorial Rate -->
                                    <div class="w-full md:w-1/12 px-2 mb-4">
                                        <label for="accessorial_rate_{{ $index }}" class="block text-sm font-medium text-gray-700">Accessorial Rate</label>
                                        <input type="text" name="multi_pickup[{{ $index }}][accessorial_rate]" id="accessorial_rate_{{ $index }}"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring focus:ring-blue-200"
                                            value="{{ old('multi_pickup.' . $index . '.accessorial_rate', $lineItem->accessorial_rate) }}">
                                        @error('multi_pickup.' . $index . '.accessorial_rate')
                                            <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                           <div class="w-full md:w-1/12 px-2 mb-4 flex items-end">
                                <a href="{{ route('deliveryRequest.split.form', ['id' => $lineItem->id]) }}?request_id={{ $deliveryRequest->id }}"
                                class="inline-block text-sm px-3 py-1 border border-blue-500 text-blue-500 rounded hover:bg-blue-500 hover:text-white transition">
                                    Split
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="mt-3 text-center text-gray-500 text-sm">No Data</div>
        @endif
    @endif

@endsection
