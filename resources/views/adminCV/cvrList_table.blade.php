@php
    $printedCount = $cashVoucherRequests->filter(fn ($request) => (string) optional($request->cashVoucher)->print_status === '1')->count();
    $pendingCount = $cashVoucherRequests->count() - $printedCount;
    $visibleAmount = $cashVoucherRequests->sum(function ($request) {
        $amounts = json_decode(optional($request->cashVoucher)->amount_details, true);
        return is_array($amounts) ? array_sum(array_map('floatval', $amounts)) : 0;
    });
@endphp

<div class="space-y-5 p-5">
    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4"><div class="flex items-center gap-3"><span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-100 text-violet-600"><i class="fas fa-file-lines"></i></span><div><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Visible CVRs</p><p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($cashVoucherRequests->total()) }}</p></div></div></div>
        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4"><div class="flex items-center gap-3"><span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-100 text-sky-600"><i class="fas fa-user-shield"></i></span><div><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Admin</p><p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($cashVoucherRequests->filter(fn ($request) => optional($request->cashVoucher)->cvr_type === 'admin')->count()) }}</p></div></div></div>
        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4"><div class="flex items-center gap-3"><span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-100 text-amber-600"><i class="fas fa-gas-pump"></i></span><div><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">RPM</p><p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($cashVoucherRequests->filter(fn ($request) => optional($request->cashVoucher)->cvr_type === 'rpm')->count()) }}</p></div></div></div>
        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4"><div class="flex items-center gap-3"><span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600"><i class="fas fa-peso-sign"></i></span><div><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Visible Amount</p><p class="mt-1 text-lg font-bold text-slate-900">PHP {{ number_format($visibleAmount, 2) }}</p></div></div></div>
    </div>

    <form id="admin-cvr-print-form" method="POST" action="{{ route('adminCV.printMultiple') }}" target="_blank" class="space-y-4">
        @csrf
        <div class="flex flex-col gap-4 rounded-[24px] border border-slate-200 bg-white p-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" id="admin-cvr-print-selected" class="hidden inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700"><span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/15"><i class="fas fa-print text-xs"></i></span>Print Selected CVRs</button>
                <label for="admin-cvr-list-per-page" class="text-sm font-medium text-slate-600">Show</label>
                <select id="admin-cvr-list-per-page" class="rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm text-slate-700 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-100">@foreach ([5, 10, 25, 50] as $size)<option value="{{ $size }}" {{ (int) $perPage === $size ? 'selected' : '' }}>{{ $size }}</option>@endforeach</select>
                <span class="text-sm text-slate-500">entries</span>
            </div>
            <div class="relative w-full lg:max-w-md">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i class="fas fa-magnifying-glass text-sm"></i></span>
                <input type="text" id="admin-cvr-list-search" value="{{ $search }}" placeholder="Search CVR, company, supplier, truck..." class="w-full rounded-2xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm text-slate-700 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-100">
            </div>
        </div>

        <div class="overflow-hidden rounded-[24px] border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm text-slate-700">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                        <tr>
                            <th class="px-4 py-3"><input type="checkbox" id="admin-cvr-select-all" class="h-4 w-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500"></th>
                            <th class="px-4 py-3">CVR / Type</th>
                            <th class="px-4 py-3">Company / Supplier</th>
                            <th class="px-4 py-3">Amount</th>
                            <th class="px-4 py-3">Printed By</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse($cashVoucherRequests as $cashVoucherRequest)
                            @php
                                $voucher = $cashVoucherRequest->cashVoucher;
                                $amounts = json_decode(optional($voucher)->amount_details, true);
                                $total = is_array($amounts) ? array_sum(array_map('floatval', $amounts)) : 0;
                                $formattedCvr = preg_replace('/\/\d+$/', '', $cashVoucherRequest->cvr_number);
                                $displayNumber = optional($voucher)->cvr_type === 'admin'
                                    ? $formattedCvr . '-' . (optional(optional($voucher)->company)->company_code ?? 'N/A') . (optional(optional($voucher)->expenseTypes)->expense_code ?? '')
                                    : $formattedCvr . '-' . (optional(optional($voucher)->trucks)->truck_name ?? 'N/A') . '-' . (optional(optional($voucher)->company)->company_code ?? 'N/A') . (optional(optional($voucher)->expenseTypes)->expense_code ?? '');
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-4 align-top"><input type="checkbox" class="admin-cvr-print-checkbox h-4 w-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500" data-request-id="{{ $cashVoucherRequest->id }}" data-cash-voucher-id="{{ $voucher->id ?? '' }}" data-cvr-type="{{ $voucher->cvr_type ?? '' }}" value="{{ $cashVoucherRequest->id }}"></td>
                                <td class="px-4 py-4 align-top"><div class="font-semibold text-slate-900">{{ $displayNumber }}</div><div class="mt-1 text-xs text-slate-500">{{ strtoupper($voucher->cvr_type ?? 'N/A') }}</div></td>
                                <td class="px-4 py-4 align-top"><div class="font-medium text-slate-900">{{ optional($voucher->company)->company_name ?? 'N/A' }}</div><div class="mt-1 text-xs text-slate-500">{{ optional($voucher->suppliers)->supplier_name ?? 'N/A' }}</div></td>
                                <td class="px-4 py-4 align-top"><span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">PHP {{ number_format((float) $total, 2) }}</span></td>
                                <td class="px-4 py-4 align-top"><div class="font-medium text-slate-900">{{ trim((optional($voucher->print_name)->fname ?? '') . ' ' . (optional($voucher->print_name)->lname ?? '')) ?: 'N/A' }}</div><div class="mt-1 text-xs text-slate-500">{{ (string) optional($voucher)->print_status === '1' ? 'Printed' : 'For printing' }}</div></td>
                                <td class="px-4 py-4 align-top"><div class="flex flex-wrap justify-end gap-2"><a href="{{ route('adminCV.print', ['id' => $cashVoucherRequest->id, 'cvr_number' => $voucher->id]) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-violet-700"><i class="fas {{ empty($cashVoucherRequest->print_status) || $cashVoucherRequest->print_status === '0' ? 'fa-print' : 'fa-rotate-right' }} text-[11px]"></i>{{ empty($cashVoucherRequest->print_status) || $cashVoucherRequest->print_status === '0' ? 'Print' : 'Reprint' }}</a><a href="{{ route('adminCV.printView', ['id' => $cashVoucherRequest->id, 'cvr_number' => $voucher->id]) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 transition hover:border-slate-400 hover:text-slate-900"><i class="fas fa-eye text-[11px] text-blue-600"></i>View</a><a href="{{ route('adminCV.editPrintView', ['id' => $cashVoucherRequest->id, 'cvr_number' => $voucher->id]) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 transition hover:border-slate-400 hover:text-slate-900"><i class="fas fa-pen-to-square text-[11px] text-violet-600"></i>Edit</a></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-12 text-center text-sm text-slate-500">No admin cash voucher requests found for the current filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500 md:flex-row md:items-center md:justify-between">
                <p>Showing {{ $cashVoucherRequests->firstItem() ?? 0 }} to {{ $cashVoucherRequests->lastItem() ?? 0 }} of {{ $cashVoucherRequests->total() }} voucher{{ $cashVoucherRequests->total() === 1 ? '' : 's' }}</p>
                <div class="admin-cvr-list-pagination">{{ $cashVoucherRequests->links('pagination::tailwind') }}</div>
            </div>
        </div>
    </form>
</div>
