@php
    $totalAmount = 0;
    foreach ($cashVouchers as $voucher) {
        $details = json_decode($voucher->amount_details, true);
        $totalAmount += $details['amount'] ?? 0;
    }
@endphp

<table class="table-auto w-full text-sm text-left text-gray-600">
    <thead class="bg-gray-100 text-gray-700">
        <tr>
            <th class="py-2 px-4 border-b">CVR Number</th>
            <th class="py-2 px-4 border-b">Company</th>
            <th class="py-2 px-4 border-b">Supplier</th>
            <th class="py-2 px-4 border-b">Amount Details</th>
            <th class="py-2 px-4 border-b">CV Type</th>
            <th class="py-2 px-4 border-b">Voucher Type</th>
            <th class="py-2 px-4 border-b">Expense Type</th>
            <th class="py-2 px-4 border-b">Actions</th>
        </tr>
    </thead>
    <tbody>
    @foreach($cashVouchers as $cashVoucher)
        @php
            $details = json_decode($cashVoucher->amount_details, true);
            $sum = is_array($details) ? array_sum(array_map('floatval', $details)) : 0;
        @endphp
        <tr class="hover:bg-gray-50">
            <td class="py-2 px-4 border-b">
                @if ($cashVoucher->cvr_type === 'admin')
                    {{ $cashVoucher->cvr_number ?? 'N/A' }}-{{ $cashVoucher->company->company_code ?? 'N/A' }}{{ $cashVoucher->expenseTypes->expense_code ?? 'N/A' }}
                @elseif ($cashVoucher->cvr_type === 'rpm')
                    {{ $cashVoucher->cvr_number ?? 'N/A' }}-{{ $cashVoucher->trucks->truck_name ?? 'N/A' }}-{{ $cashVoucher->company->company_code ?? 'N/A' }}{{ $cashVoucher->expenseTypes->expense_code ?? 'N/A' }}
                @else
                    {{ $cashVoucher->cvr_number ?? 'N/A' }}
                @endif
            </td>
            <td class="py-2 px-4 border-b">{{ $cashVoucher->company->company_code ?? 'N/A' }}</td>
            <td class="py-2 px-4 border-b">{{ $cashVoucher->suppliers->supplier_code ?? 'N/A' }}</td>
            <td class="py-2 px-4 border-b">₱{{ number_format($sum, 2) ?? 'N/A' }}</td>
             <td class="py-2 px-4 border-b">{{ $cashVoucher->cvr_type ?? 'N/A' }}</td>
            <td class="py-2 px-4 border-b">{{ $cashVoucher->voucher_type ?? 'N/A' }}</td>
            <td class="py-2 px-4 border-b">{{ $cashVoucher->expenseTypes->expense_code ?? 'N/A' }}</td>
            <td class="py-2 px-4 border-b">
                <a href="{{ route('admin.edit', $cashVoucher->id) }}" class="btn btn-warning bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Edit</a>
                <a href="{{ route('adminCV.printPreview', $cashVoucher->id) }}" class="btn btn-success bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">View</a>
                <form action="{{ route('admin.destroy', $cashVoucher->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</button>
                </form>
            </td>
        </tr>
    @endforeach

    <!-- Total Row -->
    <!-- <tr class="font-semibold bg-gray-100 text-gray-700">
        <td class="py-2 px-4 border-t text-right" colspan="3">Total:</td>
        <td class="py-2 px-4 border-t">₱{{ number_format($totalAmount, 2) }}</td>
        <td colspan="3" class="py-2 px-4 border-t"></td>
    </tr> -->
    </tbody>
</table>
