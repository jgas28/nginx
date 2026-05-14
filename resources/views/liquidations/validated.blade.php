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
                        <span class="capitalize">
                            {{ $field === 'roro_expense' ? 'Freight' : str_replace('_', ' ', $field) }}
                        </span>
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


    <!-- Existing Create Return Button -->
    @if ($return && $difference != 0)
        <button id="openCollectedModalBtn" 
            class="mt-6 bg-indigo-600 text-white px-5 py-2 rounded hover:bg-indigo-700 transition">
            Create Return Collected
        </button>

        <button id="openUncollectedModalBtn" 
            class="mt-6 bg-yellow-600 text-white px-5 py-2 rounded hover:bg-yellow-700 transition">
            Create Return Uncollected
        </button>
    @endif

    <!-- Collected Modal Backdrop -->
    <div id="collectedModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 overflow-y-auto p-4">
        <!-- Collected Modal Panel -->
        <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 relative max-h-[90vh] overflow-y-auto">
            <h3 class="text-xl font-semibold mb-4">Create Reimbursement (Collected)</h3>

            <form action="{{ route('running-balance.collected') }}" method="POST" class="space-y-4" id="reimbursementForm">
                @csrf
                <input type="hidden" name="liquidation_id" value="{{ $liquidation->id }}" />

                <div>
                    <label class="block mb-1 font-medium" for="amount_label">Amount Difference (₱)</label>
                    <input type="number" step="0.01" min="0" name="amount_label" id="amount_label" value="{{ abs($difference) }}" readonly
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>

                <div>
                    <label class="block mb-1 font-medium" for="amount_collected">Collected Amount (₱)</label>
                    <input type="number" step="0.01" min="0" name="amount_collected" id="amount_collected" required oninput="calculateUncollected()"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>

                <div>
                    <label class="block mb-1 font-medium" for="description_collected">Description</label>
                    <input type="text" name="description_collected" id="description_collected" placeholder="Enter description" required
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>

                <div>
                    <label class="block mb-1 font-medium" for="cvr_number_collected">CVR Number</label>
                    <input type="text" name="cvr_number_collected" id="cvr_number_collected" value="{{ $liquidation->cvr_number }}" readonly
                        class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100 cursor-not-allowed" />
                </div>

                @include('partials.party-selector', [
                    'idPrefix' => 'validated-collected-party',
                    'employees' => $staffs,
                    'suppliers' => $suppliers,
                    'partyRequired' => true,
                    'wrapperClass' => 'space-y-4',
                    'selectClass' => 'w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500',
                    'typeLabel' => 'Recipient Type',
                    'typePlaceholder' => 'Select employee or supplier',
                    'employeeLabel' => 'Employee for Collected Amount',
                    'supplierLabel' => 'Supplier for Collected Amount',
                ])

                <div>
                    <label class="block mb-1 font-medium" for="approver_id_collected">Approver</label>
                    <select name="approver_id_collected" id="approver_id_collected" required
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="" disabled selected>Select approver</option>
                        @foreach ($approvers as $approver)
                            <option value="{{ $approver->id }}">{{ $approver->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end space-x-2 pt-4 border-t border-gray-200">
                    <button type="button" id="closeCollectedModalBtn" class="px-4 py-2 rounded border border-gray-300 hover:bg-gray-100 transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700 transition">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Uncollected Modal Backdrop -->
    <div id="uncollectedModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 overflow-y-auto p-4">
        <!-- Uncollected Modal Panel -->
        <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 relative max-h-[90vh] overflow-y-auto">
            <h3 class="text-xl font-semibold mb-4">Create Reimbursement (Uncollected)</h3>

            <form action="{{ route('running-balance.uncollected') }}" method="POST" class="space-y-4" id="reimbursementFormUncollected">

                @csrf
                <input type="hidden" name="liquidation_id" value="{{ $liquidation->id }}" />
                
                <div>
                    <label class="block mb-1 font-medium" for="amount_uncollected">Uncollected Amount (₱)</label>
                    <input type="number" step="0.01" min="0" name="amount_uncollected" id="amount_uncollected" value="{{ abs($difference) }}" readonly required
                        class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100" />
                </div>

                <div>
                    <label class="block mb-1 font-medium" for="period">Period (e.g. May 2025)</label>
                    <input type="text" id="period" placeholder="Enter payroll period"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>

                <div>
                    <label class="block mb-1 font-medium" for="description_uncollected">Description</label>
                    <input type="text" name="description_uncollected" id="description_uncollected" placeholder="Auto-filled"
                        readonly required
                        class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100" />
                </div>

                <div>
                    <label class="block mb-1 font-medium" for="cvr_number_uncollected">CVR Number</label>
                    <input type="text" name="cvr_number_uncollected" id="cvr_number_uncollected" value="{{ $liquidation->cvr_number }}" readonly
                        class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100 cursor-not-allowed" />
                </div>

                <div>
                    <label class="block mb-1 font-medium" for="employee_deductions_uncollected">Employee Deductions for Uncollected</label>
                    <div id="employee-deductions-uncollected-container">
                        <!-- Dynamic Employee and Deduction rows for Uncollected will be added here -->
                    </div>
                    <button type="button" id="add-employee-uncollected-btn" class="mt-2 px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 transition">
                        Add Employee
                    </button>
                </div>

                <div>
                    <label class="block mb-1 font-medium" for="approver_id_uncollected">Approver</label>
                    <select name="approver_id_uncollected" id="approver_id_uncollected" required
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="" disabled selected>Select approver</option>
                        @foreach ($approvers as $approver)
                            <option value="{{ $approver->id }}">{{ $approver->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end space-x-2 pt-4 border-t border-gray-200">
                    <button type="button" id="closeUncollectedModalBtn" class="px-4 py-2 rounded border border-gray-300 hover:bg-gray-100 transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700 transition">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-8 pt-6 border-t border-gray-200">
        <form id="collectForm" action="{{ route('liquidations.collect', $liquidation->id) }}" method="POST" class="bg-gray-50 p-4 rounded-lg shadow-sm">
            @csrf
            <label for="collected_by" class="block mb-2 font-medium text-gray-700">Collected By</label>
            <select id="collected_by" name="collected_by" class="w-full border border-gray-300 rounded px-3 py-2 mb-4 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="" disabled selected>Select employee</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->fname }} {{ $employee->lname }}</option>
                @endforeach
            </select>

            <input type="hidden" name="action" id="form-action" value="">
            <div class="flex gap-4">
                <button type="button" id="validate-btn" class="w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700">Collect</button>
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
    // Existing Elements for modal handling
    const openCollectedModalBtn = document.getElementById('openCollectedModalBtn');
    const openUncollectedModalBtn = document.getElementById('openUncollectedModalBtn');
    const collectedModal = document.getElementById('collectedModal');
    const uncollectedModal = document.getElementById('uncollectedModal');
    const closeCollectedModalBtn = document.getElementById('closeCollectedModalBtn');
    const closeUncollectedModalBtn = document.getElementById('closeUncollectedModalBtn');
    const addEmployeeUncollectedBtn = document.getElementById('add-employee-uncollected-btn');
    const employeeDeductionsUncollectedContainer = document.getElementById('employee-deductions-uncollected-container');
    
    let deductionUncollectedCount = 0; // To keep track of dynamic rows for uncollected

    // Function to create employee deduction row for Uncollected
    function createEmployeeDeductionRowUncollected(count) {
        const row = document.createElement('div');
        row.classList.add('employee-deduction-row', 'flex', 'items-center', 'mb-4');

        // Employee Dropdown for Uncollected
        const employeeSelect = document.createElement('select');
        employeeSelect.name = `employee_id_uncollected[]`;
        employeeSelect.classList.add('employee_id_uncollected', 'w-full', 'border', 'border-gray-300', 'rounded', 'px-3', 'py-2', 'mr-4');
        employeeSelect.required = true;

        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.disabled = true;
        defaultOption.selected = true;
        defaultOption.textContent = 'Select employee';
        employeeSelect.appendChild(defaultOption);

        // Use Blade to insert the employee options into the select dropdown
        let employeeOptions = '';
        @foreach ($staffs as $staff)
            employeeOptions += `<option value="{{ $staff->id }}">{{ $staff->fname }} {{ $staff->lname }}</option>`;
        @endforeach
        employeeSelect.innerHTML += employeeOptions;

        // Deduction Amount Input for Uncollected
        const deductionInput = document.createElement('input');
        deductionInput.type = 'number';
        deductionInput.step = '0.01';
        deductionInput.min = '0';
        deductionInput.name = `deduction_amount_uncollected[]`;
        deductionInput.classList.add('deduction_amount_uncollected', 'w-full', 'border', 'border-gray-300', 'rounded', 'px-3', 'py-2', 'mr-4');
        deductionInput.required = true;
        deductionInput.setAttribute('oninput', 'calculateUncollected()'); // Recalculate uncollected amount on input

        // Remove Button with Font Awesome icon (Trash icon)
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.classList.add('text-red-500', 'ml-2', 'hover:text-red-700');
        removeBtn.innerHTML = '<i class="fas fa-trash-alt"></i>'; // Font Awesome trash icon
        removeBtn.addEventListener('click', function () {
            employeeDeductionsUncollectedContainer.removeChild(row);
            calculateUncollected(); // Recalculate after removal
        });

        // Append elements to row
        row.appendChild(employeeSelect);
        row.appendChild(deductionInput);
        row.appendChild(removeBtn);
        return row;
    }

    // Event listener for adding a new employee deduction row (Uncollected Tab)
    if (addEmployeeUncollectedBtn) {
        addEmployeeUncollectedBtn.addEventListener('click', function () {
            deductionUncollectedCount++;
            const newRow = createEmployeeDeductionRowUncollected(deductionUncollectedCount);
            employeeDeductionsUncollectedContainer.appendChild(newRow);
        });
    }

    // Open Collected Modal
    if (openCollectedModalBtn && collectedModal && closeCollectedModalBtn) {
        openCollectedModalBtn.addEventListener('click', () => {
            collectedModal.classList.remove('hidden'); // Open the modal
        });

        closeCollectedModalBtn.addEventListener('click', () => {
            collectedModal.classList.add('hidden'); // Close the modal
        });

        collectedModal.addEventListener('click', (e) => {
            if (e.target === collectedModal) collectedModal.classList.add('hidden'); // Close if clicked outside
        });
    }

    // Open Uncollected Modal
    if (openUncollectedModalBtn && uncollectedModal && closeUncollectedModalBtn) {
        openUncollectedModalBtn.addEventListener('click', () => {
            uncollectedModal.classList.remove('hidden'); // Open the modal
        });

        closeUncollectedModalBtn.addEventListener('click', () => {
            uncollectedModal.classList.add('hidden'); // Close the modal
        });

        uncollectedModal.addEventListener('click', (e) => {
            if (e.target === uncollectedModal) uncollectedModal.classList.add('hidden'); // Close if clicked outside
        });
    }

    // Handle the "Collect" and "Reject" buttons
    const validateBtn = document.getElementById('validate-btn');
    const rejectBtn = document.getElementById('reject-btn');

    // Show the confirmation modal for validation
    if (validateBtn) {
        validateBtn.addEventListener('click', () => {
            const confirmValidationModal = document.getElementById('confirmValidationModal');
            confirmValidationModal.classList.remove('hidden'); // Open the confirmation modal
        });
    }

    // Handle Reject button
    if (rejectBtn) {
        rejectBtn.addEventListener('click', () => {
            const rejectModal = document.getElementById('rejectModal');
            rejectModal.classList.remove('hidden'); // Show reject modal
        });
    }

    // Cancel Reject Button
    const cancelRejectBtn = document.getElementById('cancelRejectBtn');
    if (cancelRejectBtn) {
        cancelRejectBtn.addEventListener('click', () => {
            const rejectModal = document.getElementById('rejectModal');
            rejectModal.classList.add('hidden'); // Close the reject modal
        });
    }

    // Confirm Reject Button
    const rejectForm = document.getElementById('rejectForm');
    if (rejectForm) {
        rejectForm.addEventListener('submit', function (event) {
            event.preventDefault();
            // Process reject logic here, maybe via AJAX
            const rejectModal = document.getElementById('rejectModal');
            rejectModal.classList.add('hidden'); // Hide the modal after submit
            this.submit();
        });
    }

    // Cancel Confirmation Modal
    const cancelConfirmBtn = document.getElementById('cancelConfirmBtn');
    if (cancelConfirmBtn) {
        cancelConfirmBtn.addEventListener('click', () => {
            const confirmValidationModal = document.getElementById('confirmValidationModal');
            confirmValidationModal.classList.add('hidden'); // Close the confirmation modal
        });
    }

    // Confirm Validation
    const confirmSubmitBtn = document.getElementById('confirmSubmitBtn');
    if (confirmSubmitBtn) {
        confirmSubmitBtn.addEventListener('click', () => {
            const confirmValidationModal = document.getElementById('confirmValidationModal');
            confirmValidationModal.classList.add('hidden'); // Close the modal
            document.getElementById('form-action').value = 'validate';
            document.getElementById('collectForm').submit();
        });
    }

    // Function to calculate uncollected total deductions (you might want to define it as needed)
    function calculateUncollected() {
        let totalDeductions = 0;
        const deductionInputs = document.querySelectorAll('.deduction_amount_uncollected');
        deductionInputs.forEach(input => {
            totalDeductions += parseFloat(input.value) || 0;
        });
        // Update the total deduction somewhere if necessary
        console.log('Total Uncollected Deductions: ', totalDeductions);
    }

    const periodInput = document.getElementById('period');
    const descriptionUncollectedInput = document.getElementById('description_uncollected');

    // Function to update the description field based on the period input
    function updateDescription() {
        const periodValue = periodInput.value.trim();

        // If period is entered, set the description
        if (periodValue) {
            descriptionUncollectedInput.value = `SD - Period ${periodValue}`;
        } else {
            descriptionUncollectedInput.value = '';  // Clear if no period is entered
        }
    }

        // Listen for changes in the period input field
    if (periodInput) {
        periodInput.addEventListener('input', updateDescription);
    }


    function calculateUncollected() {
        const totalDiff = {{ abs($difference) }}; // Ensure this PHP variable is passed correctly
        const collected = parseFloat(document.getElementById('amount_collected')?.value) || 0; // Get the collected amount
        const uncollectedField = document.getElementById('amount_uncollected'); // Uncollected amount field
            
        // Sum up deductions
        const deductions = document.querySelectorAll('.deduction_amount_uncollected');
        let totalDeductions = 0;

        deductions.forEach(function (input) {
            const deductionValue = parseFloat(input.value) || 0;
            totalDeductions += deductionValue;
        });

        // Calculate the uncollected amount
        const result = totalDiff - (collected + totalDeductions);
            
        // Ensure the result is not negative
        if (uncollectedField) {
            uncollectedField.value = result > 0 ? result.toFixed(2) : '0.00';
        }
    }

    window.calculateUncollected = calculateUncollected;
});
</script>
@endsection
