@extends('layouts.app')

@section('title', 'Cash Voucher Approval')

@section('content')

    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-blue-600 text-white text-center py-4">
            <h4 class="text-xl font-semibold flex items-center justify-center gap-2">
            <!-- Heroicon: Document -->
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 4H7a2 2 0 01-2-2V6a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z"/>
            </svg>
            Cash Voucher Details
            </h4>
        </div>
        <div class="p-6">
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

                   @php
                        $amounts = json_decode($cashVouchers->amount_details, true);
                        $totalAmount = is_array($amounts) ? array_sum($amounts) : 0;
                    @endphp

                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700">Total Amount</label>
                        <input type="text" name="amount" id="amount"
                            class="mt-1 block w-full rounded border-gray-300 shadow-sm bg-gray-100"
                            value="{{ number_format($totalAmount, 2) }}" readonly>
                    </div>

                    <div>
                        <label for="comapny_id" class="block text-sm font-medium text-gray-700">Company</label>
                        <input type="text" name="comapny_id" id="comapny_id" class="mt-1 block w-full rounded border-gray-300 shadow-sm bg-gray-100" value="{{ $cashVouchers->company->company_code }}" readonly>
                    </div>

                    <div>
                        <label for="supplier_id" class="block text-sm font-medium text-gray-700">Supplier</label>
                        <input type="text" name="supplier_id" id="supplier_id" class="mt-1 block w-full rounded border-gray-300 shadow-sm bg-gray-100" value="{{ $cashVouchers->suppliers->supplier_name }}" readonly>
                    </div>

                    <div>
                        <label for="expense_type_id" class="block text-sm font-medium text-gray-700">Expense Type</label>
                        <input type="text" name="expense_type_id" id="expense_type_id" class="mt-1 block w-full rounded border-gray-300 shadow-sm bg-gray-100" value="{{ $cashVouchers->expenseTypes->expense_code }}" readonly>
                    </div>

                </div>
                <div>
                    @php
                        $descriptions = json_decode($cashVouchers->description, true);
                        $amountDetails = json_decode($cashVouchers->amount_details, true);
                    @endphp

                    @if(!empty($descriptions) && !empty($amountDetails))
                        <div class="mt-6">
                            <span class="font-medium text-gray-700">Details:</span>
                            <ul class="list-disc list-inside text-gray-600 mt-2 space-y-1">
                                @foreach($descriptions as $index => $desc)
                                    <li>
                                        {{ $desc }} - {{ number_format($amountDetails[$index] ?? 0, 2) }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                <div>
                    @php $remarks = json_decode($cashVouchers->remarks, true); @endphp
                    @if(!empty($remarks))
                    <div class="mt-6">
                        <span class="font-medium text-gray-700">Remarks:</span>
                        <ul class="list-disc list-inside text-gray-600 mt-2 space-y-1">
                        @foreach($remarks as $remark)
                            <li>{{ $remark }}</li>
                        @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
                <div class="my-2">
                   <a href="{{ route('adminCV.printPreview', $cashVouchers->id) }}"
                        target="_blank"
                        class="inline-block bg-yellow-500 text-white px-5 py-2 rounded hover:bg-yellow-600 transition">
                        View CVR
                    </a>
                </div>
            </fieldset>
        </div>
    </div>

    <!-- Approval Form -->
    <form method="POST" action="{{ route('admin.storeRequest') }}">
        @csrf
        <input type="hidden" name="cvr_number" value="{{ $cashVouchers->cvr_number }}">
          <input type="hidden" name="cvr_id" value="{{ $cashVouchers->id }}">
        <!-- Payment Type Selection -->
        <fieldset class="my-6 p-4 border border-gray-200 rounded bg-gray-50">
            <legend class="text-blue-600 font-semibold text-sm mb-3">Select Payment Type</legend>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <label class="flex items-center bg-white border rounded p-4 shadow cursor-pointer">
                    <input type="radio" name="payment_type" value="cash" class="mr-3" />
                    <span>Cash</span>
                </label>
                <label class="flex items-center bg-white border rounded p-4 shadow cursor-pointer">
                    <input type="radio" name="payment_type" value="bank_transfer" class="mr-3" />
                    <span>Bank Transfer</span>
                </label>
                <label class="flex items-center bg-white border rounded p-4 shadow cursor-pointer">
                    <input type="radio" name="payment_type" value="outlet_transfer" class="mr-3" />
                    <span>Outlet Transfer</span>
                </label>
            </div>
        </fieldset>

        <!-- Cash Fields -->
        <fieldset id="cashFields" class="hidden mb-6 p-4 border border-gray-200 rounded bg-gray-50 space-y-4">
            <legend class="text-blue-600 font-semibold text-sm mb-3">Cash Details</legend>

            <div>
                <label class="block text-gray-700">Reference Number</label>
                <input type="text" name="reference_number" class="w-full border border-gray-300 rounded px-3 py-2" />
            </div>
            <div>
                <label class="block text-gray-700">Amount</label>
                <input type="number" name="cash_amount" step="0.01" class="w-full border border-gray-300 rounded px-3 py-2" />
            </div>
            <div>
                <label class="block text-gray-700">Fund Source</label>
                <select name="cash_fund_source" class="w-full border border-gray-300 rounded px-3 py-2">
                    <option value="">Select Funds</option>
                    @foreach($approves as $approve)
                        <option value="{{ $approve->id }}">{{ $approve->name }}</option>
                    @endforeach
                </select>
            </div>
        </fieldset>

        <!-- Bank Transfer Fields -->
        <fieldset id="bankTransferFields" class="hidden mb-6 p-4 border border-gray-200 rounded bg-gray-50 space-y-4">
            <legend class="text-blue-600 font-semibold text-sm mb-3">Bank Transfer Details</legend>

            <div>
                <label class="block text-gray-700">Bank Name</label>
                <input type="text" name="bank_name" class="w-full border border-gray-300 rounded px-3 py-2" />
            </div>
            <div>
                <label class="block text-gray-700">Reference Number</label>
                <input type="text" name="bank_reference_number" class="w-full border border-gray-300 rounded px-3 py-2" />
            </div>
            <div>
                <label class="block text-gray-700">Amount</label>
                <input type="number" name="bank_amount" step="0.01" class="w-full border border-gray-300 rounded px-3 py-2" />
            </div>
            <div>
                <label class="block text-gray-700">Receiver</label>
                <select nambere="bank_receiver" class="w-full border border-gray-300 rounded px-3 py-2">
                    <option value="">Select Receiver</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->fname }} {{ $employee->lname }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-700">Fund Source</label>
                <select name="bank_fund_source" class="w-full border border-gray-300 rounded px-3 py-2">
                    <option value="">Select Funds</option>
                    @foreach($approves as $approve)
                        <option value="{{ $approve->id }}">{{ $approve->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-700">Bank Charge</label>
                <input type="text" name="bank_charge" class="w-full border border-gray-300 rounded px-3 py-2" />
            </div>
        </fieldset>

        <!-- Outlet Transfer Fields -->
        <fieldset id="storeTransferFields" class="hidden mb-6 p-4 border border-gray-200 rounded bg-gray-50 space-y-4">
            <legend class="text-blue-600 font-semibold text-sm mb-3">Outlet Transfer Details</legend>

            <div>
                <label class="block text-gray-700">Outlet Name</label>
                <input type="text" name="outlet_name" class="w-full border border-gray-300 rounded px-3 py-2" />
            </div>
            <div>
                <label class="block text-gray-700">Reference Number</label>
                <input type="text" name="outlet_reference_number" class="w-full border border-gray-300 rounded px-3 py-2" />
            </div>
            <div>
                <label class="block text-gray-700">Amount</label>
                <input type="number" name="outlet_amount" step="0.01" class="w-full border border-gray-300 rounded px-3 py-2" />
            </div>
            <div>
                <label class="block text-gray-700">Receiver</label>
                <select name="outlet_receiver" class="w-full border border-gray-300 rounded px-3 py-2">
                    <option value="">Select Receiver</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->fname }} {{ $employee->lname }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-700">Fund Source</label>
                <select name="outlet_fund_source" class="w-full border border-gray-300 rounded px-3 py-2">
                    <option value="">Select Funds</option>
                    @foreach($approves as $approve)
                        <option value="{{ $approve->id }}">{{ $approve->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-700">Outlet Charge</label>
                <input type="text" name="outlet_charge" class="w-full border border-gray-300 rounded px-3 py-2" />
            </div>
        </fieldset>

        <!-- Submit Buttons -->
        <div class="mt-6 text-right">
            <button type="button" id="rejectBtn" class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700 transition">
                Reject
            </button>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
                Approve
            </button>
        </div>
    </form>


<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
        <h2 class="text-xl font-semibold mb-4">Reject Cash Voucher</h2>
        <form method="POST" action="">
            @csrf
            <input type="hidden" name="cvr_number" value="{{ $cashVouchers->cvr_number }}" />
            <div class="mb-4">
                <input type="hidden" name="cvr_id" value="{{ $cashVouchers->id }}">
                <label for="remarks" class="block mb-2 font-medium text-gray-700">Remarks (required)</label>
                <textarea id="reject_remarks" name="reject_remarks" rows="4" required
                          class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" id="modalCloseBtn" class="px-4 py-2 rounded border border-gray-300 hover:bg-gray-100">
                    Cancel
                </button>
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">
                    Submit Reject
                </button>
            </div>
        </form>
    </div>
</div>

<!-- JS to toggle form sections -->
<script>

    const rejectBtn = document.getElementById('rejectBtn');
    const rejectModal = document.getElementById('rejectModal');
    const modalCloseBtn = document.getElementById('modalCloseBtn');

    rejectBtn.addEventListener('click', () => {
        rejectModal.classList.remove('hidden');
    });

    modalCloseBtn.addEventListener('click', () => {
        rejectModal.classList.add('hidden');
    });

    // Close modal on click outside the modal content
    rejectModal.addEventListener('click', (e) => {
        if(e.target === rejectModal) {
            rejectModal.classList.add('hidden');
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const paymentFields = {
            cash: document.getElementById('cashFields'),
            bank_transfer: document.getElementById('bankTransferFields'),
            outlet_transfer: document.getElementById('storeTransferFields')
        };

        document.querySelectorAll('input[name="payment_type"]').forEach(radio => {
            radio.addEventListener('change', () => {
                Object.values(paymentFields).forEach(section => section.classList.add('hidden'));
                const selected = radio.value;
                if (paymentFields[selected]) {
                    paymentFields[selected].classList.remove('hidden');
                }
            });
        });
    });
</script>
@endsection
