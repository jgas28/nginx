@php
    use Illuminate\Support\Str;
    $formatCurrency = fn ($value) => 'PHP ' . number_format((float) $value, 2);
    $statusBadgeClasses = [
        'Pending Cash Approval'    => 'bg-amber-100 text-amber-800 border border-amber-200',
        'Rejected CVR'             => 'bg-rose-100 text-rose-800 border border-rose-200',
        'Completed'                => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
        'Rejected Liquidation'     => 'bg-red-100 text-red-800 border border-red-200',
        'For Validation'           => 'bg-sky-100 text-sky-800 border border-sky-200',
        'For Collection'           => 'bg-violet-100 text-violet-800 border border-violet-200',
        'For Approval'             => 'bg-indigo-100 text-indigo-800 border border-indigo-200',
        'For Liquidation'          => 'bg-cyan-100 text-cyan-800 border border-cyan-200',
        'Liquidation In Progress'  => 'bg-slate-100 text-slate-800 border border-slate-200',
    ];
@endphp

<section class="overflow-hidden rounded-[30px] border border-white/70 bg-white shadow-[0_24px_70px_rgba(15,23,42,0.08)]">
    <div class="border-b border-slate-200 px-6 py-5">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Status Overview</h2>
                <p class="mt-1 text-sm text-slate-500">Paginated results help the page load faster while keeping your current filters intact.</p>
            </div>
            <div class="text-sm text-slate-500">
                Total liquidated: <span class="font-semibold text-slate-800">{{ $formatCurrency($summary['liquidated'] ?? 0) }}</span>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm text-slate-700">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                <tr>
                    <th class="px-4 py-4 text-left sm:px-6">CVR No.</th>
                    <th class="px-4 py-4 text-left sm:px-6">Type</th>
                    <th class="px-4 py-4 text-left sm:px-6">Request Type</th>
                    <th class="px-4 py-4 text-right sm:px-6">Requested</th>
                    <th class="px-4 py-4 text-right sm:px-6">Approved</th>
                    <th class="px-4 py-4 text-right sm:px-6">Liquidated Cash</th>
                    <th class="px-4 py-4 text-right sm:px-6">Liquidated Card</th>
                    <th class="px-4 py-4 text-left sm:px-6">Status</th>
                    <th class="px-4 py-4 text-left sm:px-6">Created By</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse ($cashVouchers as $voucher)
                    <tr class="transition hover:bg-slate-50/80">
                        <td class="px-4 py-4 font-medium text-slate-900 sm:px-6">
                            @if($voucher->cvr_type === 'admin')
                                {{ Str::before($voucher->cvr_number, '/') }}-{{ $voucher->company_code }}{{ $voucher->expense_code }}
                            @else
                                {{ Str::before($voucher->cvr_number, '/') }}-{{ $voucher->truck_name ?? 'N/A' }}-{{ $voucher->company_code }}{{ $voucher->expense_code }}
                            @endif
                        </td>
                        <td class="px-4 py-4 capitalize sm:px-6">{{ $voucher->cvr_type }}</td>
                        <td class="px-4 py-4 sm:px-6">{{ $voucher->request_code ?: 'N/A' }}</td>
                        <td class="px-4 py-4 text-right tabular-nums sm:px-6">{{ number_format((float) $voucher->requested_amount, 2) }}</td>
                        <td class="px-4 py-4 text-right tabular-nums sm:px-6">{{ number_format((float) $voucher->approved_amount, 2) }}</td>
                        <td class="px-4 py-4 text-right tabular-nums sm:px-6">{{ number_format((float) ($voucher->liquidated_amount_cash ?? 0), 2) }}</td>
                        <td class="px-4 py-4 text-right tabular-nums sm:px-6">{{ number_format((float) ($voucher->liquidated_amount_card ?? 0), 2) }}</td>
                        <td class="px-4 py-4 sm:px-6">
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusBadgeClasses[$voucher->overall_status] ?? 'bg-slate-100 text-slate-800 border border-slate-200' }}">
                                {{ $voucher->overall_status }}
                            </span>
                        </td>
                        <td class="px-4 py-4 sm:px-6">{{ $voucher->created_by ?: 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center text-slate-500 sm:px-6">
                            No records found for the selected filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="overall-pagination border-t border-slate-200 px-4 py-4 sm:px-6">
        {{ $cashVouchers->links('pagination::tailwind') }}
    </div>
</section>
