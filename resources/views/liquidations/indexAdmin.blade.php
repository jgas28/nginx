@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <!-- Filters -->
        <form action="{{ route('liquidations.indexAdmin') }}" method="GET" class="flex flex-wrap space-x-4">
            <!-- Supplier Filter -->
            <div class="flex-1">
                <label for="supplier_id" class="block text-sm font-medium text-gray-700">Supplier</label>
                <select name="supplier_id" id="supplier_id" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">All Suppliers</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->supplier_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Button -->
            <div class="flex items-end">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto bg-white shadow-md rounded-lg">
        <table id="sortableTable" class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 cursor-pointer text-left text-xs font-medium text-gray-500 uppercase tracking-wider" onclick="sortTable(0)">CVR Number</th>
                    <th class="px-6 py-3 cursor-pointer text-left text-xs font-medium text-gray-500 uppercase tracking-wider" onclick="sortTable(1)">Total Amount</th>
                    <th class="px-6 py-3 cursor-pointer text-left text-xs font-medium text-gray-500 uppercase tracking-wider" onclick="sortTable(2)">Company Code</th>
                    <th class="px-6 py-3 cursor-pointer text-left text-xs font-medium text-gray-500 uppercase tracking-wider" onclick="sortTable(3)">Supplier Code</th>
                    <th class="px-6 py-3 cursor-pointer text-left text-xs font-medium text-gray-500 uppercase tracking-wider" onclick="sortTable(4)">Request Type</th>
                    <th class="px-6 py-3 cursor-pointer text-left text-xs font-medium text-gray-500 uppercase tracking-wider" onclick="sortTable(5)">Date Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
             
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($data as $approval)
                    @php
                        $voucher = $approval->cashVoucher;
                        $amountDetails = json_decode($voucher->amount_details ?? '[]', true);
                        $totalAmount = is_array($amountDetails) ? array_sum(array_map('floatval', $amountDetails)) : 0;
                    @endphp
                    <tr class="hover:bg-gray-50 transition duration-150 ease-in-out">
                        @if($voucher->cvr_type === 'admin')
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ preg_replace('/\/\d+$/', '',$voucher->cvr_number ?? '-') }}-{{ $voucher->company->company_code ?? '-' }}{{ ucfirst($voucher->expenseTypes->expense_code ?? '-') }}
                            </td>
                        @elseif($voucher->cvr_type === 'rpm')
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ preg_replace('/\/\d+$/', '',$voucher->cvr_number ?? '-') }}-{{ ucfirst($voucher->trucks->truck_name ?? '-' ) }}-{{ $voucher->company->company_code ?? '-' }}{{ ucfirst($voucher->expenseTypes->expense_code ?? '-' ) }}
                            </td>
                        @endif
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-indigo-600 font-semibold">
                            ₱{{ number_format($totalAmount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $voucher->company->company_code ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $voucher->suppliers->supplier_code ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ ucfirst($voucher->expenseTypes->expense_code ?? '-') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($voucher->created_at)->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <a href="{{ route('liquidations.liquidate', $approval->id) }}" 
                                class="inline-block px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                                Liquidate
                            </a>
                            <a href="{{ route('liquidations.edit', $approval->id) }}" 
                                class="inline-block px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Edit
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Sorting Script --}}
<script>
    let sortDirection = {}; // To keep track of sort direction per column

    function sortTable(n) {
        const table = document.getElementById("sortableTable");
        const tbody = table.tBodies[0];
        const rows = Array.from(tbody.rows);

        // Toggle sort direction
        sortDirection[n] = !sortDirection[n];
        const dir = sortDirection[n] ? "asc" : "desc";

        rows.sort((rowA, rowB) => {
            const cellA = rowA.cells[n].innerText.trim();
            const cellB = rowB.cells[n].innerText.trim();

            const a = parseFloat(cellA.replace(/,/g, '')) || cellA.toLowerCase();
            const b = parseFloat(cellB.replace(/,/g, '')) || cellB.toLowerCase();

            if (a < b) return dir === "asc" ? -1 : 1;
            if (a > b) return dir === "asc" ? 1 : -1;
            return 0;
        });

        // Append sorted rows back to tbody
        rows.forEach(row => tbody.appendChild(row));
    }
</script>
@endsection
