@php
    $totalAmount = $cashVouchers->sum(function ($voucher) {
        $amounts = json_decode($voucher->amount_details, true);
        return is_array($amounts) ? array_sum(array_map('floatval', $amounts)) : 0;
    });
@endphp

<div class="space-y-5 p-5">
    <div class="grid gap-3" style="grid-template-columns: repeat(4, minmax(0, 1fr));">
        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-100 text-sky-600">
                    <i class="fas fa-file-circle-check"></i>
                </span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Pending</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($cashVouchers->total()) }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                    <i class="fas fa-user-shield"></i>
                </span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Admin</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($cashVouchers->where('cvr_type', 'admin')->count()) }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-100 text-violet-600">
                    <i class="fas fa-gas-pump"></i>
                </span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">RPM</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($cashVouchers->where('cvr_type', 'rpm')->count()) }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-peso-sign"></i>
                </span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Visible Amount</p>
                    <p class="mt-1 text-lg font-bold text-slate-900">PHP {{ number_format($totalAmount, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid items-center gap-4 rounded-[24px] border border-slate-200 bg-white p-4" style="grid-template-columns: minmax(0, 620px) auto; justify-content: space-between;">
        <div class="relative min-w-0 max-w-[620px]">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <i class="fas fa-magnifying-glass text-sm"></i>
            </span>
            <input
                type="text"
                id="admin-cv-approval-search"
                value="{{ $search }}"
                placeholder="Search CVR, company, supplier, truck..."
                class="w-full rounded-2xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100"
            >
        </div>

        <div class="flex items-center justify-end gap-3 whitespace-nowrap">
            <label for="admin-cv-approval-per-page" class="text-sm font-medium text-slate-600">Show</label>
            <select id="admin-cv-approval-per-page" class="rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                @foreach ([5, 10, 25, 50] as $size)
                    <option value="{{ $size }}" {{ (int) $perPage === $size ? 'selected' : '' }}>{{ $size }}</option>
                @endforeach
            </select>
            <span class="text-sm text-slate-500">entries</span>
        </div>
    </div>

    <div class="overflow-hidden rounded-[24px] border border-slate-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm text-slate-700">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                    <tr>
                        <th class="px-4 py-3">CVR / Company</th>
                        <th class="px-4 py-3">Supplier / Expense</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($cashVouchers as $voucher)
                        @php
                            $total = collect(json_decode($voucher->amount_details, true))->sum();
                            $formattedCvr = preg_replace('/\/\d+$/', '', $voucher->cvr_number);
                            $label = $voucher->cvr_type === 'admin'
                                ? $formattedCvr . '-' . ($voucher->company->company_code ?? 'N/A') . ($voucher->expenseTypes->expense_code ?? '')
                                : $formattedCvr . '-' . ($voucher->trucks->truck_name ?? 'N/A') . '-' . ($voucher->company->company_code ?? 'N/A') . ($voucher->expenseTypes->expense_code ?? '');
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-4 align-top">
                                <div class="font-semibold text-slate-900">{{ $label }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $voucher->company->company_name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-4 py-4 align-top">
                                <div class="font-medium text-slate-900">{{ $voucher->suppliers->supplier_name ?? 'N/A' }}</div>
                                <div class="mt-1 text-xs text-slate-500">Expense: {{ $voucher->expenseTypes->expense_code ?? 'N/A' }}</div>
                            </td>
                            <td class="px-4 py-4 align-top">
                                <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">
                                    PHP {{ number_format((float) $total, 2) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 align-top">
                                <span class="inline-flex rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700 ring-1 ring-sky-100">
                                    {{ strtoupper($voucher->cvr_type) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 align-top">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('admin.approvalRequest', $voucher->id) }}" class="inline-flex items-center gap-2 rounded-xl bg-sky-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-sky-700">
                                        <i class="fas fa-circle-check text-[11px]"></i>
                                        Confirm
                                    </a>
                                    <a href="{{ route('admin.edit', $voucher->id) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 transition hover:border-slate-400 hover:text-slate-900">
                                        <i class="fas fa-pen-to-square text-[11px] text-blue-600"></i>
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-sm text-slate-500">
                                No admin or RPM cash vouchers found for the current filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500 md:flex-row md:items-center md:justify-between">
            <p>
                Showing {{ $cashVouchers->firstItem() ?? 0 }} to {{ $cashVouchers->lastItem() ?? 0 }} of {{ $cashVouchers->total() }} voucher{{ $cashVouchers->total() === 1 ? '' : 's' }}
            </p>
            <div class="admin-cv-approval-pagination">
                {{ $cashVouchers->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
</div>
