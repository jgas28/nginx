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

    <form id="approvalForm" action="{{ route('liquidations.approvedEdit', $liquidation->id) }}" method="POST" class="bg-gray-50 p-4 rounded-lg shadow-sm">
        @csrf
        <div class="space-y-8">

    {{-- 🧾 Expenses Section --}}
    <div class="bg-white p-6 rounded-xl shadow border">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Expenses</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach (['allowance', 'manpower', 'hauling', 'right_of_way', 'roro_expense'] as $field)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 capitalize">
                        {{ $field === 'roro_expense' ? 'Freight' : str_replace('_', ' ', $field) }}
                    </label>
                    <input 
                        type="number" step="0.01" name="{{ $field }}"
                        value="{{ old($field, $liquidation->$field ?? 0) }}"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                    />
                </div>
            @endforeach

            {{-- Readonly Cash Charge --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cash Charge</label>
                <input 
                    type="text" readonly 
                    value="₱{{ number_format($liquidation->cash_charge ?? 0, 2) }}"
                    class="w-full px-3 py-2 bg-gray-100 text-gray-600 rounded-lg border border-gray-300 cursor-default text-sm"
                />
            </div>
            <input type="hidden" name="cash_charge" value="{{ old('cash_charge', $liquidation->cash_charge ?? 0) }}" />
        </div>
    </div>

    {{-- 🛢️ Gasoline + RFID --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        {{-- Gasoline --}}
        <div class="bg-white p-6 rounded-xl shadow border">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h2 class="text-lg font-semibold text-gray-800">Gasoline</h2>
                <button type="button" onclick="addGasolineField()" class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded">
                    + Add
                </button>
            </div>
            <div class="space-y-3" id="gasolineList">
                @foreach ($gasoline as $index => $item)
                    <div class="flex flex-nowrap items-center gap-3" data-index="{{ $index }}">
                        <select name="gasoline[{{ $index }}][type]" class="w-36 shrink-0 border rounded px-3 py-2 text-sm">
                            <option value="" {{ empty($item['type']) ? 'selected' : '' }}>Type</option>
                            <option value="cash" {{ ($item['type'] ?? '') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="card" {{ ($item['type'] ?? '') === 'card' ? 'selected' : '' }}>Card</option>
                        </select>
                        <input type="number" step="0.01" name="gasoline[{{ $index }}][amount]" value="{{ $item['amount'] ?? '' }}"
                            placeholder="Amount" class="min-w-0 flex-1 border rounded px-3 py-2 text-sm" />
                        <button type="button" onclick="this.closest('[data-index]').remove()" class="text-red-500 hover:text-red-700 text-sm">✕</button>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- RFID --}}
        <div class="bg-white p-6 rounded-xl shadow border">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h2 class="text-lg font-semibold text-gray-800">RFID</h2>
                <button type="button" onclick="addRFIDField()" class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded">
                    + Add
                </button>
            </div>
            <div class="space-y-3" id="rfidList">
                @foreach ($rfid as $index => $item)
                    <div class="flex flex-nowrap items-center gap-3" data-index="{{ $index }}">
                        <select name="rfid[{{ $index }}][tag]" class="w-40 shrink-0 border rounded px-3 py-2 text-sm">
                            <option value="">Select Tag</option>
                            <option value="autosweep" {{ ($item['tag'] ?? '') === 'autosweep' ? 'selected' : '' }}>AutoSweep</option>
                            <option value="easytrip" {{ ($item['tag'] ?? '') === 'easytrip' ? 'selected' : '' }}>EasyTrip</option>
                        </select>
                        <select name="rfid[{{ $index }}][type]" class="w-32 shrink-0 border rounded px-3 py-2 text-sm">
                            <option value="">Type</option>
                            <option value="cash" {{ ($item['type'] ?? '') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="card" {{ ($item['type'] ?? '') === 'card' ? 'selected' : '' }}>Card</option>
                        </select>
                        <input type="number" step="0.01" name="rfid[{{ $index }}][amount]" value="{{ $item['amount'] ?? '' }}"
                            placeholder="Amount" class="min-w-0 flex-1 border rounded px-3 py-2 text-sm" />
                        <button type="button" onclick="this.closest('[data-index]').remove()" class="text-red-500 hover:text-red-700 text-sm">✕</button>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 🧾 Others Section --}}
    <div class="bg-white p-6 rounded-xl shadow border">
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h2 class="text-lg font-semibold text-gray-800">Others</h2>
            <button type="button" onclick="addOthersField()" class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded">
                + Add
            </button>
        </div>
        <div class="space-y-4" id="othersList">
            @foreach ($others as $index => $item)
                <div class="flex w-full items-center gap-3" data-index="{{ $index }}">
                    <input type="text" name="others[{{ $index }}][description]" placeholder="Description" value="{{ $item['description'] ?? '' }}"
                        class="w-[60%] min-w-0 rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 sm:px-5 sm:text-base" />
                    <input type="number" step="0.01" name="others[{{ $index }}][amount]" placeholder="Amount" value="{{ $item['amount'] ?? '' }}"
                        class="w-[35%] min-w-0 rounded-2xl border border-slate-300 px-3 py-3 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 sm:px-4 sm:text-base" />
                    <button type="button" onclick="this.closest('[data-index]').remove()" class="inline-flex h-10 w-[5%] min-w-[44px] items-center justify-center rounded-2xl bg-red-600 text-2xl leading-none text-white transition hover:bg-red-700 sm:h-11" aria-label="Remove others row">&times;</button>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Submit Button --}}
    <div class="flex justify-end">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-6 py-2.5 rounded-md shadow">
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
        <!-- <button id="openModalBtn"
            class="mt-6 {{ $difference > 0 ? 'bg-yellow-500' : 'bg-red-600' }} text-white px-5 py-2 rounded hover:{{ $difference > 0 ? 'bg-yellow-600' : 'bg-red-700' }} transition">
            {{ $difference > 0 ? 'Create Return' : 'Create Refund' }}
        </button> -->
        @if($difference > 0)
            <div>
                <button id="openCollectedModalBtn" 
                    class="mt-6 bg-indigo-600 text-white px-5 py-2 rounded hover:bg-indigo-700 transition">
                    Create Return Collected
                </button>

                <button id="openUncollectedModalBtn" 
                    class="mt-6 bg-yellow-600 text-white px-5 py-2 rounded hover:bg-yellow-700 transition">
                    Create Return Uncollected
                </button>
            </div>
        @elseif($difference < 0)
            <button id="openRefundModalBtn" 
                class="mt-6 bg-indigo-600 text-white px-5 py-2 rounded hover:bg-indigo-700 transition">
                Create Refund
            </button>
        @endif
    @endif

    <!--Return Modal (collected and uncollected)-->
    <!-- Collected Modal Backdrop -->
    <div id="collectedModal" class="hidden fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-slate-950/60 p-3 sm:items-center sm:p-4">
        <!-- Collected Modal Panel -->
        <div class="relative w-full max-w-xl max-h-[calc(100vh-1.5rem)] overflow-y-auto rounded-[26px] border border-slate-200 bg-white p-4 shadow-[0_24px_60px_rgba(15,23,42,0.22)] sm:max-h-[calc(100vh-3rem)] sm:p-6">
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
                    'idPrefix' => 'approval-collected-party',
                    'employees' => $staffs,
                    'suppliers' => $suppliers,
                    'partyRequired' => true,
                    'wrapperClass' => 'space-y-4',
                    'selectClass' => 'h-[48px] w-full rounded-2xl border border-slate-200 px-4 text-[15px] text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100',
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

                <div class="flex flex-col-reverse gap-3 border-t border-gray-200 pt-4 sm:flex-row sm:justify-end">
                    <button type="button" id="closeCollectedModalBtn" class="w-full rounded-2xl border border-slate-200 px-4 py-3 font-semibold text-slate-700 transition hover:bg-gray-100 sm:w-auto">
                        Cancel
                    </button>
                    <button type="submit" class="w-full rounded-2xl bg-indigo-600 px-4 py-3 font-semibold text-white transition hover:bg-indigo-700 sm:w-auto">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Uncollected Modal Backdrop -->
    <div id="uncollectedModal" class="hidden fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-slate-950/60 p-3 sm:items-center sm:p-4">
        <!-- Uncollected Modal Panel -->
        <div class="relative w-full max-w-xl max-h-[calc(100vh-1.5rem)] overflow-y-auto rounded-[26px] border border-slate-200 bg-white p-4 shadow-[0_24px_60px_rgba(15,23,42,0.22)] sm:max-h-[calc(100vh-3rem)] sm:p-6">
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
                    <div id="employee-deductions-uncollected-container" class="space-y-3">
                        <!-- Dynamic Employee and Deduction rows for Uncollected will be added here -->
                    </div>
                    <button type="button" id="add-employee-uncollected-btn" class="mt-3 inline-flex w-full items-center justify-center rounded-2xl bg-blue-600 px-4 py-3 font-semibold text-white transition hover:bg-blue-700 sm:w-auto">
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

                <div class="flex flex-col-reverse gap-3 border-t border-gray-200 pt-4 sm:flex-row sm:justify-end">
                    <button type="button" id="closeUncollectedModalBtn" class="w-full rounded-2xl border border-slate-200 px-4 py-3 font-semibold text-slate-700 transition hover:bg-gray-100 sm:w-auto">
                        Cancel
                    </button>
                    <button type="submit" class="w-full rounded-2xl bg-indigo-600 px-4 py-3 font-semibold text-white transition hover:bg-indigo-700 sm:w-auto">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!--Return Modal-->
    <div id="reimbursementModal" class="fixed inset-0 z-50 hidden flex items-start justify-center overflow-y-auto bg-slate-950/60 p-3 sm:items-center sm:p-4">
        <div class="relative w-full max-w-xl max-h-[calc(100vh-1.5rem)] overflow-y-auto rounded-[26px] border border-slate-200 bg-white p-4 shadow-[0_24px_60px_rgba(15,23,42,0.22)] sm:max-h-[calc(100vh-3rem)] sm:p-6" id="modalPanel">
            <h3 class="text-xl font-semibold mb-4">Create Reimbursement</h3>
            <form action="{{ route('running-balance.reimburse') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="created_by" value="{{ auth()->user()->id }}" />
                <input type="hidden" name="liquidation_id" value="{{ $liquidation->id }}" />
                <input type="hidden" name="type" value="{{ $difference > 0 ? 'refund' : 'return' }}" />
                <div><label class="block mb-1 font-medium">Amount Difference (₱)</label><input type="number" step="0.01" min="0" value="{{ abs($difference) }}" readonly class="w-full border rounded px-3 py-2 bg-gray-100" /></div>
                <div><label class="block mb-1 font-medium">Amount (₱)</label><input type="number" step="0.01" min="0" name="amount" required class="w-full border rounded px-3 py-2" /></div>
                <div><label class="block mb-1 font-medium">Description</label><input type="text" name="description" value="Refund - {{ $liquidation->cvr_number }}" required class="w-full border rounded px-3 py-2" /></div>
                <div><label class="block mb-1 font-medium">CVR Number</label><input type="text" name="cvr_number" value="{{ $liquidation->cvr_number }}" readonly class="w-full border rounded px-3 py-2 bg-gray-100" /></div>
                @include('partials.party-selector', [
                    'idPrefix' => 'approval-refund-party',
                    'employees' => $staffs,
                    'suppliers' => $suppliers,
                    'partyRequired' => true,
                    'wrapperClass' => 'space-y-4',
                    'selectClass' => 'h-[48px] w-full rounded-2xl border border-slate-200 px-4 text-[15px] text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100',
                    'typeLabel' => 'Recipient Type',
                    'typePlaceholder' => 'Select employee or supplier',
                ])
                <div><label class="block mb-1 font-medium">Approver</label><select name="approver_id" required class="h-[48px] w-full rounded-2xl border border-slate-200 px-4 text-[15px] text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">@foreach ($approvers as $approver)<option value="{{ $approver->id }}">{{ $approver->name }}</option>@endforeach</select></div>
                <div class="flex flex-col-reverse gap-3 border-t border-gray-200 pt-4 sm:flex-row sm:justify-end">
                    <button type="button" id="closeModalBtn" class="w-full rounded-2xl border border-slate-200 px-4 py-3 font-semibold text-slate-700 transition hover:bg-gray-100 sm:w-auto">Cancel</button>
                    <button type="submit" class="w-full rounded-2xl bg-indigo-600 px-4 py-3 font-semibold text-white transition hover:bg-indigo-700 sm:w-auto">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- <div id="reimbursementModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 overflow-y-auto p-4">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 relative max-h-[90vh] overflow-y-auto" id="modalPanel">
            <h3 class="text-xl font-semibold mb-4">
                  {{ $difference > 0 ? 'Create Return' : 'Create Refund' }}
            </h3>

            <form action="{{ $difference > 0 ? route('running-balance.collectedAdmin') : route('running-balance.reimburseAdmin') }}"
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
                @if ($difference < 0)
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
    </div> -->

    <div class="mt-8 pt-6 border-t border-gray-200">
        <form id="approvalFormOK" action="{{ route('liquidations.approved', $liquidation->id) }}" method="POST" class="bg-gray-50 p-4 rounded-lg shadow-sm">
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
<div id="rejectModal" class="fixed inset-0 z-50 hidden flex items-start justify-center overflow-y-auto bg-slate-950/60 p-3 sm:items-center sm:p-4">
    <div class="w-full max-w-md rounded-[24px] border border-slate-200 bg-white p-4 shadow-[0_24px_60px_rgba(15,23,42,0.22)] sm:p-6">
        <form id="rejectForm" action="{{ route('liquidations.reject', $liquidation->id) }}" method="POST">
            @csrf
            <input type="hidden" name="validated_by" value="{{ auth()->user()->id }}">
            <h3 class="text-xl font-semibold mb-4 text-red-600">Reject Liquidation</h3>
            <p class="text-sm text-gray-600 mb-3">Please provide remarks for rejecting this liquidation:</p>
            <textarea name="remarks" id="rejectRemarks" rows="3" required class="w-full border rounded px-3 py-2" placeholder="Enter reason..."></textarea>
            <div class="mt-4 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" id="cancelRejectBtn" class="w-full rounded-2xl border border-slate-200 px-4 py-3 font-semibold text-slate-700 transition hover:bg-gray-100 sm:w-auto">Cancel</button>
                <button type="submit" class="w-full rounded-2xl bg-red-600 px-4 py-3 font-semibold text-white transition hover:bg-red-700 sm:w-auto">Confirm Reject</button>
            </div>
        </form>
    </div>
</div>

{{-- Confirm Validation Modal --}}
<div id="confirmValidationModal" class="fixed inset-0 z-50 hidden flex items-start justify-center overflow-y-auto bg-slate-950/60 p-3 sm:items-center sm:p-4">
    <div class="w-full max-w-md rounded-[24px] border border-slate-200 bg-white p-4 shadow-[0_24px_60px_rgba(15,23,42,0.22)] sm:p-6">
        <h3 class="text-xl font-semibold mb-4 text-indigo-700">Confirm Aprroval</h3>
        <p class="text-sm text-gray-600 mb-3">
            Are you sure you want to confirm the validation?
            <span id="validationDifferenceWarning" class="text-red-600 font-medium hidden">
                There is a ₱<span id="differenceAmount">0.00</span> difference between approved and liquidated amounts.
            </span>
        </p>
        <div class="mt-4 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <button type="button" id="cancelConfirmValidationBtn" class="w-full rounded-2xl border border-slate-200 px-4 py-3 font-semibold text-slate-700 transition hover:bg-gray-100 sm:w-auto">Cancel</button>
            <button type="button" id="confirmValidationBtn" class="w-full rounded-2xl bg-indigo-600 px-4 py-3 font-semibold text-white transition hover:bg-indigo-700 sm:w-auto" disabled>Confirm</button>
        </div>
    </div>
</div>

<!-- Edit Refund Modal -->
<div id="editRefundModal" class="fixed inset-0 z-50 hidden flex items-start justify-center overflow-y-auto bg-slate-950/60 p-3 sm:items-center sm:p-4">
    <div class="relative w-full max-w-md rounded-[24px] border border-slate-200 bg-white p-4 shadow-[0_24px_60px_rgba(15,23,42,0.22)] sm:p-6">
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
            <div class="flex flex-col-reverse gap-3 border-t pt-4 sm:flex-row sm:justify-end">
                <button type="button" class="w-full rounded-2xl border border-slate-200 px-4 py-3 font-semibold text-slate-700 transition hover:bg-gray-100 sm:w-auto" onclick="closeEditRefundModal()">Cancel</button>
                <button type="submit" class="w-full rounded-2xl bg-indigo-600 px-4 py-3 font-semibold text-white transition hover:bg-indigo-700 sm:w-auto">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Return Modal -->
<div id="editReturnModal" class="fixed inset-0 z-50 hidden flex items-start justify-center overflow-y-auto bg-slate-950/60 p-3 sm:items-center sm:p-4">
    <div class="relative w-full max-w-md rounded-[24px] border border-slate-200 bg-white p-4 shadow-[0_24px_60px_rgba(15,23,42,0.22)] sm:p-6">
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
            <div class="flex flex-col-reverse gap-3 border-t pt-4 sm:flex-row sm:justify-end">
                <button type="button" class="w-full rounded-2xl border border-slate-200 px-4 py-3 font-semibold text-slate-700 transition hover:bg-gray-100 sm:w-auto" onclick="closeEditReturnModal()">Cancel</button>
                <button type="submit" class="w-full rounded-2xl bg-indigo-600 px-4 py-3 font-semibold text-white transition hover:bg-indigo-700 sm:w-auto">Save</button>
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
        wrapper.insertAdjacentHTML('afterbegin', `
            <div class="flex flex-nowrap items-center gap-3" data-index="${gasolineIndex}">
                <select name="gasoline[${gasolineIndex}][type]" class="w-36 shrink-0 border rounded px-3 py-2 text-sm">
                    <option value="">Type</option>
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                </select>
                <input type="number" step="0.01" name="gasoline[${gasolineIndex}][amount]" placeholder="Amount" class="min-w-0 flex-1 border rounded px-3 py-2 text-sm" />
                <button type="button" onclick="this.closest('[data-index]').remove()" class="inline-flex h-[72px] w-[72px] items-center justify-center rounded-2xl bg-red-600 text-3xl font-light text-white transition hover:bg-red-700" aria-label="Remove others row">&times;</button>
            </div>
        `);
        gasolineIndex++;
    }

    function addRFIDField() {
        const wrapper = document.getElementById('rfidList');
        wrapper.insertAdjacentHTML('afterbegin', `
            <div class="flex flex-nowrap items-center gap-3" data-index="${rfidIndex}">
                <select name="rfid[${rfidIndex}][tag]" class="w-40 shrink-0 border rounded px-3 py-2 text-sm">
                    <option value="">Select Tag</option>
                    <option value="autosweep">AutoSweep</option>
                    <option value="easytrip">EasyTrip</option>
                </select>
                <select name="rfid[${rfidIndex}][type]" class="w-32 shrink-0 border rounded px-3 py-2 text-sm">
                    <option value="">Type</option>
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                </select>
                <input type="number" step="0.01" name="rfid[${rfidIndex}][amount]" placeholder="Amount" class="min-w-0 flex-1 border rounded px-3 py-2 text-sm" />
                <button type="button" onclick="this.closest('[data-index]').remove()" class="inline-flex h-12 w-full items-center justify-center rounded-2xl bg-red-600 text-2xl font-light text-white transition hover:bg-red-700 sm:h-14 sm:w-14 lg:h-[72px] lg:w-[72px] lg:text-3xl" aria-label="Remove others row">&times;</button>
            </div>
        `);
        rfidIndex++;
    }

    function addOthersField() {
        const wrapper = document.getElementById('othersList');
        wrapper.insertAdjacentHTML('afterbegin', `
            <div class="flex w-full items-center gap-3" data-index="${othersIndex}">
                <input type="text" name="others[${othersIndex}][description]" placeholder="Description" class="w-[60%] min-w-0 rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 sm:px-5 sm:text-base" />
                <input type="number" step="0.01" name="others[${othersIndex}][amount]" placeholder="Amount" class="w-[35%] min-w-0 rounded-2xl border border-slate-300 px-3 py-3 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 sm:px-4 sm:text-base" />
                <button type="button" onclick="this.closest('[data-index]').remove()" class="inline-flex h-10 w-[5%] min-w-[44px] items-center justify-center rounded-2xl bg-red-600 text-2xl leading-none text-white transition hover:bg-red-700 sm:h-11" aria-label="Remove others row">&times;</button>
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
    const rejectBtn = document.getElementById('reject-btn');
    const rejectModal = document.getElementById('rejectModal');
    const cancelRejectBtn = document.getElementById('cancelRejectBtn');
    const validateBtn = document.getElementById('validate-btn');
    const confirmValidationModal = document.getElementById('confirmValidationModal');
    const confirmValidationBtn = document.getElementById('confirmValidationBtn');
    const cancelConfirmValidationBtn = document.getElementById('cancelConfirmValidationBtn');
    const validationForm = document.getElementById('approvalForm');
    const difference = parseFloat({{ abs($difference) }});
    const warningSpan = confirmValidationModal.querySelector('.text-red-600');
    const differenceAmount = document.getElementById('differenceAmount');
    
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

        if (difference !== 0) {
            warningSpan?.classList.remove('hidden');
            differenceAmount.textContent = difference.toFixed(2);
            confirmValidationBtn.disabled = true;
            confirmValidationBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            warningSpan?.classList.add('hidden');
            confirmValidationBtn.disabled = false;
            confirmValidationBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    });

    confirmValidationBtn?.addEventListener('click', function () {
        const validationForm = document.getElementById('approvalFormOK');
        if (!validationForm) {
            console.error('Approval form not found.');
            return;
        }

        document.getElementById('form-action').value = 'validate';

        if (validationForm.reportValidity()) {
            validationForm.submit();
        }
    });

    cancelConfirmValidationBtn?.addEventListener('click', function () {
        confirmValidationModal?.classList.add('hidden');
    });

    // New modal buttons
    const openCollectedModalBtn = document.getElementById('openCollectedModalBtn');
    const openUncollectedModalBtn = document.getElementById('openUncollectedModalBtn');
    const openRefundModalBtn = document.getElementById('openRefundModalBtn');

    const collectedModal = document.getElementById('collectedModal');
    const uncollectedModal = document.getElementById('uncollectedModal');
    const reimbursementModal = document.getElementById('reimbursementModal');

    const closeCollectedModalBtn = document.getElementById('closeCollectedModalBtn');
    const closeUncollectedModalBtn = document.getElementById('closeUncollectedModalBtn');
    const closeRefundModalBtn = document.getElementById('closeModalBtn');

    // Open collected modal
    openCollectedModalBtn?.addEventListener('click', function () {
        collectedModal?.classList.remove('hidden');
    });

    // Open uncollected modal
    openUncollectedModalBtn?.addEventListener('click', function () {
        uncollectedModal?.classList.remove('hidden');
    });

    // Open refund modal
    openRefundModalBtn?.addEventListener('click', function () {
        reimbursementModal?.classList.remove('hidden');
    });

    // Close collected modal
    closeCollectedModalBtn?.addEventListener('click', function () {
        collectedModal?.classList.add('hidden');
    });

    // Close uncollected modal
    closeUncollectedModalBtn?.addEventListener('click', function () {
        uncollectedModal?.classList.add('hidden');
    });

    // Close refund modal
    closeRefundModalBtn?.addEventListener('click', function () {
        reimbursementModal?.classList.add('hidden');
    });

    // Additional dynamic functionality for uncollected modal
    const addEmployeeUncollectedBtn = document.getElementById('add-employee-uncollected-btn');
    const employeeDeductionsUncollectedContainer = document.getElementById('employee-deductions-uncollected-container');

    // Add employee row in uncollected modal
    addEmployeeUncollectedBtn?.addEventListener('click', function () {
        const employeeRow = document.createElement('div');
        employeeRow.classList.add('flex', 'space-x-2', 'mt-2');
        employeeRow.innerHTML = `
            <input type="text" name="employee_name[]" placeholder="Employee Name" class="w-full border border-gray-300 rounded px-3 py-2" />
            <input type="number" step="0.01" name="deduction_amount[]" placeholder="Deduction Amount" class="w-full border border-gray-300 rounded px-3 py-2" />
            <button type="button" class="remove-employee-btn px-2 text-red-600" style="cursor: pointer;">Remove</button>
        `;
        employeeDeductionsUncollectedContainer?.appendChild(employeeRow);

        // Remove employee row
        employeeRow.querySelector('.remove-employee-btn')?.addEventListener('click', function () {
            employeeRow.remove();
        });
    });

    // Handle form submissions and validations
    const collectedForm = document.getElementById('reimbursementForm');
    const uncollectedForm = document.getElementById('reimbursementFormUncollected');
    const reimbursementForm = document.querySelector('#reimbursementModal form');

    // Submit collected form
    collectedForm?.addEventListener('submit', function (e) {
        e.preventDefault();
        // Submit the collected form here
        collectedForm.submit();
    });

    // Submit uncollected form
    uncollectedForm?.addEventListener('submit', function (e) {
        e.preventDefault();
        // Submit the uncollected form here
        uncollectedForm.submit();
    });

    // Submit refund form
    reimbursementForm?.addEventListener('submit', function (e) {
        e.preventDefault();
        // Submit the refund form here
        reimbursementForm.submit();
    });
});

</script>
@endsection
