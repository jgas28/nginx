<div class="p-4">
    <!-- Print Selected Button (initially hidden) -->
    <form id="multi-print-form" method="POST" action="{{ route('cashVoucherRequests.printMultiple') }}" target="_blank">
        @csrf
        <div class="mb-4">
            <button type="submit" id="print-selected" class="hidden bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                Print Selected CVRs
            </button>
        </div>

        <!-- Data Table -->
        <table class="table-auto w-full text-sm text-left text-gray-700 border border-gray-200 rounded">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-2 px-4 border-b">
                        <input type="checkbox" id="select-all" class="form-checkbox text-blue-500">
                    </th>
                    <th class="py-2 px-4 border-b">MTM</th>
                    <th class="py-2 px-4 border-b">CVR NUMBER</th>
                    <th class="py-2 px-4 border-b">Amount</th>
                    <th class="py-2 px-4 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cashVoucherRequests as $cashVoucherRequest)
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 border-b">
                            <input type="checkbox" class="select-cvr-checkbox form-checkbox text-blue-500"
                                data-cvr-id="{{ $cashVoucherRequest->id }}"
                                data-cvr-type="{{ $cashVoucherRequest->cvr_type }}"
                                value="{{ $cashVoucherRequest->id }}">
                        </td>
                        <td class="py-2 px-4 border-b">{{ $cashVoucherRequest->mtm }}</td>
                        <td class="py-2 px-4 border-b">
                            @php
                                $tripType = strtolower($cashVoucherRequest->cvr_type);

                                $allocations = match($tripType) {
                                    'delivery' => $cashVoucherRequest->deliveryRequest->deliveryAllocations ?? [],
                                    'pullout' => $cashVoucherRequest->deliveryRequest->pulloutAllocations ?? [],
                                    'accessorial' => $cashVoucherRequest->deliveryRequest->accessorialAllocations ?? [],
                                    'others' => $cashVoucherRequest->deliveryRequest->othersAllocations ?? [],
                                    'freight' => $cashVoucherRequest->deliveryRequest->freightAllocations ?? [],
                                    default => [],
                                };

                                $truckId = $allocations[0]->truck->truck_name ?? 'N/A';
                                $companyId = $cashVoucherRequest->deliveryRequest->company->company_code ?? 'N/A';
                                $expenseTypeId = $cashVoucherRequest->deliveryRequest->expenseType->expense_code ?? 'N/A';
                            @endphp

                            {{ preg_replace('/\/\d+$/', '', $cashVoucherRequest->cvr_number) }}-{{ $truckId }}-{{ $companyId }}{{ $expenseTypeId }}
                        </td>
                        <td class="py-2 px-4 border-b">{{ $cashVoucherRequest->amount }}</td>
                        <td class="py-2 px-4 border-b space-x-2">
                            <a href="{{ route('cashVoucherRequests.print', ['id' => $cashVoucherRequest->id, 'cvr_number' => $cashVoucherRequest->dr_id, 'mtm' => $cashVoucherRequest->cvr_type]) }}"
                            class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600"
                            title="Print Cash Voucher" target="_blank">
                            @if(empty($cashVoucherRequest->print_status) || $cashVoucherRequest->print_status === '0')
                                    Print
                                @else
                                    Re-Print 
                                @endif
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </form>
    <!-- Pagination -->
    <div class="mt-4">
        {{ $cashVoucherRequests->links('pagination::tailwind') }}
    </div>
</div>

<!-- JavaScript to handle checkbox logic -->
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

        // Add selected CVR IDs and their types
        checkboxes.forEach(cb => {
            if (cb.checked) {
                const id = cb.dataset.cvrId;
                const type = cb.dataset.cvrType;

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'cvr_ids[]';
                idInput.value = id;
                idInput.classList.add('dynamic-cvr-input');

                const typeInput = document.createElement('input');
                typeInput.type = 'hidden';
                typeInput.name = `cvr_types[${id}]`;
                typeInput.value = type;
                typeInput.classList.add('dynamic-cvr-input');

                form.appendChild(idInput);
                form.appendChild(typeInput);
            }
        });
    });
});
</script>

