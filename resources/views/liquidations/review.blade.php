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

        {{-- Refund Cash --}}
        <div class="p-4 rounded-lg shadow-inner 
            {{ $difference > 0 ? 'bg-red-100 text-red-700' : 'bg-gray-50 text-gray-700' }} 
            font-semibold text-lg flex justify-between items-center">
            <span>Refund Cash</span>
            <span>₱{{ number_format($difference > 0 ? $difference : 0, 2) }}</span>
        </div>

        {{-- Returned Cash --}}
        <div class="p-4 rounded-lg shadow-inner 
            {{ $difference < 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-50 text-gray-700' }} 
            font-semibold text-lg flex justify-between items-center">
            <span>Returned Cash</span>
            <span>₱{{ number_format($difference < 0 ? abs($difference) : 0, 2) }}</span>
        </div>

        {{-- Optional: Total Card Expenses --}}
        <!--
        <div class="bg-blue-50 p-4 rounded shadow">
            <p class="text-gray-800">Total Card Expenses (RFID & Gasoline)</p>
            <p class="text-blue-700 text-right text-xl font-bold">₱{{ number_format($totalCard, 2) }}</p>
        </div>
        -->
    </div>


    @if ($refund)
        <button id="openModalBtn" 
            class="mt-6 bg-red-600 text-white px-5 py-2 rounded hover:bg-red-700 transition">
            Create Refund
        </button>
    @endif

    <!-- Modal Backdrop -->
    <div id="reimbursementModal" 
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        
        <!-- Modal Panel -->
        <div class="bg-white rounded-lg shadow-lg max-w-lg w-full p-6 relative" id="modalPanel">
            <h3 class="text-xl font-semibold mb-4">Create Reimbursement</h3>

            <form action="{{ route('running-balance.reimburse') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block mb-1 font-medium" for="amount_label">Amount Difference (₱)</label>
                    <input type="number" step="0.01" min="0" name="amount_label" id="amount_label" 
                        value="{{ abs($difference) }}" readonly
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>

                <div>
                    <label class="block mb-1 font-medium" for="amount">Amount (₱)</label>
                    <input type="number" step="0.01" min="0" name="amount" id="amount" 
                        required
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>

                <div>
                    <label class="block mb-1 font-medium" for="description">Description</label>
                    <input type="text" name="description" id="description" placeholder="Description" required
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
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
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->fname }} {{ $employee->lname }}</option>
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
        <form action="{{ route('liquidations.validate', $liquidation->id) }}" method="POST" class="bg-gray-50 p-4 rounded-lg shadow-sm">
            @csrf
            <label for="validated_by" class="block mb-2 font-medium text-gray-700">Validated By</label>
            <select id="validated_by" name="validated_by" required class="w-full border border-gray-300 rounded px-3 py-2 mb-4 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->fname }} {{ $employee->lname }}</option>
                @endforeach
            </select>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 rounded transition duration-150">
                Validate
            </button>
        </form>
    </div>
</div>
<!-- Modal JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const openBtn = document.getElementById('openModalBtn');
        const modal = document.getElementById('reimbursementModal');
        const closeBtn = document.getElementById('closeModalBtn');

        openBtn.addEventListener('click', () => {
            modal.classList.remove('hidden');
        });

        closeBtn.addEventListener('click', () => {
            modal.classList.add('hidden');
        });

        // Close modal when clicking outside the panel
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.add('hidden');
            }
        });

        // Escape key closes modal
        document.addEventListener('keydown', function (e) {
            if (e.key === "Escape") {
                modal.classList.add('hidden');
            }
        });
    });
</script>
@endsection
