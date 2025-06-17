@extends('layouts.app')

@section('content')
@php
    use Illuminate\Support\Str;
    $groupedVouchers = collect($cashVouchers)->groupBy('company_id');
@endphp
<div x-data="{ tab: '{{ $groupedVouchers->keys()->first() }}' }">
    <!-- Tab Headers -->
    <div class="flex border-b mb-4 space-x-4">
        @foreach ($groupedVouchers as $companyId => $vouchers)
            <button 
                class="px-4 py-2 font-semibold text-sm border-b-2"
                :class="{ 'border-blue-500 text-blue-600': tab === '{{ $companyId }}', 'border-transparent text-gray-600': tab !== '{{ $companyId }}' }"
                @click="tab = '{{ $companyId }}'"
            >
                {{ $vouchers->first()->company_code ?? 'Unknown' }}
            </button>
        @endforeach
    </div>

    <!-- Tab Content -->
    @foreach ($groupedVouchers as $companyId => $vouchers)
        <div x-show="tab === '{{ $companyId }}'" class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-sm mb-8">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 border-b text-left text-gray-700 text-sm font-medium">CVR Number</th>
                        <th class="px-4 py-2 border-b text-left text-gray-700 text-sm font-medium">CVR Type</th>
                        <th class="px-4 py-2 border-b text-right text-gray-700 text-sm font-medium">Requested</th>
                        <th class="px-4 py-2 border-b text-right text-gray-700 text-sm font-medium">Approved</th>
                        <th class="px-4 py-2 border-b text-right text-gray-700 text-sm font-medium">Liquidated (Cash)</th>
                        <th class="px-4 py-2 border-b text-right text-gray-700 text-sm font-medium">Liquidated (Card)</th>
                        <th class="px-4 py-2 border-b text-left text-gray-700 text-sm font-medium">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($vouchers->sortBy('cvr_number') as $voucher)
                        <tr class="even:bg-gray-50">
                            <td class="px-4 py-2 border-b text-sm">
                                @if ($voucher->cvr_type === 'admin')
                                    {{ Str::before($voucher->cvr_number, '/') }}-{{ $voucher->company_code }}{{ $voucher->expense_code }}
                                @elseif (in_array($voucher->cvr_type, ['delivery', 'pullout', 'accessorial', 'freight', 'others', 'rpm']))
                                    {{ Str::before($voucher->cvr_number, '/') }}-{{ $voucher->truck_name }}-{{ $voucher->company_code }}{{ $voucher->expense_code }}
                                @else
                                    {{ Str::before($voucher->cvr_number, '/') }}
                                @endif
                            </td>
                            <td class="px-4 py-2 border-b text-sm">{{ $voucher->cvr_type }}</td>
                            <td class="px-4 py-2 border-b text-sm text-right">{{ number_format($voucher->requested_amount, 2) }}</td>
                            <td class="px-4 py-2 border-b text-sm text-right">{{ number_format($voucher->approved_amount, 2) }}</td>
                            <td class="px-4 py-2 border-b text-sm text-right">{{ number_format($voucher->liquidated_amount_cash, 2) }}</td>
                            <td class="px-4 py-2 border-b text-sm text-right">{{ number_format($voucher->liquidated_amount_card, 2) }}</td>
                            <td class="px-4 py-2 border-b text-sm">{{ ucfirst($voucher->overall_status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</div>

@endsection
