<div class="border-b border-slate-200 px-6 py-4">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex items-center gap-2 text-sm text-slate-600">
                <span>Show</span>
                <select id="liquidations-per-page" class="rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    @foreach([5, 10, 25, 50] as $size)
                        <option value="{{ $size }}" {{ (int) ($perPage ?? 10) === $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
                <span>entries</span>
            </div>
            <div class="text-sm text-slate-500">
                {{ $data->total() }} liquidation requests found
            </div>
        </div>

        <div class="relative w-full lg:max-w-sm">
            <div class="pointer-events-none absolute left-3 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full bg-blue-50 text-blue-600 shadow-sm">
                <i class="fas fa-search text-sm"></i>
            </div>
            <input
                type="text"
                id="liquidations-search"
                value="{{ $search ?? '' }}"
                placeholder="Search CVR, company, requestor, expense..."
                class="w-full rounded-xl border border-slate-300 bg-slate-50 py-2.5 pl-14 pr-4 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"
            >
        </div>
    </div>
</div>

<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200 text-sm text-slate-700">
        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
            <tr>
                <th class="px-6 py-4">CVR Number</th>
                <th class="px-6 py-4">Amount</th>
                <th class="px-6 py-4">Company</th>
                <th class="px-6 py-4">Requestor</th>
                <th class="px-6 py-4">Date Created</th>
                <th class="px-6 py-4 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 bg-white">
            @forelse ($data as $item)
                @php
                    $voucher = $item->cashVoucher;
                    $companyCode = $voucher->deliveryRequest->company->company_code ?? 'N/A';
                    $expenseCode = $voucher->deliveryRequest->expenseType->expense_code ?? '';
                    $truckName = $item->allocation?->truck?->truck_name ?? 'N/A';
                    $displayNumber = preg_replace('/\/\d+$/', '', $voucher->cvr_number ?? 'N/A') . '-' . $truckName . '-' . $companyCode . $expenseCode;
                    $requestorName = trim(($voucher->employee->fname ?? 'N/A') . ' ' . ($voucher->employee->lname ?? ''));
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
                        PHP {{ number_format((float) ($item->amount ?? 0), 2) }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                            <i class="fas fa-building text-[10px] text-blue-500"></i>
                            {{ $companyCode }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-slate-700">
                        <div class="inline-flex items-center gap-2">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                                <i class="fas fa-user text-xs"></i>
                            </span>
                            <span>{{ trim($requestorName) !== '' ? $requestorName : 'N/A' }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-slate-500">
                        {{ optional($voucher->created_at)->format('Y-m-d') ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap items-center justify-end gap-2">
                            <a href="{{ route('liquidations.liquidate', $item->id) }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                <i class="fas fa-file-circle-check text-[11px]"></i>
                                Liquidate
                            </a>
                            <a href="{{ route('liquidations.edit', $item->id) }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-blue-700">
                                <i class="fas fa-pen-to-square text-[11px]"></i>
                                Edit
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                            <i class="fas fa-folder-open text-lg"></i>
                        </div>
                        <p class="mt-3 font-medium text-slate-700">No liquidation requests found</p>
                        <p class="mt-1 text-sm">Try a different requestor filter or search term.</p>
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
    <div class="liquidations-pagination">
        {{ $data->links('pagination::tailwind') }}
    </div>
</div>
