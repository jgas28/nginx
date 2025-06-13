@extends('layouts.app')

@section('content')
<div class="mx-auto bg-white p-8 shadow-lg rounded-lg">

    <h2 class="text-2xl font-bold mb-6 border-b border-gray-200 pb-3">Validate Liquidation</h2>

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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Expenses --}}
        <div class="p-4 rounded-lg bg-gray-50 shadow-sm">
            <h3 class="font-semibold text-lg mb-3 border-b border-gray-300 pb-2">Expenses</h3>
            <ul class="space-y-2">
                @foreach (['allowance', 'manpower', 'hauling', 'right_of_way', 'roro_expense'] as $field)
                    <li class="flex justify-between">
                        <span class="capitalize">{{ str_replace('_', ' ', $field) }}</span>
                        <span class="font-semibold">₱{{ number_format($liquidation->$field ?? 0, 2) }}</span>
                    </li>
                @endforeach
                <li class="flex justify-between">
                    <span>Cash Charge</span>
                    <span class="font-semibold text-indigo-600">₱{{ number_format($liquidation->cash_charge ?? 0, 2) }}</span>
                </li>
            </ul>
        </div>

        {{-- Gasoline --}}
        <div class="p-4 rounded-lg bg-gray-50 shadow-sm">
            <h3 class="font-semibold text-lg mb-3 border-b border-gray-300 pb-2">Gasoline</h3>
            <ul class="space-y-2">
                @forelse ($liquidation->gasoline ?? [] as $item)
                    <li class="flex justify-between">
                        <span>{{ ucfirst($item['type'] ?? '') }}</span>
                        <span class="font-semibold">₱{{ number_format($item['amount'] ?? 0, 2) }}</span>
                    </li>
                @empty
                    <li class="text-gray-400 italic">No gasoline entries.</li>
                @endforelse
            </ul>
        </div>

        {{-- RFID --}}
        <div class="p-4 rounded-lg bg-gray-50 shadow-sm">
            <h3 class="font-semibold text-lg mb-3 border-b border-gray-300 pb-2">RFID</h3>
            <ul class="space-y-2">
                @forelse ($liquidation->rfid ?? [] as $item)
                    <li class="flex justify-between">
                        <span>{{ ucfirst($item['tag'] ?? '') }} ({{ ucfirst($item['type'] ?? '') }})</span>
                        <span class="font-semibold">₱{{ number_format($item['amount'] ?? 0, 2) }}</span>
                    </li>
                @empty
                    <li class="text-gray-400 italic">No RFID entries.</li>
                @endforelse
            </ul>
        </div>

        {{-- Others --}}
        <div class="p-4 rounded-lg bg-gray-50 shadow-sm">
            <h3 class="font-semibold text-lg mb-3 border-b border-gray-300 pb-2">Others</h3>
            <ul class="space-y-2">
                @forelse ($liquidation->others ?? [] as $item)
                    <li class="flex justify-between">
                        <span>{{ $item['description'] ?? '' }}</span>
                        <span class="font-semibold">₱{{ number_format($item['amount'] ?? 0, 2) }}</span>
                    </li>
                @empty
                    <li class="text-gray-400 italic">No other entries.</li>
                @endforelse
            </ul>
        </div>
    </div>

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
    </div>

    @php
        // Calculate raw difference
        $rawDifference = $totalCash - $approvedAmount;

        // Calculate refund total (e.g., existing refund records)
        $refundTotal = $runningRefunds->sum(function ($item) {
            return isset($item->amount) ? abs($item->amount) : 0;
        });

        // Combine returns and uncollected
        $combinedReturns = collect();
        if (isset($runningReturns)) {
            $combinedReturns = $combinedReturns->merge($runningReturns);
        }
        if (isset($runningUncollected)) {
            $combinedReturns = $combinedReturns->merge($runningUncollected);
        }

        // Calculate total of returned/uncollected
        $returnedTotal = $combinedReturns->sum(function ($item) {
            return isset($item->amount) ? abs($item->amount) : 0;
        });

        // Final difference, adjusted by refund and returns
        $difference = $rawDifference - $refundTotal + $returnedTotal;
        $difference = round($difference, 2); // Optional rounding
    @endphp
    <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-6 font-medium text-lg">
        <div class="p-4 rounded-lg shadow-inner 
        {{ $difference > 0 ? 'bg-red-100 text-red-700' : 'bg-gray-50 text-gray-700' }}">
            <div class="flex justify-between items-center mb-2">
                <span>Refund Request</span>
                <span>₱{{ number_format($refundTotal, 2) }}</span>
            </div>
            @if(isset($runningRefunds) && $runningRefunds->count() > 0)
                <ul class="text-sm text-red-800 space-y-1 max-h-32 overflow-auto border border-red-300 p-2 rounded bg-red-50">
                    @foreach ($runningRefunds as $refund)
                        <li class="flex justify-between">
                            <a href="{{ route('refunds.print', $refund['id']) }}" target="_blank" class="flex justify-between w-full">
                                <span>{{ $refund->description ?? 'No description' }}</span>
                                <span>₱{{ number_format(abs($refund->amount), 2) }}</span>
                            </a>
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

            @if($combinedReturns->count() > 0)
                <ul class="text-sm text-yellow-800 space-y-1 max-h-32 overflow-auto border border-yellow-300 p-2 rounded bg-yellow-50">
                @foreach ($combinedReturns as $item)
                    <li class="flex justify-between">
                        @if ($item->type == 4)
                            <a href="{{ route('returns.print', $item->id) }}" target="_blank" class="flex justify-between w-full">
                                <span>{{ $item->description ?? 'No description' }}</span>
                                <span>₱{{ number_format($item->amount, 2) }}</span>
                            </a>
                        @else
                            <div class="flex justify-between w-full">
                                <span>{{ $item->description ?? 'No description' }}</span>
                                <span>₱{{ number_format($item->amount, 2) }}</span>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
            @else
                <p class="text-xs italic text-yellow-700 mt-1">No returned cash details available.</p>
            @endif
        </div>
    </div>


    @if ($return && $difference != 0)
        <button id="openModalBtn" 
            class="mt-6 bg-red-600 text-white px-5 py-2 rounded hover:bg-red-700 transition">
            Create Return
        </button>
    @endif

    <!-- Modal Backdrop -->
    <div id="reimbursementModal" 
       class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 
            overflow-y-auto p-4">
        
        <!-- Modal Panel -->
        <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 relative max-h-[90vh] overflow-y-auto" id="modalPanel">
            <h3 class="text-xl font-semibold mb-4">Create Reimbursement</h3>
            <form action="{{ route('running-balance.collected') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="liquidation_id" value="{{ $liquidation->id }}" />
                <div>
                    <label class="block mb-1 font-medium" for="amount_label">Amount Difference (₱)</label>
                    <input type="number" step="0.01" min="0" name="amount_label" id="amount_label" 
                        value="{{ abs($difference) }}" readonly
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>

                <div>
                    <label class="block mb-1 font-medium" for="amount_collected">Collected Amount (₱)</label>
                    <input type="number" step="0.01" min="0" name="amount_collected" id="amount_collected" 
                    required oninput="calculateUncollected()"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>

                <div>
                    <label class="block mb-1 font-medium" for="description">Description</label>
                    <input type="text" name="description" id="description" placeholder="Description" required
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>

                <div>
                    <label class="block mb-1 font-medium" for="amount_uncollected">Uncollected Amount (₱)</label>
                    <input type="number" step="0.01" min="0" name="amount_uncollected" id="amount_uncollected" 
                    readonly required
                    class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100" />
                </div>

                <div>
                    <label class="block mb-1 font-medium" for="period">Period (e.g. May 2025)</label>
                    <input type="text" id="period" placeholder="Enter payroll period"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>

                <div>
                    <label class="block mb-1 font-medium" for="description1">Description</label>
                    <input type="text" name="description1" id="description1" placeholder="Auto-filled"
                    readonly required
                    class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100" />
                </div>

                <div>
                    <label class="block mb-1 font-medium" for="cvr_number">CVR Number</label>
                    <input type="text" name="cvr_number" id="cvr_number" value="{{ $liquidation->cvr_number }}" readonly
                        class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100 cursor-not-allowed" />
                </div>

                <div>
                    <label class="block mb-1 font-medium" for="employee_id">Employee</label>
                    <select name="employee_id" id="employee_id" required
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="" disabled selected>Select employee</option>
                        @foreach ($staffs as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->fname }} {{ $staff->lname }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1 font-medium" for="approver_id">Approver</label>
                    <select name="approver_id" id="approver_id" required
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="" disabled selected>Select approver</option>
                        @foreach ($approvers as $approver)
                            <option value="{{ $approver->id }}">{{ $approver->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Created by (Hidden) -->
                <input type="hidden" name="created_by" value="{{ auth()->user()->id }}" />
                <input type="hidden" name="type" value="{{ $difference > 0 ? 'refund' : 'return' }}" />

                <div class="flex justify-end space-x-2 pt-4 border-t border-gray-200">
                    <button type="button" id="closeModalBtn"
                            class="px-4 py-2 rounded border border-gray-300 hover:bg-gray-100 transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700 transition">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="mt-8 pt-6 border-t border-gray-200">
        <form action="{{ route('liquidations.collect', $liquidation->id) }}" method="POST" class="bg-gray-50 p-4 rounded-lg shadow-sm">
            @csrf
            <label for="collected_by" class="block mb-2 font-medium text-gray-700">Collected By</label>
            <select id="collected_by" name="collected_by" required class="w-full border border-gray-300 rounded px-3 py-2 mb-4 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="" disabled selected>Select employee</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->fname }} {{ $employee->lname }}</option>
                @endforeach
            </select>

            <input type="hidden" name="action" id="form-action" value="">
            <div class="flex gap-4">
                <button type="button" id="validate-btn" class="w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700">Validate</button>
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

<!-- Confirmation Modal -->
<div id="confirmValidationModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
        <h2 class="text-lg font-semibold mb-4 text-gray-800">Confirm Validation</h2>
        
        @if($difference != 0)
            <p class="text-red-600 font-medium mb-4">There's still a difference between approved and liquidated amount.</p>
        @else
            <p class="text-gray-700 mb-4">Are you sure you want to confirm the validation?</p>
        @endif

        <div class="flex justify-end space-x-2">
            <button id="cancelConfirmBtn" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
            @if($difference == 0)
                <button id="confirmSubmitBtn" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Confirm</button>
            @endif
        </div>
    </div>
</div>

<!-- Modal JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const openBtn = document.getElementById('openModalBtn');
        const modal = document.getElementById('reimbursementModal');
        const closeBtn = document.getElementById('closeModalBtn');

        const validateBtn = document.getElementById('validate-btn');
        const rejectBtn = document.getElementById('reject-btn');
        const confirmModal = document.getElementById('confirmValidationModal');
        const cancelConfirm = document.getElementById('cancelConfirmBtn');
        const confirmSubmit = document.getElementById('confirmSubmitBtn');
        const collectForm = document.querySelector("form[action='{{ route('liquidations.collect', $liquidation->id) }}']");
        const rejectModal = document.getElementById('rejectModal');
        const cancelRejectBtn = document.getElementById('cancelRejectBtn');

        // Open reimbursement modal
        if (openBtn && modal && closeBtn) {
            openBtn.addEventListener('click', () => modal.classList.remove('hidden'));
            closeBtn.addEventListener('click', () => modal.classList.add('hidden'));
            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.classList.add('hidden');
            });
        }

        // Validate button logic
        if (validateBtn && confirmModal) {
            validateBtn.addEventListener('click', () => {
                confirmModal.classList.remove('hidden');
            });
        }

        if (cancelConfirm) {
            cancelConfirm.addEventListener('click', () => {
                confirmModal.classList.add('hidden');
            });
        }

        if (confirmSubmit && collectForm) {
            confirmSubmit.addEventListener('click', () => {
                document.getElementById('form-action').value = 'validate';
                collectForm.submit();
            });
        }

        // Reject button logic
        if (rejectBtn && rejectModal) {
            rejectBtn.addEventListener('click', () => {
                rejectModal.classList.remove('hidden');
            });
        }

        if (cancelRejectBtn && rejectModal) {
            cancelRejectBtn.addEventListener('click', () => {
                rejectModal.classList.add('hidden');
            });
        }

        // Escape key closes all modals
        document.addEventListener('keydown', function (e) {
            if (e.key === "Escape") {
                modal?.classList.add('hidden');
                confirmModal?.classList.add('hidden');
                rejectModal?.classList.add('hidden');
            }
        });

        // Auto-fill description1 based on period input
        const periodInput = document.getElementById('period');
        const descriptionInput = document.getElementById('description1');

        if (periodInput && descriptionInput) {
            periodInput.addEventListener('input', function () {
                const val = this.value.trim();
                descriptionInput.value = val ? `SD - Payroll (${val})` : '';
            });
        }
    });

    // Calculate uncollected amount
    function calculateUncollected() {
        const totalDiff = {{ abs($difference) }};
        const collected = parseFloat(document.getElementById('amount_collected')?.value) || 0;
        const uncollectedField = document.getElementById('amount_uncollected');
        const result = totalDiff - collected;
        if (uncollectedField) {
            uncollectedField.value = result > 0 ? result.toFixed(2) : '0.00';
        }
    }

    window.calculateUncollected = calculateUncollected;
</script>
@endsection
