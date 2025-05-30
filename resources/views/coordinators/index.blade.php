@extends('layouts.app')

@section('title', 'FCZCNYX')

@section('content')
<div class="container">
    <form method="GET" action="{{ route('coordinators.index') }}" class="mb-4">
        <input type="text" name="search" placeholder="Search MTM or Province" value="{{ request('search') }}"
            class="border rounded px-4 py-2 w-1/3">
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Search</button>
    </form>
    <!-- Tab Headers -->
    <div class="flex border-b space-x-4" id="tabs">
        <button class="tab-button border-b-2 px-4 py-2 text-gray-600 hover:text-blue-600" data-tab="list">List</button>
        <button class="tab-button border-b-2 px-4 py-2 text-gray-600 hover:text-blue-600" data-tab="status4">Pull Out</button>
        <button class="tab-button border-b-2 px-4 py-2 text-gray-600 hover:text-blue-600" data-tab="status8">For Allocation</button>
        <button class="tab-button border-b-2 px-4 py-2 text-gray-600 hover:text-blue-600" data-tab="status9">Allocated</button>
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
