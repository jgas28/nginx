@php
    $overview = $overview ?? [
        'visible_requests' => $deliveryRequests->total(),
        'regular' => 0,
        'multi_drop' => 0,
        'multi_pickup' => 0,
    ];
@endphp

<div class="border-b border-slate-200 bg-gradient-to-r from-indigo-50 via-white to-cyan-50 px-4 py-5 sm:px-6">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
                    <i class="fas fa-truck-ramp-box"></i>
                </span>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Visible Requests</div>
                    <div class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($overview['visible_requests']) }}</div>
                    <div class="mt-1 text-[11px] text-slate-400">Current filtered result set</div>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-route"></i>
                </span>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Regular</div>
                    <div class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($overview['regular']) }}</div>
                    <div class="mt-1 text-[11px] text-slate-400">Single-route delivery requests</div>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                    <i class="fas fa-arrow-right-arrow-left"></i>
                </span>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Multi-Drop</div>
                    <div class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($overview['multi_drop']) }}</div>
                    <div class="mt-1 text-[11px] text-slate-400">One trip with several drop points</div>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-100 text-sky-600">
                    <i class="fas fa-warehouse"></i>
                </span>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Multi Pick-Up</div>
                    <div class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($overview['multi_pickup']) }}</div>
                    <div class="mt-1 text-[11px] text-slate-400">Several pick-up points per request</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="space-y-4 p-4 sm:p-5">
    <div class="grid grid-cols-1 items-center gap-4 xl:ml-auto xl:grid-cols-[minmax(0,720px)_auto] xl:justify-between">
        <label class="relative block w-full max-w-[720px]">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <i class="fas fa-magnifying-glass text-sm"></i>
            </span>
            <input
                id="delivery-request-report-search"
                type="text"
                value="{{ $search ?? '' }}"
                placeholder="Search MTM, project, company, customer, area, status..."
                class="h-14 w-full rounded-2xl border border-slate-300 bg-white pl-11 pr-4 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100"
            >
        </label>
        <div class="flex flex-wrap items-center justify-start gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 px-3 py-3 xl:justify-end">
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white text-slate-500 ring-1 ring-slate-200">
                <i class="fas fa-sliders text-xs"></i>
            </span>
            <span class="text-sm font-medium text-slate-500">Show</span>
            <select id="delivery-request-report-per-page" class="h-14 rounded-2xl border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                @foreach ([5, 10, 25, 50] as $size)
                    <option value="{{ $size }}" {{ (int) ($perPage ?? 10) === $size ? 'selected' : '' }}>{{ $size }}</option>
                @endforeach
            </select>
            <span class="text-sm text-slate-500">entries</span>
        </div>
    </div>

    <div class="overflow-hidden rounded-[24px] border border-slate-200">
        <div class="space-y-3 p-4 md:hidden">
            @forelse($deliveryRequests as $request)
                <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
                                <i class="fas fa-boxes-stacked"></i>
                            </span>
                            <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">MTM / Project</p>
                            <p class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $request->mtm ?? 'N/A' }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $request->project_name ?: 'No project name' }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Booking Date</p>
                                <p class="mt-1 text-sm text-slate-700">{{ optional($request->booking_date ? \Carbon\Carbon::parse($request->booking_date) : null)->format('M d, Y') ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Delivery Date</p>
                                <p class="mt-1 text-sm text-slate-700">{{ optional($request->delivery_date ? \Carbon\Carbon::parse($request->delivery_date) : null)->format('M d, Y') ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Type</p>
                                <span class="mt-1 inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                                    <i class="fas fa-layer-group"></i>
                                    {{ $request->delivery_type ?? 'N/A' }}
                                </span>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Area / Status</p>
                                <p class="mt-1 text-sm font-medium text-slate-900">{{ optional($request->area)->area_code ?? 'N/A' }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ optional($request->deliveryStatus)->status_name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Company / Customer</p>
                            <p class="mt-1 text-sm font-medium text-slate-900">{{ optional($request->company)->company_name ?? 'N/A' }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ optional($request->customer)->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Rates</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">Delivery: PHP {{ number_format((float) ($request->delivery_rate ?? 0), 2) }}</p>
                            <p class="mt-1 text-sm text-slate-500">Accessorial: PHP {{ number_format((float) ($request->total_accessorial_rate ?? 0), 2) }}</p>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-200 px-4 py-10 text-center text-sm text-slate-500">
                    No delivery requests available for the current filters.
                </div>
            @endforelse
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full divide-y divide-slate-200 text-sm text-slate-700">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                    <tr>
                        <th class="px-4 py-3">MTM / Project</th>
                        <th class="px-4 py-3">Booking Date</th>
                        <th class="px-4 py-3">Delivery Date</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Company / Customer</th>
                        <th class="px-4 py-3">Area / Status</th>
                        <th class="px-4 py-3">Rates</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($deliveryRequests as $request)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-4 align-top">
                                <div class="font-semibold text-slate-900">{{ $request->mtm ?? 'N/A' }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $request->project_name ?: 'No project name' }}</div>
                            </td>
                            <td class="px-4 py-4 align-top">
                                {{ optional($request->booking_date ? \Carbon\Carbon::parse($request->booking_date) : null)->format('M d, Y') ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-4 align-top">
                                {{ optional($request->delivery_date ? \Carbon\Carbon::parse($request->delivery_date) : null)->format('M d, Y') ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-4 align-top">
                                <span class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                                    <i class="fas fa-layer-group"></i>
                                    {{ $request->delivery_type ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 align-top">
                                <div class="font-medium text-slate-900">{{ optional($request->company)->company_name ?? 'N/A' }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ optional($request->customer)->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-4 py-4 align-top">
                                <div class="font-medium text-slate-900">{{ optional($request->area)->area_code ?? 'N/A' }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ optional($request->deliveryStatus)->status_name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-4 py-4 align-top">
                                <div class="font-semibold text-slate-900">Delivery: PHP {{ number_format((float) ($request->delivery_rate ?? 0), 2) }}</div>
                                <div class="mt-1 text-xs text-slate-500">Accessorial: PHP {{ number_format((float) ($request->total_accessorial_rate ?? 0), 2) }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-sm text-slate-500">No delivery requests available for the current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500 md:flex-row md:items-center md:justify-between">
            <p>Showing {{ $deliveryRequests->firstItem() ?? 0 }} to {{ $deliveryRequests->lastItem() ?? 0 }} of {{ $deliveryRequests->total() }} request{{ $deliveryRequests->total() === 1 ? '' : 's' }}</p>
            <div class="delivery-request-report-pagination">{{ $deliveryRequests->links('pagination::tailwind') }}</div>
        </div>
    </div>
</div>
