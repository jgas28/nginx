@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8 bg-white">
        <!-- Filter Form -->
        <form action="{{ route('reports.rpm') }}" method="GET" class="mb-4 flex flex-wrap gap-4" id="filterForm">
            <!-- Date Range Filters -->
            <div class="flex-1 min-w-[200px]">
                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" id="start_date" name="start_date" value="{{ request('start_date') ?: \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}" class="px-4 py-2 border rounded-md text-sm w-full">
            </div>

            <div class="flex-1 min-w-[200px]">
                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" id="end_date" name="end_date" value="{{ request('end_date') ?: \Carbon\Carbon::now()->format('Y-m-d') }}" class="px-4 py-2 border rounded-md text-sm w-full">
            </div>

            <!-- Status Filter -->
            <div class="flex-1 min-w-[200px]">
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="status" name="status" class="px-4 py-2 border rounded-md text-sm w-full">
                    <option value="">All</option>
                    <option value="For Approval" {{ request('status') == 'For Approval' ? 'selected' : '' }}>For Approval</option>
                    <option value="For Liquidation" {{ request('status') == 'For Liquidation' ? 'selected' : '' }}>For Liquidation</option>
                    <option value="For Validation" {{ request('status') == 'For Validation' ? 'selected' : '' }}>For Validation</option>
                    <option value="For Collection" {{ request('status') == 'For Collection' ? 'selected' : '' }}>For Collection</option>
                    <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                    <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <!-- CVR Type Filter -->
            <div class="flex-1 min-w-[200px]">
                <label for="cvr_type" class="block text-sm font-medium text-gray-700">CVR Type</label>
                <select id="cvr_type" name="cvr_type" class="px-4 py-2 border rounded-md text-sm w-full">
                    <option value="">All</option>
                    @foreach ($cvrTypes as $cvrType)
                        <option value="{{ $cvrType->id }}" {{ request('cvr_type') == $cvrType->id ? 'selected' : '' }}>
                            {{ $cvrType->request_type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Supplier Filter -->
            <div class="flex-1 min-w-[200px]">
                <label for="supplier" class="block text-sm font-medium text-gray-700">Supplier</label>
                <select id="supplier" name="supplier" class="px-4 py-2 border rounded-md text-sm w-full">
                    <option value="">All</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->supplier_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Submit Filter Button and Reset Filter Button -->
            <div class="flex space-x-4 items-center">
                <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded-md">Apply Filters</button>
                <!-- Reset Button -->
                <button type="button" id="resetFilters" class="px-6 py-2 bg-gray-400 text-white rounded-md">Reset Filters</button>
            </div>
        </form>

        @if($voucherStatuses->isEmpty())
            <p class="text-gray-500">No cash vouchers available for this period.</p>
        @else
            <table class="min-w-full table-auto border-collapse border border-gray-200">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Voucher ID</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">CVR Type</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Supplier</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Amount</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Approved Amount</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Liquidation Cash</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Liquidation Card</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($voucherStatuses as $voucher)
                        <tr class="border-t border-gray-200">
                            <td class="px-4 py-2 text-sm text-gray-800">
                                {{ preg_replace('/\/\d+/', '', $voucher->cvr_number) }}-{{ $voucher->trucks->truck_name}}-{{ $voucher->company->company_code}}{{ $voucher->expenseTypes->expense_code}}
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-800">{{ $voucher->cvrTypes->request_type ?? '' }}</td>
                            <td class="px-4 py-2 text-sm text-gray-800">{{ $voucher->suppliers->supplier_name ?? '' }}</td>
                            <td class="px-4 py-2 text-sm text-gray-800">{{ number_format(array_sum(json_decode($voucher->amount_details, true) ?? []), 2) }}</td>
                            <td class="px-4 py-2 text-sm text-gray-800">{{ number_format($voucher->cvrApprovals->sum('amount'), 2) }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ number_format($voucher->liquidation_cash, 2) }}</td>
                            <td class="px-4 py-2 text-sm text-gray-800">{{ number_format($voucher->liquidation_card, 2) }}</td>
                            <td class="px-4 py-2">
                                <span class="@if($voucher->status_text == 'Rejected') bg-red-500 text-white @endif
                                    @if($voucher->status_text == 'For Approval') bg-yellow-500 text-white @endif
                                    @if($voucher->status_text == 'For Liquidation') bg-blue-500 text-white @endif
                                    @if($voucher->status_text == 'For Validation') bg-indigo-500 text-white @endif
                                    @if($voucher->status_text == 'For Collection') bg-green-500 text-white @endif
                                    @if($voucher->status_text == 'Approved') bg-green-600 text-white @endif
                                    @if($voucher->status_text == 'Pending') bg-gray-400 text-white @endif
                                    px-3 py-1 text-xs rounded-full">
                                    {{ $voucher->status_text }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- JavaScript to Reset Filters -->
    <script>
        document.getElementById('resetFilters').addEventListener('click', function() {
            // Reset form fields
            document.getElementById('filterForm').reset();
            // Clear query parameters by redirecting to the same route
            window.location.href = '{{ route('reports.rpm') }}';
        });
    </script>
@endsection
