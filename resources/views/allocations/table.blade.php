<div class="border-b border-slate-200 px-4 py-4 sm:px-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex items-center gap-2 text-sm text-slate-600">
                <span>Show</span>
                <select id="allocation-per-page" class="rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    @foreach([5, 10, 25, 50] as $size)
                        <option value="{{ $size }}" {{ (int) ($perPage ?? 10) === $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
                <span>entries</span>
            </div>
            <div class="text-sm text-slate-500">{{ $deliveryRequests->total() }} ready requests</div>
        </div>

        <div class="relative w-full lg:max-w-sm">
            <div class="pointer-events-none absolute left-3 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full bg-blue-50 text-blue-600 shadow-sm">
                <i class="fas fa-search text-sm"></i>
            </div>
            <input
                type="text"
                id="allocation-search"
                value="{{ $search ?? '' }}"
                placeholder="Search MTM, company, area, province..."
                class="w-full rounded-xl border border-slate-300 bg-slate-50 py-2.5 pl-14 pr-4 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"
            >
        </div>
    </div>
</div>

<div id="allocate-action-bar" class="hidden border-b border-slate-200 bg-gradient-to-r from-emerald-50 to-blue-50 px-4 py-3 sm:px-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white text-emerald-600 shadow-sm">
                <i class="fas fa-check-double text-xs"></i>
            </span>
            <span><span id="allocation-selected-count">0</span> delivery request(s) selected</span>
        </div>
        <button id="allocate-selected-button" type="button" class="inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-emerald-600/20 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-emerald-600/30">
            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/20">
                <i class="fas fa-route text-xs"></i>
            </span>
            Allocate Selected
        </button>
    </div>
</div>

<div class="overflow-x-auto">
    <table class="min-w-full text-sm text-left text-slate-700">
        <thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500">
            <tr>
                <th class="px-4 py-3 font-semibold sm:px-6">
                    <input id="select-all-allocation" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                </th>
                <th class="px-4 py-3 font-semibold sm:px-6">MTM</th>
                <th class="px-4 py-3 font-semibold sm:px-6">Delivery Date</th>
                <th class="px-4 py-3 font-semibold sm:px-6">Company</th>
                <th class="px-4 py-3 font-semibold sm:px-6">Truck Type</th>
                <th class="px-4 py-3 font-semibold sm:px-6">Area</th>
                <th class="px-4 py-3 font-semibold sm:px-6">Province</th>
                <th class="px-4 py-3 font-semibold sm:px-6">Amount</th>
                <th class="px-4 py-3 font-semibold sm:px-6">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 bg-white">
            @forelse($deliveryRequests as $deliveryRequest)
                @php
                    $lineStatuses = $deliveryRequest->lineItems
                        ->map(fn ($lineItem) => optional($lineItem->deliveryStatus)->status_name)
                        ->filter()
                        ->unique()
                        ->values();
                    $statusLabel = $lineStatuses->isNotEmpty() ? $lineStatuses->implode(', ') : 'N/A';
                @endphp
                <tr class="hover:bg-slate-50/80">
                    <td class="px-4 py-3.5 sm:px-6">
                        <input type="checkbox" class="allocation-select-item h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" value="{{ $deliveryRequest->id }}">
                    </td>
                    <td class="px-4 py-3.5 sm:px-6">
                        <div class="flex flex-col gap-1">
                            <span class="font-semibold text-slate-900">{{ $deliveryRequest->mtm }}</span>
                            <span class="text-xs text-slate-500">#{{ $deliveryRequest->id }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3.5 sm:px-6">{{ \Carbon\Carbon::parse($deliveryRequest->delivery_date)->format('M d, Y') }}</td>
                    <td class="px-4 py-3.5 sm:px-6">
                        <div class="inline-flex items-center gap-2">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-blue-600">
                                <i class="fas fa-building text-[11px]"></i>
                            </span>
                            <div>
                                <p class="font-semibold text-slate-900">{{ $deliveryRequest->company->company_name ?? 'N/A' }}</p>
                                <p class="text-xs text-slate-500">{{ $deliveryRequest->company->company_code ?? 'No code' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3.5 sm:px-6">{{ $deliveryRequest->truckType->truck_code ?? 'N/A' }}</td>
                    <td class="px-4 py-3.5 sm:px-6">{{ $deliveryRequest->area->area_code ?? 'N/A' }}</td>
                    <td class="px-4 py-3.5 sm:px-6">{{ $deliveryRequest->region->province ?? 'N/A' }}</td>
                    <td class="px-4 py-3.5 sm:px-6">
                        <span class="inline-flex items-center rounded-full bg-gradient-to-r from-emerald-50 to-teal-100 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">
                            PHP {{ number_format((float) $deliveryRequest->delivery_rate, 2) }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 sm:px-6">
                        <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 ring-1 ring-blue-100">
                            {{ $statusLabel }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-4 py-12 text-center text-slate-500 sm:px-6">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                            <i class="fas fa-route text-lg"></i>
                        </div>
                        <p class="mt-3 font-medium text-slate-700">No allocation requests found</p>
                        <p class="mt-1 text-sm">Try a different search term or wait for new ready-for-allocation requests.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="flex flex-col gap-3 border-t border-slate-200 px-4 py-4 text-sm text-slate-500 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
    <p>Showing <span class="font-semibold text-slate-700">{{ $deliveryRequests->firstItem() ?? 0 }}</span> to <span class="font-semibold text-slate-700">{{ $deliveryRequests->lastItem() ?? 0 }}</span> of <span class="font-semibold text-slate-700">{{ $deliveryRequests->total() }}</span> entries</p>
    <div class="allocation-pagination">
        {{ $deliveryRequests->links('pagination::tailwind') }}
    </div>
</div>
