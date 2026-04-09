<div class="space-y-6">
    <div class="grid gap-4 lg:grid-cols-4">
        <div class="rounded-[26px] border border-blue-200 bg-gradient-to-br from-blue-50 to-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-white">
                    <i class="fas fa-layer-group text-lg"></i>
                </span>
                <div>
                    <div class="text-[13px] font-semibold uppercase tracking-[0.16em] text-blue-700">Visible Entries</div>
                    <div class="mt-1 text-3xl font-black text-slate-900">{{ number_format($visibleCount ?? 0) }}</div>
                </div>
            </div>
        </div>

        <div class="rounded-[26px] border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-600 text-white">
                    <i class="fas fa-arrow-trend-up text-lg"></i>
                </span>
                <div>
                    <div class="text-[13px] font-semibold uppercase tracking-[0.16em] text-emerald-700">In Movement</div>
                    <div class="mt-1 text-3xl font-black text-slate-900">{{ number_format($inCount ?? 0) }}</div>
                </div>
            </div>
        </div>

        <div class="rounded-[26px] border border-rose-200 bg-gradient-to-br from-rose-50 to-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-600 text-white">
                    <i class="fas fa-arrow-trend-down text-lg"></i>
                </span>
                <div>
                    <div class="text-[13px] font-semibold uppercase tracking-[0.16em] text-rose-700">Out Movement</div>
                    <div class="mt-1 text-3xl font-black text-slate-900">{{ number_format($outCount ?? 0) }}</div>
                </div>
            </div>
        </div>

        <div class="rounded-[26px] border border-violet-200 bg-gradient-to-br from-violet-50 to-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-600 text-white">
                    <i class="fas fa-wallet text-lg"></i>
                </span>
                <div>
                    <div class="text-[13px] font-semibold uppercase tracking-[0.16em] text-violet-700">Visible Amount</div>
                    <div class="mt-1 text-3xl font-black text-slate-900">PHP {{ number_format($visibleAmount ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach($approvers as $approver)
            @php
                $runningTotal = $runningTotalsByApprover[$approver->id] ?? 0;
                $uncollectedTotal = $uncollectedByApprover[$approver->id] ?? 0;
            @endphp
            <div class="rounded-[26px] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-[13px] font-semibold uppercase tracking-[0.14em] text-slate-500">Source Funds</div>
                        <div class="mt-1 text-lg font-bold text-slate-900">{{ $approver->name }}</div>
                    </div>
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl {{ $runningTotal < 0 ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }}">
                        <i class="fas fa-building-columns text-lg"></i>
                    </span>
                </div>
                <div class="mt-4 text-3xl font-black {{ $runningTotal < 0 ? 'text-rose-600' : 'text-slate-900' }}">
                    PHP {{ number_format($runningTotal, 2) }}
                </div>
                <div class="mt-2 text-sm text-slate-500">
                    Uncollected:
                    <span class="font-semibold text-amber-700">PHP {{ number_format($uncollectedTotal, 2) }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-[0_12px_28px_rgba(15,23,42,0.05)]">
        <div class="flex flex-col gap-4 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                    <i class="fas fa-table text-lg"></i>
                </span>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Running Balance Transactions</h2>
                    <p class="text-sm text-slate-500">Search and paginate without reloading the whole page.</p>
                </div>
            </div>

            <div class="flex w-full items-center gap-4">
                <div class="relative min-w-0 flex-1 max-w-xl">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fas fa-search"></i>
                    </span>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search description, CVR, employee..."
                        form="running-balance-filter-form"
                        class="h-[52px] w-full rounded-2xl border border-slate-200 bg-white pl-11 pr-4 text-[15px] text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                </div>

                <div class="ml-auto flex flex-none items-center justify-end gap-3 text-right">
                    <span class="whitespace-nowrap text-[15px] font-semibold text-slate-700">Show</span>
                    <select
                        name="per_page"
                        form="running-balance-filter-form"
                        class="h-[52px] w-[150px] rounded-2xl border border-slate-200 bg-white px-4 text-[15px] font-medium text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                        @foreach ([5, 10, 25, 50] as $size)
                            <option value="{{ $size }}" {{ (int) request('per_page', 10) === $size ? 'selected' : '' }}>{{ $size }} rows</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left">
                <thead class="bg-slate-50 text-[12px] font-bold uppercase tracking-[0.14em] text-slate-600">
                    <tr>
                        <th class="px-5 py-4">Date</th>
                        <th class="px-5 py-4">Source</th>
                        <th class="px-5 py-4">Type</th>
                        <th class="px-5 py-4">Movement</th>
                        <th class="px-5 py-4">Amount</th>
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
                            <td class="px-5 py-4 text-slate-600">{{ $balance->description ?: 'N/A' }}</td>
                            <td class="px-5 py-4">
                                {{ $employeeName !== '' ? $employeeName : (optional($balance->suppliers)->supplier_name ?? 'N/A') }}
                            </td>
                            <td class="px-5 py-4">{{ $creatorName !== '' ? $creatorName : 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-[15px] text-slate-500">
                                No transactions found for the current filters.
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
