@extends('layouts.app')

@section('title', 'Edit SOA - ' . ($soa->soa_number ?? 'N/A'))

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-6xl mx-auto px-4">
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit SOA</h1>
                    <p class="text-gray-600 mt-2">Update statement details for {{ $soa->soa_number ?? 'N/A' }}</p>
                </div>
                <a href="{{ route('billing.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
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

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-600">
                        <p id="editSelectedRequests"><strong>Selected Delivery Requests:</strong> 0</p>
                        <p id="editTotalAmount"><strong>Total Amount:</strong> P0.00</p>
                        <p><strong>Paid Amount:</strong> P{{ number_format($soa->paid_amount, 2) }}</p>
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                        <i class="fas fa-save mr-2"></i>Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function updateEditSummary() {
    const checkboxes = document.querySelectorAll('.edit-delivery-checkbox:checked');
    let total = 0;

    checkboxes.forEach((checkbox) => {
        total += Number(checkbox.dataset.amount || 0);
    });

    document.getElementById('editSelectedRequests').textContent = `Selected Delivery Requests: ${checkboxes.length}`;
    document.getElementById('editTotalAmount').textContent = `Total Amount: P${total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function selectAllRequests() {
    document.querySelectorAll('.edit-delivery-checkbox').forEach((checkbox) => {
        checkbox.checked = true;
    });
    updateEditSummary();
}

function unselectAllRequests() {
    document.querySelectorAll('.edit-delivery-checkbox').forEach((checkbox) => {
        checkbox.checked = false;
    });
    updateEditSummary();
}

document.addEventListener('DOMContentLoaded', function () {
    updateEditSummary();
});
</script>
@endsection
