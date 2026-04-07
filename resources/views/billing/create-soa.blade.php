@extends('layouts.app')

@section('title', 'Create Statement of Account')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-6xl mx-auto px-4">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Create Statement of Account</h1>
                    <p class="text-gray-600 mt-2">Generate a new SOA for billing purposes</p>
                </div>
                <a href="{{ route('billing.dashboard') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>
        </div>

        <!-- SOA Form -->
        <form action="{{ route('billing.createSOA') }}" method="POST" id="soaForm">
            @csrf
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">SOA Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Company Selection -->
                    <div>
                        <label for="company_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Company <span class="text-red-500">*</span>
                        </label>
                        <select id="company_id" name="company_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="filterDeliveryRequests()">
                            <option value="">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->company_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('company_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Customer Selection -->
                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Customer <span class="text-red-500">*</span>
                        </label>
                        <select id="customer_id" name="customer_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="filterDeliveryRequests()">
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Billing Period From -->
                    <div>
                        <label for="billing_period_from" class="block text-sm font-medium text-gray-700 mb-2">
                            Billing Period From <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="billing_period_from" name="billing_period_from"
                               value="{{ old('billing_period_from') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               onchange="filterDeliveryRequests()">
                        @error('billing_period_from')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Billing Period To -->
                    <div>
                        <label for="billing_period_to" class="block text-sm font-medium text-gray-700 mb-2">
                            Billing Period To <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="billing_period_to" name="billing_period_to"
                               value="{{ old('billing_period_to') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               onchange="filterDeliveryRequests()">
                        @error('billing_period_to')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Statement Date -->
                    <div>
                        <label for="statement_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Statement Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="statement_date" name="statement_date"
                               value="{{ old('statement_date', date('Y-m-d')) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('statement_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Due Date -->
                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Due Date
                        </label>
                        <input type="date" id="due_date" name="due_date"
                               value="{{ old('due_date') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('due_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Notes -->
                <div class="mt-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Notes
                    </label>
                    <textarea id="notes" name="notes" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Additional notes for this SOA...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Delivery Requests Selection -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Select Delivery Requests</h2>

                <div id="deliveryRequestsContainer">
                    @if($deliveryLineItems->isEmpty())
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-truck text-3xl mb-4"></i>
                            @if(!$debug['delivery_request_line_items_table_exists'])
                                <p class="text-red-600 font-semibold mb-2">Database tables not found!</p>
                                <p>The required database tables for delivery requests have not been created yet.</p>
                                <p class="text-sm mt-2">Please run the pending migrations to create the necessary tables.</p>
                                <div class="mt-4 p-4 bg-gray-100 rounded text-left text-sm">
                                    <p><strong>Missing tables:</strong></p>
                                    <ul class="list-disc list-inside mt-1">
                                        @if(!$debug['delivery_requests_table_exists'])<li>delivery_requests</li>@endif
                                        @if(!$debug['delivery_request_line_items_table_exists'])<li>delivery_request_line_items</li>@endif
                                        @if(!$debug['soa_delivery_line_items_table_exists'])<li>soa_delivery_line_items</li>@endif
                                    </ul>
                                </div>
                            @elseif(!$debug['delivery_requests_table_exists'])
                                <p class="text-orange-600 font-semibold mb-2">Delivery requests table missing!</p>
                                <p>The delivery_requests table needs to be created.</p>
                            @else
                                <p>Please select a company/customer and billing period to view available delivery requests.</p>
                                <div class="mt-4 p-4 bg-blue-50 rounded text-left text-sm">
                                    <p><strong>Debug Info:</strong></p>
                                    <ul class="list-disc list-inside mt-1">
                                        <li>Total delivery requests: {{ $debug['total_delivery_requests'] ?? 0 }}</li>
                                        <li>Total line items: {{ $debug['total_line_items'] ?? 0 }}</li>
                                        <li>Available statuses: {{ implode(', ', $debug['delivery_request_statuses'] ?? []) }}</li>
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-truck text-3xl mb-4"></i>
                            <p>Please select a company/customer and billing period to view available delivery requests.</p>
                        </div>
                    @endif
                </div>

                @error('delivery_request_ids')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Summary and Actions -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">SOA Summary</h3>
                        <div class="mt-2 text-sm text-gray-600">
                            <p id="selectedRequests">Selected Line Items: 0</p>
                            <p id="totalAmount">Total Amount: ₱0.00</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <button type="button" onclick="calculateTotal()" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                            <i class="fas fa-calculator mr-2"></i>Calculate Total
                        </button>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">
                            <i class="fas fa-save mr-2"></i>Create SOA
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Delivery line items data
const deliveryLineItems = [
    @foreach($deliveryLineItems as $lineItem)
    {
        id: {{ $lineItem->id }},
        mtm: '{{ addslashes($lineItem->mtm) }}',
        accessorialRate: {{ $lineItem->accessorial_rate ?? 0 }},
        addOnRate: {{ $lineItem->add_on_rate ?? 0 }},
        siteName: '{{ addslashes(is_array($lineItem->site_name) ? implode(', ', $lineItem->site_name) : $lineItem->site_name) }}',
        deliveryRequest: {
            deliveryDate: '{{ $lineItem->deliveryRequest ? $lineItem->deliveryRequest->delivery_date : '' }}',
            companyId: '{{ $lineItem->deliveryRequest ? $lineItem->deliveryRequest->company_id : '' }}',
            customerId: '{{ $lineItem->deliveryRequest ? $lineItem->deliveryRequest->customer_id : '' }}',
            companyName: '{{ $lineItem->deliveryRequest && $lineItem->deliveryRequest->company ? addslashes($lineItem->deliveryRequest->company->company_name) : 'N/A' }}',
            customerName: '{{ $lineItem->deliveryRequest && $lineItem->deliveryRequest->customer ? addslashes($lineItem->deliveryRequest->customer->name) : 'N/A' }}'
        }
    }@if(!$loop->last),@endif
    @endforeach
];

console.log('Delivery line items loaded:', deliveryLineItems.length);

function filterDeliveryRequests() {
    try {
        const companyId = document.getElementById('company_id').value;
        const customerId = document.getElementById('customer_id').value;
        const fromDate = document.getElementById('billing_period_from').value;
        const toDate = document.getElementById('billing_period_to').value;

        console.log('Filtering with:', { companyId, customerId, fromDate, toDate });

        if (!companyId && !customerId) {
            document.getElementById('deliveryRequestsContainer').innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-truck text-3xl mb-4"></i>
                    <p>Please select a company or customer to view available delivery requests.</p>
                </div>
            `;
            return;
        }

        if (!fromDate || !toDate) {
            document.getElementById('deliveryRequestsContainer').innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-truck text-3xl mb-4"></i>
                    <p>Please select billing period dates to filter delivery requests.</p>
                </div>
            `;
            return;
        }

        const container = document.getElementById('deliveryRequestsContainer');
        container.innerHTML = `
            <div class="text-center py-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 mx-auto mb-4">
                    <i class="fas fa-spinner fa-spin text-3xl text-blue-600"></i>
                </div>
                <p class="mt-2 text-gray-600">Loading delivery requests...</p>
            </div>
        `;

        setTimeout(() => {
            let html = '<div class="space-y-4">';
            let hasItems = false;

            deliveryLineItems.forEach(function(lineItem) {
                try {
                    const drDate = lineItem.deliveryRequest.deliveryDate;
                    const drCompanyId = lineItem.deliveryRequest.companyId;
                    const drCustomerId = lineItem.deliveryRequest.customerId;

                    const companyMatch = !companyId || drCompanyId == companyId;
                    const customerMatch = !customerId || drCustomerId == customerId;
                    const dateMatch = drDate >= fromDate && drDate <= toDate;

                    if (companyMatch && customerMatch && dateMatch) {
                        hasItems = true;
                        const totalRate = (lineItem.accessorialRate + lineItem.addOnRate);
                        const formattedDate = drDate ? new Date(drDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A';

                        html += `
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4 w-full">
                                        <input type="checkbox" name="delivery_line_item_ids[]" value="${lineItem.id}"
                                               id="line_item_${lineItem.id}" class="mr-3 delivery-checkbox"
                                               onchange="updateSummary()">
                                        <div class="w-full">
                                            <label for="line_item_${lineItem.id}" class="font-medium text-gray-900 cursor-pointer">
                                                MTM: ${lineItem.mtm} | Line Item #${lineItem.id}
                                            </label>
                                            <p class="text-sm text-gray-600 mt-1">
                                                Date: ${formattedDate} |
                                                Amount: ₱${totalRate.toFixed(2)}
                                            </p>
                                            <p class="text-sm text-gray-500 mt-1">
                                                Site: ${lineItem.siteName} |
                                                Company: ${lineItem.deliveryRequest.companyName} |
                                                Customer: ${lineItem.deliveryRequest.customerName}
                                            </p>
                                            <p class="text-sm text-gray-500 mt-1">
                                                Accessorial: ₱${lineItem.accessorialRate.toFixed(2)} |
                                                Add-on: ₱${lineItem.addOnRate.toFixed(2)}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                } catch (error) {
                    console.error('Error processing line item:', lineItem, error);
                }
            });

            html += '</div>';

            if (!hasItems) {
                html = `
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-inbox text-3xl mb-4"></i>
                        <p>No delivery requests found for the selected criteria.</p>
                        <p class="text-sm mt-2">Try adjusting your filters or check if delivery requests exist in the system.</p>
                    </div>
                `;
            }

            container.innerHTML = html;
            console.log('Filtering complete, found items:', hasItems);
        }, 250);
    } catch (error) {
        console.error('Error in filterDeliveryRequests:', error);
        document.getElementById('deliveryRequestsContainer').innerHTML = `
            <div class="text-center py-8 text-red-500">
                <i class="fas fa-exclamation-triangle text-3xl mb-4"></i>
                <p>An error occurred while filtering delivery requests.</p>
                <p class="text-sm mt-2">Please check the browser console for details.</p>
            </div>
        `;
    }
}

function updateSummary() {
    const checkboxes = document.querySelectorAll('.delivery-checkbox:checked');
    const count = checkboxes.length;
    document.getElementById('selectedRequests').textContent = `Selected Line Items: ${count}`;
    calculateTotal();
}

function calculateTotal() {
    const checkboxes = document.querySelectorAll('.delivery-checkbox:checked');
    let total = 0;

    checkboxes.forEach(checkbox => {
        const lineItemId = parseInt(checkbox.value);
        // Find the line item in our JavaScript array
        const lineItem = deliveryLineItems.find(item => item.id === lineItemId);
        if (lineItem) {
            total += lineItem.accessorialRate + lineItem.addOnRate;
        }
    });

    document.getElementById('totalAmount').textContent = `Total Amount: ₱${total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set default dates if not set
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    if (!document.getElementById('billing_period_from').value) {
        document.getElementById('billing_period_from').value = firstDay.toISOString().split('T')[0];
    }
    if (!document.getElementById('billing_period_to').value) {
        document.getElementById('billing_period_to').value = lastDay.toISOString().split('T')[0];
    }

    filterDeliveryRequests();
});
</script>
@endsection