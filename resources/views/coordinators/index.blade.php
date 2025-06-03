@extends('layouts.app')

@section('title', 'FCZCNYX')

@section('content')
<div class="container">
    <form method="GET" action="{{ route('coordinators.index') }}" class="mb-4 flex flex-wrap gap-4">
        <input type="text" name="mtm" placeholder="Search MTM" value="{{ request('mtm') }}"
            class="border rounded px-4 py-2">
        
        <input type="date" name="date_from" value="{{ request('date_from') }}"
            class="border rounded px-4 py-2">

        <input type="date" name="date_to" value="{{ request('date_to') }}"
            class="border rounded px-4 py-2">

        <select name="status" class="border rounded px-4 py-2">
            <option value="">All Status</option>
           <option value='"1"' {{ request('status') == '"1"' ? 'selected' : '' }}>Delivered</option>
            <option value='"2"' {{ request('status') == '"2"' ? 'selected' : '' }}>In-Transit</option>
            <option value='"3"' {{ request('status') == '"3"' ? 'selected' : '' }}>Staging</option>
            <option value='"4"' {{ request('status') == '"4"' ? 'selected' : '' }}>For Pullout</option>
            <option value='"5"' {{ request('status') == '"5"' ? 'selected' : '' }}>For Return</option>
            <option value='"6"' {{ request('status') == '"6"' ? 'selected' : '' }}>Foultrip</option>
            <option value='"7"' {{ request('status') == '"7"' ? 'selected' : '' }}>Site Pullout</option>
            <option value='"8"' {{ request('status') == '"8"' ? 'selected' : '' }}>Truck Allocation</option>
            <option value='"11"' {{ request('status') == '"11"' ? 'selected' : '' }}>Cancel</option>
            <option value='"12"' {{ request('status') == '"12"' ? 'selected' : '' }}>Hold</option>
            <option value='"13"' {{ request('status') == '"13"' ? 'selected' : '' }}>Ongoing Pullout</option>
            <option value='"14"' {{ request('status') == '"14"' ? 'selected' : '' }}>Allocated</option>
            <option value='"15"' {{ request('status') == '"15"' ? 'selected' : '' }}>Return to Warehouse</option>
        </select>

        <input type="hidden" name="tab" id="active-tab" value="{{ request('tab', 'list') }}">

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Search</button>
    </form>
    <!-- Tab Headers -->
    <div class="flex border-b space-x-4" id="tabs">
        <button class="tab-button border-b-2 px-4 py-2 text-gray-600 hover:text-blue-600" data-tab="list">List</button>
        <button class="tab-button border-b-2 px-4 py-2 text-gray-600 hover:text-blue-600" data-tab="status4">Pull Out</button>
        <button class="tab-button border-b-2 px-4 py-2 text-gray-600 hover:text-blue-600" data-tab="status8">For Allocation</button>
        <button class="tab-button border-b-2 px-4 py-2 text-gray-600 hover:text-blue-600" data-tab="status9">Allocated</button>
         <button class="tab-button border-b-2 px-4 py-2 text-gray-600 hover:text-blue-600" data-tab="status10">Delivered</button>
          <button class="tab-button border-b-2 px-4 py-2 text-gray-600 hover:text-blue-600" data-tab="status11">Hold/Cancel</button>
    </div>

    <!-- Tab Contents -->
    <div>
        <!-- List Tab -->
        <div id="tab-list" class="tab-content">
            <table class="min-w-full bg-white border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 border-b">MTM</th>
                        <th class="px-4 py-2 border-b">Company Code</th>
                        <th class="px-4 py-2 border-b">Delivery Number</th>
                        <th class="px-4 py-2 border-b">Site ID</th>
                        <th class="px-4 py-2 border-b">Delivery Address</th>
                        <th class="px-4 py-2 border-b">Delivery Rate</th>
                        <th class="px-4 py-2 border-b">Truck Type</th>
                        <th class="px-4 py-2 border-b">Region</th>
                        <th class="px-4 py-2 border-b">Province</th>
                        <th class="px-4 py-2 border-b">Status</th>
                        <th class="px-4 py-2 border-b">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deliveryRequests as $deliveryRequest)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->mtm ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->company->company_code ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">
                                @foreach($deliveryRequest->lineItems as $lineItem)
                                    {{ $lineItem->delivery_number ?? 'N/A' }}@if(!$loop->last)/ @endif
                                @endforeach
                            </td>
                            <td class="px-4 py-2 border-b">
                                @foreach($deliveryRequest->lineItems as $lineItem)
                                    {{ $lineItem->site_name ?? 'N/A' }}@if(!$loop->last)/ @endif
                                @endforeach
                            </td>
                            <td class="px-4 py-2 border-b">
                                @foreach($deliveryRequest->lineItems as $lineItem)
                                    {{ $lineItem->delivery_address ?? 'N/A' }}@if(!$loop->last)/ @endif
                                @endforeach
                            </td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->delivery_rate ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->truckType->truck_code ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->area->area_code ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->region->province ?? 'N/A' }}</td>
                           
                            <td class="px-4 py-2 border-b">
                                @foreach($deliveryRequest->lineItems as $lineItem)
                                    {{ $lineItem->deliveryStatus->status_name ?? 'N/A' }}@if(!$loop->last), @endif
                                @endforeach
                            </td>
                            <td class="px-4 py-2 border-b space-x-2">
                                <a href="{{ route('coordinators.edit', $deliveryRequest) }}" class="text-yellow-600 hover:underline">Edit</a>
                                @if($deliveryRequest->delivery_type != 'Regular')
                                    <a href="{{ route('coordinators.splitView', $deliveryRequest) }}" class="text-green-600 hover:underline">Split</a>
                                @endif
                                <form action="{{ route('coordinators.destroy', $deliveryRequest) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this delivery request?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <!-- Pagination -->
            <div class="mt-4">
                {{ $deliveryRequests->appends(['tab' => 'list', 'search' => request('search')])->links() }}
            </div>
        </div>

        <!-- For Pullout Tab -->
        <div id="tab-status4" class="tab-content hidden">
            <table class="min-w-full bg-white border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 border-b">MTM</th>
                        <th class="px-4 py-2 border-b">Company Code</th>
                        <th class="px-4 py-2 border-b">Delivery Number</th>
                        <th class="px-4 py-2 border-b">Site ID</th>
                        <th class="px-4 py-2 border-b">Delivery Address</th>
                        <th class="px-4 py-2 border-b">Delivery Rate</th>
                        <th class="px-4 py-2 border-b">Truck Type</th>
                        <th class="px-4 py-2 border-b">Region</th>
                        <th class="px-4 py-2 border-b">Province</th>
                        <th class="px-4 py-2 border-b">Status</th>
                        <th class="px-4 py-2 border-b">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pullouts as $deliveryRequest)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->mtm ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->company->company_code ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">
                                @foreach($deliveryRequest->lineItems as $lineItem)
                                    {{ $lineItem->delivery_number ?? 'N/A' }}@if(!$loop->last)/ @endif
                                @endforeach
                            </td>
                            <td class="px-4 py-2 border-b">
                                @foreach($deliveryRequest->lineItems as $lineItem)
                                    {{ $lineItem->site_name ?? 'N/A' }}@if(!$loop->last)/ @endif
                                @endforeach
                            </td>
                            <td class="px-4 py-2 border-b">
                                @foreach($deliveryRequest->lineItems as $lineItem)
                                    {{ $lineItem->delivery_address ?? 'N/A' }}@if(!$loop->last)/ @endif
                                @endforeach
                            </td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->delivery_rate ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->truckType->truck_code ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->area->area_code ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->region->province ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">
                                @foreach($deliveryRequest->lineItems as $lineItem)
                                    {{ $lineItem->deliveryStatus->status_name ?? 'N/A' }}@if(!$loop->last), @endif
                                @endforeach
                            </td>
                            <td>
                                <a href="{{ route('coordinators.editAllocation', $deliveryRequest) }}" class="text-yellow-600 hover:underline">Edit</a>
                                <a href="{{ route('coordinators.coordinators', $deliveryRequest) }}" class="text-yellow-600 hover:underline">Request CV</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <!-- Pagination -->
            <div class="mt-4">
                {{ $pullouts->appends(['tab' => 'status4', 'search' => request('search')])->links() }}
            </div>
        </div>

        <!-- For Allocation Tab -->
        <div id="tab-status8" class="tab-content hidden">
            <table class="min-w-full bg-white border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 border-b">MTM</th>
                        <th class="px-4 py-2 border-b">Company Code</th>
                        <th class="px-4 py-2 border-b">Delivery Number</th>
                        <th class="px-4 py-2 border-b">Site ID</th>
                        <th class="px-4 py-2 border-b">Delivery Address</th>
                        <th class="px-4 py-2 border-b">Delivery Rate</th>
                        <th class="px-4 py-2 border-b">Truck Type</th>
                        <th class="px-4 py-2 border-b">Region</th>
                        <th class="px-4 py-2 border-b">Province</th>
                        <th class="px-4 py-2 border-b">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($forAllocations as $deliveryRequest)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->mtm ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->company->company_code ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">
                                @foreach($deliveryRequest->lineItems as $lineItem)
                                    {{ $lineItem->delivery_number ?? 'N/A' }}@if(!$loop->last)/ @endif
                                @endforeach
                            </td>
                            <td class="px-4 py-2 border-b">
                                @foreach($deliveryRequest->lineItems as $lineItem)
                                    {{ $lineItem->site_name ?? 'N/A' }}@if(!$loop->last)/ @endif
                                @endforeach
                            </td>
                            <td class="px-4 py-2 border-b">
                                @foreach($deliveryRequest->lineItems as $lineItem)
                                    {{ $lineItem->delivery_address ?? 'N/A' }}@if(!$loop->last)/ @endif
                                @endforeach
                            </td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->delivery_rate ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->truckType->truck_code ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->area->area_code ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->region->province ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">
                                @foreach($deliveryRequest->lineItems as $lineItem)
                                    {{ $lineItem->deliveryStatus->status_name ?? 'N/A' }}@if(!$loop->last), @endif
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <!-- Pagination -->
            <div class="mt-4">
                {{ $forAllocations->appends(['tab' => 'status8', 'search' => request('search')])->links() }}
            </div>
        </div>

        <!-- Allocated Tab -->
        <div id="tab-status9" class="tab-content hidden">
            <table class="min-w-full bg-white border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 border-b">MTM</th>
                        <th class="px-4 py-2 border-b">Company Code</th>
                        <th class="px-4 py-2 border-b">Delivery Number</th>
                        <th class="px-4 py-2 border-b">Site ID</th>
                        <th class="px-4 py-2 border-b">Delivery Address</th>
                        <th class="px-4 py-2 border-b">Delivery Rate</th>
                        <th class="px-4 py-2 border-b">Truck Type</th>
                        <th class="px-4 py-2 border-b">Region</th>
                        <th class="px-4 py-2 border-b">Province</th>
                        <th class="px-4 py-2 border-b">Status</th>
                        <th class="px-4 py-2 border-b">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allocated as $deliveryRequest)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->mtm ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->company->company_code ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">
                                @foreach($deliveryRequest->lineItems as $lineItem)
                                    {{ $lineItem->delivery_number ?? 'N/A' }}@if(!$loop->last)/ @endif
                                @endforeach
                            </td>
                            <td class="px-4 py-2 border-b">
                                @foreach($deliveryRequest->lineItems as $lineItem)
                                    {{ $lineItem->site_name ?? 'N/A' }}@if(!$loop->last)/ @endif
                                @endforeach
                            </td>
                            <td class="px-4 py-2 border-b">
                                @foreach($deliveryRequest->lineItems as $lineItem)
                                    {{ $lineItem->delivery_address ?? 'N/A' }}@if(!$loop->last)/ @endif
                                @endforeach
                            </td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->delivery_rate ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->truckType->truck_code ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->area->area_code ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->region->province ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">
                                @foreach($deliveryRequest->lineItems as $lineItem)
                                    {{ $lineItem->deliveryStatus->status_name ?? 'N/A' }}@if(!$loop->last), @endif
                                @endforeach
                            </td>
                            <td class="px-4 py-2 border-b space-x-2">
                                <a href="{{ route('coordinators.editAllocation', $deliveryRequest) }}" class="text-yellow-600 hover:underline">Edit</a>
                                <a href="{{ route('cashVoucherRequests.request', $deliveryRequest) }}" class="text-yellow-600 hover:underline">Request CV</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <!-- Pagination -->
            <div class="mt-4">
                {{ $allocated->appends(['tab' => 'status9', 'search' => request('search')])->links() }}
            </div>
        </div>
        <!-- Delivered Tab -->
        <div id="tab-status10" class="tab-content hidden">
            <table class="min-w-full bg-white border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 border-b">MTM</th>
                        <th class="px-4 py-2 border-b">Company Code</th>
                        <th class="px-4 py-2 border-b">Delivery Number</th>
                        <th class="px-4 py-2 border-b">Site ID</th>
                        <th class="px-4 py-2 border-b">Delivery Address</th>
                        <th class="px-4 py-2 border-b">Delivery Rate</th>
                        <th class="px-4 py-2 border-b">Truck Type</th>
                        <th class="px-4 py-2 border-b">Region</th>
                        <th class="px-4 py-2 border-b">Province</th>
                        <th class="px-4 py-2 border-b">Status</th>
                        <th class="px-4 py-2 border-b">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($delivered as $deliveryRequest)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->mtm ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->company->company_code ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">
                                @foreach($deliveryRequest->lineItems as $lineItem)
                                    {{ $lineItem->delivery_number ?? 'N/A' }}@if(!$loop->last)/ @endif
                                @endforeach
                            </td>
                            <td class="px-4 py-2 border-b">
                                @foreach($deliveryRequest->lineItems as $lineItem)
                                    {{ $lineItem->site_name ?? 'N/A' }}@if(!$loop->last)/ @endif
                                @endforeach
                            </td>
                            <td class="px-4 py-2 border-b">
                                @foreach($deliveryRequest->lineItems as $lineItem)
                                    {{ $lineItem->delivery_address ?? 'N/A' }}@if(!$loop->last)/ @endif
                                @endforeach
                            </td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->delivery_rate ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->truckType->truck_code ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->area->area_code ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->region->province ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">
                                @foreach($deliveryRequest->lineItems as $lineItem)
                                    {{ $lineItem->deliveryStatus->status_name ?? 'N/A' }}@if(!$loop->last), @endif
                                @endforeach
                            </td>
                            <td class="px-4 py-2 border-b space-x-2">
                                <a href="{{ route('coordinators.editAllocation', $deliveryRequest) }}" class="text-yellow-600 hover:underline">Edit</a>
                                <a href="{{ route('cashVoucherRequests.request', $deliveryRequest) }}" class="text-yellow-600 hover:underline">Request CV</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <!-- Pagination -->
            <div class="mt-4">
                {{ $delivered->appends(['tab' => 'status10', 'search' => request('search')])->links() }}
            </div>
        </div>
        <!-- Hold/Cancel Tab -->
        <div id="tab-status11" class="tab-content hidden">
            <table class="min-w-full bg-white border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 border-b">MTM</th>
                        <th class="px-4 py-2 border-b">Company Code</th>
                        <th class="px-4 py-2 border-b">Delivery Number</th>
                        <th class="px-4 py-2 border-b">Site ID</th>
                        <th class="px-4 py-2 border-b">Delivery Address</th>
                        <th class="px-4 py-2 border-b">Delivery Rate</th>
                        <th class="px-4 py-2 border-b">Truck Type</th>
                        <th class="px-4 py-2 border-b">Region</th>
                        <th class="px-4 py-2 border-b">Province</th>
                        <th class="px-4 py-2 border-b">Status</th>
                        <th class="px-4 py-2 border-b">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cancel as $deliveryRequest)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->mtm ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->company->company_code ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">
                                @foreach($deliveryRequest->lineItems as $lineItem)
                                    {{ $lineItem->delivery_number ?? 'N/A' }}@if(!$loop->last)/ @endif
                                @endforeach
                            </td>
                            <td class="px-4 py-2 border-b">
                                @foreach($deliveryRequest->lineItems as $lineItem)
                                    {{ $lineItem->site_name ?? 'N/A' }}@if(!$loop->last)/ @endif
                                @endforeach
                            </td>
                            <td class="px-4 py-2 border-b">
                                @foreach($deliveryRequest->lineItems as $lineItem)
                                    {{ $lineItem->delivery_address ?? 'N/A' }}@if(!$loop->last)/ @endif
                                @endforeach
                            </td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->delivery_rate ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->truckType->truck_code ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->area->area_code ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">{{ $deliveryRequest->region->province ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b">
                                @foreach($deliveryRequest->lineItems as $lineItem)
                                    {{ $lineItem->deliveryStatus->status_name ?? 'N/A' }}@if(!$loop->last), @endif
                                @endforeach
                            </td>
                            <td class="px-4 py-2 border-b space-x-2">
                                <a href="{{ route('coordinators.editAllocation', $deliveryRequest) }}" class="text-yellow-600 hover:underline">Edit</a>
                                <a href="{{ route('cashVoucherRequests.request', $deliveryRequest) }}" class="text-yellow-600 hover:underline">Request CV</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <!-- Pagination -->
            <div class="mt-4">
                {{ $delivered->appends(['tab' => 'status11', 'search' => request('search')])->links() }}
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Tabs -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');

        function activateTab(tabName) {
            tabContents.forEach(content => content.classList.add('hidden'));
            tabButtons.forEach(btn => {
                btn.classList.remove('border-blue-500', 'text-blue-600');
                btn.classList.add('text-gray-600');
            });

            const targetTab = document.getElementById('tab-' + tabName);
            const targetButton = document.querySelector(`.tab-button[data-tab="${tabName}"]`);

            if (targetTab && targetButton) {
                targetTab.classList.remove('hidden');
                targetButton.classList.add('border-blue-500', 'text-blue-600');
            }
        }

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const tab = button.getAttribute('data-tab');
                const url = new URL(window.location);
                url.searchParams.set('tab', tab); // Update URL with tab param
                window.history.replaceState({}, '', url); // Push state without reload
                activateTab(tab);
            });
        });

        // Check for ?tab=... in the URL
        const urlParams = new URLSearchParams(window.location.search);
        const initialTab = urlParams.get('tab') || 'list';
        activateTab(initialTab);
    });
</script>
@endsection
