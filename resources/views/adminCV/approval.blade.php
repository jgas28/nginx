@extends('layouts.app')

@section('title', 'ADMIN/RPM Cash Voucher Approval')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <form method="GET" action="" class="flex items-center w-full space-x-4">
            <input type="text" id="search" name="search" value="{{ request('search') }}" class="px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full" placeholder="Search CVR Number...">
        </form>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="table-auto w-full text-sm text-left text-gray-600">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="py-2 px-4 border-b">CVR Number</th>
                    <th class="py-2 px-4 border-b">Company</th>
                    <th class="py-2 px-4 border-b">Amount</th>
                    <th class="py-2 px-4 border-b">Type</th>
                    <th class="py-2 px-4 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cashVouchers as $voucher)
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 border-b">{{ $voucher->cvr_number }}</td>
                        <td class="py-2 px-4 border-b">{{ $voucher->company->company_code ?? 'N/A' }}</td>
                        <td class="py-2 px-4 border-b">
                            @php
                                $totalAmount = collect(json_decode($voucher->amount_details, true))->sum();
                            @endphp
                            {{ number_format($totalAmount, 2) }}
                        </td>
                        <td class="py-2 px-4 border-b">{{ strtoupper($voucher->cvr_type) }}</td>
                        <td class="py-2 px-4 border-b">
                            <a href="{{ route('admin.approvalRequest', $voucher->id) }}" class="btn bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600" title="Confirm Release">
                                Confirm
                            </a>
                            <a href="{{ route('admin.editApproval', $voucher->id) }}" class="btn bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600" title="Edit">
                                Edit
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">No cash vouchers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="mt-4 px-4">
            {{ $cashVouchers->links('pagination::tailwind') }}
        </div>
    </div>
@endsection
