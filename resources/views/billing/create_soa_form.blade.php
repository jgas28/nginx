@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <form action="{{ route('billing.createSOA') }}" method="POST">
        @csrf

        <!-- Collapsible MTM List -->
        <div class="mb-8">
            <label class="block text-xl font-semibold text-gray-800">Selected Delivery Requests</label>

            <!-- Button to toggle collapse -->
            <button type="button" id="toggleMTMList" class="mt-3 text-blue-600 hover:text-blue-800 focus:outline-none transition duration-300">
                <span class="font-medium">Show/Hide MTM List</span>
            </button>

            <!-- Collapsible list of selected MTM items -->
            <div id="mtmList" class="mt-4 h-0 overflow-hidden transition-all duration-500 opacity-0">
                <!-- Table for displaying delivery request details -->
                <div class="overflow-x-auto bg-white shadow-lg rounded-lg border border-gray-200">
                    <table class="table-auto w-full text-sm text-gray-600">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-left font-semibold text-gray-700">Project Name</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-700">MTM</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-700">Company Code</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-700">Delivery Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalDeliveryRate = 0; // Initialize total delivery rate
                            @endphp

                            @foreach($deliveryRequests as $deliveryRequest)
                                @php
                                    $adjustedDeliveryRate = $deliveryRequest->delivery_rate; // Default to the original delivery rate

                                    if ($deliveryRequest->lineItems->isNotEmpty()) {
                                        $lineItem = $deliveryRequest->lineItems->first();
                                        $addOnRate = $lineItem->addOnRate;

                                        if ($addOnRate) {
                                            if (in_array($addOnRate->add_on_rate_type_code, ['FC Less 5KM', 'ZC Less 5KM'])) {
                                                $adjustedDeliveryRate = $deliveryRequest->delivery_rate - $addOnRate->rate;
                                            } else {
                                                $percentRate = (float)str_replace('%', '', $addOnRate->percent_rate) / 100;
                                                $rate = $percentRate * $deliveryRequest->delivery_rate;
                                                $adjustedDeliveryRate = $deliveryRequest->delivery_rate - $rate;
                                            }
                                        }
                                    }

                                    $totalDeliveryRate += $adjustedDeliveryRate;
                                @endphp

                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-6 py-3">{{ $deliveryRequest->project_name }}</td>
                                    <td class="px-6 py-3">{{ $deliveryRequest->mtm }}</td>
                                    <td class="px-6 py-3">{{ $deliveryRequest->company->company_code }}</td>
                                    <td class="px-6 py-3 text-right">{{ number_format($adjustedDeliveryRate, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Total Delivery Rate -->
        <div class="mb-8">
            <label class="block text-xl font-semibold text-gray-800">Total Delivery Rate</label>
            <div class="mt-2 text-2xl font-semibold text-gray-900">
                ₱{{ number_format($totalDeliveryRate, 2) }}
            </div>
        </div>

        <!-- SOA Details Form -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
            <!-- SOA Number -->
            <input type="hidden" name="total_price" id="total_price" value="{{ $totalDeliveryRate }}">
            <div>
                <label for="soa_number" class="block text-lg font-medium text-gray-700">SOA Number</label>
                <input type="text" name="soa_number" id="soa_number" class="form-input mt-1 block w-full border-gray-300 shadow-sm rounded-md focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>

            <!-- Billing Date -->
            <div>
                <label for="billing_date" class="block text-lg font-medium text-gray-700">Billing Date</label>
                <input type="date" name="billing_date" id="billing_date" class="form-input mt-1 block w-full border-gray-300 shadow-sm rounded-md focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>

            <!-- Billed To -->
            <div>
                <label for="billed_to" class="block text-lg font-medium text-gray-700">Billed To</label>
                <input type="text" name="billed_to" id="billed_to" class="form-input mt-1 block w-full border-gray-300 shadow-sm rounded-md focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>

            <!-- Address To -->
            <div>
                <label for="billing_address" class="block text-lg font-medium text-gray-700">Address To</label>
                <input type="text" name="billing_address" id="billing_address" class="form-input mt-1 block w-full border-gray-300 shadow-sm rounded-md focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>

            <!-- Withholding Tax -->
            <div>
                <label for="withholding_tax_id" class="block text-lg font-medium text-gray-700">Withholding Tax</label>
                <select name="withholding_tax_id" id="withholding_tax_id" class="form-select mt-1 block w-full border-gray-300 shadow-sm rounded-md focus:ring-indigo-500 focus:border-indigo-500" required>
                    <option value="">Select Withholding Tax</option>
                    @foreach($withholdingTaxes as $tax)
                        <option value="{{ $tax->id }}">{{ $tax->description }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Prepared By -->
            <div>
                <label for="prepared_by" class="block text-lg font-medium text-gray-700">Prepared By</label>
                <select name="prepared_by" id="prepared_by" class="form-select mt-1 block w-full border-gray-300 shadow-sm rounded-md focus:ring-indigo-500 focus:border-indigo-500" required>
                    <option value="">Select Prepared By</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->fname }} {{ $user->lname }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Received By -->
            <div>
                <label for="received_by" class="block text-lg font-medium text-gray-700">Received By</label>
                <input type="text" name="received_by" id="received_by" class="form-input mt-1 block w-full border-gray-300 shadow-sm rounded-md focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="mt-8 text-left">
            <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg shadow-md hover:bg-blue-700 focus:outline-none transition duration-300">
                Create SOA
            </button>
        </div>

        <!-- Hidden field to pass selected requests -->
        <input type="hidden" name="delivery_requests" value="{{ json_encode($deliveryRequests->pluck('id')->toArray()) }}">
    </form>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const toggleButton = document.getElementById("toggleMTMList");
        const mtmList = document.getElementById("mtmList");

        toggleButton.addEventListener("click", function () {
            if (mtmList.classList.contains("h-0")) {
                mtmList.classList.remove("h-0", "opacity-0");
                mtmList.classList.add("h-auto", "opacity-100");
            } else {
                mtmList.classList.add("h-0", "opacity-0");
                mtmList.classList.remove("h-auto", "opacity-100");
            }
        });
    });
</script>
@endsection
