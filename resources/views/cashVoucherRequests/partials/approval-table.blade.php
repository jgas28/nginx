@php
    $totalAmount = $deliveryRequests->sum(fn ($voucher) => (float) $voucher->amount);
@endphp

<div class="space-y-5 p-4 sm:p-5">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                    <i class="fas fa-list-check"></i>
                </span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Queued</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($deliveryRequests->total()) }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-truck-fast"></i>
                </span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Delivery</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($deliveryRequests->where('cvr_type', 'delivery')->count()) }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-100 text-violet-600">
                    <i class="fas fa-arrow-right-arrow-left"></i>
                </span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Pullout</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($deliveryRequests->where('cvr_type', 'pullout')->count()) }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                    <i class="fas fa-peso-sign"></i>
                </span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Visible Amount</p>
                    <p class="mt-1 text-lg font-bold text-slate-900">PHP {{ number_format($totalAmount, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-4 rounded-[24px] border border-slate-200 bg-white p-4 lg:grid-cols-[minmax(0,720px)_auto] lg:items-center lg:justify-between">
        <div class="relative min-w-0 lg:max-w-[720px]">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <i class="fas fa-magnifying-glass text-sm"></i>
            </span>
            <input
                type="text"
                id="cash-voucher-approval-search"
                value="{{ $search }}"
                placeholder="Search MTM, company, type, expense..."
                class="w-full rounded-2xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100"
            >
        </div>

        <div class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center sm:justify-end sm:gap-3">
            <label for="cash-voucher-approval-per-page" class="text-sm font-medium text-slate-600">Show</label>
            <select id="cash-voucher-approval-per-page" class="rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100">
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
                        <th class="px-4 py-3">MTM / CVR</th>
                        <th class="px-4 py-3">Company / Expense</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Request Type</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($deliveryRequests as $deliveryRequest)
                        @php
                            $allocation = $deliveryRequest->matched_allocation ?? null;
                            $truckName = $allocation->truck->truck_name ?? 'N/A';
                            $companyCode = $deliveryRequest->deliveryRequest->company->company_code ?? 'N/A';
                            $expenseTypeCode = $deliveryRequest->deliveryRequest->expenseType->expense_code ?? 'N/A';
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-4 align-top">
                                <div class="font-semibold text-slate-900">{{ $deliveryRequest->mtm ?: 'N/A' }}</div>
                                <div class="mt-1 text-xs text-slate-500">
                                    {{ preg_replace('/\/\d+$/', '', $deliveryRequest->cvr_number) }}-{{ $truckName }}-{{ $companyCode }}{{ $expenseTypeCode }}
                                </div>
                            </td>
                            <td class="px-4 py-4 align-top">
                                <div class="font-medium text-slate-900">{{ $deliveryRequest->deliveryRequest->company->company_name ?? 'N/A' }}</div>
                                <div class="mt-1 text-xs text-slate-500">Expense: {{ $expenseTypeCode }}</div>
                            </td>
                            <td class="px-4 py-4 align-top">
                                <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">
                                    PHP {{ number_format((float) $deliveryRequest->amount, 2) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 align-top">
                                <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-100">
                                    {{ $deliveryRequest->cvrTypes->request_type ?? strtoupper($deliveryRequest->cvr_type) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 align-top">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('cashVoucherRequests.approvalRequest', $deliveryRequest->id) }}" class="inline-flex items-center gap-2 rounded-2xl bg-amber-500 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-amber-600">
                                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/15">
                                            <i class="fas fa-eye text-[11px]"></i>
                                        </span>
                                        View
                                    </a>
                                    <a href="{{ route('cashVoucherRequests.editView', ['id' => $deliveryRequest->id]) }}" class="inline-flex min-w-[104px] items-center justify-center gap-2 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-xs font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-100 hover:text-blue-800">
                                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white text-blue-600 ring-1 ring-blue-100">
                                            <i class="fas fa-pen-to-square text-[11px]"></i>
                                        </span>
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-sm text-slate-500">
                                No cash voucher approvals found for the current filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500 md:flex-row md:items-center md:justify-between">
            <p>
                Showing {{ $deliveryRequests->firstItem() ?? 0 }} to {{ $deliveryRequests->lastItem() ?? 0 }} of {{ $deliveryRequests->total() }} voucher{{ $deliveryRequests->total() === 1 ? '' : 's' }}
            </p>
            <div class="cash-voucher-approval-pagination">
                {{ $deliveryRequests->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
</div>
