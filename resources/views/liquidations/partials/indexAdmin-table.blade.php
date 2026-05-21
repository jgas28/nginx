<div class="space-y-3 p-4 md:hidden">
    @forelse ($data as $approval)
        @php
            $voucher = $approval->cashVoucher;
            $amountDetails = json_decode($voucher->amount_details ?? '[]', true);
            $totalAmount = is_array($amountDetails) ? array_sum(array_map('floatval', $amountDetails)) : 0;
            $displayNumber = '-';

            if ($voucher?->cvr_type === 'admin') {
                $displayNumber = preg_replace('/\/\d+$/', '', $voucher->cvr_number ?? '-') . '-' . ($voucher->company->company_code ?? '-') . ucfirst($voucher->expenseTypes->expense_code ?? '-');
            } elseif ($voucher?->cvr_type === 'rpm') {
                $displayNumber = preg_replace('/\/\d+$/', '', $voucher->cvr_number ?? '-') . '-' . ucfirst($voucher->trucks->truck_name ?? '-') . '-' . ($voucher->company->company_code ?? '-') . ucfirst($voucher->expenseTypes->expense_code ?? '-');
            }
        @endphp
        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="space-y-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">CVR Number</p>
                    <p class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $displayNumber }}</p>
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Total Amount</p>
                        <p class="mt-1 text-sm font-semibold text-emerald-600">PHP {{ number_format($totalAmount, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Company Code</p>
                        <span class="mt-1 inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                            <i class="fas fa-building text-[10px] text-blue-500"></i>
                            {{ $voucher->company->company_code ?? '-' }}
                        </span>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Supplier Code</p>
                        <span class="mt-1 inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                            <i class="fas fa-industry text-[10px] text-violet-500"></i>
                            {{ $voucher->suppliers->supplier_code ?? '-' }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Request Type</p>
                        <p class="mt-1 text-sm text-slate-700">{{ ucfirst($voucher->expenseTypes->expense_code ?? '-') }}</p>
                    </div>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Date Created</p>
                        <p class="mt-1 text-sm text-slate-500">{{ \Carbon\Carbon::parse($voucher->created_at)->format('Y-m-d') }}</p>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <a href="{{ route('liquidations.liquidate', $approval->id) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-3.5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 sm:w-auto">
                            <i class="fas fa-file-circle-check text-[11px]"></i>
                            Liquidate
                        </a>
                        <a href="{{ route('liquidations.edit', $approval->id) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-3.5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 sm:w-auto">
                            <i class="fas fa-pen-to-square text-[11px]"></i>
                            Edit
                        </a>
                    </div>
                </div>
            </div>
        </article>
    @empty
        <div class="rounded-2xl border border-dashed border-slate-200 px-4 py-10 text-center text-sm text-slate-500">
            No admin liquidation records found.
        </div>
    @endforelse
</div>

<div class="hidden overflow-x-auto md:block">
    <table class="min-w-full divide-y divide-slate-200 text-sm text-slate-700">
        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
            <tr>
                <th class="px-6 py-4">CVR Number</th>
                <th class="px-6 py-4">Total Amount</th>
                <th class="px-6 py-4">Company Code</th>
                <th class="px-6 py-4">Supplier Code</th>
                <th class="px-6 py-4">Request Type</th>
                <th class="px-6 py-4">Date Created</th>
                <th class="px-6 py-4 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 bg-white">
            @forelse ($data as $approval)
                @php
                    $voucher = $approval->cashVoucher;
                    $amountDetails = json_decode($voucher->amount_details ?? '[]', true);
                    $totalAmount = is_array($amountDetails) ? array_sum(array_map('floatval', $amountDetails)) : 0;
                    $displayNumber = '-';

                    if ($voucher?->cvr_type === 'admin') {
                        $displayNumber = preg_replace('/\/\d+$/', '', $voucher->cvr_number ?? '-') . '-' . ($voucher->company->company_code ?? '-') . ucfirst($voucher->expenseTypes->expense_code ?? '-');
                    } elseif ($voucher?->cvr_type === 'rpm') {
                        $displayNumber = preg_replace('/\/\d+$/', '', $voucher->cvr_number ?? '-') . '-' . ucfirst($voucher->trucks->truck_name ?? '-') . '-' . ($voucher->company->company_code ?? '-') . ucfirst($voucher->expenseTypes->expense_code ?? '-');
                    }
                @endphp
                <tr class="transition hover:bg-slate-50/80">
                    <td class="px-6 py-4 font-semibold text-slate-900">
                        <div class="inline-flex items-center gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                                <i class="fas fa-file-invoice text-sm"></i>
                            </span>
                            <span>{{ $displayNumber }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 font-semibold text-emerald-600">
                        PHP {{ number_format($totalAmount, 2) }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                            <i class="fas fa-building text-[10px] text-blue-500"></i>
                            {{ $voucher->company->company_code ?? '-' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                            <i class="fas fa-industry text-[10px] text-violet-500"></i>
                            {{ $voucher->suppliers->supplier_code ?? '-' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-slate-700">
                        {{ ucfirst($voucher->expenseTypes->expense_code ?? '-') }}
                    </td>
                    <td class="px-6 py-4 text-slate-500">
                        {{ \Carbon\Carbon::parse($voucher->created_at)->format('Y-m-d') }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap items-center justify-end gap-2">
                            <a href="{{ route('liquidations.liquidate', $approval->id) }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                <i class="fas fa-file-circle-check text-[11px]"></i>
                                Liquidate
                            </a>
                            <a href="{{ route('liquidations.edit', $approval->id) }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-blue-700">
                                <i class="fas fa-pen-to-square text-[11px]"></i>
                                Edit
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                            <i class="fas fa-folder-open text-lg"></i>
                        </div>
                        <p class="mt-3 font-medium text-slate-700">No admin liquidation records found</p>
                        <p class="mt-1 text-sm">Try a different supplier filter or search term.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="flex flex-col gap-3 border-t border-slate-200 px-6 py-4 text-sm text-slate-500 lg:flex-row lg:items-center lg:justify-between">
    <p>
        Showing
        <span class="font-semibold text-slate-700">{{ $data->firstItem() ?? 0 }}</span>
        to
        <span class="font-semibold text-slate-700">{{ $data->lastItem() ?? 0 }}</span>
        of
        <span class="font-semibold text-slate-700">{{ $data->total() }}</span>
        entries
    </p>
    <div class="liquidations-admin-pagination">
        {{ $data->links('pagination::tailwind') }}
    </div>
</div>
