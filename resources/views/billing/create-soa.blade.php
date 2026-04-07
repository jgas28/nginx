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
                        <select id="company_id" name="company_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="onCompanyChange()">
                            <option value="">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->company_name }}
                                </option>
                            @endforeach
                        </select>
                        @if($companies->isEmpty())
                            <p class="mt-1 text-sm text-red-600">No companies found in database</p>
                        @endif
                        @error('company_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Customer Selection -->
                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Customer <span class="text-red-500">*</span>
                        </label>
                        <select id="customer_id" name="customer_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="onCustomerChange()">
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($customers->isEmpty())
                            <p class="mt-1 text-sm text-red-600">No customers found in database</p>
                        @endif
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
                               onchange="onFromDateChange()">
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
                               onchange="onToDateChange()">
                        @error('billing_period_to')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Booking Date -->
                    <div>
                        <label for="booking_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Booking Date
                        </label>
                        <input type="date" id="booking_date" name="booking_date"
                               value="{{ old('booking_date', '2026-04-07') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('booking_date')
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
                <div class="flex flex-col gap-3 md:flex-row md:justify-between md:items-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">Select Delivery Requests</h2>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="selectAllItems()" class="inline-flex items-center justify-center px-3 py-2 text-sm bg-blue-500 text-white rounded hover:bg-blue-600 whitespace-nowrap">
                            Select All
                        </button>
                        <button type="button" onclick="selectNoneItems()" class="inline-flex items-center justify-center px-3 py-2 text-sm bg-gray-500 text-white rounded hover:bg-gray-600 whitespace-nowrap">
                            Unselect All
                        </button>
                    </div>
                </div>

                <!-- Search and Filter Controls -->
                <div class="mb-4 flex gap-4">
                    <div class="flex-1">
                        <input type="text" id="searchDeliveryRequests" placeholder="Search by MTM, site, or company..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               onkeyup="filterBySearch()">
                    </div>
                    <div class="flex gap-2">
                        <select id="filterByCompany" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="filterByCompany()">
                            <option value="">All Companies</option>
                        </select>
                        <select id="filterByCustomer" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="filterByCustomer()">
                            <option value="">All Customers</option>
                        </select>
                    </div>
                </div>

                <!-- Results Summary -->
                <div class="mb-4 text-sm text-gray-600">
                    <span id="resultsCount">0</span> delivery requests found
                </div>

                <div id="deliveryRequestsContainer" class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg">
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
                                        @if(!$debug['soa_delivery_requests_table_exists'])<li>soa_delivery_requests</li>@endif
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
                                        <li>Using mock data: {{ $deliveryLineItems->count() }} items</li>
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
        accessorialRate: @if(is_array($lineItem->accessorial_rate)) {{ array_sum($lineItem->accessorial_rate) }} @else {{ $lineItem->accessorial_rate ?? 0 }} @endif,
        addOnRate: @if(is_array($lineItem->add_on_rate)) {{ array_sum($lineItem->add_on_rate) }} @else {{ $lineItem->add_on_rate ?? 0 }} @endif,
        siteName: '{{ addslashes(is_array($lineItem->site_name) ? implode(', ', $lineItem->site_name) : ($lineItem->site_name ?? 'N/A')) }}',
        bookingDate: '{{ $lineItem->delivery_request_booking_date ?? '' }}',
        requestAmount: {{ $lineItem->delivery_request_amount ?? 0 }},
        requestStatus: '{{ addslashes($lineItem->joined_delivery_status_name ?? ($lineItem->delivery_request_delivery_status ?? $lineItem->delivery_request_status ?? 'N/A')) }}',
        deliveryRequest: {
            deliveryDate: '{{ $lineItem->delivery_request_delivery_date ?? ($lineItem->deliveryRequest ? $lineItem->deliveryRequest->delivery_date : '') }}',
            companyId: '{{ $lineItem->delivery_request_company_id ?? ($lineItem->deliveryRequest ? $lineItem->deliveryRequest->company_id : '') }}',
            customerId: '{{ $lineItem->delivery_request_customer_id ?? ($lineItem->deliveryRequest ? $lineItem->deliveryRequest->customer_id : '') }}',
            companyName: '{{ addslashes($lineItem->joined_company_name ?? ($lineItem->deliveryRequest && $lineItem->deliveryRequest->company ? $lineItem->deliveryRequest->company->company_name : 'N/A')) }}',
            customerName: '{{ addslashes($lineItem->joined_customer_name ?? ($lineItem->deliveryRequest && $lineItem->deliveryRequest->customer ? $lineItem->deliveryRequest->customer->name : 'N/A')) }}'
        },
        deliveryStatusId: '{{ $lineItem->delivery_status ?? '' }}',
        deliveryStatusName: '{{ addslashes(optional($lineItem->deliveryStatus)->status_name ?? 'N/A') }}'
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

        // Get additional filters
        const searchTerm = document.getElementById('searchDeliveryRequests').value;
        const companyFilter = document.getElementById('filterByCompany').value;
        const customerFilter = document.getElementById('filterByCustomer').value;

        console.log('=== SOA Filter Query ===');
        console.log('Main Filters:', {
            companyId: companyId || 'All',
            customerId: customerId || 'All',
            fromDate: fromDate || 'Not set',
            toDate: toDate || 'Not set'
        });
        console.log('Additional Filters:', {
            searchTerm: searchTerm || 'Empty',
            companyFilter: companyFilter || 'All',
            customerFilter: customerFilter || 'All'
        });
        console.log('Total delivery line items to filter:', deliveryLineItems.length);

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
            let html = '<div class="space-y-2">'; // Reduced spacing for more compact display
            let hasItems = false;
            let visibleItems = 0;

            // Get current filters
            const searchTerm = document.getElementById('searchDeliveryRequests').value.toLowerCase();
            const companyFilter = document.getElementById('filterByCompany').value;
            const customerFilter = document.getElementById('filterByCustomer').value;

            deliveryLineItems.forEach(function(lineItem) {
                try {
                    let shouldShow = true;

                    // Always extract delivery request data for display purposes
                    const drDate = lineItem.deliveryRequest ? lineItem.deliveryRequest.deliveryDate : null;
                    const drCompanyId = lineItem.deliveryRequest ? lineItem.deliveryRequest.companyId : '';
                    const drCustomerId = lineItem.deliveryRequest ? lineItem.deliveryRequest.customerId : '';

                    // Apply filters only if they are set
                    if (companyId || customerId || (fromDate && toDate)) {
                        console.log('Checking item:', lineItem.id, 'drCompanyId:', drCompanyId, 'drCustomerId:', drCustomerId, 'drDate:', drDate);
                        console.log('Filters: companyId:', companyId, 'customerId:', customerId, 'fromDate:', fromDate, 'toDate:', toDate);

                        const companyMatch = !companyId || drCompanyId == companyId;
                        const customerMatch = !customerId || drCustomerId == customerId;

                        console.log('Matches: companyMatch:', companyMatch, 'customerMatch:', customerMatch);

                        // Parse dates for proper comparison
                        let dateMatch = true;
                        if (drDate && fromDate && toDate) {
                            try {
                                const deliveryDate = new Date(drDate + 'T00:00:00');
                                const from = new Date(fromDate + 'T00:00:00');
                                const to = new Date(toDate + 'T23:59:59');
                                dateMatch = deliveryDate >= from && deliveryDate <= to;
                                console.log('Date check:', drDate, '->', deliveryDate, '>=', from, '&&', deliveryDate, '<=', to, '=', dateMatch);
                            } catch (e) {
                                console.log('Date parsing error for:', drDate, e);
                                dateMatch = false;
                            }
                        }

                        shouldShow = companyMatch && customerMatch && dateMatch;
                        console.log('Should show:', shouldShow, '(company:', companyMatch, 'customer:', customerMatch, 'date:', dateMatch, ')');
                    }

                    // Apply additional filters (search and dropdown filters)
                    if (shouldShow) {
                        const mtm = lineItem.mtm.toLowerCase();
                        const siteName = (lineItem.siteName || '').toString().toLowerCase();
                        const companyName = (lineItem.deliveryRequest ? lineItem.deliveryRequest.companyName : '').toLowerCase();
                        const customerName = (lineItem.deliveryRequest ? lineItem.deliveryRequest.customerName : '').toLowerCase();

                        const searchMatch = !searchTerm ||
                            mtm.includes(searchTerm) ||
                            siteName.includes(searchTerm) ||
                            companyName.includes(searchTerm) ||
                            customerName.includes(searchTerm);

                        const companyDropdownMatch = !companyFilter || (lineItem.deliveryRequest && lineItem.deliveryRequest.companyId == companyFilter);
                        const customerDropdownMatch = !customerFilter || (lineItem.deliveryRequest && lineItem.deliveryRequest.customerId == customerFilter);

                        shouldShow = shouldShow && searchMatch && companyDropdownMatch && customerDropdownMatch;
                    }

                    if (shouldShow) {
                        hasItems = true;
                        visibleItems++;
                        const totalRate = Number(lineItem.requestAmount || 0) > 0
                            ? Number(lineItem.requestAmount || 0)
                            : (lineItem.accessorialRate + lineItem.addOnRate);
                        const amountToDisplay = totalRate;
                        // Properly format the date for display
                        let formattedBookingDate = 'N/A';
                        let formattedDate = 'N/A';
                        if (lineItem.bookingDate) {
                            try {
                                const bookingDateStr = lineItem.bookingDate.includes('T') ? lineItem.bookingDate : lineItem.bookingDate + 'T00:00:00';
                                const bookingDateObj = new Date(bookingDateStr);
                                if (!isNaN(bookingDateObj.getTime())) {
                                    formattedBookingDate = bookingDateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                                }
                            } catch (e) {
                                console.warn('Date formatting error for booking date:', lineItem.bookingDate, e);
                                formattedBookingDate = lineItem.bookingDate;
                            }
                        }
                        if (drDate) {
                            try {
                                // Ensure proper date parsing by adding time component if missing
                                const dateStr = drDate.includes('T') ? drDate : drDate + 'T00:00:00';
                                const dateObj = new Date(dateStr);
                                if (!isNaN(dateObj.getTime())) {
                                    formattedDate = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                                }
                            } catch (e) {
                                console.warn('Date formatting error for:', drDate, e);
                                formattedDate = drDate; // Fallback to raw date if formatting fails
                            }
                        }

                        html += `
                            <div class="border border-gray-200 rounded-lg p-3 hover:bg-gray-50 transition-colors">
                                <div class="flex items-start gap-3">
                                    <input type="checkbox" name="delivery_line_item_ids[]" value="${lineItem.id}"
                                           id="line_item_${lineItem.id}" class="mt-1 delivery-checkbox"
                                           onchange="updateSummary()">
                                    <div class="flex-1 min-w-0">
                                        <label for="line_item_${lineItem.id}" class="font-medium text-gray-900 cursor-pointer block">
                                            MTM: ${lineItem.mtm} | Line Item #${lineItem.id}
                                        </label>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-1 mt-1 text-sm text-gray-600">
                                            <div>Booking Date: ${formattedBookingDate}</div>
                                            <div>Delivery Date: ${formattedDate}</div>
                                            <div>Amount: ₱${totalRate.toFixed(2)}</div>
                                            <div class="truncate">Site: ${lineItem.siteName}</div>
                                            <div class="truncate">Company: ${lineItem.deliveryRequest ? lineItem.deliveryRequest.companyName : 'N/A'}</div>
                                            <div class="truncate">Customer: ${lineItem.deliveryRequest ? lineItem.deliveryRequest.customerName : 'N/A'}</div>
                                            <div class="truncate md:col-span-2">Status: ${lineItem.requestStatus || lineItem.deliveryStatusName || 'N/A'}</div>
                                        </div>
                                        <div class="flex gap-4 mt-1 text-xs text-gray-500">
                                            <span>Accessorial: ₱${lineItem.accessorialRate.toFixed(2)}</span>
                                            <span>Add-on: ₱${lineItem.addOnRate.toFixed(2)}</span>
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
            document.getElementById('resultsCount').textContent = visibleItems + ' delivery requests found';

            console.log('=== SOA Filter Results ===');
            console.log('Items found:', visibleItems);
            console.log('Has items to display:', hasItems);
            console.log('Total items processed:', deliveryLineItems.length);
            console.log('========================');

            // Populate filter dropdowns with available companies/customers
            populateFilterDropdowns();
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
            const lineItemTotal = Number(lineItem.requestAmount || 0) > 0
                ? Number(lineItem.requestAmount || 0)
                : (Number(lineItem.accessorialRate || 0) + Number(lineItem.addOnRate || 0));
            total += lineItemTotal;
        }
    });

    document.getElementById('totalAmount').textContent = `Total Amount: ₱${total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
}

// Bulk selection functions
function selectAllItems() {
    const checkboxes = document.querySelectorAll('.delivery-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    updateSummary();
}

function selectNoneItems() {
    const checkboxes = document.querySelectorAll('.delivery-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    updateSummary();
}

// Search and filter functions
function onCompanyChange() {
    const companyValue = document.getElementById('company_id').value;
    console.log('🏢 Main Company filter changed - Selected company ID:', companyValue || 'None');
    filterDeliveryRequests();
}

function onCustomerChange() {
    const customerValue = document.getElementById('customer_id').value;
    console.log('👥 Main Customer filter changed - Selected customer ID:', customerValue || 'None');
    filterDeliveryRequests();
}

function onFromDateChange() {
    const fromDateValue = document.getElementById('billing_period_from').value;
    console.log('📅 From Date filter changed - Selected date:', fromDateValue || 'Not set');
    filterDeliveryRequests();
}

function onToDateChange() {
    const toDateValue = document.getElementById('billing_period_to').value;
    console.log('📅 To Date filter changed - Selected date:', toDateValue || 'Not set');
    filterDeliveryRequests();
}

function filterBySearch() {
    const searchValue = document.getElementById('searchDeliveryRequests').value;
    console.log('🔍 Search filter triggered - Search term:', searchValue || 'Empty');
    filterDeliveryRequests();
}

function filterByCompany() {
    const companyValue = document.getElementById('filterByCompany').value;
    console.log('🏢 Company dropdown filter triggered - Selected company:', companyValue || 'All');
    filterDeliveryRequests();
}

function filterByCustomer() {
    const customerValue = document.getElementById('filterByCustomer').value;
    console.log('👥 Customer dropdown filter triggered - Selected customer:', customerValue || 'All');
    filterDeliveryRequests();
}

// Populate filter dropdowns with available companies/customers
function populateFilterDropdowns() {
    const companySelect = document.getElementById('filterByCompany');
    const customerSelect = document.getElementById('filterByCustomer');

    // Clear existing options except the first one
    companySelect.innerHTML = '<option value="">All Companies</option>';
    customerSelect.innerHTML = '<option value="">All Customers</option>';

    const companies = new Set();
    const customers = new Set();

    // Collect unique companies and customers from ALL items (not just filtered ones)
    deliveryLineItems.forEach(item => {
        if (item.deliveryRequest) {
            if (item.deliveryRequest.companyId && item.deliveryRequest.companyName) {
                companies.add(JSON.stringify({
                    id: item.deliveryRequest.companyId,
                    name: item.deliveryRequest.companyName
                }));
            }
            if (item.deliveryRequest.customerId && item.deliveryRequest.customerName) {
                customers.add(JSON.stringify({
                    id: item.deliveryRequest.customerId,
                    name: item.deliveryRequest.customerName
                }));
            }
        }
    });

    // Add company options
    Array.from(companies).sort((a, b) => {
        const nameA = JSON.parse(a).name;
        const nameB = JSON.parse(b).name;
        return nameA.localeCompare(nameB);
    }).forEach(companyJson => {
        const company = JSON.parse(companyJson);
        const option = document.createElement('option');
        option.value = company.id;
        option.textContent = company.name;
        companySelect.appendChild(option);
    });

    // Add customer options
    Array.from(customers).sort((a, b) => {
        const nameA = JSON.parse(a).name;
        const nameB = JSON.parse(b).name;
        return nameA.localeCompare(nameB);
    }).forEach(customerJson => {
        const customer = JSON.parse(customerJson);
        const option = document.createElement('option');
        option.value = customer.id;
        option.textContent = customer.name;
        customerSelect.appendChild(option);
    });
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

    // Populate filter dropdowns first
    populateFilterDropdowns();

    // Then filter delivery requests
    filterDeliveryRequests();
});
</script>
@endsection
