@extends('layouts.app')

@section('title', 'Rejected Approval')

@section('content')
<div class="max-w-7xl mx-auto p-6 bg-white shadow rounded">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Rejected Cash Vouchers</h1>

    @if ($cashVouchers->isEmpty())
        <p class="text-gray-600">No rejected cash vouchers found.</p>
    @else
        <form id="batchPrintForm" method="GET" action="{{ route('cashVoucherRequests.rejectPrintViewMultiple') }}" target="_blank">
            <div>
                <button type="submit"
                        class="mt-4 bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition"
                        style="display: none;">
                    Batch Print Selected
                </button>
            </div>
            <table class="min-w-full border border-collapse border-gray-300 text-sm text-left">
                <thead>
                    <tr class="bg-gray-100 text-gray-700">
                        <th class="border px-4 py-2">
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th class="border px-4 py-2">CVR Number</th>
                        <th class="border px-4 py-2 text-right">Amount</th>
                        <th class="border px-4 py-2">Reject Remarks</th>
                        <th class="border px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cashVouchers as $voucher)
                        @php
                            $formattedCvrNumber = preg_replace('/\/\d+$/', '', $voucher->cvr_number);
                            $truckId = optional(optional($voucher->matched_allocation)->truck)->truck_name ?? 'N/A';
                            $companyId = optional(optional($voucher->deliveryRequest)->company)->company_code ?? 'N/A';
                            $expenseTypeId = optional(optional($voucher->deliveryRequest)->expenseType)->expense_code ?? 'N/A';
                            $remarks = json_decode($voucher->reject_remarks, true);
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="border px-4 py-2">
                                <input type="checkbox" name="ids[]" value="{{ $voucher->id }}" class="select-checkbox">
                            </td>
                            <td class="border px-4 py-2">
                                {{ $formattedCvrNumber }}-{{ $truckId }}-{{ $companyId }}{{ $expenseTypeId }}
                            </td>
                            <td class="border px-4 py-2 text-right">
                                ₱{{ number_format($voucher->amount, 2) }}
                            </td>
                            <td class="border px-4 py-2">
                                @if (is_array($remarks))
                                    <ul class="list-disc pl-5 space-y-1">
                                        @foreach ($remarks as $remark)
                                            <li>{{ $remark }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span>{{ $voucher->reject_remarks }}</span>
                                @endif
                            </td>
                            <td class="border px-4 py-2">
                                <a href="{{ route('cashVoucherRequests.editCVR', $voucher->id) }}"
                                class="inline-block bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-xs transition">
                                    Edit
                                </a>
                                <a href="{{ route('cashVoucherRequests.rejectPrintView', ['id' => $voucher->id, 'cvr_number' => $voucher->dr_id, 'cvr_type' => $voucher->cvr_type]) }}"
                                target="_blank"
                                class="inline-block bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-xs transition">
                                    Print
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </form>
    @endif
</div>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const selectAll = document.getElementById("selectAll");
        const checkboxes = document.querySelectorAll(".select-checkbox");
        const batchPrintBtn = document.querySelector("#batchPrintForm button[type='submit']");

        // Initial button state
        toggleButton();

        // Select all logic
        selectAll.addEventListener("change", function () {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            toggleButton();
        });

        // Individual checkbox change logic
        checkboxes.forEach(cb => {
            cb.addEventListener("change", () => {
                // Uncheck "select all" if any item is unchecked
                if (!cb.checked) selectAll.checked = false;

                // If all are checked manually, check "select all"
                if ([...checkboxes].every(c => c.checked)) {
                    selectAll.checked = true;
                }

                toggleButton();
            });
        });

        function toggleButton() {
            const anyChecked = [...checkboxes].some(cb => cb.checked);
            batchPrintBtn.style.display = anyChecked ? "inline-block" : "none";
        }
    });
</script>
@endsection
