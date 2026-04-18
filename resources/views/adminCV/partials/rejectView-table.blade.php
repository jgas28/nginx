@php
    $totalAmount = collect($cashVouchers->items())->sum('amount');
    $adminCount = collect($cashVouchers->items())->where('cvr_type', 'admin')->count();
    $rpmCount = collect($cashVouchers->items())->where('cvr_type', 'rpm')->count();
@endphp

<div class="border-b border-slate-200 bg-gradient-to-r from-rose-50 via-white to-amber-50 px-6 py-5">
    <div class="grid gap-3 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                    <i class="fas fa-file-circle-xmark"></i>
                </span>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Rejected</div>
                    <div class="mt-1 text-2xl font-bold text-slate-900">{{ $cashVouchers->total() }}</div>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                    <i class="fas fa-building"></i>
                </span>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Admin</div>
                    <div class="mt-1 text-2xl font-bold text-slate-900">{{ $adminCount }}</div>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-100 text-violet-600">
                    <i class="fas fa-gas-pump"></i>
                </span>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">RPM</div>
                    <div class="mt-1 text-2xl font-bold text-slate-900">{{ $rpmCount }}</div>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-money-bill-wave"></i>
                </span>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Visible Amount</div>
                    <div class="mt-1 text-xl font-bold text-slate-900">PHP {{ number_format($totalAmount, 2) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="space-y-4 px-6 py-5">
    <form id="admin-reject-multiple-print-form" action="{{ route('adminCV.rejectPrintMultiple') }}" method="POST" target="_blank" class="space-y-4">
        @csrf

        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <button type="submit"
                    id="admin-reject-print-selected-btn"
                    class="hidden inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/15">
                    <i class="fas fa-print text-xs"></i>
                </span>
                Print Selected
            </button>

            <div class="grid grid-cols-1 items-center gap-4 xl:ml-auto xl:grid-cols-[minmax(0,760px)_auto] xl:justify-between">
                <label class="relative block w-full xl:max-w-[760px]">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fas fa-magnifying-glass text-base"></i>
                    </span>
                    <input
                        id="admin-reject-search"
                        type="text"
                        value="{{ $search ?? '' }}"
                        placeholder="Search CVR number, type, remarks, amount..."
                        class="w-full border border-slate-200 bg-white text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                        style="height: 62px; border-radius: 24px; padding-left: 46px; padding-right: 18px; font-size: 16px;"
                    >
                </label>

                <div class="flex flex-wrap items-center justify-start gap-3 xl:justify-end">
                    <span class="text-base font-medium text-slate-500">Show</span>
                    <select
                        id="admin-reject-per-page"
                        class="border border-slate-200 bg-white font-semibold text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                        style="height: 62px; min-width: 92px; border-radius: 24px; padding-left: 18px; padding-right: 18px; font-size: 16px;"
                    >
                        @foreach ([5, 10, 25, 50] as $size)
                            <option value="{{ $size }}" {{ (int) ($perPage ?? 10) === $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                    <span class="text-base font-medium text-slate-500">entries</span>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-[28px] border border-slate-200">
            <div class="space-y-3 p-4 md:hidden">
                @forelse ($cashVouchers as $voucher)
                    <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="space-y-3">
                            <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-600">
                                <input type="checkbox" name="voucher_ids[]" value="{{ $voucher->id }}" class="admin-reject-voucher-checkbox h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                Select voucher
                            </label>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">CVR Number</p>
                                <p class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $voucher->cvr_number }}</p>
                                <p class="mt-1 text-sm text-slate-500">Updated {{ optional($voucher->updated_at)->format('M d, Y h:i A') ?? 'N/A' }}</p>
                            </div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Type</p>
                                    <span class="mt-1 inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-wide {{ $voucher->cvr_type === 'rpm' ? 'border-violet-200 bg-violet-50 text-violet-700' : 'border-blue-200 bg-blue-50 text-blue-700' }}">
                                        <i class="fas {{ $voucher->cvr_type === 'rpm' ? 'fa-gas-pump' : 'fa-building' }}"></i>
                                        {{ strtoupper($voucher->cvr_type ?? 'N/A') }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Amount</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-900">PHP {{ number_format((float) $voucher->amount, 2) }}</p>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Reject Remarks</p>
                                @php $remarks = json_decode($voucher->reject_remarks, true); @endphp
                                @if (is_array($remarks) && count($remarks))
                                    <ul class="mt-2 space-y-2">
                                        @foreach ($remarks as $remark)
                                            <li class="rounded-2xl bg-rose-50 px-3 py-2 text-xs leading-5 text-rose-700 ring-1 ring-rose-100">{{ $remark }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="mt-1 text-sm text-slate-500">{{ $voucher->reject_remarks ?: 'No remarks' }}</p>
                                @endif
                            </div>
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <a href="{{ route('adminCV.editCVR', $voucher->id) }}"
                                   class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-100 sm:w-auto">
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white text-blue-600 ring-1 ring-blue-100">
                                        <i class="fas fa-pen-to-square text-xs"></i>
                                    </span>
                                    <span>Edit</span>
                                </a>
                                <a href="{{ route('adminCV.rejectPrintView', ['id' => $voucher->id]) }}"
                                   target="_blank"
                                   class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:border-emerald-300 hover:bg-emerald-100 sm:w-auto">
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white text-emerald-600 ring-1 ring-emerald-100">
                                        <i class="fas fa-print text-xs"></i>
                                    </span>
                                    <span>Print</span>
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 px-4 py-10 text-center text-sm text-slate-500">
                        No rejected cash vouchers found.
                    </div>
                @endforelse
            </div>

            <div class="hidden overflow-x-auto md:block">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-slate-700">
                        <tr>
                            <th class="px-4 py-4 text-left font-semibold">
                                <input type="checkbox" id="admin-reject-select-all" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <th class="px-4 py-4 text-left font-semibold">CVR Number</th>
                            <th class="px-4 py-4 text-left font-semibold">Type</th>
                            <th class="px-4 py-4 text-left font-semibold">Amount</th>
                            <th class="px-4 py-4 text-left font-semibold">Reject Remarks</th>
                            <th class="px-4 py-4 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($cashVouchers as $voucher)
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-4 py-4 align-top">
                                    <input type="checkbox" name="voucher_ids[]" value="{{ $voucher->id }}" class="admin-reject-voucher-checkbox h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <div class="font-semibold text-slate-900">{{ $voucher->cvr_number }}</div>
                                    <div class="mt-1 text-xs text-slate-500">Updated {{ optional($voucher->updated_at)->format('M d, Y h:i A') ?? 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-wide {{ $voucher->cvr_type === 'rpm' ? 'border-violet-200 bg-violet-50 text-violet-700' : 'border-blue-200 bg-blue-50 text-blue-700' }}">
                                        <i class="fas {{ $voucher->cvr_type === 'rpm' ? 'fa-gas-pump' : 'fa-building' }}"></i>
                                        {{ strtoupper($voucher->cvr_type ?? 'N/A') }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 align-top font-semibold text-slate-900">PHP {{ number_format((float) $voucher->amount, 2) }}</td>
                                <td class="px-4 py-4 align-top">
                                    @php $remarks = json_decode($voucher->reject_remarks, true); @endphp
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
                                        <a href="{{ route('adminCV.editCVR', $voucher->id) }}"
                                           class="inline-flex min-w-[120px] items-center justify-center gap-2 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-100">
                                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white text-blue-600 ring-1 ring-blue-100">
                                                <i class="fas fa-pen-to-square text-xs"></i>
                                            </span>
                                            <span>Edit</span>
                                        </a>
                                        <a href="{{ route('adminCV.rejectPrintView', ['id' => $voucher->id]) }}"
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
                                <td colspan="6" class="px-4 py-16 text-center">
                                    <div class="mx-auto max-w-md">
                                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                            <i class="fas fa-inbox text-xl"></i>
                                        </div>
                                        <h3 class="mt-4 text-lg font-semibold text-slate-900">No rejected cash vouchers found</h3>
                                        <p class="mt-2 text-sm text-slate-500">Try a different search term or wait for new rejected requests to appear.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200 px-4 py-4 text-sm text-slate-500 md:flex-row md:items-center md:justify-between">
                <div>
                    @if ($cashVouchers->total() > 0)
                        Showing {{ $cashVouchers->firstItem() }} to {{ $cashVouchers->lastItem() }} of {{ $cashVouchers->total() }} rejected vouchers
                    @else
                        No rejected vouchers to show
                    @endif
                </div>
                <div class="admin-reject-pagination">
                    {{ $cashVouchers->links() }}
                </div>
            </div>
        </div>
    </form>
</div>
