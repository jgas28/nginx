@php
    $runningTotal = $runningTotalsByApprover[$approverId] ?? 0;
    $uncollectedTotal = $uncollectedByApprover[$approverId] ?? 0;
@endphp

<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-[26px] border border-blue-200 bg-gradient-to-br from-blue-50 to-white p-5 shadow-sm">
            <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-600 text-white sm:h-12 sm:w-12">
                    <i class="fas fa-layer-group text-base sm:text-lg"></i>
                </span>
                <div class="min-w-0">
                    <div class="text-[12px] font-semibold uppercase tracking-[0.14em] text-blue-700 sm:text-[13px] sm:tracking-[0.16em]">Visible Entries</div>
                    <div class="mt-1 break-words text-[2rem] font-black leading-none text-slate-900 sm:text-3xl">{{ number_format($visibleCount ?? 0) }}</div>
                </div>
            </div>
        </div>

        <div class="rounded-[26px] border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm">
            <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-600 text-white sm:h-12 sm:w-12">
                    <i class="fas fa-wallet text-base sm:text-lg"></i>
                </span>
                <div class="min-w-0">
                    <div class="text-[12px] font-semibold uppercase tracking-[0.14em] text-emerald-700 sm:text-[13px] sm:tracking-[0.16em]">Running Balance</div>
                    <div class="mt-1 break-words text-[1.75rem] font-black leading-tight {{ $runningTotal < 0 ? 'text-rose-600' : 'text-slate-900' }} sm:text-3xl">
                        PHP {{ number_format($runningTotal, 2) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-[26px] border border-amber-200 bg-gradient-to-br from-amber-50 to-white p-5 shadow-sm">
            <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-500 text-white sm:h-12 sm:w-12">
                    <i class="fas fa-triangle-exclamation text-base sm:text-lg"></i>
                </span>
                <div class="min-w-0">
                    <div class="text-[12px] font-semibold uppercase tracking-[0.14em] text-amber-700 sm:text-[13px] sm:tracking-[0.16em]">Uncollected</div>
                    <div class="mt-1 break-words text-[1.75rem] font-black leading-tight text-slate-900 sm:text-3xl">PHP {{ number_format($uncollectedTotal, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="rounded-[26px] border border-violet-200 bg-gradient-to-br from-violet-50 to-white p-5 shadow-sm">
            <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-violet-600 text-white sm:h-12 sm:w-12">
                    <i class="fas fa-scale-balanced text-base sm:text-lg"></i>
                </span>
                <div class="min-w-0">
                    <div class="text-[12px] font-semibold uppercase tracking-[0.14em] text-violet-700 sm:text-[13px] sm:tracking-[0.16em]">Visible Amount</div>
                    <div class="mt-1 break-words text-[1.75rem] font-black leading-tight text-slate-900 sm:text-3xl">PHP {{ number_format($visibleAmount ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-[0_12px_28px_rgba(15,23,42,0.05)]">
        <div class="flex flex-col gap-4 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-orange-50 text-orange-600">
                    <i class="fas fa-table text-lg"></i>
                </span>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">{{ $locationLabel }} Transactions</h2>
                    <p class="text-sm text-slate-500">Search and paginate without reloading the full page.</p>
                </div>
            </div>

            <div class="flex w-full flex-col gap-4 lg:flex-row lg:items-center">
                <div class="relative min-w-0 w-full flex-1 max-w-xl">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fas fa-search"></i>
                    </span>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search description, CVR, employee, or supplier..."
                        form="running-balance-filter-form"
                        class="h-[52px] w-full rounded-2xl border border-slate-200 bg-white pl-11 pr-4 text-[15px] text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                </div>

                <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center sm:justify-end lg:w-auto">
                    <span class="whitespace-nowrap text-[15px] font-semibold text-slate-700">Show</span>
                    <select
                        name="per_page"
                        form="running-balance-filter-form"
                        class="h-[52px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-[15px] font-medium text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100 sm:w-[150px]"
                    >
                        @foreach ([5, 10, 25, 50] as $size)
                            <option value="{{ $size }}" {{ (int) request('per_page', 10) === $size ? 'selected' : '' }}>{{ $size }} rows</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="space-y-4 p-4 md:hidden">
            @forelse($balances as $balance)
                @php
                    $employeeName = trim((optional($balance->employee)->fname ?? '') . ' ' . (optional($balance->employee)->lname ?? ''));
                    $creatorName = trim((optional($balance->creator)->fname ?? '') . ' ' . (optional($balance->creator)->lname ?? ''));
                @endphp
                <article class="rounded-[24px] border border-slate-200 bg-slate-50/70 p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-slate-900">{{ optional($balance->created_at)->format('M d, Y') }}</div>
                            <div class="mt-1 text-xs uppercase tracking-[0.12em] text-slate-500">
                                @switch($balance->type)
                                    @case(1) Top-up @break
                                    @case(2) Collected @break
                                    @case(3) Refund @break
                                    @case(4) Uncollected Funds @break
                                    @case(5) Salary Deduction @break
                                    @case(6) Liquidated Amount @break
                                    @case(7) Transfer @break
                                    @case(8) Release Approved Amount @break
                                    @case(10) Transfer @break
                                    @case(11) Adjustment @break
                                    @case(12) Adjustment for Uncollected @break
                                    @default Reimbursement
                                @endswitch
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold {{ $balance->adjustment_type === 'Out' ? 'bg-rose-50 text-rose-700' : ($balance->adjustment_type === 'In' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700') }}">
                            {{ $balance->adjustment_type ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="mt-4 text-xl font-black {{ $balance->amount < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                        PHP {{ number_format($balance->amount, 2) }}
                    </div>
                    <dl class="mt-4 space-y-3 text-sm text-slate-600">
                        <div>
                            <dt class="font-semibold text-slate-700">Source</dt>
                            <dd>{{ optional($balance->approver)->name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-slate-700">CVR Number</dt>
                            <dd>{{ $balance->cvr_number ?: 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-slate-700">Description</dt>
                            <dd>{{ $balance->description ?: 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-slate-700">Employee / Supplier</dt>
                            <dd>{{ $employeeName !== '' ? $employeeName : (optional($balance->suppliers)->supplier_name ?? 'N/A') }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-slate-700">Created By</dt>
                            <dd>{{ $creatorName !== '' ? $creatorName : 'N/A' }}</dd>
                        </div>
                    </dl>
                </article>
            @empty
                <div class="rounded-[24px] border border-slate-200 bg-slate-50 px-4 py-10 text-center text-[15px] text-slate-500">
                    No transactions found for the current {{ strtolower($locationLabel) }} filters.
                </div>
            @endforelse
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full text-left">
                <thead class="bg-slate-50 text-[12px] font-bold uppercase tracking-[0.14em] text-slate-600">
                    <tr>
                        <th class="px-5 py-4">Date</th>
                        <th class="px-5 py-4">Source</th>
                        <th class="px-5 py-4">Type</th>
                        <th class="px-5 py-4">Movement</th>
                        <th class="px-5 py-4">Amount</th>
                        <th class="px-5 py-4">CVR Number</th>
                        <th class="px-5 py-4">Description</th>
                        <th class="px-5 py-4">Employee / Supplier</th>
                        <th class="px-5 py-4">Created By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white text-[15px] text-slate-700">
                    @forelse($balances as $balance)
                        @php
                            $employeeName = trim((optional($balance->employee)->fname ?? '') . ' ' . (optional($balance->employee)->lname ?? ''));
                            $creatorName = trim((optional($balance->creator)->fname ?? '') . ' ' . (optional($balance->creator)->lname ?? ''));
                        @endphp
                        <tr class="hover:bg-slate-50/90">
                            <td class="px-5 py-4 font-medium text-slate-800">{{ optional($balance->created_at)->format('M d, Y') }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-sm font-semibold text-blue-700">
                                    <i class="fas fa-building-columns text-xs"></i>
                                    {{ optional($balance->approver)->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                @switch($balance->type)
                                    @case(1) Top-up @break
                                    @case(2) Collected @break
                                    @case(3) Refund @break
                                    @case(4) Uncollected Funds @break
                                    @case(5) Salary Deduction @break
                                    @case(6) Liquidated Amount @break
                                    @case(7) Transfer @break
                                    @case(8) Release Approved Amount @break
                                    @case(10) Transfer @break
                                    @case(11) Adjustment @break
                                    @case(12) Adjustment for Uncollected @break
                                    @default Reimbursement
                                @endswitch
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold {{ $balance->adjustment_type === 'Out' ? 'bg-rose-50 text-rose-700' : ($balance->adjustment_type === 'In' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700') }}">
                                    <i class="fas {{ $balance->adjustment_type === 'Out' ? 'fa-arrow-trend-down' : ($balance->adjustment_type === 'In' ? 'fa-arrow-trend-up' : 'fa-wave-square') }} text-xs"></i>
                                    {{ $balance->adjustment_type ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 font-bold {{ $balance->amount < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                PHP {{ number_format($balance->amount, 2) }}
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $balance->cvr_number ?: 'N/A' }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $balance->description ?: 'N/A' }}</td>
                            <td class="px-5 py-4">
                                {{ $employeeName !== '' ? $employeeName : (optional($balance->suppliers)->supplier_name ?? 'N/A') }}
                            </td>
                            <td class="px-5 py-4">{{ $creatorName !== '' ? $creatorName : 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-12 text-center text-[15px] text-slate-500">
                                No transactions found for the current Davao filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($balances, 'links'))
            <div class="border-t border-slate-200 px-5 py-4">
                {{ $balances->links() }}
            </div>
        @endif
    </div>
</div>
