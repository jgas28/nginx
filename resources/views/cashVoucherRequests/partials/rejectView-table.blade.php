@php
    $printedCount = collect($cashVouchers->items())->filter(fn ($voucher) => (string) $voucher->print_status === '1')->count();
    $pendingCount = collect($cashVouchers->items())->count() - $printedCount;
    $visibleAmount = collect($cashVouchers->items())->sum('amount');
@endphp

<div class="border-b border-slate-200 bg-gradient-to-r from-rose-50 via-white to-orange-50 px-4 py-5 sm:px-6">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                    <i class="fas fa-receipt"></i>
                </span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Visible CVRs</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($cashVouchers->total()) }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-print"></i>
                </span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Printed</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($printedCount) }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                    <i class="fas fa-clock"></i>
                </span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Pending Print</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($pendingCount) }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                    <i class="fas fa-peso-sign"></i>
                </span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Approval Amount</p>
                    <p class="mt-1 text-lg font-bold text-slate-900">PHP {{ number_format($visibleAmount, 2) }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="space-y-5 p-4 sm:p-5">
    <form id="cash-voucher-reject-batch-form" method="GET" action="{{ route('cashVoucherRequests.rejectPrintViewMultiple') }}" target="_blank" class="space-y-4">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <button type="submit"
                    id="cash-voucher-reject-batch-btn"
                    class="hidden inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-rose-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-rose-700 sm:w-auto">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/15">
                    <i class="fas fa-print text-xs"></i>
                </span>
                Batch Print Selected
            </button>

            <div class="grid gap-4 xl:ml-auto xl:grid-cols-[minmax(0,720px)_auto] xl:items-center xl:justify-between">
                <label class="relative block w-full xl:max-w-[720px]">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fas fa-magnifying-glass text-sm"></i>
                    </span>
                    <input
                        type="text"
                        id="cash-voucher-reject-search"
                        value="{{ $search ?? '' }}"
                        placeholder="Search CVR, MTM, company, expense, remarks..."
                        class="h-14 w-full rounded-2xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm text-slate-700 shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100"
                    >
                </label>
                <div class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center sm:justify-end">
                    <span class="text-sm font-medium text-slate-500">Show</span>
                    <select id="cash-voucher-reject-per-page" class="h-14 rounded-2xl border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100">
                        @foreach ([5, 10, 25, 50] as $size)
                            <option value="{{ $size }}" {{ (int) ($perPage ?? 10) === $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                    <span class="text-sm text-slate-500">entries</span>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-[24px] border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm text-slate-700">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                        <tr>
                            <th class="px-4 py-3">
                                <input type="checkbox" id="cash-voucher-reject-select-all" class="h-4 w-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                            </th>
                            <th class="px-4 py-3">CVR / MTM</th>
                            <th class="px-4 py-3">Amount</th>
                            <th class="px-4 py-3">Reject Remarks</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($cashVouchers as $voucher)
                            @php
                                $formattedCvrNumber = preg_replace('/\/\d+$/', '', $voucher->cvr_number);
                                $truckId = optional(optional($voucher->matched_allocation)->truck)->truck_name ?? 'N/A';
                                $companyId = optional(optional($voucher->deliveryRequest)->company)->company_code ?? 'N/A';
                                $expenseTypeId = optional(optional($voucher->deliveryRequest)->expenseType)->expense_code ?? 'N/A';
                                $remarks = json_decode($voucher->reject_remarks, true);
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-4 align-top">
                                    <input type="checkbox" name="ids[]" value="{{ $voucher->id }}" class="cash-voucher-reject-checkbox h-4 w-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <div class="font-semibold text-slate-900">{{ $voucher->mtm ?: 'N/A' }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $formattedCvrNumber }}-{{ $truckId }}-{{ $companyId }}{{ $expenseTypeId }}</div>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">
                                        PHP {{ number_format((float) $voucher->amount, 2) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    @if (is_array($remarks) && count($remarks))
                                        <ul class="space-y-2">
                                            @foreach ($remarks as $remark)
                                                <li class="rounded-2xl bg-rose-50 px-3 py-2 text-xs leading-5 text-rose-700 ring-1 ring-rose-100">{{ $remark }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-sm text-slate-500">{{ $voucher->reject_remarks ?: 'No remarks' }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <a href="{{ route('cashVoucherRequests.editCVR', $voucher->id) }}"
                                           class="inline-flex min-w-[120px] items-center justify-center gap-2 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-100">
                                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white text-blue-600 ring-1 ring-blue-100">
                                                <i class="fas fa-pen-to-square text-xs"></i>
                                            </span>
                                            <span>Edit</span>
                                        </a>
                                        <a href="{{ route('cashVoucherRequests.rejectPrintView', ['id' => $voucher->id, 'cvr_number' => $voucher->dr_id, 'cvr_type' => $voucher->cvr_type]) }}"
                                           target="_blank"
                                           class="inline-flex min-w-[120px] items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:border-emerald-300 hover:bg-emerald-100">
                                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white text-emerald-600 ring-1 ring-emerald-100">
                                                <i class="fas fa-print text-xs"></i>
                                            </span>
                                            <span>Print</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-sm text-slate-500">No rejected cash vouchers found for the current filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500 md:flex-row md:items-center md:justify-between">
                <p>Showing {{ $cashVouchers->firstItem() ?? 0 }} to {{ $cashVouchers->lastItem() ?? 0 }} of {{ $cashVouchers->total() }} voucher{{ $cashVouchers->total() === 1 ? '' : 's' }}</p>
                <div class="cash-voucher-reject-pagination">{{ $cashVouchers->links('pagination::tailwind') }}</div>
            </div>
        </div>
    </form>
</div>
