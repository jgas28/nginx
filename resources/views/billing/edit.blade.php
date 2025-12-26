@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h2 class="text-3xl font-semibold mb-6 text-gray-800">Edit Billing</h2>

    <form action="" method="POST">
        @csrf
        @method('POST') <!-- Use POST for update form submission -->

        {{-- ================= BILLING SUMMARY ================= --}}
        <div class="bg-indigo-50 rounded-xl p-5 shadow mb-6">
            <h3 class="font-semibold text-lg mb-3">Billing Summary</h3>

            <div class="max-h-40 overflow-y-auto text-sm space-y-3">

                {{-- DELIVERY --}}
                <div>
                    <p class="font-semibold">Delivery</p>
                    <ul id="selected-delivery-requests" class="space-y-1">
                        @php $deliveryTotal = 0; @endphp
                        @foreach($deliveryRequests as $dr)
                            <li class="flex justify-between items-center border p-2 rounded"
                                data-id="{{ $dr->id }}"
                                data-rate="{{ $dr->delivery_rate }}">
                                <span>{{ $dr->mtm }} ({{ number_format($dr->delivery_rate, 2) }})</span>
                                <button type="button" class="text-red-500"
                                    onclick="removeItem('dr', {{ $dr->id }})">&times;</button>
                            </li>
                            @php $deliveryTotal += $dr->delivery_rate; @endphp
                        @endforeach
                    </ul>
                </div>

                {{-- ACCESSORIAL --}}
                <div>
                    <p class="font-semibold">Accessorial</p>
                    <ul id="selected-line-items" class="space-y-1">
                        @php $accessorialTotal = 0; @endphp
                        @foreach($lineItems as $item)
                            <li class="flex justify-between items-center border p-2 rounded"
                                data-id="{{ $item->id }}"
                                data-rate="{{ $item->accessorial_rate }}">
                                <span>{{ $item->delivery_number }} ({{ number_format($item->accessorial_rate, 2) }})</span>
                                <button type="button" class="text-red-500"
                                    onclick="removeItem('li', {{ $item->id }})">&times;</button>
                            </li>
                            @php $accessorialTotal += $item->accessorial_rate; @endphp
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- TOTALS --}}
            <div class="grid grid-cols-3 gap-4 text-sm mt-4">
                <div>
                    <p class="text-gray-500">Delivery Total</p>
                    <p id="delivery_total" class="font-semibold">{{ number_format($deliveryTotal, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Accessorial Total</p>
                    <p id="accessorial_total" class="font-semibold">{{ number_format($accessorialTotal, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Grand Total</p>
                    <p id="grand_total" class="font-bold text-lg">
                        {{ number_format($deliveryTotal + $accessorialTotal, 2) }}
                    </p>
                </div>
            </div>

            <button type="button"
                onclick="openModal()"
                class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg">
                Add Items
            </button>
        </div>

        {{-- ================= BILLING DETAILS ================= --}}
        <div class="grid grid-cols-2 gap-6 mb-6">

            <div>
                <label class="block text-sm font-medium">SOA Number</label>
                <input name="soa_number" class="w-full border rounded-lg p-3" value="{{ old('soa_number', $billing->soa_number) }}" required>
            </div>

            <div>
                <label class="block text-sm font-medium">Company</label>
                <select id="company_id" name="company_id" class="w-full border rounded-lg p-3" required>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}"
                            {{ $company->id == old('company_id', $billing->company_id) ? 'selected' : '' }}>
                            {{ $company->company_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium">Withholding Tax</label>
                <select name="withholding_tax_id" class="w-full border rounded-lg p-3" required>
                    @foreach($withholdingTaxes as $tax)
                        <option value="{{ $tax->id }}" {{ $tax->id == old('withholding_tax_id', $billing->withholding_tax_id) ? 'selected' : '' }}>
                            {{ $tax->description }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium">Billed To</label>
                <input name="billed_to" class="w-full border rounded-lg p-3" value="{{ old('billed_to', $billing->billed_to) }}" required>
            </div>

            <div>
                <label class="block text-sm font-medium">Billing Address</label>
                <textarea name="billing_address" class="w-full border rounded-lg p-3" required>{{ old('billing_address', $billing->billing_address) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium">Billing Date</label>
                <input type="date" name="billing_date" class="w-full border rounded-lg p-3" value="{{ old('billing_date', $billing->billing_date) }}" required>
            </div>
        </div>

        {{-- HIDDEN INPUTS (ARRAY SAFE) --}}
        <div id="hidden-inputs"></div>

        <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-lg">
            Update Billing
        </button>
    </form>
</div>

<!-- Include your modal code as in the create-billing form -->

<script>
// Include JavaScript logic from your create-billing view here
</script>

@endsection
