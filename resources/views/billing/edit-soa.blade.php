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

                <div id="editDeliveryRequestsContainer" class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg">
                    @forelse($editableDeliveryRequests as $deliveryRequest)
                        @php
                            $selectedIds = old('delivery_request_ids', $selectedDeliveryRequestIds);
                            $isSelected = in_array((int) $deliveryRequest->id, collect($selectedIds)->map(fn ($id) => (int) $id)->all(), true);
                        @endphp
                        <div class="border-b border-gray-200 p-4 hover:bg-gray-50">
                            <div class="flex items-start gap-3">
                                <input
                                    type="checkbox"
                                    name="delivery_request_ids[]"
                                    value="{{ $deliveryRequest->id }}"
                                    class="mt-1 edit-delivery-checkbox"
                                    data-amount="{{ (float) ($deliveryRequest->delivery_rate ?? 0) }}"
                                    {{ $isSelected ? 'checked' : '' }}
                                    onchange="updateEditSummary()"
                                >
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-gray-900">
                                        MTM: {{ $deliveryRequest->mtm ?? 'N/A' }} | Delivery Request #{{ $deliveryRequest->id }}
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-1 mt-1 text-sm text-gray-600">
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
                                <span class="flex items-center gap-1.5"><i class="fas fa-boxes text-gray-400 text-xs"></i> Selected Delivery Requests</span>
                                <span id="editSelectedRequests" class="font-semibold text-gray-800">0</span>
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

                    <!-- Right: save button -->
                    <div class="flex flex-col gap-3 sm:flex-row lg:flex-col lg:w-44">
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-6 py-2.5 text-white hover:bg-blue-700">
                            <i class="fas fa-save mr-2"></i>Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function formatPesoEdit(amount) {
    return `₱${Number(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function updateEditSummary() {
    const checkboxes = document.querySelectorAll('.edit-delivery-checkbox:checked');
    let subtotal = 0;
    checkboxes.forEach((cb) => { subtotal += Number(cb.dataset.amount || 0); });

    const discountType    = document.getElementById('edit_discount_type').value;
    const discountAmt     = Math.max(0, parseFloat(document.getElementById('edit_discount_amount').value) || 0);
    const discountRemarks = document.getElementById('edit_discount_remarks').value;
    const adjustmentAmt   = parseFloat(document.getElementById('edit_adjustment_amount').value) || 0;
    const adjustmentRemarks = document.getElementById('edit_adjustment_remarks').value;
    const finalTotal = Math.max(0, subtotal - discountAmt + adjustmentAmt);

    // Count
    document.getElementById('editSelectedRequests').textContent = checkboxes.length;
    // Subtotal
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

    // Final total
    document.getElementById('editTotalAmount').textContent = formatPesoEdit(finalTotal);
}

function selectAllRequests() {
    document.querySelectorAll('.edit-delivery-checkbox').forEach((cb) => { cb.checked = true; });
    updateEditSummary();
}

function unselectAllRequests() {
    document.querySelectorAll('.edit-delivery-checkbox').forEach((cb) => { cb.checked = false; });
    updateEditSummary();
}

document.addEventListener('DOMContentLoaded', function () {
    updateEditSummary();
});
</script>
@endsection
