@extends('layouts.app')

@section('title', 'Cash Voucher Allocation')

@section('content')
<div class="max-w mx-auto py-10 px-6">
    <div class="bg-white border border-gray-200 rounded-xl shadow-lg p-8">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Cash Voucher Allocation</h1>

        <form action="{{ route('allocations.store') }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Truck Selection -->
                <div>
                    <label for="truck" class="block text-sm font-medium text-gray-700 mb-1">Truck</label>
                    <select name="truck" id="truck" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Select Truck</option>
                        @foreach($trucks as $truck)
                            <option value="{{ $truck->id }}">{{ $truck->truck_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Budget Amount -->
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Budget Amount</label>
                    <input type="number" step="0.01" name="amount" id="amount" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>

                <!-- Requestor -->
                <div>
                    <label for="requestor_id" class="block text-sm font-medium text-gray-700 mb-1">Requestor</label>
                    <select name="requestor_id" id="requestor_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Select Employee</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->fname }} {{ $employee->lname }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Driver -->
                <div>
                    <label for="driver_id" class="block text-sm font-medium text-gray-700 mb-1">Driver</label>
                    <select name="driver_id" id="driver_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Select Driver</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->fname }} {{ $employee->lname }}</option>
                        @endforeach
                    </select>
                </div>
            </div>


            <!-- Helpers -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Helpers</label>
                <div id="helpers-container">
                    <div class="flex items-center gap-2 mb-2">
                        <input type="text" name="helpers[]" class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Helper name">
                        <button type="button" class="remove-helper text-red-500 hover:underline">Remove</button>
                    </div>
                </div>
                <button type="button" id="add-helper" class="text-sm text-blue-600 hover:underline mt-2">+ Add Helper</button>
            </div>

            <!-- Remarks -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                <div id="remarks-container">
                    <div class="flex items-center gap-2 mb-2">
                        <input type="text" name="remarks[]" class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Remark">
                        <button type="button" class="remove-remark text-red-500 hover:underline">Remove</button>
                    </div>
                </div>
                <button type="button" id="add-remarks" class="text-sm text-blue-600 hover:underline mt-2">+ Add Remark</button>
            </div>

            <!-- Delivery Requests -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($deliveryRequests as $deliveryRequest)
                    @php $company = $deliveryRequest->company; @endphp

                    <div class="border border-gray-300 bg-gray-50 rounded-lg p-4 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Delivery Request #{{ $deliveryRequest->id }}</h3>

                        <!-- Hidden Inputs -->
                        <input type="hidden" name="delivery_request_ids[]" value="{{ $deliveryRequest->id }}">
                        <input type="hidden" name="company_ids[{{ $deliveryRequest->id }}]" value="{{ $company->id }}">
                        <input type="hidden" name="expense_type_ids[{{ $deliveryRequest->id }}]" value="{{ $deliveryRequest->expense_type_id }}">
                        <input type="hidden" name="dr_id[{{ $deliveryRequest->id }}]" value="{{ $deliveryRequest->id }}">
                        <input type="hidden" name="mtm[{{ $deliveryRequest->mtm }}]" value="{{ $deliveryRequest->mtm }}">
                        @foreach ($deliveryRequest->lineItems as $lineItem)
                            <input type="hidden" name="line_items_id[{{ $lineItem->id }}]" value="{{ $lineItem->id }}">
                            <input type="hidden" name="line_item_dr_ids[{{ $deliveryRequest->id }}]" value="{{ $deliveryRequest->id }}">
                        @endforeach

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm text-gray-600">Delivery Date</label>
                                <input type="text" class="w-full px-4 py-2 border rounded-md bg-white" value="{{ $deliveryRequest->delivery_date }}" readonly>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600">MTM</label>
                                <input type="text" class="w-full px-4 py-2 border rounded-md bg-white" value="{{ $deliveryRequest->mtm }}" readonly>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600">Company</label>
                                <input type="text" class="w-full px-4 py-2 border rounded-md bg-white" value="{{ $company->company_name }}" readonly>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600">Delivery Rate</label>
                                <input type="text" class="w-full px-4 py-2 border rounded-md bg-white" value="{{ $deliveryRequest->delivery_rate }}" readonly>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600">Truck Type</label>
                                <input type="text" class="w-full px-4 py-2 border rounded-md bg-white" value="{{ optional($deliveryRequest->truckType)->truck_code ?? 'N/A' }}" readonly>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600">Province</label>
                                <input type="text" class="w-full px-4 py-2 border rounded-md bg-white" value="{{ $deliveryRequest->area->area_code }}" readonly>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600">Region</label>
                                <input type="text" class="w-full px-4 py-2 border rounded-md bg-white" value="{{ $deliveryRequest->region->region_code }}" readonly>
                            </div>
                        </div>

                        @foreach($deliveryRequest->lineItems as $item)
                            <div class="border border-gray-200 rounded-md p-3 bg-white mb-2">
                                <div class="mb-2">
                                    <label class="block text-sm text-gray-600">Site Name</label>
                                    <input type="text" class="w-full px-4 py-2 border rounded-md bg-white" value="{{ $item->site_name }}" readonly>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600">Address</label>
                                    <input type="text" class="w-full px-4 py-2 border rounded-md bg-white" value="{{ $item->delivery_address }}" readonly>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <!-- Submit Button -->
            <div class="pt-6">
                <button type="submit" class="w-full sm:w-auto px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript for Add/Remove Helper and Remark Fields -->
<script>
    // Helpers
    document.getElementById('add-helper').addEventListener('click', function () {
        const container = document.getElementById('helpers-container');
        const newInput = document.createElement('div');
        newInput.classList.add('flex', 'items-center', 'gap-2', 'mb-2');
        newInput.innerHTML = `
            <input type="text" name="helpers[]" class="w-full border-gray-300 rounded-lg px-4 py-2" placeholder="Helper name">
            <button type="button" class="remove-helper text-red-500 hover:underline">Remove</button>
        `;
        container.appendChild(newInput);
    });

    document.getElementById('helpers-container').addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-helper')) {
            e.target.closest('div').remove();
        }
    });

    // Remarks
    document.getElementById('add-remarks').addEventListener('click', function () {
        const container = document.getElementById('remarks-container');
        const newInput = document.createElement('div');
        newInput.classList.add('flex', 'items-center', 'gap-2', 'mb-2');
        newInput.innerHTML = `
            <input type="text" name="remarks[]" class="w-full border-gray-300 rounded-lg px-4 py-2" placeholder="Remarks">
            <button type="button" class="remove-remark text-red-500 hover:underline">Remove</button>
        `;
        container.appendChild(newInput);
    });

    document.getElementById('remarks-container').addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-remark')) {
            e.target.closest('div').remove();
        }
    });
</script>
@endsection
