@php
    $printedCount = $cashVoucherRequests->filter(fn ($voucher) => (string) $voucher->print_status === '1')->count();
    $pendingCount = $cashVoucherRequests->count() - $printedCount;
    $visibleAmount = $cashVoucherRequests->sum(fn ($voucher) => $voucher->cvrApprovals->sum('amount'));
@endphp

<div class="space-y-5 p-5">
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>
    @endif

    <div class="grid gap-3" style="grid-template-columns: repeat(4, minmax(0, 1fr));">
        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4"><div class="flex items-center gap-3"><span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-fuchsia-100 text-fuchsia-600"><i class="fas fa-receipt"></i></span><div><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Visible CVRs</p><p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($cashVoucherRequests->total()) }}</p></div></div></div>
        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4"><div class="flex items-center gap-3"><span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600"><i class="fas fa-print"></i></span><div><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Printed</p><p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($printedCount) }}</p></div></div></div>
        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4"><div class="flex items-center gap-3"><span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-100 text-amber-600"><i class="fas fa-clock"></i></span><div><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Pending Print</p><p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($pendingCount) }}</p></div></div></div>
        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4"><div class="flex items-center gap-3"><span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-100 text-blue-600"><i class="fas fa-peso-sign"></i></span><div><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Approval Amount</p><p class="mt-1 text-lg font-bold text-slate-900">PHP {{ number_format($visibleAmount, 2) }}</p></div></div></div>
    </div>

    <form id="cash-voucher-print-form" method="POST" action="{{ route('cashVoucherRequests.printMultiple') }}" target="_blank" class="space-y-4">
        @csrf
        <div class="flex flex-col gap-4 rounded-[24px] border border-slate-200 bg-white p-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" id="cash-voucher-print-selected" class="hidden inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700"><span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/15"><i class="fas fa-print text-xs"></i></span>Print Selected CVRs</button>
                <label for="cash-voucher-list-per-page" class="text-sm font-medium text-slate-600">Show</label>
                <select id="cash-voucher-list-per-page" class="rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm text-slate-700 shadow-sm focus:border-fuchsia-500 focus:outline-none focus:ring-2 focus:ring-fuchsia-100">@foreach ([5, 10, 25, 50] as $size)<option value="{{ $size }}" {{ (int) $perPage === $size ? 'selected' : '' }}>{{ $size }}</option>@endforeach</select>
                <span class="text-sm text-slate-500">entries</span>
            </div>
            <div class="relative w-full lg:max-w-md">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i class="fas fa-magnifying-glass text-sm"></i></span>
                <input type="text" id="cash-voucher-list-search" value="{{ $search }}" placeholder="Search MTM, company, expense, printed by..." class="w-full rounded-2xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm text-slate-700 shadow-sm focus:border-fuchsia-500 focus:outline-none focus:ring-2 focus:ring-fuchsia-100">
            </div>
        </div>

        <div class="overflow-hidden rounded-[24px] border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm text-slate-700">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                        <tr>
                            <th class="px-4 py-3"><input type="checkbox" id="cash-voucher-select-all" class="h-4 w-4 rounded border-slate-300 text-fuchsia-600 focus:ring-fuchsia-500"></th>
                            <th class="px-4 py-3">MTM / CVR</th>
                            <th class="px-4 py-3">Amount</th>
                            <th class="px-4 py-3">Printed By</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse($cashVoucherRequests as $cashVoucherRequest)
                            @php
                                $matched = $cashVoucherRequest->matched_allocation ?? null;
                                $truckId = optional(optional($matched)->truck)->truck_name ?? 'N/A';
                                $companyId = optional(optional($cashVoucherRequest->deliveryRequest)->company)->company_code ?? 'N/A';
                                $expenseTypeId = optional(optional($cashVoucherRequest->deliveryRequest)->expenseType)->expense_code ?? 'N/A';
                                $formattedCvrNumber = preg_replace('/\/\d+$/', '', $cashVoucherRequest->cvr_number);
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-4 align-top"><input type="checkbox" class="cash-voucher-print-checkbox h-4 w-4 rounded border-slate-300 text-fuchsia-600 focus:ring-fuchsia-500" data-cvr-id="{{ $cashVoucherRequest->id }}" data-cvr-type="{{ $cashVoucherRequest->cvr_type }}" value="{{ $cashVoucherRequest->id }}"></td>
                                <td class="px-4 py-4 align-top"><div class="font-semibold text-slate-900">{{ $cashVoucherRequest->mtm }}</div><div class="mt-1 text-xs text-slate-500">{{ $formattedCvrNumber }}-{{ $truckId }}-{{ $companyId }}{{ $expenseTypeId }}</div></td>
                                <td class="px-4 py-4 align-top"><div class="space-y-1">@forelse($cashVoucherRequest->cvrApprovals as $approval)<div class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">PHP {{ number_format((float) $approval->amount, 2) }}</div>@empty<span class="text-xs text-slate-400">No approvals</span>@endforelse</div></td>
                                <td class="px-4 py-4 align-top"><div class="font-medium text-slate-900">{{ trim(($cashVoucherRequest->print_name->fname ?? '') . ' ' . ($cashVoucherRequest->print_name->lname ?? '')) ?: 'N/A' }}</div><div class="mt-1 text-xs text-slate-500">{{ (string) $cashVoucherRequest->print_status === '1' ? 'Printed' : 'For printing' }}</div></td>
                                <td class="px-4 py-4 align-top"><div class="flex flex-wrap justify-end gap-2"><a href="{{ route('cashVoucherRequests.print', ['id' => $cashVoucherRequest->id, 'cvr_number' => $cashVoucherRequest->dr_id, 'mtm' => $cashVoucherRequest->cvr_type, 'sequence' => $cashVoucherRequest->sequence]) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-fuchsia-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-fuchsia-700"><i class="fas {{ empty($cashVoucherRequest->print_status) || $cashVoucherRequest->print_status === '0' ? 'fa-print' : 'fa-rotate-right' }} text-[11px]"></i>{{ empty($cashVoucherRequest->print_status) || $cashVoucherRequest->print_status === '0' ? 'Print' : 'Reprint' }}</a><a href="{{ route('cashVoucherRequests.printView', ['id' => $cashVoucherRequest->id, 'cvr_number' => $cashVoucherRequest->dr_id, 'mtm' => $cashVoucherRequest->cvr_type, 'sequence' => $cashVoucherRequest->sequence]) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 transition hover:border-slate-400 hover:text-slate-900"><i class="fas fa-eye text-[11px] text-blue-600"></i>View</a><a href="{{ route('cashVoucherRequests.editPrint', ['id' => $cashVoucherRequest->id, 'cvr_number' => $cashVoucherRequest->dr_id, 'mtm' => $cashVoucherRequest->cvr_type, 'sequence' => $cashVoucherRequest->sequence]) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 transition hover:border-slate-400 hover:text-slate-900"><i class="fas fa-pen-to-square text-[11px] text-violet-600"></i>Edit</a></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-12 text-center text-sm text-slate-500">No prepared cash voucher requests found for the current filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500 md:flex-row md:items-center md:justify-between">
                <p>Showing {{ $cashVoucherRequests->firstItem() ?? 0 }} to {{ $cashVoucherRequests->lastItem() ?? 0 }} of {{ $cashVoucherRequests->total() }} voucher{{ $cashVoucherRequests->total() === 1 ? '' : 's' }}</p>
                <div class="cash-voucher-list-pagination">{{ $cashVoucherRequests->links('pagination::tailwind') }}</div>
            </div>
        </div>
    </form>
</div>
