@php
    $overviewCards = [
        ['label' => 'Pending Review', 'value' => number_format($overview['total'] ?? 0), 'icon' => 'fa-clipboard-list', 'bg' => 'from-cyan-100 to-sky-100', 'text' => 'text-cyan-700'],
        ['label' => 'Admin', 'value' => number_format($overview['admin'] ?? 0), 'icon' => 'fa-user-shield', 'bg' => 'from-blue-100 to-indigo-100', 'text' => 'text-blue-700'],
        ['label' => 'RPM', 'value' => number_format($overview['rpm'] ?? 0), 'icon' => 'fa-gas-pump', 'bg' => 'from-amber-100 to-yellow-100', 'text' => 'text-amber-700'],
        ['label' => 'Delivery Related', 'value' => number_format($overview['delivery_related'] ?? 0), 'icon' => 'fa-truck-fast', 'bg' => 'from-emerald-100 to-green-100', 'text' => 'text-emerald-700'],
    ];
@endphp

<div class="space-y-5 p-5">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($overviewCards as $card)
            <div class="rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-slate-50 px-4 py-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br {{ $card['bg'] }} {{ $card['text'] }} ring-1 ring-white shadow-sm">
                        <i class="fas {{ $card['icon'] }} text-sm"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="truncate text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">{{ $card['label'] }}</p>
                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ $card['value'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex flex-col gap-3 rounded-[24px] border border-slate-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between">
        {{-- Search row --}}
        <div class="flex flex-1 items-center gap-2 min-w-0">
            <div class="relative flex-1">
                <div class="pointer-events-none absolute left-3 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full bg-cyan-50 text-cyan-600 shadow-sm">
                    <i class="fas fa-magnifying-glass text-sm"></i>
                </div>
                <input
                    type="text"
                    id="liquidations-review-search"
                    value="{{ $search }}"
                    placeholder="Search CVR, company, supplier, expense…"
                    autocomplete="off"
                    class="w-full rounded-2xl border border-slate-300 bg-slate-50 py-3 pl-14 pr-4 text-sm text-slate-700 shadow-sm outline-none transition focus:border-cyan-500 focus:bg-white focus:ring-2 focus:ring-cyan-100"
                >
            </div>
            {{-- Search button --}}
            <button type="button" id="liq-review-search-btn"
                class="inline-flex shrink-0 items-center gap-2 rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-cyan-300 disabled:opacity-60 disabled:cursor-not-allowed">
                <span id="liq-search-icon"><i class="fas fa-magnifying-glass text-xs"></i></span>
                <span id="liq-search-spinner" class="hidden">
                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </span>
                <span id="liq-search-label" class="hidden sm:inline">Search</span>
            </button>
            {{-- Clear button --}}
            @if($search)
            <button type="button" id="liq-review-clear-btn"
                class="inline-flex shrink-0 items-center gap-2 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-600 shadow-sm transition hover:border-rose-300 hover:bg-rose-50 hover:text-rose-600 focus:outline-none">
                <i class="fas fa-times text-xs"></i>
                <span class="hidden sm:inline">Clear</span>
            </button>
            @endif
        </div>

        <div class="flex flex-wrap items-center justify-start gap-3 sm:justify-end">
            <label for="liquidations-review-per-page" class="text-sm font-medium text-slate-600">Show</label>
            <select id="liquidations-review-per-page" class="rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                @foreach ([5, 10, 25, 50] as $size)
                    <option value="{{ $size }}" {{ (int) $perPage === $size ? 'selected' : '' }}>{{ $size }}</option>
                @endforeach
            </select>
            <span class="text-sm text-slate-500">entries</span>
        </div>
    </div>

    {{-- Loading overlay (hidden by default) --}}
    <div id="liq-review-loading" class="hidden">
        <div class="flex flex-col items-center justify-center gap-4 py-16">
            <div class="relative flex h-16 w-16 items-center justify-center">
                <svg class="h-16 w-16 animate-spin text-cyan-200" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <i class="fas fa-magnifying-glass text-lg text-cyan-500"></i>
                </div>
            </div>
            <div class="text-center">
                <p class="text-sm font-semibold text-slate-700">Searching…</p>
                <p class="mt-1 text-xs text-slate-400">Fetching matching records</p>
            </div>
            {{-- Skeleton rows --}}
            <div class="w-full max-w-2xl space-y-3 px-6">
                @for($i = 0; $i < 4; $i++)
                <div class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <div class="h-9 w-9 animate-pulse rounded-xl bg-slate-200"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-3 w-3/4 animate-pulse rounded bg-slate-200"></div>
                        <div class="h-2.5 w-1/2 animate-pulse rounded bg-slate-100"></div>
                    </div>
                    <div class="h-6 w-20 animate-pulse rounded-full bg-slate-200"></div>
                </div>
                @endfor
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-[24px] border border-slate-200">
        <div class="space-y-3 p-4 md:hidden">
            @forelse ($liquidations as $liquidation)
                @php
                    $cashVoucher = optional($liquidation->cashVoucher);
                    $cvrType = $cashVoucher->cvr_type ?? null;
                    $cvrNumber = preg_replace('/\/\d+$/', '', $cashVoucher->cvr_number ?? 'N/A');
                    $details = '';

                    if ($cvrType === 'rpm') {
                        $truckName = optional($cashVoucher->trucks)->truck_name ?? 'N/A';
                        $companyCode = optional($cashVoucher->company)->company_code ?? 'N/A';
                        $expenseCodeVal = optional($cashVoucher->expenseTypes)->expense_code ?? 'N/A';
                        $details = "-$truckName-$companyCode$expenseCodeVal";
                    } elseif ($cvrType === 'admin') {
                        $companyId = optional($cashVoucher->company)->company_code ?? 'N/A';
                        $expenseCodeVal = optional($cashVoucher->expenseTypes)->expense_code ?? 'N/A';
                        $details = "-$companyId$expenseCodeVal";
                    } elseif (in_array($cvrType, ['delivery', 'pullout', 'accessorial', 'freight', 'others'])) {
                        $truckId = optional(optional($liquidation->allocation)->truck)->truck_name ?? 'N/A';
                        $companyId = optional(optional($liquidation->deliveryRequest)->company)->company_code ?? 'N/A';
                        $expenseCodeVal = optional(optional($liquidation->deliveryRequest)->expenseType)->expense_code ?? 'N/A';
                        $details = "-$truckId-$companyId$expenseCodeVal";
                    }

                    $typeStyle = match (strtolower((string) $cvrType)) {
                        'admin' => 'bg-blue-50 text-blue-700 ring-blue-100',
                        'rpm' => 'bg-amber-50 text-amber-700 ring-amber-100',
                        'delivery', 'pullout', 'accessorial', 'freight', 'others' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                        default => 'bg-slate-100 text-slate-700 ring-slate-200',
                    };
                @endphp
                <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">CVR Number</p>
                            <p class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $cvrNumber }}{!! $details !!}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ optional($cashVoucher->company)->company_name ?? optional(optional($cashVoucher->deliveryRequest)->company)->company_name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Voucher Type</p>
                            <span class="mt-1 inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $typeStyle }}">
                                {{ strtoupper($cvrType ?? 'N/A') }}
                            </span>
                        </div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Prepared By</p>
                                <p class="mt-1 text-sm text-slate-700">{{ trim(($liquidation->preparedBy->fname ?? '') . ' ' . ($liquidation->preparedBy->lname ?? '')) ?: 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Noted By</p>
                                <p class="mt-1 text-sm text-slate-700">{{ trim(($liquidation->notedBy->fname ?? '') . ' ' . ($liquidation->notedBy->lname ?? '')) ?: 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Created</p>
                                <p class="mt-1 text-sm text-slate-500">{{ optional($liquidation->created_at)->format('Y-m-d') ?? 'N/A' }}</p>
                            </div>
                            <a href="{{ route('liquidations.review', $liquidation->id) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border px-4 py-2.5 text-sm font-semibold shadow-md transition sm:w-auto" style="background-color:#0891b2;color:#ffffff;border-color:#0e7490;">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white text-cyan-700" style="background-color:#ffffff;color:#0e7490;">
                                    <i class="fas fa-circle-check text-[11px]"></i>
                                </span>
                                <span style="color:#ffffff;">Validate</span>
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-200 px-4 py-10 text-center text-sm text-slate-500">
                    No liquidations found.
                </div>
            @endforelse
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full divide-y divide-slate-200 text-sm text-slate-700">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                    <tr>
                        <th class="px-6 py-4">CVR Number</th>
                        <th class="px-6 py-4">Voucher Type</th>
                        <th class="px-6 py-4">Prepared / Noted By</th>
                        <th class="px-6 py-4">Created</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($liquidations as $liquidation)
                        @php
                            $cashVoucher = optional($liquidation->cashVoucher);
                            $cvrType = $cashVoucher->cvr_type ?? null;
                            $cvrNumber = preg_replace('/\/\d+$/', '', $cashVoucher->cvr_number ?? 'N/A');
                            $details = '';

                            if ($cvrType === 'rpm') {
                                $truckName = optional($cashVoucher->trucks)->truck_name ?? 'N/A';
                                $companyCode = optional($cashVoucher->company)->company_code ?? 'N/A';
                                $expenseCodeVal = optional($cashVoucher->expenseTypes)->expense_code ?? 'N/A';
                                $details = "-$truckName-$companyCode$expenseCodeVal";
                            } elseif ($cvrType === 'admin') {
                                $companyId = optional($cashVoucher->company)->company_code ?? 'N/A';
                                $expenseCodeVal = optional($cashVoucher->expenseTypes)->expense_code ?? 'N/A';
                                $details = "-$companyId$expenseCodeVal";
                            } elseif (in_array($cvrType, ['delivery', 'pullout', 'accessorial', 'freight', 'others'])) {
                                $truckId = optional(optional($liquidation->allocation)->truck)->truck_name ?? 'N/A';
                                $companyId = optional(optional($liquidation->deliveryRequest)->company)->company_code ?? 'N/A';
                                $expenseCodeVal = optional(optional($liquidation->deliveryRequest)->expenseType)->expense_code ?? 'N/A';
                                $details = "-$truckId-$companyId$expenseCodeVal";
                            }

                            $typeStyle = match (strtolower((string) $cvrType)) {
                                'admin' => 'bg-blue-50 text-blue-700 ring-blue-100',
                                'rpm' => 'bg-amber-50 text-amber-700 ring-amber-100',
                                'delivery', 'pullout', 'accessorial', 'freight', 'others' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                                default => 'bg-slate-100 text-slate-700 ring-slate-200',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 align-top">
                                <div class="flex items-start gap-3">
                                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-600">
                                        <i class="fas fa-file-invoice text-sm"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-slate-900">{{ $cvrNumber }}{!! $details !!}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ optional($cashVoucher->company)->company_name ?? optional(optional($cashVoucher->deliveryRequest)->company)->company_name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 align-top">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $typeStyle }}">
                                    {{ strtoupper($cvrType ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 text-sm text-slate-700">
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                                            <i class="fas fa-user-pen text-xs"></i>
                                        </span>
                                        <span>{{ trim(($liquidation->preparedBy->fname ?? '') . ' ' . ($liquidation->preparedBy->lname ?? '')) ?: 'N/A' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-slate-700">
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-violet-50 text-violet-600">
                                            <i class="fas fa-user-check text-xs"></i>
                                        </span>
                                        <span>{{ trim(($liquidation->notedBy->fname ?? '') . ' ' . ($liquidation->notedBy->lname ?? '')) ?: 'N/A' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 align-top text-slate-500">
                                {{ optional($liquidation->created_at)->format('Y-m-d') ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 align-top">
                                <div class="flex justify-end">
                                    <a href="{{ route('liquidations.review', $liquidation->id) }}" class="inline-flex min-w-[140px] items-center justify-center gap-2 rounded-2xl border px-4 py-2.5 text-xs font-semibold shadow-md transition" style="background-color:#0891b2;color:#ffffff;border-color:#0e7490;">
                                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white text-cyan-700" style="background-color:#ffffff;color:#0e7490;">
                                            <i class="fas fa-circle-check text-[11px]"></i>
                                        </span>
                                        <span style="color:#ffffff;">Validate</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                    <i class="fas fa-folder-open text-lg"></i>
                                </div>
                                <p class="mt-3 font-medium text-slate-700">No liquidations found</p>
                                <p class="mt-1 text-sm">Try a different search term or voucher type.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 text-sm text-slate-500 lg:flex-row lg:items-center lg:justify-between">
            <p>
                Showing
                <span class="font-semibold text-slate-700">{{ $liquidations->firstItem() ?? 0 }}</span>
                to
                <span class="font-semibold text-slate-700">{{ $liquidations->lastItem() ?? 0 }}</span>
                of
                <span class="font-semibold text-slate-700">{{ $liquidations->total() }}</span>
                entries
            </p>
            <div class="liquidations-review-pagination">
                {{ $liquidations->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const input    = document.getElementById('liquidations-review-search');
    const btn      = document.getElementById('liq-review-search-btn');
    const clearBtn = document.getElementById('liq-review-clear-btn');
    const table    = input?.closest('[data-fast-table]');
    if (!input || !table) return;

    const iconEl     = document.getElementById('liq-search-icon');
    const spinnerEl  = document.getElementById('liq-search-spinner');
    const labelEl    = document.getElementById('liq-search-label');
    const loadingEl  = document.getElementById('liq-review-loading');

    // Block the global fast-table auto-search on every keystroke
    input.addEventListener('input', function (e) {
        e.stopImmediatePropagation();
    }, true);

    function setLoading(loading) {
        if (loading) {
            // Button: show spinner
            iconEl?.classList.add('hidden');
            spinnerEl?.classList.remove('hidden');
            if (labelEl) labelEl.textContent = 'Searching…';
            btn.disabled = true;

            // Hide table content, show skeleton loader
            const tableContent = table.querySelectorAll(':scope > *:not(#liq-review-loading)');
            tableContent.forEach(el => el.style.display = 'none');
            loadingEl?.classList.remove('hidden');
        } else {
            // Button: restore
            iconEl?.classList.remove('hidden');
            spinnerEl?.classList.add('hidden');
            if (labelEl) labelEl.textContent = 'Search';
            if (btn) btn.disabled = false;
        }
    }

    function doSearch() {
        const url = new URL(table.dataset.endpoint || window.location.href, window.location.origin);
        const val = input.value.trim();
        val ? url.searchParams.set('search', val) : url.searchParams.delete('search');
        url.searchParams.delete('page');

        setLoading(true);

        fetch(url.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(payload => {
            table.innerHTML = payload.html || '';
            window.history.replaceState({}, '', url.toString());
        })
        .catch(() => {
            setLoading(false);
        });
    }

    // Enter key
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); doSearch(); }
    });

    // Search button
    btn?.addEventListener('click', doSearch);

    // Clear button
    clearBtn?.addEventListener('click', function () {
        input.value = '';
        doSearch();
    });
})();
</script>
