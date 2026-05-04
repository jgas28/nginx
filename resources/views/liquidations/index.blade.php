@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <form action="{{ route('liquidations.index') }}" method="GET" class="flex space-x-4">
            <!-- Requestor Filter -->
            <div class="flex-1">
                <label for="requestor" class="block text-sm font-medium text-gray-700">Requestor</label>
                <select name="requestor" id="requestor" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">All Requestors</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" {{ request('requestor') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->fname }} {{ $employee->lname }}
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
                    <th class="px-6 py-3 cursor-pointer text-left text-xs font-medium text-gray-500 uppercase tracking-wider" onclick="sortTable(1)">Amount</th>
                    <th class="px-6 py-3 cursor-pointer text-left text-xs font-medium text-gray-500 uppercase tracking-wider" onclick="sortTable(2)">Company Code</th>
                    <th class="px-6 py-3 cursor-pointer text-left text-xs font-medium text-gray-500 uppercase tracking-wider" onclick="sortTable(3)">Requestor</th>
                    <th class="px-6 py-3 cursor-pointer text-left text-xs font-medium text-gray-500 uppercase tracking-wider" onclick="sortTable(4)">Date Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($data as $item)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ preg_replace('/\/\d+$/', '',$item->cashVoucher->cvr_number ?? 'N/A') }}-{{ $item->allocation?->truck?->truck_name }}-{{ $item->cashVoucher->deliveryRequest->company->company_code ?? 'N/A' }}{{ $item->cashVoucher->deliveryRequest->expenseType->expense_code ?? '' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($item->amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 max-w-xs break-words">
                            {{ $item->cashVoucher->deliveryRequest->company->company_code ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $item->cashVoucher->employee->fname ?? 'N/A' }} {{ $item->cashVoucher->employee->lname ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                           {{ optional($item->cashVoucher->created_at)->format('Y-m-d') ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 space-x-2">
                            <a href="{{ route('liquidations.liquidate', $item->id) }}" 
                            class="inline-block px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                            Liquidate
                            </a>
                            <a href="{{ route('liquidations.edit', $item->id) }}" 
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
