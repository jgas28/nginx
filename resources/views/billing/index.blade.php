@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">
        <h2 class="text-2xl font-bold mb-4">Delivery Requests</h2>

        <!-- Show success message if available -->
        @if(session('success'))
            <div class="alert alert-success bg-green-200 p-4 mb-4 rounded-lg">
                <span class="text-green-800">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Filter by company -->
        <form action="{{ route('billing.index') }}" method="GET" class="mb-4 flex items-center space-x-4">
            <div>
                <label for="company_id" class="text-sm">Filter by Company:</label>
                <select name="company_id" id="company_id" class="form-select mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ $company->id == $companyId ? 'selected' : '' }}>
                            {{ $company->company_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary bg-blue-600 text-white px-6 py-2 rounded-lg shadow-md">
                Apply Filter
            </button>
        </form>

        <!-- Table for displaying delivery requests -->
        <form id="soaForm" action="{{ route('billing.createSOA.form') }}" method="GET">
            @csrf
            <!-- Button to create SOA, initially hidden -->
            <div class="mb-4">
                <button type="submit" id="createSOAButton" class="bg-blue-600 text-white px-6 py-2 rounded-lg shadow-md opacity-50 cursor-not-allowed" disabled>
                    Create SOA
                </button>
            </div>

            <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                <table class="table-auto w-full border-collapse">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 border text-left">
                                <input type="checkbox" id="selectAll" class="cursor-pointer" />
                            </th>
                            <th class="px-4 py-2 border text-left">MTM Name</th>
                            <th class="px-4 py-2 border text-left">Delivery Rate</th>
                            <th class="px-4 py-2 border text-left">Add On rate</th>
                            <th class="px-4 py-2 border text-left">Total</th>
                            <th class="px-4 py-2 border text-left">Delivery Date</th>
                            <th class="px-4 py-2 border text-left">Delivery Type</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($deliveryRequests as $deliveryRequest)
                        @php
                            $addOnRate = null;
                            $adjustedDeliveryRate = $deliveryRequest->delivery_rate; // Default adjusted delivery rate
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
                        @endphp

                        <tr>
                            <td class="px-4 py-2 border text-center">
                                <input type="checkbox" name="delivery_requests[]" value="{{ $deliveryRequest->id }}" class="delivery-checkbox cursor-pointer">
                            </td>
                            <td class="px-4 py-2 border">{{ $deliveryRequest->mtm }}</td>
                            <td class="px-4 py-2 border">
                                {{ number_format($adjustedDeliveryRate, 2) }}
                            </td>
                            <td class="px-4 py-2 border">
                                @if($addOnRate)
                                    @if(in_array($addOnRate->add_on_rate_type_code, ['FC Less 5KM', 'ZC Less 5KM']))
                                        <div>{{ $addOnRate->rate }}</div>
                                    @else
                                        <div>{{ $addOnRate->percent_rate }}</div>
                                    @endif
                                @else
                                    <div></div>
                                @endif
                            </td>
                            <td class="px-4 py-2 border">{{ number_format($deliveryRequest->delivery_rate,2) }}</td>
                            <td class="px-4 py-2 border">{{ $deliveryRequest->delivery_date }}</td>
                            <td class="px-4 py-2 border">{{ $deliveryRequest->delivery_type }}</td>
                        </tr>
                    @endforeach
                </tbody>

                </table>
            </div>
        </form>
    </div>

    <!-- Tailwind custom script to enable/disable button based on checkbox selection -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const selectAllCheckbox = document.getElementById('selectAll');
            const deliveryCheckboxes = document.querySelectorAll('.delivery-checkbox');
            const createSOAButton = document.getElementById('createSOAButton');
            const soaForm = document.getElementById('soaForm');

            // Function to check/uncheck all checkboxes
            selectAllCheckbox.addEventListener('change', function() {
                deliveryCheckboxes.forEach(function(checkbox) {
                    checkbox.checked = selectAllCheckbox.checked;
                });
                toggleCreateSOAButton();
            });

            // Function to toggle button enabled/disabled
            function toggleCreateSOAButton() {
                const checkedCheckboxes = document.querySelectorAll('.delivery-checkbox:checked');
                if (checkedCheckboxes.length > 0) {
                    createSOAButton.disabled = false;
                    createSOAButton.classList.remove('opacity-50', 'cursor-not-allowed');
                    createSOAButton.classList.add('opacity-100', 'cursor-pointer');
                } else {
                    createSOAButton.disabled = true;
                    createSOAButton.classList.remove('opacity-100', 'cursor-pointer');
                    createSOAButton.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }

            // Event listener for individual checkbox changes
            deliveryCheckboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', toggleCreateSOAButton);
            });

            // Submit the form with the selected delivery request IDs (no hidden input needed)
            soaForm.addEventListener('submit', function(event) {
                const selectedRequests = [];
                deliveryCheckboxes.forEach(function(checkbox) {
                    if (checkbox.checked) {
                        selectedRequests.push(checkbox.value);
                    }
                });

                // If no checkboxes are selected, prevent form submission
                if (selectedRequests.length === 0) {
                    event.preventDefault();
                    alert("Please select at least one delivery request.");
                    return;
                }

                // The form will now automatically submit the selected delivery_requests[] array
            });
        });
    </script>
@endsection
