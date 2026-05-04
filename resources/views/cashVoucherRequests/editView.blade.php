@extends('layouts.app')

@section('title', 'FCZCNYX')

@section('content')
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="bg-blue-600 text-white text-center py-4">
        <h4 class="text-xl font-semibold flex items-center justify-center gap-2">
            <!-- Heroicon: Document -->
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 4H7a2 2 0 01-2-2V6a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z"/>
            </svg>
            Cash Voucher Request
        </h4> 
    </div>
    <div class="p-6">
        <form action="{{ route('cashVoucherRequests.cvrUpdate', ['id' => $deliveryRequestId]) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- CVR Info -->
            <fieldset class="mb-6 p-4 border border-gray-200 rounded bg-gray-50">
                <legend class="text-blue-600 font-semibold text-sm mb-3 flex items-center gap-1">
                    <!-- Info Icon -->
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/>
                    </svg>
                    CVR Information
                </legend>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="cvr_number" class="block text-sm font-medium text-gray-700">CVR Number</label>
                        <input type="text" name="cvr_number" id="cvr_number" class="mt-1 block w-full rounded border-gray-300 shadow-sm bg-gray-100" value="{{ $cashVouchers->cvr_number }}" readonly>
                    </div>

                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
                        <input type="number" step="0.01" min="0" required name="amount" id="amount"
                               class="mt-1 block w-full rounded border-gray-300 shadow-sm"
                               value="{{ old('amount', $cashVouchers->amount ?? '') }}">
                    </div>

                    <div>
                        <label for="request_type" class="block text-sm font-medium text-gray-700">Request Type</label>
                        <select name="request_type" id="request_type" required class="mt-1 block w-full rounded border-gray-300 shadow-sm">
                            <option value="" disabled {{ old('request_type', $cashVouchers->request_type_id ?? '') == '' ? 'selected' : '' }}>Select Type</option>
                            @foreach($requestType as $requestTypes)
                                <option value="{{ $requestTypes->id }}"
                                    {{ old('request_type', $cashVouchers->request_type ?? '') == $requestTypes->id ? 'selected' : '' }}>
                                    {{ $requestTypes->request_type }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="requestor" class="block text-sm font-medium text-gray-700">Requestor</label>
                        <select name="requestor" id="requestor" required class="mt-1 block w-full rounded border-gray-300 shadow-sm">
                            <option value="" disabled {{ old('requestor', $cashVouchers->requestor ?? '') == '' ? 'selected' : '' }}>Select Employee</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}"
                                    {{ old('requestor', $cashVouchers->requestor ?? '') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->fname }} {{ $employee->lname }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </fieldset>

            <!-- Remarks -->
            <fieldset class="mb-6 p-4 border border-gray-200 rounded bg-gray-50">
                <legend class="text-blue-600 font-semibold text-sm mb-3 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2M7 8H5a2 2 0 00-2 2v6a2 2 0 002 2h2m10-4H7"/>
                    </svg>
                    Remarks Information
                </legend>
                <div id="remarks_fields" class="space-y-2">
                    @if(!empty($remarks))
                        @foreach($remarks as $remark)
                            <div class="flex gap-2">
                                <input type="text" name="remarks[]" class="flex-1 border rounded px-3 py-2" value="{{ $remark }}" placeholder="Enter Remarks value">
                                <button type="button" class="remove_remarks bg-red-500 text-white text-sm px-3 py-1 rounded">Remove</button>
                            </div>
                        @endforeach
                    @endif
                </div>
                <button type="button" id="add_remarks" class="mt-3 text-blue-600 text-sm hover:underline">
                    + Add Remarks
                </button>
            </fieldset>

            <!-- Submit -->
            <div class="text-center">
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded shadow hover:bg-green-700 transition">
                    Update Request
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Scripts -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const addRemarksBtn = document.getElementById('add_remarks');
        const remarksContainer = document.getElementById('remarks_fields');

        if (addRemarksBtn && remarksContainer) {
            addRemarksBtn.addEventListener('click', function () {
                const div = document.createElement('div');
                div.className = 'flex gap-2 mt-2';
                div.innerHTML = `
                    <input type="text" name="remarks[]" class="flex-1 border rounded px-3 py-2" placeholder="Enter Remarks value">
                    <button type="button" class="remove_remarks bg-red-500 text-white text-sm px-3 py-1 rounded">Remove</button>
                `;
                remarksContainer.appendChild(div);
            });

            remarksContainer.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove_remarks')) {
                    e.target.parentElement.remove();
                }
            });
        }
    });
</script>
@endsection
