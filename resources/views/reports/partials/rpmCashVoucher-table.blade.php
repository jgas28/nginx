@php
    $totalAmount = collect($voucherStatuses->items())->sum(fn ($voucher) => array_sum(json_decode($voucher->amount_details, true) ?? []));
    $approvedAmount = collect($voucherStatuses->items())->sum(fn ($voucher) => $voucher->cvrApprovals->sum('amount'));
    $liquidationCash = collect($voucherStatuses->items())->sum('liquidation_cash');
    $liquidationCard = collect($voucherStatuses->items())->sum('liquidation_card');
@endphp

<div class="border-b border-slate-200 bg-gradient-to-r from-violet-50 via-white to-cyan-50 px-6 py-5">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-100 text-violet-600">
                    <i class="fas fa-file-invoice-dollar"></i>
                </span>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Visible Vouchers</div>
                    <div class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($voucherStatuses->total()) }}</div>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-circle-check"></i>
                </span>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Approved Amount</div>
                    <div class="mt-1 text-lg font-bold text-slate-900">PHP {{ number_format($approvedAmount, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                    <i class="fas fa-money-bills"></i>
                </span>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Liquidation Cash</div>
                    <div class="mt-1 text-lg font-bold text-slate-900">PHP {{ number_format($liquidationCash, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-100 text-sky-600">
                    <i class="fas fa-credit-card"></i>
                </span>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Liquidation Card</div>
                    <div class="mt-1 text-lg font-bold text-slate-900">PHP {{ number_format($liquidationCard, 2) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="space-y-4 p-5">
    <div class="grid grid-cols-1 items-center gap-4 xl:ml-auto xl:grid-cols-[minmax(0,720px)_auto] xl:justify-between">
        <label class="relative block w-full max-w-[720px]">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <i class="fas fa-magnifying-glass text-sm"></i>
            </span>
            <input
                id="rpm-report-search"
                type="text"
                value="{{ $search ?? '' }}"
                placeholder="Search CVR, type, supplier, truck, company..."
                class="h-14 w-full rounded-2xl border border-slate-300 bg-white pl-11 pr-4 text-sm text-slate-700 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-100"
            >
        </label>
        <div class="flex flex-wrap items-center justify-start gap-3 xl:justify-end">
            <a href="{{ route('reports.rpm.export', request()->query()) }}"
               class="inline-flex h-14 items-center gap-2 rounded-2xl bg-emerald-600 px-5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/15">
                    <i class="fas fa-file-excel text-xs"></i>
                </span>
                Download Excel
            </a>
            <span class="text-sm font-medium text-slate-500">Show</span>
            <select id="rpm-report-per-page" class="h-14 rounded-2xl border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-100">
                @foreach ([5, 10, 25, 50] as $size)
                    <option value="{{ $size }}" {{ (int) ($perPage ?? 10) === $size ? 'selected' : '' }}>{{ $size }}</option>
                @endforeach
            </select>
            <span class="text-sm text-slate-500">entries</span>
        </div>
    </div>

    <div class="overflow-hidden rounded-[24px] border border-slate-200">
        <div class="space-y-3 p-4 md:hidden">
            @forelse($voucherStatuses as $voucher)
                <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Voucher ID</p>
                            <p class="mt-1 break-words text-sm font-semibold text-slate-900">
                                {{ preg_replace('/\/\d+/', '', $voucher->cvr_number) }}-{{ optional($voucher->trucks)->truck_name ?? 'N/A' }}-{{ optional($voucher->company)->company_code ?? 'N/A' }}{{ optional($voucher->expenseTypes)->expense_code ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">CVR Type</p>
                                <span class="mt-1 inline-flex items-center gap-2 rounded-full border border-violet-200 bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700">
                                    <i class="fas fa-layer-group"></i>
                                    {{ $voucher->cvrTypes->request_type ?? 'N/A' }}
                                </span>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Supplier</p>
                                <p class="mt-1 text-sm text-slate-700">{{ $voucher->suppliers->supplier_name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Amount</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900">PHP {{ number_format(array_sum(json_decode($voucher->amount_details, true) ?? []), 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Approved Amount</p>
                                <p class="mt-1 text-sm text-slate-700">PHP {{ number_format($voucher->cvrApprovals->sum('amount'), 2) }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Liquidation Cash</p>
                                <p class="mt-1 text-sm text-slate-700">PHP {{ number_format($voucher->liquidation_cash, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Liquidation Card</p>
                                <p class="mt-1 text-sm text-slate-700">PHP {{ number_format($voucher->liquidation_card, 2) }}</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Status</p>
                            <span class="@if($voucher->status_text == 'Rejected') border-red-200 bg-red-50 text-red-700 @endif
                                @if($voucher->status_text == 'For Approval') border-yellow-200 bg-yellow-50 text-yellow-700 @endif
                                @if($voucher->status_text == 'For Liquidation') border-blue-200 bg-blue-50 text-blue-700 @endif
                                @if($voucher->status_text == 'For Validation') border-indigo-200 bg-indigo-50 text-indigo-700 @endif
                                @if($voucher->status_text == 'For Collection') border-emerald-200 bg-emerald-50 text-emerald-700 @endif
                                @if($voucher->status_text == 'Approved') border-emerald-200 bg-emerald-50 text-emerald-700 @endif
                                @if($voucher->status_text == 'Pending') border-slate-200 bg-slate-50 text-slate-700 @endif
                                mt-1 inline-flex rounded-full border px-3 py-1 text-xs font-semibold">
                                {{ $voucher->status_text }}
                            </span>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-200 px-4 py-10 text-center text-sm text-slate-500">
                    No RPM cash vouchers available for the current filters.
                </div>
            @endforelse
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full divide-y divide-slate-200 text-sm text-slate-700">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Voucher ID</th>
                        <th class="px-4 py-3">CVR Type</th>
                        <th class="px-4 py-3">Supplier</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Approved Amount</th>
                        <th class="px-4 py-3">Liquidation Cash</th>
                        <th class="px-4 py-3">Liquidation Card</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($voucherStatuses as $voucher)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-4">
                                <div class="font-semibold text-slate-900">
                                    {{ preg_replace('/\/\d+/', '', $voucher->cvr_number) }}-{{ optional($voucher->trucks)->truck_name ?? 'N/A' }}-{{ optional($voucher->company)->company_code ?? 'N/A' }}{{ optional($voucher->expenseTypes)->expense_code ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center gap-2 rounded-full border border-violet-200 bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700">
                                    <i class="fas fa-layer-group"></i>
                                    {{ $voucher->cvrTypes->request_type ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-4">{{ $voucher->suppliers->supplier_name ?? 'N/A' }}</td>
                            <td class="px-4 py-4 font-semibold text-slate-900">PHP {{ number_format(array_sum(json_decode($voucher->amount_details, true) ?? []), 2) }}</td>
                            <td class="px-4 py-4">PHP {{ number_format($voucher->cvrApprovals->sum('amount'), 2) }}</td>
                            <td class="px-4 py-4">PHP {{ number_format($voucher->liquidation_cash, 2) }}</td>
                            <td class="px-4 py-4">PHP {{ number_format($voucher->liquidation_card, 2) }}</td>
                            <td class="px-4 py-4">
                                <span class="@if($voucher->status_text == 'Rejected') border-red-200 bg-red-50 text-red-700 @endif
                                    @if($voucher->status_text == 'For Approval') border-yellow-200 bg-yellow-50 text-yellow-700 @endif
                                    @if($voucher->status_text == 'For Liquidation') border-blue-200 bg-blue-50 text-blue-700 @endif
                                    @if($voucher->status_text == 'For Validation') border-indigo-200 bg-indigo-50 text-indigo-700 @endif
                                    @if($voucher->status_text == 'For Collection') border-emerald-200 bg-emerald-50 text-emerald-700 @endif
                                    @if($voucher->status_text == 'Approved') border-emerald-200 bg-emerald-50 text-emerald-700 @endif
                                    @if($voucher->status_text == 'Pending') border-slate-200 bg-slate-50 text-slate-700 @endif
                                    inline-flex rounded-full border px-3 py-1 text-xs font-semibold">
                                    {{ $voucher->status_text }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-sm text-slate-500">No RPM cash vouchers available for the current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500 md:flex-row md:items-center md:justify-between">
            <p>Showing {{ $voucherStatuses->firstItem() ?? 0 }} to {{ $voucherStatuses->lastItem() ?? 0 }} of {{ $voucherStatuses->total() }} voucher{{ $voucherStatuses->total() === 1 ? '' : 's' }}</p>
            <div class="rpm-report-pagination">{{ $voucherStatuses->links('pagination::tailwind') }}</div>
        </div>
    </div>
</div>
