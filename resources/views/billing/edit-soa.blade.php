@extends('layouts.app')

@section('title', 'Edit SOA - ' . ($soa->soa_number ?? 'N/A'))

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-6xl mx-auto px-4">
        <div class="mb-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit SOA</h1>
                    <p class="text-gray-600 mt-2">Update statement details for {{ $soa->soa_number ?? 'N/A' }}</p>
                </div>
                <a href="{{ route('billing.index') }}" class="inline-flex w-full items-center justify-center rounded-lg bg-gray-500 px-4 py-2 text-white hover:bg-gray-600 sm:w-auto">
                    <i class="fas fa-arrow-left mr-2"></i>Back to SOA List
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('billing.updateSoa', $soa->id) }}" id="editSoaForm">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">SOA Number</label>
                        <p class="px-4 py-2 bg-gray-100 rounded-lg">{{ $soa->soa_number }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @foreach(['draft','pending','approved','paid','overdue'] as $status)
                                <option value="{{ $status }}" {{ old('status', $soa->status) === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Billing Period From</label>
                        <input type="date" name="billing_period_from" value="{{ old('billing_period_from', $soa->billing_period_from?->format('Y-m-d')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('billing_period_from')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Billing Period To</label>
                        <input type="date" name="billing_period_to" value="{{ old('billing_period_to', $soa->billing_period_to?->format('Y-m-d')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('billing_period_to')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Booking Date</label>
                        <input type="date" name="booking_date" value="{{ old('booking_date', $soa->due_date?->format('Y-m-d')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('booking_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea name="notes" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('notes', $soa->notes) }}</textarea>
                        @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <div class="flex flex-col gap-3 md:flex-row md:justify-between md:items-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">Edit Delivery Requests</h2>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="selectAllRequests()" class="inline-flex items-center justify-center px-3 py-2 text-sm bg-blue-500 text-white rounded hover:bg-blue-600 whitespace-nowrap">
                            Select All
                        </button>
                        <button type="button" onclick="unselectAllRequests()" class="inline-flex items-center justify-center px-3 py-2 text-sm bg-gray-500 text-white rounded hover:bg-gray-600 whitespace-nowrap">
                            Unselect All
                        </button>
                    </div>
                </div>

                <div class="mb-4 flex flex-col gap-4 lg:flex-row">
                    <div class="flex-1">
                        <input type="text" id="editSearchDeliveryRequests" placeholder="Search by MTM, site, or company..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               oninput="filterEditDeliveryRequests()">
                    </div>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:w-auto">
                        <select id="editFilterByCompany" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="filterEditDeliveryRequests()">
                            <option value="">All Companies</option>
                        </select>
                        <select id="editFilterByCustomer" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="filterEditDeliveryRequests()">
                            <option value="">All Customers</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4 text-sm text-gray-600">
                    <span id="editResultsCount">{{ count($editableDeliveryRequests) }}</span> delivery requests found
                </div>

                <div id="editDeliveryRequestsContainer" class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg">
                    @forelse($editableDeliveryRequests as $deliveryRequest)
                        @php
                            $selectedIds = old('delivery_request_ids', $selectedDeliveryRequestIds);
                            $isSelected = in_array((int) $deliveryRequest->id, collect($selectedIds)->map(fn ($id) => (int) $id)->all(), true);
                        @endphp
                        <div class="edit-delivery-item border-b border-gray-200 p-4 hover:bg-gray-50"
                             data-id="{{ $deliveryRequest->id }}"
                             data-mtm="{{ strtolower($deliveryRequest->mtm ?? '') }}"
                             data-site="{{ strtolower($deliveryRequest->site_name ?? '') }}"
                             data-company-id="{{ $deliveryRequest->company_id ?? '' }}"
                             data-company-name="{{ strtolower($deliveryRequest->company_name ?? '') }}"
                             data-customer-id="{{ $deliveryRequest->customer_id ?? '' }}"
                             data-customer-name="{{ strtolower($deliveryRequest->customer_name ?? '') }}">
                            <div class="flex items-start gap-3">
                                <input
                                    type="checkbox"
                                    name="delivery_request_ids[]"
                                    value="{{ $deliveryRequest->id }}"
                                    class="mt-1 edit-delivery-checkbox"
                                    data-amount="{{ (float) ($deliveryRequest->delivery_rate ?? 0) }}"
                                    data-delivery-rate="{{ (float) ($deliveryRequest->delivery_rate ?? 0) }}"
                                    data-accessorial="{{ (float) ($deliveryRequest->accessorial_total ?? 0) }}"
                                    {{ $isSelected ? 'checked' : '' }}
                                    onchange="updateEditSummary()"
                                >
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-gray-900">
                                        MTM: {{ $deliveryRequest->mtm ?? 'N/A' }} | Delivery Request #{{ $deliveryRequest->id }}
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-1 mt-1 text-sm text-gray-600">
                                        <div>Site: {{ $deliveryRequest->site_name ?: 'N/A' }}</div>
                                        <div>Booking Date: {{ $deliveryRequest->booking_date ? \Carbon\Carbon::parse($deliveryRequest->booking_date)->format('M d, Y') : 'N/A' }}</div>
                                        <div>Delivery Date: {{ $deliveryRequest->delivery_date ? \Carbon\Carbon::parse($deliveryRequest->delivery_date)->format('M d, Y') : 'N/A' }}</div>
                                        <div>Amount: P{{ number_format((float) ($deliveryRequest->delivery_rate ?? 0), 2) }}</div>
                                        <div>Company: {{ $deliveryRequest->company_name ?? 'N/A' }}</div>
                                        <div>Customer: {{ $deliveryRequest->customer_name ?? 'N/A' }}</div>
                                        <div>Status: {{ $deliveryRequest->status_name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-4"></i>
                            <p>No delivery requests available for this SOA.</p>
                        </div>
                    @endforelse
                    @if(count($editableDeliveryRequests) > 0)
                        <div id="editDeliveryRequestsEmptyState" class="hidden text-center py-8 text-gray-500">
                            <i class="fas fa-search text-3xl mb-4"></i>
                            <p>No delivery requests found for the selected criteria.</p>
                        </div>
                    @endif
                </div>
                @error('delivery_request_ids')<p class="mt-3 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <!-- SOA Summary -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">SOA Summary</h3>
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">

                    <!-- Left: adjustments first, breakdown second -->
                    <div class="flex-1 min-w-0 space-y-4">

                        <!-- 1. Adjustments (first) -->
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-sliders-h text-gray-400"></i> Adjustments
                            </h4>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 mb-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                                    <select name="discount_type" id="edit_discount_type" onchange="updateEditSummary()"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                        <option value="" {{ old('discount_type', $soa->discount_type) == '' ? 'selected' : '' }}>None</option>
                                        <option value="discount" {{ old('discount_type', $soa->discount_type) == 'discount' ? 'selected' : '' }}>Discount</option>
                                        <option value="dispute" {{ old('discount_type', $soa->discount_type) == 'dispute' ? 'selected' : '' }}>Dispute</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Amount (₱)</label>
                                    <input type="number" name="discount_amount" id="edit_discount_amount"
                                           min="0" step="0.01"
                                           value="{{ old('discount_amount', $soa->discount_amount ?? 0) }}"
                                           onchange="updateEditSummary()" oninput="updateEditSummary()"
                                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Remarks / Reason</label>
                                    <input type="text" name="discount_remarks" id="edit_discount_remarks"
                                           maxlength="1000"
                                           value="{{ old('discount_remarks', $soa->discount_remarks) }}"
                                           placeholder="Why this discount/dispute?"
                                           onchange="updateEditSummary()" oninput="updateEditSummary()"
                                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Manual Adjustment (₱)</label>
                                    <input type="number" name="adjustment_amount" id="edit_adjustment_amount"
                                           step="0.01"
                                           value="{{ old('adjustment_amount', $soa->adjustment_amount ?? 0) }}"
                                           placeholder="+ or −"
                                           onchange="updateEditSummary()" oninput="updateEditSummary()"
                                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    <p class="mt-1 text-xs text-gray-400">Negative to deduct, positive to add</p>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Adjustment Remarks</label>
                                    <input type="text" name="adjustment_remarks" id="edit_adjustment_remarks"
                                           maxlength="1000"
                                           value="{{ old('adjustment_remarks', $soa->adjustment_remarks) }}"
                                           placeholder="Reason for manual adjustment"
                                           onchange="updateEditSummary()" oninput="updateEditSummary()"
                                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                </div>
                            </div>
                        </div>

                        <!-- 2. Live breakdown (second) -->
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm space-y-2">
                            <div class="flex justify-between text-gray-600">
                                <span class="flex items-center gap-1.5"><i class="fas fa-boxes text-gray-400 text-xs"></i> Selected Line Items</span>
                                <span id="editSelectedRequests" class="font-semibold text-gray-800">0</span>
                            </div>

                            <!-- Rate source breakdown -->
                            <div id="editRateBreakdown" class="hidden flex-col gap-1 pl-1">
                                <div class="flex justify-between text-sky-600 text-xs">
                                    <span class="flex items-center gap-1"><i class="fas fa-truck text-xs w-3"></i> Delivery Rate</span>
                                    <span id="editDeliveryRateAmt" class="font-medium">₱0.00</span>
                                </div>
                                <div class="flex justify-between text-purple-600 text-xs">
                                    <span class="flex items-center gap-1"><i class="fas fa-tags text-xs w-3"></i> Accessorial</span>
                                    <span id="editAccessorialAmt" class="font-medium">₱0.00</span>
                                </div>
                            </div>

                            <div class="flex justify-between text-gray-600">
                                <span class="flex items-center gap-1.5"><i class="fas fa-receipt text-gray-400 text-xs"></i> Subtotal</span>
                                <span id="editSubtotal" class="font-semibold text-gray-800">₱0.00</span>
                            </div>
                            <div id="editDiscountRow" class="hidden flex-col gap-0.5">
                                <div class="flex justify-between text-red-600">
                                    <span class="flex items-center gap-1.5"><i class="fas fa-tag text-xs"></i> <span id="editDiscountLabel">Discount</span></span>
                                    <span id="editDiscountAmt" class="font-semibold">-₱0.00</span>
                                </div>
                                <p id="editDiscountRemarks" class="text-xs text-gray-400 italic pl-5 hidden"></p>
                            </div>
                            <div id="editAdjustmentRow" class="hidden flex-col gap-0.5">
                                <div class="flex justify-between text-blue-600">
                                    <span class="flex items-center gap-1.5"><i class="fas fa-sliders-h text-xs"></i> Manual Adjustment</span>
                                    <span id="editAdjustmentAmt" class="font-semibold">₱0.00</span>
                                </div>
                                <p id="editAdjustmentRemarks" class="text-xs text-gray-400 italic pl-5 hidden"></p>
                            </div>
                            <div class="flex justify-between font-bold text-gray-900 border-t border-gray-300 pt-2">
                                <span class="flex items-center gap-1.5"><i class="fas fa-check-circle text-emerald-500 text-xs"></i> Final Total</span>
                                <span id="editTotalAmount" class="text-emerald-700 text-base">₱0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: action buttons -->
                    <div class="flex flex-col gap-3 sm:flex-row lg:flex-col lg:w-44">
                        <button type="button" onclick="calculateEditTotal()" class="inline-flex w-full items-center justify-center rounded-lg bg-blue-500 px-6 py-2.5 text-white hover:bg-blue-600">
                            <i class="fas fa-calculator mr-2"></i>Show Summary
                        </button>
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-green-600 px-6 py-2.5 text-white hover:bg-green-700">
                            <i class="fas fa-save mr-2"></i>Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Show Summary Modal -->
<div id="editSummaryModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/65 px-4 backdrop-blur-[2px]">
    <div class="w-full max-w-4xl rounded-xl bg-white shadow-[0_30px_80px_rgba(15,23,42,0.35)] ring-1 ring-slate-200/80">
        <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-4 py-4 sm:px-6">
            <div>
                <h3 class="text-xl font-semibold text-gray-900">Selected Delivery Request Summary</h3>
                <p class="mt-1 text-sm text-gray-500">Review selected delivery requests and totals before saving.</p>
            </div>
            <button type="button" onclick="closeEditSummaryModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="max-h-[70vh] overflow-y-auto px-4 py-4 sm:px-6">
            <div id="editSummaryModalEmpty" class="hidden rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-10 text-center text-gray-500">
                <i class="fas fa-inbox text-3xl mb-3"></i>
                <p class="font-medium">No delivery requests selected yet.</p>
            </div>

            <div id="editSummaryModalContent" class="hidden">
                <div class="mb-4 grid grid-cols-2 gap-3 md:grid-cols-4">
                    <div class="rounded-lg bg-blue-50 px-4 py-3">
                        <p class="text-xs text-blue-600 font-medium">Selected</p>
                        <p id="editModalSelectedCount" class="mt-1 text-2xl font-bold text-blue-900">0</p>
                    </div>
                    <div class="rounded-lg bg-sky-50 px-4 py-3">
                        <p class="text-xs text-sky-600 font-medium flex items-center gap-1"><i class="fas fa-truck text-xs"></i> Delivery Rate</p>
                        <p id="editModalDeliveryRateTotal" class="mt-1 text-lg font-bold text-sky-700">PHP 0.00</p>
                    </div>
                    <div class="rounded-lg bg-purple-50 px-4 py-3">
                        <p class="text-xs text-purple-600 font-medium flex items-center gap-1"><i class="fas fa-tags text-xs"></i> Accessorial</p>
                        <p id="editModalAccessorialTotal" class="mt-1 text-lg font-bold text-purple-700">PHP 0.00</p>
                    </div>
                    <div class="rounded-lg bg-emerald-50 px-4 py-3">
                        <p class="text-xs text-emerald-600 font-medium">Grand Subtotal</p>
                        <p id="editModalGrandTotal" class="mt-1 text-lg font-bold text-emerald-700">PHP 0.00</p>
                    </div>
                </div>

                <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p id="editModalPaginationText" class="text-sm text-gray-500">Showing 0 to 0 of 0 rows</p>
                    <div class="flex items-center gap-2 self-start sm:self-auto">
                        <span class="text-sm text-gray-500">Rows</span>
                        <select id="editModalPageSize" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            <option value="5" selected>5</option>
                            <option value="10">10</option>
                            <option value="15">15</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100 text-left text-gray-700">
                            <tr>
                                <th class="px-4 py-3 font-semibold">MTM</th>
                                <th class="px-4 py-3 font-semibold">Booking</th>
                                <th class="px-4 py-3 font-semibold">Delivery</th>
                                <th class="px-4 py-3 font-semibold">Company</th>
                                <th class="px-4 py-3 font-semibold">Customer</th>
                                <th class="px-4 py-3 font-semibold text-sky-700">Delivery Rate</th>
                                <th class="px-4 py-3 font-semibold text-purple-700">Accessorial</th>
                                <th class="px-4 py-3 font-semibold">Total</th>
                            </tr>
                        </thead>
                        <tbody id="editModalRows" class="divide-y divide-gray-200 text-gray-700"></tbody>
                    </table>
                </div>

                <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
                    <button type="button" id="editModalPrev" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50">Previous</button>
                    <span id="editModalPageIndicator" class="min-w-[88px] text-center text-sm font-medium text-gray-700">Page 1 of 1</span>
                    <button type="button" id="editModalNext" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50">Next</button>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-gray-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-end sm:px-6">
            <button type="button" onclick="closeEditSummaryModal()" class="inline-flex w-full items-center justify-center rounded-lg bg-gray-100 px-4 py-2 text-gray-700 hover:bg-gray-200 sm:w-auto">
                Close
            </button>
        </div>
    </div>
</div>

<script>
const editLineItems = [
    @foreach($editableDeliveryRequests as $req)
    {
        id: {{ $req->id }},
        mtm: '{{ addslashes($req->mtm ?? 'N/A') }}',
        siteName: '{{ addslashes($req->site_name ?? '') }}',
        bookingDate: '{{ $req->booking_date ?? '' }}',
        deliveryDate: '{{ $req->delivery_date ?? '' }}',
        deliveryRate: {{ (float) ($req->delivery_rate ?? 0) }},
        accessorialTotal: {{ (float) ($req->accessorial_total ?? 0) }},
        companyId: '{{ $req->company_id ?? '' }}',
        companyName: '{{ addslashes($req->company_name ?? 'N/A') }}',
        customerId: '{{ $req->customer_id ?? '' }}',
        customerName: '{{ addslashes($req->customer_name ?? 'N/A') }}'
    }@if(!$loop->last),@endif
    @endforeach
];

let editModalItems = [];
let editModalCurrentPage = 1;

function formatPesoEdit(amount) {
    return `₱${Number(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function formatPHP(amount) {
    return `PHP ${Number(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function formatDateEdit(val) {
    if (!val) return 'N/A';
    try {
        const d = new Date((val.includes('T') ? val : val + 'T00:00:00'));
        return isNaN(d.getTime()) ? val : d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    } catch { return val; }
}

function updateEditSummary() {
    const checkboxes = document.querySelectorAll('.edit-delivery-checkbox:checked');
    let drTotal = 0, acTotal = 0;
    checkboxes.forEach((cb) => {
        drTotal += Number(cb.dataset.deliveryRate || 0);
        acTotal += Number(cb.dataset.accessorial || 0);
    });
    const subtotal = drTotal + acTotal;

    const discountType     = document.getElementById('edit_discount_type').value;
    const discountAmt      = Math.max(0, parseFloat(document.getElementById('edit_discount_amount').value) || 0);
    const discountRemarks  = document.getElementById('edit_discount_remarks').value;
    const adjustmentAmt    = parseFloat(document.getElementById('edit_adjustment_amount').value) || 0;
    const adjustmentRemarks = document.getElementById('edit_adjustment_remarks').value;
    const finalTotal = Math.max(0, subtotal - discountAmt + adjustmentAmt);

    document.getElementById('editSelectedRequests').textContent = checkboxes.length;

    // Rate breakdown panel
    const ratePanel = document.getElementById('editRateBreakdown');
    if (checkboxes.length > 0 && (drTotal > 0 || acTotal > 0)) {
        document.getElementById('editDeliveryRateAmt').textContent = formatPesoEdit(drTotal);
        document.getElementById('editAccessorialAmt').textContent  = formatPesoEdit(acTotal);
        ratePanel.classList.remove('hidden');
        ratePanel.classList.add('flex');
    } else {
        ratePanel.classList.add('hidden');
        ratePanel.classList.remove('flex');
    }

    document.getElementById('editSubtotal').textContent = formatPesoEdit(subtotal);

    // Discount row
    const discRow = document.getElementById('editDiscountRow');
    if (discountType && discountAmt > 0) {
        document.getElementById('editDiscountLabel').textContent = discountType === 'dispute' ? 'Dispute' : 'Discount';
        document.getElementById('editDiscountAmt').textContent = `-${formatPesoEdit(discountAmt)}`;
        const dr = document.getElementById('editDiscountRemarks');
        dr.textContent = discountRemarks || '';
        dr.classList.toggle('hidden', !discountRemarks);
        discRow.classList.remove('hidden');
        discRow.classList.add('flex');
    } else {
        discRow.classList.add('hidden');
        discRow.classList.remove('flex');
    }

    // Adjustment row
    const adjRow = document.getElementById('editAdjustmentRow');
    if (adjustmentAmt !== 0) {
        const sign = adjustmentAmt >= 0 ? '' : '-';
        document.getElementById('editAdjustmentAmt').textContent = `${sign}${formatPesoEdit(Math.abs(adjustmentAmt))}`;
        const ar = document.getElementById('editAdjustmentRemarks');
        ar.textContent = adjustmentRemarks || '';
        ar.classList.toggle('hidden', !adjustmentRemarks);
        adjRow.classList.remove('hidden');
        adjRow.classList.add('flex');
    } else {
        adjRow.classList.add('hidden');
        adjRow.classList.remove('flex');
    }

    document.getElementById('editTotalAmount').textContent = formatPesoEdit(finalTotal);
}

function calculateEditTotal() {
    const checkedIds = new Set(
        Array.from(document.querySelectorAll('.edit-delivery-checkbox:checked')).map(cb => parseInt(cb.value))
    );
    editModalItems = editLineItems.filter(item => checkedIds.has(item.id));

    const modal     = document.getElementById('editSummaryModal');
    const emptyEl   = document.getElementById('editSummaryModalEmpty');
    const contentEl = document.getElementById('editSummaryModalContent');

    if (editModalItems.length === 0) {
        emptyEl.classList.remove('hidden');
        contentEl.classList.add('hidden');
    } else {
        emptyEl.classList.add('hidden');
        contentEl.classList.remove('hidden');

        let drTotal = 0, acTotal = 0;
        editModalItems.forEach(item => { drTotal += item.deliveryRate; acTotal += item.accessorialTotal; });

        document.getElementById('editModalSelectedCount').textContent    = editModalItems.length;
        document.getElementById('editModalDeliveryRateTotal').textContent = formatPHP(drTotal);
        document.getElementById('editModalAccessorialTotal').textContent  = formatPHP(acTotal);
        document.getElementById('editModalGrandTotal').textContent        = formatPHP(drTotal + acTotal);

        editModalCurrentPage = 1;
        renderEditModalRows();
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function renderEditModalRows() {
    const pageSize  = parseInt(document.getElementById('editModalPageSize').value, 10);
    const total     = editModalItems.length;
    const totalPages = Math.max(1, Math.ceil(total / pageSize));
    if (editModalCurrentPage > totalPages) editModalCurrentPage = totalPages;

    const start   = total === 0 ? 0 : (editModalCurrentPage - 1) * pageSize;
    const end     = Math.min(start + pageSize, total);
    const visible = editModalItems.slice(start, end);

    document.getElementById('editModalRows').innerHTML = visible.map(item => {
        const rowTotal = item.deliveryRate + item.accessorialTotal;
        return `<tr class="hover:bg-gray-50">
            <td class="px-4 py-3 align-top">
                <div class="font-medium text-gray-900">${item.mtm}</div>
                <div class="text-xs text-gray-500">DR #${item.id}</div>
            </td>
            <td class="px-4 py-3 align-top text-sm">${formatDateEdit(item.bookingDate)}</td>
            <td class="px-4 py-3 align-top text-sm">${formatDateEdit(item.deliveryDate)}</td>
            <td class="px-4 py-3 align-top text-sm">${item.companyName}</td>
            <td class="px-4 py-3 align-top text-sm">${item.customerName}</td>
            <td class="px-4 py-3 align-top text-sm ${item.deliveryRate > 0 ? 'text-sky-700 font-medium' : 'text-gray-300'}">${item.deliveryRate > 0 ? formatPHP(item.deliveryRate) : '—'}</td>
            <td class="px-4 py-3 align-top text-sm ${item.accessorialTotal > 0 ? 'text-purple-700 font-medium' : 'text-gray-300'}">${item.accessorialTotal > 0 ? formatPHP(item.accessorialTotal) : '—'}</td>
            <td class="px-4 py-3 align-top font-bold text-gray-900">${formatPHP(rowTotal)}</td>
        </tr>`;
    }).join('');

    const startDisplay = total === 0 ? 0 : start + 1;
    document.getElementById('editModalPaginationText').textContent  = `Showing ${startDisplay} to ${total === 0 ? 0 : end} of ${total} rows`;
    document.getElementById('editModalPageIndicator').textContent   = `Page ${editModalCurrentPage} of ${totalPages}`;
    document.getElementById('editModalPrev').disabled = editModalCurrentPage <= 1;
    document.getElementById('editModalNext').disabled = editModalCurrentPage >= totalPages;
}

function closeEditSummaryModal() {
    const modal = document.getElementById('editSummaryModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

function populateEditFilterDropdowns() {
    const companySelect = document.getElementById('editFilterByCompany');
    const customerSelect = document.getElementById('editFilterByCustomer');

    if (!companySelect || !customerSelect) {
        return;
    }

    companySelect.innerHTML = '<option value="">All Companies</option>';
    customerSelect.innerHTML = '<option value="">All Customers</option>';

    const companies = new Map();
    const customers = new Map();

    editLineItems.forEach((item) => {
        if (item.companyId && item.companyName) {
            companies.set(String(item.companyId), item.companyName);
        }

        if (item.customerId && item.customerName) {
            customers.set(String(item.customerId), item.customerName);
        }
    });

    Array.from(companies.entries())
        .sort((a, b) => a[1].localeCompare(b[1]))
        .forEach(([id, name]) => {
            const option = document.createElement('option');
            option.value = id;
            option.textContent = name;
            companySelect.appendChild(option);
        });

    Array.from(customers.entries())
        .sort((a, b) => a[1].localeCompare(b[1]))
        .forEach(([id, name]) => {
            const option = document.createElement('option');
            option.value = id;
            option.textContent = name;
            customerSelect.appendChild(option);
        });
}

function filterEditDeliveryRequests() {
    const items = document.querySelectorAll('.edit-delivery-item');
    const searchTerm = (document.getElementById('editSearchDeliveryRequests')?.value || '').toLowerCase().trim();
    const companyId = document.getElementById('editFilterByCompany')?.value || '';
    const customerId = document.getElementById('editFilterByCustomer')?.value || '';
    const emptyState = document.getElementById('editDeliveryRequestsEmptyState');
    const resultsCount = document.getElementById('editResultsCount');

    let visibleCount = 0;

    items.forEach((item) => {
        const mtm = item.dataset.mtm || '';
        const site = item.dataset.site || '';
        const companyName = item.dataset.companyName || '';
        const customerName = item.dataset.customerName || '';
        const itemCompanyId = item.dataset.companyId || '';
        const itemCustomerId = item.dataset.customerId || '';

        const matchesSearch = !searchTerm
            || mtm.includes(searchTerm)
            || site.includes(searchTerm)
            || companyName.includes(searchTerm)
            || customerName.includes(searchTerm);
        const matchesCompany = !companyId || itemCompanyId === companyId;
        const matchesCustomer = !customerId || itemCustomerId === customerId;
        const isVisible = matchesSearch && matchesCompany && matchesCustomer;

        item.classList.toggle('hidden', !isVisible);

        if (isVisible) {
            visibleCount++;
        }
    });

    if (resultsCount) {
        resultsCount.textContent = visibleCount;
    }

    if (emptyState) {
        emptyState.classList.toggle('hidden', visibleCount !== 0);
    }
}

function selectAllRequests() {
    document.querySelectorAll('.edit-delivery-item:not(.hidden) .edit-delivery-checkbox').forEach((cb) => { cb.checked = true; });
    updateEditSummary();
}

function unselectAllRequests() {
    document.querySelectorAll('.edit-delivery-item:not(.hidden) .edit-delivery-checkbox').forEach((cb) => { cb.checked = false; });
    updateEditSummary();
}

document.addEventListener('DOMContentLoaded', function () {
    populateEditFilterDropdowns();
    filterEditDeliveryRequests();
    updateEditSummary();

    document.getElementById('editSummaryModal').addEventListener('click', function (e) {
        if (e.target === this) closeEditSummaryModal();
    });

    document.getElementById('editModalPageSize').addEventListener('change', function () {
        editModalCurrentPage = 1;
        renderEditModalRows();
    });

    document.getElementById('editModalPrev').addEventListener('click', function () {
        if (editModalCurrentPage > 1) { editModalCurrentPage--; renderEditModalRows(); }
    });

    document.getElementById('editModalNext').addEventListener('click', function () {
        const pageSize   = parseInt(document.getElementById('editModalPageSize').value, 10);
        const totalPages = Math.max(1, Math.ceil(editModalItems.length / pageSize));
        if (editModalCurrentPage < totalPages) { editModalCurrentPage++; renderEditModalRows(); }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeEditSummaryModal();
    });
});
</script>
@endsection
