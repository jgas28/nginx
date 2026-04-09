@php
    $regularCount = $deliveryRequests->filter(fn ($request) => strtolower((string) $request->delivery_type) === 'regular')->count();
    $visibleAmount = $deliveryRequests->sum(function ($request) {
        return collect($request->lineItems)->sum(function ($lineItem) {
            return (float) ($lineItem->amount ?? 0);
        });
    });
    $uniqueCompanies = $deliveryRequests->pluck('company.company_code')->filter()->unique()->count();
@endphp

<div class="border-b border-slate-200 bg-gradient-to-r from-amber-50 via-white to-blue-50 px-6 py-5">
    <div class="grid gap-3" style="grid-template-columns: repeat(4, minmax(0, 1fr));">
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                    <i class="fas fa-file-invoice"></i>
                </span>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Visible Requests</div>
                    <div class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($deliveryRequests->total()) }}</div>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                    <i class="fas fa-route"></i>
                </span>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Regular</div>
                    <div class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($regularCount) }}</div>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-100 text-violet-600">
                    <i class="fas fa-building"></i>
                </span>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Companies</div>
                    <div class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($uniqueCompanies) }}</div>
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
                    <div class="mt-1 text-lg font-bold text-slate-900">PHP {{ number_format($visibleAmount, 2) }}</div>
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
                id="cash-voucher-request-search"
                type="text"
                value="{{ $search ?? '' }}"
                placeholder="Search MTM, company, province, site, delivery number..."
                class="h-14 w-full rounded-2xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100"
            >
        </label>
        <div class="flex items-center justify-end gap-3 whitespace-nowrap">
            <span class="text-sm font-medium text-slate-500">Show</span>
            <select id="cash-voucher-request-per-page" class="h-14 rounded-2xl border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100">
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
                        <th class="px-4 py-3">MTM</th>
                        <th class="px-4 py-3">Delivery Number</th>
                        <th class="px-4 py-3">Province</th>
                        <th class="px-4 py-3">Site</th>
                        <th class="px-4 py-3">Company</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($deliveryRequests as $deliveryRequest)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-4 align-top">
                                <div class="font-semibold text-slate-900">{{ $deliveryRequest->mtm }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $deliveryRequest->project_name ?: 'No project name' }}</div>
                            </td>
                            <td class="px-4 py-4 align-top">
                                <div class="flex flex-wrap gap-2">
                                    @foreach($deliveryRequest->lineItems as $lineItem)
                                        <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 ring-1 ring-blue-100">
                                            {{ $lineItem->delivery_number ?: 'N/A' }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-4 align-top">{{ $deliveryRequest->region->province ?? 'N/A' }}</td>
                            <td class="px-4 py-4 align-top">
                                <div class="flex flex-wrap gap-2">
                                    @foreach($deliveryRequest->lineItems as $lineItem)
                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">{{ $lineItem->site_name ?: 'N/A' }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-4 align-top">
                                <span class="inline-flex items-center gap-2 rounded-full border border-violet-200 bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700">
                                    <i class="fas fa-building"></i>
                                    {{ $deliveryRequest->company->company_code ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 align-top">
                                <div class="flex flex-wrap gap-2">
                                    @foreach($deliveryRequest->lineItems as $lineItem)
                                        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">
                                            {{ $lineItem->deliveryStatus->status_name ?? 'N/A' }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-4 align-top">
                                <div class="flex justify-end">
                                    <a href="{{ route('cashVoucherRequests.request', $deliveryRequest) }}"
                                       class="inline-flex min-w-[168px] items-center justify-center gap-2 rounded-2xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-600">
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/15">
                                            <i class="fas fa-plus text-xs"></i>
                                        </span>
                                        Create Cash Voucher
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-sm text-slate-500">No delivery requests are ready for cash voucher creation.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500 md:flex-row md:items-center md:justify-between">
            <p>Showing {{ $deliveryRequests->firstItem() ?? 0 }} to {{ $deliveryRequests->lastItem() ?? 0 }} of {{ $deliveryRequests->total() }} request{{ $deliveryRequests->total() === 1 ? '' : 's' }}</p>
            <div class="cash-voucher-request-pagination">{{ $deliveryRequests->links('pagination::tailwind') }}</div>
        </div>
    </div>
</div>
