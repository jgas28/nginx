@php
    $overviewCards = [
        ['label' => 'Total Requests', 'value' => number_format($overview['total'] ?? 0), 'icon' => 'fa-layer-group', 'bg' => 'from-sky-100 to-blue-100', 'text' => 'text-sky-700'],
        ['label' => 'Regular', 'value' => number_format($overview['regular'] ?? 0), 'icon' => 'fa-route', 'bg' => 'from-emerald-100 to-green-100', 'text' => 'text-emerald-700'],
        ['label' => 'Multi-Drop', 'value' => number_format($overview['multi_drop'] ?? 0), 'icon' => 'fa-arrows-turn-right', 'bg' => 'from-amber-100 to-yellow-100', 'text' => 'text-amber-700'],
        ['label' => 'Multi Pick-Up', 'value' => number_format($overview['multi_pickup'] ?? 0), 'icon' => 'fa-arrows-turn-to-dots', 'bg' => 'from-violet-100 to-fuchsia-100', 'text' => 'text-violet-700'],
    ];
@endphp

<div class="border-b border-slate-200 px-4 py-4 sm:px-6">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($overviewCards as $card)
            <div class="rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-slate-50 px-4 py-3 shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br {{ $card['bg'] }} {{ $card['text'] }} ring-1 ring-white shadow-sm">
                        <i class="fas {{ $card['icon'] }} text-sm"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="truncate text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">{{ $card['label'] }}</p>
                        <p class="mt-0.5 text-base font-bold text-slate-900">{{ $card['value'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="border-b border-slate-200 px-4 py-4 sm:px-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex items-center gap-2 text-sm text-slate-600">
                <span>Show</span>
                <select id="delivery-request-per-page" class="rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    @foreach([5, 10, 25, 50] as $size)
                        <option value="{{ $size }}" {{ (int) ($perPage ?? 10) === $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
                <span>entries</span>
            </div>
            <div class="text-sm text-slate-500">
                {{ $deliveryRequests->total() }} delivery requests found
            </div>
        </div>

        <div class="relative w-full lg:max-w-sm">
            <div class="pointer-events-none absolute left-3 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full bg-blue-50 text-blue-600 shadow-sm">
                <i class="fas fa-search text-sm"></i>
            </div>
            <input
                type="text"
                id="delivery-request-search"
                value="{{ $search ?? '' }}"
                placeholder="Search MTM, project, company, area..."
                class="w-full rounded-xl border border-slate-300 bg-slate-50 py-2.5 pl-14 pr-4 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"
            >
        </div>
    </div>
</div>

<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200 text-sm text-slate-700">
        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
            <tr>
                <th class="px-6 py-4">MTM / Project</th>
                <th class="px-6 py-4">Schedule</th>
                <th class="px-6 py-4">Truck / Area</th>
                <th class="px-6 py-4">Company</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 bg-white">
            @forelse($deliveryRequests as $deliveryRequest)
                @php
                    $deliveryNumbers = $deliveryRequest->lineItems->pluck('delivery_number')->filter()->take(2)->implode(', ');
                    $moreNumbers = max(0, $deliveryRequest->lineItems->pluck('delivery_number')->filter()->count() - 2);
                    $statusName = $deliveryRequest->deliveryStatus->status_name ?? 'N/A';
                    $statusClass = match (strtolower($statusName)) {
                        'delivered' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                        'for pullout', 'requested', 'allocated' => 'bg-amber-50 text-amber-700 ring-amber-100',
                        'in-transit', 'staging' => 'bg-sky-50 text-sky-700 ring-sky-100',
                        'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-100',
                        default => 'bg-slate-100 text-slate-700 ring-slate-200',
                    };
                    $billedInfo   = ($billedDrMap ?? collect())->get($deliveryRequest->id);
                    $soaStatus    = $billedInfo->soa_status ?? null;
                    $soaBadgeCfg  = $soaStatus ? match($soaStatus) {
                        'paid'     => ['dot' => 'bg-emerald-500', 'cls' => 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'icon' => 'fa-circle-check',       'label' => 'Paid'],
                        'approved' => ['dot' => 'bg-blue-500',    'cls' => 'bg-blue-50 text-blue-700 ring-blue-200',         'icon' => 'fa-thumbs-up',          'label' => 'Approved'],
                        'pending'  => ['dot' => 'bg-amber-500',   'cls' => 'bg-amber-50 text-amber-700 ring-amber-200',      'icon' => 'fa-clock',              'label' => 'Pending'],
                        'overdue'  => ['dot' => 'bg-rose-500',    'cls' => 'bg-rose-50 text-rose-700 ring-rose-200',         'icon' => 'fa-triangle-exclamation','label' => 'Overdue'],
                        default    => ['dot' => 'bg-slate-400',   'cls' => 'bg-slate-100 text-slate-600 ring-slate-200',     'icon' => 'fa-file-pen',           'label' => 'Draft'],
                    } : null;
                @endphp
                <tr class="transition hover:bg-slate-50/80">
                    <td class="px-6 py-4">
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-100 to-cyan-100 text-blue-700 ring-1 ring-blue-200 shadow-sm">
                                <i class="fas fa-box text-sm"></i>
                            </span>
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-900">{{ $deliveryRequest->mtm }}</p>
                                <p class="mt-1 truncate text-xs text-slate-500">{{ $deliveryRequest->project_name ?: 'No project name' }}</p>
                                <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                    @if($deliveryNumbers !== '')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-600">
                                            <i class="fas fa-hashtag text-[10px] text-violet-500"></i>
                                            {{ $deliveryNumbers }}{{ $moreNumbers > 0 ? ' +' . $moreNumbers : '' }}
                                        </span>
                                    @endif
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-600">
                                        <i class="fas fa-user-group text-[10px] text-fuchsia-500"></i>
                                        {{ $deliveryRequest->customer->name ?? 'N/A' }}
                                    </span>
                                </div>
                                {{-- SOA Billing Badge --}}
                                @if($billedInfo && $soaBadgeCfg)
                                <div class="mt-2">
                                    <a href="{{ route('billing.showSoa', $billedInfo->soa_id) }}"
                                       class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 transition hover:brightness-95 {{ $soaBadgeCfg['cls'] }}"
                                       title="View SOA: {{ $billedInfo->soa_number }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $soaBadgeCfg['dot'] }}"></span>
                                        <i class="fas {{ $soaBadgeCfg['icon'] }} text-[9px]"></i>
                                        <span>Billed</span>
                                        <span class="font-bold">{{ $billedInfo->soa_number }}</span>
                                        <span class="opacity-70">·</span>
                                        <span>{{ $soaBadgeCfg['label'] }}</span>
                                        <span class="opacity-60">₱{{ number_format($billedInfo->amount ?? 0, 2) }}</span>
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="space-y-2">
                            <div class="inline-flex items-center gap-2 text-sm text-slate-700">
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-amber-50 text-amber-600">
                                    <i class="fas fa-calendar-plus text-xs"></i>
                                </span>
                                <div>
                                    <p class="text-[11px] uppercase tracking-[0.14em] text-slate-400">Booking</p>
                                    <p class="font-semibold">{{ $deliveryRequest->booking_date ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="inline-flex items-center gap-2 text-sm text-slate-700">
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                                    <i class="fas fa-calendar-check text-xs"></i>
                                </span>
                                <div>
                                    <p class="text-[11px] uppercase tracking-[0.14em] text-slate-400">Delivery</p>
                                    <p class="font-semibold">{{ $deliveryRequest->delivery_date ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="space-y-2">
                            <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">
                                <i class="fas fa-truck text-[10px] text-blue-500"></i>
                                {{ $deliveryRequest->truckType->truck_code ?? 'N/A' }}
                            </div>
                            <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">
                                <i class="fas fa-location-dot text-[10px] text-rose-500"></i>
                                {{ $deliveryRequest->area->area_code ?? 'N/A' }} / {{ $deliveryRequest->region->province ?? 'N/A' }}
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">
                            <i class="fas fa-building text-[10px] text-cyan-500"></i>
                            {{ $deliveryRequest->company->company_code ?? 'N/A' }}
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold ring-1 {{ $statusClass }}">
                            <i class="fas fa-signal text-[10px]"></i>
                            {{ $statusName }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap items-center justify-end gap-2">
                            <a href="{{ route('deliveryRequest.edit', $deliveryRequest) }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-blue-700">
                                <i class="fas fa-pen-to-square text-[11px]"></i>
                                Edit
                            </a>
                            @if($deliveryRequest->delivery_type != 'Regular')
                                <a href="{{ route('deliveryRequest.splitView', $deliveryRequest) }}" class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-amber-600">
                                    <i class="fas fa-code-branch text-[11px]"></i>
                                    Split
                                </a>
                            @endif
                            <form action="{{ route('deliveryRequest.destroy', $deliveryRequest) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-rose-700">
                                    <i class="fas fa-trash text-[11px]"></i>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                            <i class="fas fa-folder-open text-lg"></i>
                        </div>
                        <p class="mt-3 font-medium text-slate-700">No delivery requests found</p>
                        <p class="mt-1 text-sm">Try a different filter or search term.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="flex flex-col gap-3 border-t border-slate-200 px-6 py-4 text-sm text-slate-500 lg:flex-row lg:items-center lg:justify-between">
    <p>
        Showing
        <span class="font-semibold text-slate-700">{{ $deliveryRequests->firstItem() ?? 0 }}</span>
        to
        <span class="font-semibold text-slate-700">{{ $deliveryRequests->lastItem() ?? 0 }}</span>
        of
        <span class="font-semibold text-slate-700">{{ $deliveryRequests->total() }}</span>
        entries
    </p>
    <div class="delivery-request-pagination">
        {{ $deliveryRequests->links('pagination::tailwind') }}
    </div>
</div>
