@php
    $items = collect($cashVouchers->items());
    $totalAmount = $items->sum(function ($voucher) {
        $details = json_decode($voucher->amount_details, true) ?? [];
        return is_array($details) ? array_sum(array_map('floatval', $details)) : 0;
    });
    $adminCount = $items->where('cvr_type', 'admin')->count();
    $rpmCount = $items->where('cvr_type', 'rpm')->count();
@endphp

<div class="border-b border-slate-200 bg-gradient-to-r from-blue-50 via-white to-cyan-50 px-6 py-5">
    <div class="grid gap-3" style="grid-template-columns: repeat(4, minmax(0, 1fr));">
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                    <i class="fas fa-file-invoice-dollar"></i>
                </span>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Visible Vouchers</div>
                    <div class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($cashVouchers->total()) }}</div>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-100 text-violet-600">
                    <i class="fas fa-building-shield"></i>
                </span>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Admin Type</div>
                    <div class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($adminCount) }}</div>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                    <i class="fas fa-gas-pump"></i>
                </span>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">RPM Type</div>
                    <div class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($rpmCount) }}</div>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-peso-sign"></i>
                </span>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Visible Amount</div>
                    <div class="mt-1 text-lg font-bold text-slate-900">PHP {{ number_format($totalAmount, 2) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="space-y-4 p-5">
    <div class="grid items-center gap-4 xl:ml-auto" style="grid-template-columns: minmax(0, 720px) auto; justify-content: space-between;">
        <label class="relative block w-full max-w-[720px]">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <i class="fas fa-magnifying-glass text-sm"></i>
            </span>
            <input
                id="admin-vouchers-search"
                type="text"
                value="{{ $search ?? '' }}"
                placeholder="Search CVR, type, company, supplier, expense..."
                class="h-14 w-full rounded-2xl border border-slate-300 bg-white pl-11 pr-4 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
            >
        </label>
        <div class="flex items-center justify-end gap-3 whitespace-nowrap">
            <span class="text-sm font-medium text-slate-500">Show</span>
            <select id="admin-vouchers-per-page" class="h-14 rounded-2xl border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                @foreach ([5, 10, 25, 50] as $size)
                    <option value="{{ $size }}" {{ (int) ($perPage ?? 10) === $size ? 'selected' : '' }}>{{ $size }}</option>
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
                        <th class="px-4 py-3">CVR Number</th>
                        <th class="px-4 py-3">Company / Supplier</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">CV Type</th>
                        <th class="px-4 py-3">Voucher Type</th>
                        <th class="px-4 py-3">Expense Type</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                @forelse($cashVouchers as $cashVoucher)
                    @php
                        $details = json_decode($cashVoucher->amount_details, true) ?? [];
                        $sum = is_array($details) ? array_sum(array_map('floatval', $details)) : 0;
                    @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-4">
                            <div class="font-semibold text-slate-900">
                                @if ($cashVoucher->cvr_type === 'admin')
                                    {{ $cashVoucher->cvr_number ?? 'N/A' }}-{{ optional($cashVoucher->company)->company_code ?? 'N/A' }}{{ optional($cashVoucher->expenseTypes)->expense_code ?? 'N/A' }}
                                @elseif ($cashVoucher->cvr_type === 'rpm')
                                    {{ $cashVoucher->cvr_number ?? 'N/A' }}-{{ optional($cashVoucher->trucks)->truck_name ?? 'N/A' }}-{{ optional($cashVoucher->company)->company_code ?? 'N/A' }}{{ optional($cashVoucher->expenseTypes)->expense_code ?? 'N/A' }}
                                @else
                                    {{ $cashVoucher->cvr_number ?? 'N/A' }}
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-medium text-slate-900">{{ optional($cashVoucher->company)->company_code ?? 'N/A' }}</div>
                            <div class="text-xs text-slate-500">{{ optional($cashVoucher->suppliers)->supplier_name ?? 'N/A' }}</div>
                        </td>
                        <td class="px-4 py-4 font-semibold text-slate-900">PHP {{ number_format($sum, 2) }}</td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                <i class="fas fa-layer-group"></i>
                                {{ strtoupper($cashVoucher->cvr_type ?? 'N/A') }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center gap-2 rounded-full border border-violet-200 bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700">
                                <i class="fas fa-file-signature"></i>
                                {{ strtoupper(str_replace('_', ' ', $cashVoucher->voucher_type ?? 'N/A')) }}
                            </span>
                        </td>
                        <td class="px-4 py-4">{{ optional($cashVoucher->expenseTypes)->expense_code ?? 'N/A' }}</td>
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.edit', $cashVoucher->id) }}"
                                   class="inline-flex h-11 items-center gap-2 rounded-2xl border border-blue-200 bg-blue-50 px-4 text-sm font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-100">
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white text-blue-600 shadow-sm">
                                        <i class="fas fa-pen-to-square text-xs"></i>
                                    </span>
                                    Edit
                                </a>
                                <a href="{{ route('adminCV.printPreview', $cashVoucher->id) }}"
                                   class="inline-flex h-11 items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 text-sm font-semibold text-emerald-700 transition hover:border-emerald-300 hover:bg-emerald-100">
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white text-emerald-600 shadow-sm">
                                        <i class="fas fa-eye text-xs"></i>
                                    </span>
                                    View
                                </a>
                                <form action="{{ route('admin.destroy', $cashVoucher->id) }}" method="POST" class="inline-flex">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex h-11 items-center gap-2 rounded-2xl border border-red-200 bg-red-50 px-4 text-sm font-semibold text-red-700 transition hover:border-red-300 hover:bg-red-100">
                                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white text-red-600 shadow-sm">
                                            <i class="fas fa-trash text-xs"></i>
                                        </span>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-sm text-slate-500">No admin cash vouchers available for the current filters.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500 md:flex-row md:items-center md:justify-between">
            <p>Showing {{ $cashVouchers->firstItem() ?? 0 }} to {{ $cashVouchers->lastItem() ?? 0 }} of {{ $cashVouchers->total() }} voucher{{ $cashVouchers->total() === 1 ? '' : 's' }}</p>
            <div class="admin-vouchers-pagination">{{ $cashVouchers->links('pagination::tailwind') }}</div>
        </div>
    </div>
</div>
