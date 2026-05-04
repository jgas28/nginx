<div class="p-6 bg-white shadow-md rounded-lg max-w-7xl mx-auto">
    <form id="multi-print-form" method="POST" action="{{ route('adminCV.printMultiple') }}" target="_blank">
        @csrf
        
        <!-- Buttons -->
        <div class="mb-6 flex justify-between items-center">
            <button type="submit" id="print-selected" class="hidden bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 transition duration-300">
                Print Selected CVRs
            </button>
        </div>

        <!-- Data Table -->
        <table class="min-w-full text-sm text-left text-gray-700 border border-gray-200 rounded-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-4 border-b">
                        <input type="checkbox" id="select-all" class="form-checkbox text-blue-500 rounded">
                    </th>
                    <th class="py-3 px-4 border-b">CVR NUMBER</th>
                    <th class="py-3 px-4 border-b">CVR Type</th>
                    <th class="py-3 px-4 border-b">Amount</th>
                    <th class="py-3 px-4 border-b">Company</th>
                    <th class="py-3 px-4 border-b">Supplier</th>
                    <th class="py-3 px-4 border-b">Printed By</th>
                    <th class="py-3 px-4 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cashVoucherRequests as $cashVoucherRequest)
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 border-b">
                            <input type="checkbox" class="select-cvr-checkbox form-checkbox text-blue-500 rounded"
                                data-cvr-id="{{ $cashVoucherRequest->id }}"
                                data-cvr-type="{{ $cashVoucherRequest->cvr_type }}"
                                data-cash-voucher-id="{{ $cashVoucherRequest->cashVoucher->id }}"
                                value="{{ $cashVoucherRequest->id }}">        
                        </td>
                        <td class="py-3 px-4 border-b">
                            @if ($cashVoucherRequest->cashVoucher->cvr_type === 'admin')
                                {{ preg_replace('/\/\d+$/', '', $cashVoucherRequest->cvr_number) }}-{{ $cashVoucherRequest->cashVoucher->company->company_code }}{{ $cashVoucherRequest->cashVoucher->expenseTypes->expense_code }}
                            @elseif ($cashVoucherRequest->cashVoucher->cvr_type === 'rpm')
                                {{ preg_replace('/\/\d+$/', '', $cashVoucherRequest->cvr_number) }}-{{ $cashVoucherRequest->cashVoucher->trucks->truck_name }}-{{ $cashVoucherRequest->cashVoucher->company->company_code }}{{ $cashVoucherRequest->cashVoucher->expenseTypes->expense_code }}
                            @endif
                        </td>
                        <td class="py-3 px-4 border-b">{{ $cashVoucherRequest->cashVoucher->cvr_type }}</td>
                        @php
                            $amounts = json_decode($cashVoucherRequest->cashVoucher->amount_details, true);
                            $total = is_array($amounts) ? array_sum(array_map('floatval', $amounts)) : 0;
                        @endphp
                        <td class="py-3 px-4 border-b">{{ number_format($total, 2) }}</td>
                        <td class="py-3 px-4 border-b">{{ $cashVoucherRequest->cashVoucher->company->company_code }}</td>
                        <td class="py-3 px-4 border-b">{{ $cashVoucherRequest->cashVoucher->suppliers->supplier_name }}</td>
                        <td class="py-3 px-4 border-b">{{ $cashVoucherRequest->cashVoucher->print_name->fname ?? '' }} {{ $cashVoucherRequest->cashVoucher->print_name->lname ?? '' }}</td>
                        <td class="py-3 px-4 border-b space-x-4">
                            <!-- Print Button -->
                            <a href="{{ route('adminCV.print', ['id' => $cashVoucherRequest->id, 'cvr_number' => $cashVoucherRequest->cashVoucher->id]) }}"
                            title="{{ empty($cashVoucherRequest->print_status) || $cashVoucherRequest->print_status === '0' ? 'Print Cash Voucher' : 'Re-Print Cash Voucher' }}"
                            target="_blank">
                                <i class="fas {{ empty($cashVoucherRequest->print_status) || $cashVoucherRequest->print_status === '0' ? 'fa-print' : 'fa-redo' }} text-sm"></i>
                            </a>
                            <!-- View Button -->
                            <a href="{{ route('adminCV.printView', ['id' => $cashVoucherRequest->id, 'cvr_number' => $cashVoucherRequest->cashVoucher->id]) }}"
                            title="View Cash Voucher"
                            target="_blank">
                                <i class="fas fa-eye mr-2 text-sm"></i> <!-- View Icon -->
                            </a>
                            <!-- Edit Button -->
                            <a href="{{ route('adminCV.editPrintView', ['id' => $cashVoucherRequest->id, 'cvr_number' => $cashVoucherRequest->cashVoucher->id]) }}"
                            title="Edit Cash Voucher"
                            target="_blank">
                                <i class="fas fa-pencil mr-2 text-sm"></i> <!-- View Icon -->
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $cashVoucherRequests->links('pagination::tailwind') }}
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.select-cvr-checkbox');
    const printButton = document.getElementById('print-selected');
    const form = document.getElementById('multi-print-form');

    function updatePrintButtonVisibility() {
        const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
        printButton.classList.toggle('hidden', !anyChecked);
    }

    selectAll.addEventListener('change', () => {
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updatePrintButtonVisibility();
    });

    checkboxes.forEach(cb => cb.addEventListener('change', updatePrintButtonVisibility));

    form.addEventListener('submit', function (e) {
        // Clear old hidden inputs
        document.querySelectorAll('.dynamic-cvr-input').forEach(el => el.remove());

        checkboxes.forEach(cb => {
            if (cb.checked) {
                const requestId = cb.dataset.cvrId;
                const cashVoucherId = cb.dataset.cashVoucherId;
                const cvrType = cb.dataset.cvrType;

                // request_ids[]
                const requestIdInput = document.createElement('input');
                requestIdInput.type = 'hidden';
                requestIdInput.name = 'request_ids[]';
                requestIdInput.value = requestId;
                requestIdInput.classList.add('dynamic-cvr-input');

                // cash_voucher_ids[<requestId>]
                const cashVoucherIdInput = document.createElement('input');
                cashVoucherIdInput.type = 'hidden';
                cashVoucherIdInput.name = `cash_voucher_ids[${requestId}]`;
                cashVoucherIdInput.value = cashVoucherId;
                cashVoucherIdInput.classList.add('dynamic-cvr-input');

                // cvr_types[<requestId>]
                const cvrTypeInput = document.createElement('input');
                cvrTypeInput.type = 'hidden';
                cvrTypeInput.name = `cvr_types[${requestId}]`;
                cvrTypeInput.value = cvrType;
                cvrTypeInput.classList.add('dynamic-cvr-input');

                form.appendChild(requestIdInput);
                form.appendChild(cashVoucherIdInput);
                form.appendChild(cvrTypeInput);
            }
        });
    });
});
</script>
