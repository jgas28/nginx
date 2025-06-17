@extends('layouts.app')

@section('content')
<div class="mx-auto bg-white p-8 shadow-lg rounded-lg">

    <h2 class="text-2xl font-bold mb-6 border-b border-gray-200 pb-3">Validate Liquidation</h2>
    <input type="hidden" value="{{$liquidation->id}}"/>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8 text-gray-700">
        <div>
            <span class="font-semibold text-gray-900">CVR Number:</span>
            <span class="ml-2">{{ $liquidation->cvr_number }}</span>
        </div>
        <div>
            <span class="font-semibold text-gray-900">Approved Amount:</span>
            <span class="ml-2 text-indigo-600 font-medium">₱{{ number_format($approvedAmount, 2) }}</span>
        </div>
        <div>
            <span class="font-semibold text-gray-900">Prepared By:</span>
            <span class="ml-2">{{ $liquidation->preparedBy->fname ?? '' }} {{ $liquidation->preparedBy->lname ?? '' }}</span>
        </div>
        <div>
            <span class="font-semibold text-gray-900">Noted By:</span>
            <span class="ml-2">{{ $liquidation->notedBy->fname ?? '' }} {{ $liquidation->notedBy->lname ?? '' }}</span>
        </div>
    </div>

    <form action="{{ route('liquidations.approvedEdit', $liquidation->id) }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Expenses --}}
            <div class="p-4 rounded-lg bg-gray-50 shadow-sm">
                <h3 class="font-semibold text-lg mb-3 border-b border-gray-300 pb-2">Expenses</h3>
                @foreach (['allowance', 'manpower', 'hauling', 'right_of_way', 'roro_expense', 'cash_charge'] as $field)
                    <div class="mb-3">
                        <label class="block font-medium capitalize">{{ str_replace('_', ' ', $field) }}</label>
                        <input type="number" step="0.01" name="{{ $field }}" value="{{ old($field, $liquidation->$field) }}" class="w-full px-3 py-2 border rounded" />
                    </div>
                @endforeach
            </div>

            {{-- Gasoline --}}
            <div id="gasolineList" class="flex flex-col gap-2 p-4 rounded-lg bg-gray-50 shadow-sm">
                <div class="flex justify-between items-center mb-3 border-b border-gray-300 pb-2">
                    <h3 class="font-semibold text-lg">Gasoline</h3>
                    <button type="button" onclick="addGasolineField()" class="px-3 py-1 rounded bg-indigo-600 text-white hover:bg-indigo-700 transition">
                        Add Gasoline
                    </button>
                </div>
                @foreach ($gasoline as $index => $item)
                    <div class="flex gap-2 items-center" data-index="{{ $index }}">
                        <select name="gasoline[{{ $index }}][type]" class="w-32 rounded-md border px-2 py-1">
                            <option value="" {{ empty($item['type']) ? 'selected' : '' }}>Type</option>
                            <option value="cash" {{ ($item['type'] ?? '') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="card" {{ ($item['type'] ?? '') === 'card' ? 'selected' : '' }}>Card</option>
                        </select>
                        <input type="number" step="0.01" name="gasoline[{{ $index }}][amount]" placeholder="Amount" value="{{ $item['amount'] ?? '' }}" class="w-40 rounded-md border px-2 py-1" />
                        <button type="button" onclick="this.closest('[data-index]').remove()" class="text-red-600 hover:text-red-800 text-sm">✕</button>
                    </div>
                @endforeach
            </div>

            {{-- RFID --}}
            <div id="rfidList" class="flex flex-col gap-2 p-4 rounded-lg bg-gray-50 shadow-sm">
                <div class="flex justify-between items-center mb-3 border-b border-gray-300 pb-2">
                    <h3 class="font-semibold text-lg">RFID</h3>
                    <button type="button" onclick="addRFIDField()" class="mt-2 px-3 py-1 rounded bg-indigo-600 text-white self-start">Add RFID</button>
                </div>
                @foreach ($rfid as $index => $item)
                    <div class="flex gap-2 items-center" data-index="{{ $index }}">
                        <select name="rfid[{{ $index }}][tag]" class="w-32 rounded-md border px-2 py-1">
                            <option value="" {{ empty($item['tag']) ? 'selected' : '' }}>Select Tag</option>
                            <option value="autosweep" {{ ($item['tag'] ?? '') === 'autosweep' ? 'selected' : '' }}>AutoSweep</option>
                            <option value="easytrip" {{ ($item['tag'] ?? '') === 'easytrip' ? 'selected' : '' }}>EasyTrip</option>
                        </select>
                        <select name="rfid[{{ $index }}][type]" class="w-28 rounded-md border px-2 py-1">
                            <option value="" {{ empty($item['type']) ? 'selected' : '' }}>Type</option>
                            <option value="cash" {{ ($item['type'] ?? '') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="card" {{ ($item['type'] ?? '') === 'card' ? 'selected' : '' }}>Card</option>
                        </select>
                        <input type="number" step="0.01" name="rfid[{{ $index }}][amount]" placeholder="Amount" value="{{ $item['amount'] ?? '' }}" class="w-36 rounded-md border px-2 py-1" />
                        <button type="button" onclick="this.closest('[data-index]').remove()" class="text-red-600 hover:text-red-800 text-sm">✕</button>
                    </div>
                @endforeach
            </div>

            {{-- Others --}}
            <div id="othersList" class="flex flex-col gap-2 p-4 rounded-lg bg-gray-50 shadow-sm">
                <div class="flex justify-between items-center mb-3 border-b border-gray-300 pb-2">
                    <h3 class="font-semibold text-lg">Others</h3>
                    <button type="button" onclick="addOthersField()" class="mt-2 px-3 py-1 rounded bg-indigo-600 text-white self-start">Add Other</button>
                </div>
                @foreach ($others as $index => $item)
                    <div class="flex gap-2 items-center" data-index="{{ $index }}">
                        <input type="text" name="others[{{ $index }}][description]" placeholder="Description" value="{{ $item['description'] ?? '' }}" class="w-48 rounded-md border px-2 py-1" />
                        <input type="number" step="0.01" name="others[{{ $index }}][amount]" placeholder="Amount" value="{{ $item['amount'] ?? '' }}" class="w-24 rounded-md border px-2 py-1" />
                        <button type="button" onclick="this.closest('[data-index]').remove()" class="text-red-600 hover:text-red-800 text-sm">✕</button>
                    </div>
                @endforeach
            </div>

            {{-- Save Button Spanning Full Width --}}
            <div class="md:col-span-2 mt-6">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded hover:bg-indigo-700 transition w-full md:w-auto">
                    Save Changes
                </button>
            </div>
        </div>
    </form>


    {{-- Liquidation Summary Totals --}}
    <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-6 font-medium text-lg">
        {{-- Approved Amount --}}
        <div class="bg-indigo-50 p-4 rounded shadow">
            <p class="text-gray-800">Approved Amount</p>
            <p class="text-indigo-700 text-right text-xl font-bold">₱{{ number_format($approvedAmount, 2) }}</p>
        </div>

        {{-- Total Expense --}}
        <div class="bg-green-50 p-4 rounded shadow">
            <p class="text-gray-800">Total Expense</p>
            <p class="text-green-700 text-right text-xl font-bold">₱{{ number_format($totalCash, 2) }}</p>
        </div>

        {{-- Start Here --}}
        @php
            // Calculate total amount from runningRefunds and make sure it's positive
            $refundTotal = 0;
            if (isset($runningRefunds)) {
                $refundTotal = $runningRefunds->sum(function ($item) {
                    return isset($item->amount) ? abs($item->amount) : 0;
                });
            }
        @endphp

        <div class="p-4 rounded-lg shadow-inner 
            {{ $difference > 0 ? 'bg-red-100 text-red-700' : 'bg-gray-50 text-gray-700' }}">
            <div class="flex justify-between items-center mb-2">
                <span>Refund Request</span>
                <span>₱{{ number_format($refundTotal, 2) }}</span>
            </div>

            <!-- Refunds List -->
            @if(isset($runningRefunds) && $runningRefunds->count() > 0)
                <ul class="text-sm text-red-800 space-y-1 max-h-32 overflow-auto border border-red-300 p-2 rounded bg-red-50">
                    @foreach ($runningRefunds as $refund)
                        <li class="flex justify-between items-center">
                            <div class="flex justify-between w-full items-center space-x-4">
                                <a href="{{ route('refunds.print', $refund->id) }}" class="flex-1 truncate text-blue-600 hover:underline">
                                    {{ $refund->description ?? 'No description' }}
                                </a>
                                <span class="whitespace-nowrap">₱{{ number_format(abs($refund->amount), 2) }}</span>
                                <button type="button" class="text-xs text-blue-600 hover:underline ml-2" onclick="openEditRefundModal({{ $refund->id }}, {{ $liquidation->id }})">
                                    Edit
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-xs italic text-red-700 mt-1">No refund details available.</p>
            @endif
        </div>

        
        <div class="p-4 rounded-lg shadow-inner 
        {{ $difference < 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-50 text-gray-700' }}">

            @php
                // Combine returns and uncollected into one collection or array
                $combinedReturns = collect();

                if (isset($runningReturns)) {
                    $combinedReturns = $combinedReturns->merge($runningReturns);
                }
                if (isset($runningUncollected)) {
                    $combinedReturns = $combinedReturns->merge($runningUncollected);
                }

                // Calculate total amount from the combined list
                // Use abs() for uncollected amounts if negative
                $combinedTotal = $combinedReturns->sum(function ($item) {
                    return isset($item->amount) ? abs($item->amount) : 0;
                });
            @endphp

        <div class="flex justify-between items-center mb-2">
            <span>Returned Request</span>
            <span>₱{{ number_format($combinedTotal, 2) }}</span>
        </div>

            @php
                // Combine returns and uncollected into one collection or array
                $combinedReturns = collect();

                if(isset($runningReturns)) {
                    $combinedReturns = $combinedReturns->merge($runningReturns);
                }
                if(isset($runningUncollected)) {
                    $combinedReturns = $combinedReturns->merge($runningUncollected);
                }
            @endphp

            <!-- Returns List -->
            @if($combinedReturns->count() > 0)
                <ul class="text-sm text-yellow-800 space-y-1 max-h-32 overflow-auto border border-yellow-300 p-2 rounded bg-yellow-50">
                    @foreach ($combinedReturns as $item)
                        <li class="flex justify-between items-center">
                            <div class="flex flex-col">
                                @if ($item->type == 4)
                                <a href="{{ route('returns.print', $item->id) }}" target="_blank" class="flex justify-between w-full">
                                    <span class="font-medium">{{ $item->description ?? 'No description' }}</span>
                                </a>
                                @else
                                <span class="font-medium">{{ $item->description ?? 'No description' }}</span>
                                @endif
                                <span class="text-sm text-gray-600">₱{{ number_format(abs($item->amount), 2) }}</span>
                            </div>
                            <button type="button" class="text-xs text-blue-600 hover:underline ml-2" onclick="openEditReturnModal({{ $item->id }}, {{ $liquidation->id }})">
                                Edit
                            </button>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-xs italic text-yellow-700 mt-1">No returned cash details available.</p>
            @endif
        </div>
    </div>

    @if ($difference != 0)
        <button id="openModalBtn"
            class="mt-6 {{ $difference < 0 ? 'bg-red-600' : 'bg-yellow-500' }} text-white px-5 py-2 rounded hover:{{ $difference < 0 ? 'bg-red-700' : 'bg-yellow-600' }} transition">
            {{ $difference < 0 ? 'Create Refund' : 'Create Return' }}
        </button>
    @endif

    <div id="reimbursementModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 overflow-y-auto p-4">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 relative max-h-[90vh] overflow-y-auto" id="modalPanel">
            <h3 class="text-xl font-semibold mb-4">
                {{ $difference > 0 ? 'Create Refund' : 'Create Return' }}
            </h3>

            <form action="{{ $difference > 0 ? route('running-balance.reimburseAdmin') : route('running-balance.collectedAdmin') }}"
                method="POST" class="space-y-4">
                @csrf

                <input type="hidden" name="liquidation_id" value="{{ $liquidation->id }}">
                <input type="hidden" name="cvr_number" value="{{ $liquidation->cvr_number }}">
                <input type="hidden" name="created_by" value="{{ auth()->user()->id }}">
                <input type="hidden" name="type" value="{{ $difference > 0 ? 'refund' : 'return' }}">

                {{-- Common fields --}}
                <div>
                    <label class="block mb-1 font-medium">Amount Difference (₱)</label>
                    <input type="number" step="0.01" min="0" readonly value="{{ abs($difference) }}"
                        class="w-full border rounded px-3 py-2 bg-gray-100" />
                </div>

                {{-- Refund-specific --}}
                @if ($difference > 0)
                    <div>
                        <label class="block mb-1 font-medium">Amount (₱)</label>
                        <input type="number" step="0.01" min="0" name="amount" required
                            class="w-full border rounded px-3 py-2" />
                    </div>
                    <div>
                        <label class="block mb-1 font-medium">Description</label>
                        <input type="text" name="description" value="Refund - {{ $liquidation->cvr_number }}" required
                            class="w-full border rounded px-3 py-2" />
                    </div>
                @else
                    {{-- Return-specific --}}
                    <div>
                        <label class="block mb-1 font-medium">Collected Amount (₱)</label>
                        <input type="number" step="0.01" min="0" name="amount_collected" id="amount_collected"
                            required oninput="calculateUncollected()"
                            class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Description</label>
                        <input type="text" name="description" id="description" class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Uncollected Amount (₱)</label>
                        <input type="number" step="0.01" min="0" name="amount_uncollected" id="amount_uncollected"
                            readonly class="w-full border rounded px-3 py-2 bg-gray-100" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Period</label>
                        <input type="text" name="period" id="period" placeholder="e.g. June 2025"
                            class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Description</label>
                        <input type="text" name="description1" id="description1" value="Return - {{ $liquidation->cvr_number }}"
                            readonly class="w-full border rounded px-3 py-2 bg-gray-100" />
                    </div>
                @endif

                {{-- Employee & Approver --}}
                <div>
                    <label class="block mb-1 font-medium">Employee</label>
                    <select name="employee_id" required class="w-full border rounded px-3 py-2">
                        <option disabled selected>Select employee</option>
                        @foreach ($staffs as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->fname }} {{ $staff->lname }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1 font-medium">Approver</label>
                    <select name="approver_id" required class="w-full border rounded px-3 py-2">
                        <option disabled selected>Select approver</option>
                        @foreach ($approvers as $approver)
                            <option value="{{ $approver->id }}">{{ $approver->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-gray-200">
                    <button type="button" id="closeModalBtn"
                            class="px-4 py-2 rounded border hover:bg-gray-100 transition">Cancel</button>
                    <button type="submit"
                            class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700 transition">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-8 pt-6 border-t border-gray-200">
        <form action="{{ route('liquidations.approved', $liquidation->id) }}" method="POST" class="bg-gray-50 p-4 rounded-lg shadow-sm">
            @csrf
            <label for="approved_by" class="block mb-2 font-medium text-gray-700">Approved By</label>
            <select id="approved_by" name="approved_by" required class="w-full border border-gray-300 rounded px-3 py-2 mb-4 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->fname }} {{ $employee->lname }}</option>
                @endforeach
            </select>
            <input type="hidden" name="action" id="form-action" value="">
            <div class="flex gap-4">
                <button type="button" id="validate-btn" class="w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700">Approved</button>
                <button type="button" id="reject-btn" class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700">Reject</button>
            </div>
        </form>
    </div>
</div>

{{-- Reject Modal --}}
<div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
        <form id="rejectForm" action="{{ route('liquidations.reject', $liquidation->id) }}" method="POST">
            @csrf
            <input type="hidden" name="validated_by" value="{{ auth()->user()->id }}">
            <h3 class="text-xl font-semibold mb-4 text-red-600">Reject Liquidation</h3>
            <p class="text-sm text-gray-600 mb-3">Please provide remarks for rejecting this liquidation:</p>
            <textarea name="remarks" id="rejectRemarks" rows="3" required class="w-full border rounded px-3 py-2" placeholder="Enter reason..."></textarea>
            <div class="flex justify-end space-x-2 mt-4">
                <button type="button" id="cancelRejectBtn" class="px-4 py-2 rounded border hover:bg-gray-100">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700">Confirm Reject</button>
            </div>
        </form>
    </div>
</div>

{{-- Confirm Validation Modal --}}
<div id="confirmValidationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
        <h3 class="text-xl font-semibold mb-4 text-indigo-700">Confirm Validation</h3>
        <p class="text-sm text-gray-600 mb-3">
            Are you sure you want to confirm the validation?
            <span id="validationDifferenceWarning" class="text-red-600 font-medium hidden">
                There is a ₱<span id="differenceAmount">0.00</span> difference between approved and liquidated amounts.
            </span>
        </p>
        <div class="flex justify-end space-x-2 mt-4">
            <button type="button" id="cancelConfirmValidationBtn" class="px-4 py-2 rounded border hover:bg-gray-100">Cancel</button>
            <button type="button" id="confirmValidationBtn" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700" disabled>Confirm</button>
        </div>
    </div>
</div>

<!-- Edit Refund Modal -->
<div id="editRefundModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-40 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
        <h3 class="text-lg font-semibold mb-4">Edit Refund</h3>
        <form id="editRefundForm" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="refund_id" id="refund_id">
            <input type="hidden" name="liquidation_id" id="liquidation_id_refund">
            <div class="mb-4">
                <label for="edit_refund_description" class="block text-sm font-medium">Description</label>
                <input type="text" name="description" id="edit_refund_description" class="w-full border rounded px-3 py-2 mt-1" required>
            </div>
            <div class="mb-4">
                <label for="edit_refund_amount" class="block text-sm font-medium">Amount (₱)</label>
                <input type="number" name="amount" id="edit_refund_amount" step="0.01" class="w-full border rounded px-3 py-2 mt-1" required>
            </div>
            <div class="flex justify-end gap-2 border-t pt-4">
                <button type="button" class="px-4 py-2 border rounded hover:bg-gray-100" onclick="closeEditRefundModal()">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Return Modal -->
<div id="editReturnModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-40 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
        <h3 class="text-lg font-semibold mb-4">Edit Return</h3>
        <form id="editReturnForm" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="return_id" id="return_id">
            <input type="hidden" name="liquidation_id" id="liquidation_id_return">
            <div class="mb-4">
                <label for="edit_return_description" class="block text-sm font-medium">Description</label>
                <input type="text" name="description" id="edit_return_description" class="w-full border rounded px-3 py-2 mt-1" required>
            </div>
            <div class="mb-4">
                <label for="edit_return_amount" class="block text-sm font-medium">Amount (₱)</label>
                <input type="number" name="amount" id="edit_return_amount" step="0.01" class="w-full border rounded px-3 py-2 mt-1" required>
            </div>
            <div class="flex justify-end gap-2 border-t pt-4">
                <button type="button" class="px-4 py-2 border rounded hover:bg-gray-100" onclick="closeEditReturnModal()">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save</button>
            </div>
        </form>
    </div>
</div>
<!-- Modal JavaScript -->
<script>
    const liquidationId = {{ $liquidation->id }};
    let gasolineIndex = {{ count($gasoline) }};
    let rfidIndex = {{ count($rfid) }};
    let othersIndex = {{ count($others) }};

    function addGasolineField() {
        const wrapper = document.getElementById('gasolineList');
        wrapper.insertAdjacentHTML('beforeend', `
            <div class="flex gap-2 items-center" data-index="${gasolineIndex}">
                <select name="gasoline[${gasolineIndex}][type]" class="w-32 rounded-md border px-2 py-1">
                    <option value="">Type</option>
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                </select>
                <input type="number" step="0.01" name="gasoline[${gasolineIndex}][amount]" placeholder="Amount" class="w-40 rounded-md border px-2 py-1" />
                <button type="button" onclick="this.closest('[data-index]').remove()" class="text-red-600 hover:text-red-800 text-sm">✕</button>
            </div>
        `);
        gasolineIndex++;
    }

    function addRFIDField() {
        const wrapper = document.getElementById('rfidList');
        wrapper.insertAdjacentHTML('beforeend', `
            <div class="flex gap-2 items-center" data-index="${rfidIndex}">
                <select name="rfid[${rfidIndex}][tag]" class="w-32 rounded-md border px-2 py-1">
                    <option value="">Select Tag</option>
                    <option value="autosweep">AutoSweep</option>
                    <option value="easytrip">EasyTrip</option>
                </select>
                <select name="rfid[${rfidIndex}][type]" class="w-28 rounded-md border px-2 py-1">
                    <option value="">Type</option>
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                </select>
                <input type="number" step="0.01" name="rfid[${rfidIndex}][amount]" placeholder="Amount" class="w-36 rounded-md border px-2 py-1" />
                <button type="button" onclick="this.closest('[data-index]').remove()" class="text-red-600 hover:text-red-800 text-sm">✕</button>
            </div>
        `);
        rfidIndex++;
    }

    function addOthersField() {
        const wrapper = document.getElementById('othersList');
        wrapper.insertAdjacentHTML('beforeend', `
            <div class="flex gap-2 items-center" data-index="${othersIndex}">
                <input type="text" name="others[${othersIndex}][description]" placeholder="Description" class="w-48 rounded-md border px-2 py-1" />
                <input type="number" step="0.01" name="others[${othersIndex}][amount]" placeholder="Amount" class="w-24 rounded-md border px-2 py-1" />
                <button type="button" onclick="this.closest('[data-index]').remove()" class="text-red-600 hover:text-red-800 text-sm">✕</button>
            </div>
        `);
        othersIndex++;
    }

    function calculateUncollected() {
        const total = {{ abs($difference) }};
        const collected = parseFloat(document.getElementById('amount_collected')?.value) || 0;
        const uncollectedInput = document.getElementById('amount_uncollected');
        if (uncollectedInput) {
            const uncollected = (total - collected).toFixed(2);
            uncollectedInput.value = uncollected > 0 ? uncollected : 0;
        }
    }

    function openEditRefundModal(id) {
        fetch(`/running-balance/refunds/${id}/edit`)
            .then(response => {
                if (!response.ok) throw new Error('Failed to fetch refund data');
                return response.json();
            })
            .then(data => {
                const modal = document.getElementById('editRefundModal');
                document.getElementById('liquidation_id_refund').value = liquidationId;
                document.getElementById('refund_id').value = data.id;
                document.getElementById('edit_refund_description').value = data.description;
                document.getElementById('edit_refund_amount').value = data.amount;
                document.getElementById('editRefundForm').action = `/running-balance/refunds/${data.id}`;
                modal.classList.remove('hidden');
            })
            .catch(err => {
                alert('Could not load refund data. Please check your connection or try again later.');
                console.error(err);
            });
    }

    function closeEditRefundModal() {
        const modal = document.getElementById('editRefundModal');
        modal?.classList.add('hidden');
    }

    function openEditReturnModal(id) {
        fetch(`/running-balance/returns/${id}/edit`)
            .then(response => {
                if (!response.ok) throw new Error('Failed to fetch return data');
                return response.json();
            })
            .then(data => {
                const modal = document.getElementById('editReturnModal');
                document.getElementById('liquidation_id_return').value = liquidationId;
                document.getElementById('return_id').value = data.id;
                document.getElementById('edit_return_description').value = data.description;
                document.getElementById('edit_return_amount').value = data.amount;
                document.getElementById('editReturnForm').action = `/running-balance/returns/${data.id}`;
                modal.classList.remove('hidden');
            })
            .catch(err => {
                alert('Could not load return data. Please check your connection or try again later.');
                console.error(err);
            });
    }

    function closeEditReturnModal() {
        const modal = document.getElementById('editReturnModal');
        modal?.classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const openBtn = document.getElementById('openModalBtn');
        const modal = document.getElementById('reimbursementModal');
        const closeBtn = document.getElementById('closeModalBtn');
        const rejectBtn = document.getElementById('reject-btn');
        const rejectModal = document.getElementById('rejectModal');
        const cancelRejectBtn = document.getElementById('cancelRejectBtn');
        const validateBtn = document.getElementById('validate-btn');
        const confirmValidationModal = document.getElementById('confirmValidationModal');
        const confirmValidationBtn = document.getElementById('confirmValidationBtn');
        const cancelConfirmValidationBtn = document.getElementById('cancelConfirmValidationBtn');
        const validationForm = document.querySelector('form[action*="liquidations.approved"]');
        const difference = parseFloat({{ abs($difference) }});
        const warningSpan = confirmValidationModal.querySelector('.text-red-600');
        const differenceAmount = document.getElementById('differenceAmount');

        // Reimbursement modal open/close
        if (openBtn && modal && closeBtn) {
            openBtn.addEventListener('click', () => {
                modal.classList.remove('hidden');
            });

            closeBtn.addEventListener('click', () => {
                modal.classList.add('hidden');
            });

            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === "Escape") {
                    modal.classList.add('hidden');
                }
            });
        }

        // Reject modal open/close
        rejectBtn?.addEventListener('click', function () {
            rejectModal?.classList.remove('hidden');
        });

        cancelRejectBtn?.addEventListener('click', function () {
            rejectModal?.classList.add('hidden');
        });

        // Validate modal
        validateBtn?.addEventListener('click', function () {
            confirmValidationModal?.classList.remove('hidden');

            if (difference < 0.009) {
                // Show warning and disable the button
                warningSpan?.classList.remove('hidden');
                differenceAmount.textContent = difference.toFixed(2);
                confirmValidationBtn.disabled = true;
                confirmValidationBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                // No warning, enable the button
                warningSpan?.classList.add('hidden');
                confirmValidationBtn.disabled = false;
                confirmValidationBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        });

        confirmValidationBtn?.addEventListener('click', function () {
            document.getElementById('form-action').value = 'validate';
            validationForm?.submit();
        });

        cancelConfirmValidationBtn?.addEventListener('click', function () {
            confirmValidationModal?.classList.add('hidden');
        });
    });
</script>
@endsection
