@php
    $showExtended = $showExtended ?? false;
    $dashboardTitle = $dashboardTitle ?? 'Owner Dashboard';
    $dashboardSubtitle = $dashboardSubtitle ?? 'Track finances, delivery health, and approver balances in one place.';
    $periodLabel = request('month', now()->format('Y-m'));
    $primaryMetricCards = [
        ['label' => 'Total Profit', 'value' => $totalDeliveryRates + $totalAccessorialRates, 'tone' => 'from-emerald-500/18 via-green-500/10 to-lime-500/10', 'border' => 'border-emerald-200/80', 'icon' => 'fa-chart-line', 'iconBg' => 'from-emerald-100 to-green-100', 'iconText' => 'text-emerald-700', 'valueText' => 'text-emerald-700', 'isMoney' => true],
        ['label' => 'Operational Expenses', 'value' => $totals->operation_total ?? 0, 'tone' => 'from-orange-500/18 via-amber-500/10 to-rose-500/10', 'border' => 'border-orange-200/80', 'icon' => 'fa-gears', 'iconBg' => 'from-orange-100 to-amber-100', 'iconText' => 'text-orange-700', 'valueText' => 'text-orange-700', 'isMoney' => true],
        ['label' => 'Delivered Today', 'value' => $totalDelivered ?? 0, 'tone' => 'from-sky-500/18 via-cyan-500/10 to-blue-500/10', 'border' => 'border-sky-200/80', 'icon' => 'fa-circle-check', 'iconBg' => 'from-sky-100 to-blue-100', 'iconText' => 'text-sky-700', 'valueText' => 'text-sky-700', 'isMoney' => false],
        ['label' => 'CVR Approvals', 'value' => $totalCVRapproval ?? 0, 'tone' => 'from-fuchsia-500/18 via-pink-500/10 to-rose-500/10', 'border' => 'border-fuchsia-200/80', 'icon' => 'fa-file-signature', 'iconBg' => 'from-fuchsia-100 to-pink-100', 'iconText' => 'text-fuchsia-700', 'valueText' => 'text-fuchsia-700', 'isMoney' => false],
    ];
    $secondaryMetricCards = [
        ['label' => 'Admin Expenses', 'value' => $totals->admin_total ?? 0, 'tone' => 'from-blue-500/18 via-cyan-500/10 to-sky-500/10', 'border' => 'border-blue-200/80', 'icon' => 'fa-user-shield', 'iconBg' => 'from-blue-100 to-cyan-100', 'iconText' => 'text-blue-700', 'valueText' => 'text-blue-700', 'isMoney' => true],
        ['label' => 'RPM Expenses', 'value' => $totals->rpm_total ?? 0, 'tone' => 'from-amber-500/18 via-yellow-500/10 to-orange-500/10', 'border' => 'border-amber-200/80', 'icon' => 'fa-gas-pump', 'iconBg' => 'from-amber-100 to-yellow-100', 'iconText' => 'text-amber-700', 'valueText' => 'text-amber-700', 'isMoney' => true],
        ['label' => 'Pending Deliveries', 'value' => $totalPendingDeliveries ?? 0, 'tone' => 'from-rose-500/18 via-red-500/10 to-pink-500/10', 'border' => 'border-rose-200/80', 'icon' => 'fa-hourglass-half', 'iconBg' => 'from-rose-100 to-red-100', 'iconText' => 'text-rose-700', 'valueText' => 'text-rose-700', 'isMoney' => false],
        ['label' => 'Truck Allocated', 'value' => $totalTruckAllocated ?? 0, 'tone' => 'from-violet-500/18 via-indigo-500/10 to-fuchsia-500/10', 'border' => 'border-violet-200/80', 'icon' => 'fa-truck', 'iconBg' => 'from-violet-100 to-indigo-100', 'iconText' => 'text-violet-700', 'valueText' => 'text-violet-700', 'isMoney' => false],
        ['label' => 'Liquidations', 'value' => $totalLiquidation ?? 0, 'tone' => 'from-teal-500/18 via-cyan-500/10 to-sky-500/10', 'border' => 'border-teal-200/80', 'icon' => 'fa-file-invoice-dollar', 'iconBg' => 'from-teal-100 to-cyan-100', 'iconText' => 'text-teal-700', 'valueText' => 'text-teal-700', 'isMoney' => false],
    ];
    $financialSplit = [
        ['label' => 'Profit', 'value' => $totalDeliveryRates + $totalAccessorialRates, 'bar' => 'from-emerald-500 to-green-500', 'text' => 'text-emerald-600'],
        ['label' => 'Admin Expenses', 'value' => $totals->admin_total ?? 0, 'bar' => 'from-blue-500 to-cyan-500', 'text' => 'text-blue-600', 'children' => $totals->admin_breakdown ?? []],
        ['label' => 'RPM Expenses', 'value' => $totals->rpm_total ?? 0, 'bar' => 'from-amber-500 to-yellow-500', 'text' => 'text-amber-600', 'children' => $totals->rpm_breakdown ?? []],
        ['label' => 'Operational Expenses', 'value' => $totals->operation_total ?? 0, 'bar' => 'from-orange-500 to-red-500', 'text' => 'text-orange-600', 'children' => $totals->operation_breakdown ?? []],
    ];
    $chartMax = max(
        $totalDeliveryRates + $totalAccessorialRates,
        $totals->admin_total ?? 0,
        $totals->rpm_total ?? 0,
        $totals->operation_total ?? 0,
        1
    );
    $analyticsSeries = $analyticsSeries ?? [];
    $analyticsMax = max(1, $analyticsMax ?? 1);
    $activityMix = $activityMix ?? [];
    $expenseMix = $expenseMix ?? [];
    $chartWidth = 100;
    $chartHeight = 130;
    $chartPaddingX = 10;
    $chartPaddingY = 18;
    $chartInnerWidth = $chartWidth - ($chartPaddingX * 2);
    $chartInnerHeight = $chartHeight - ($chartPaddingY * 2);
    $chartCount = max(1, count($analyticsSeries) - 1);
    $incomePoints = collect($analyticsSeries)->values()->map(function ($point, $index) use ($chartPaddingX, $chartPaddingY, $chartInnerWidth, $chartInnerHeight, $chartCount, $analyticsMax) {
        $x = $chartPaddingX + ($index / $chartCount) * $chartInnerWidth;
        $y = $chartPaddingY + ($chartInnerHeight - (($point['income'] ?? 0) / $analyticsMax) * $chartInnerHeight);
        return number_format($x, 2, '.', '') . ',' . number_format($y, 2, '.', '');
    })->implode(' ');
    $expensePoints = collect($analyticsSeries)->values()->map(function ($point, $index) use ($chartPaddingX, $chartPaddingY, $chartInnerWidth, $chartInnerHeight, $chartCount, $analyticsMax) {
        $x = $chartPaddingX + ($index / $chartCount) * $chartInnerWidth;
        $y = $chartPaddingY + ($chartInnerHeight - (($point['expenses'] ?? 0) / $analyticsMax) * $chartInnerHeight);
        return number_format($x, 2, '.', '') . ',' . number_format($y, 2, '.', '');
    })->implode(' ');
    $profitPoints = collect($analyticsSeries)->values()->map(function ($point, $index) use ($chartPaddingX, $chartPaddingY, $chartInnerWidth, $chartInnerHeight, $chartCount, $analyticsMax) {
        $value = max(0, $point['profit'] ?? 0);
        $x = $chartPaddingX + ($index / $chartCount) * $chartInnerWidth;
        $y = $chartPaddingY + ($chartInnerHeight - ($value / $analyticsMax) * $chartInnerHeight);
        return number_format($x, 2, '.', '') . ',' . number_format($y, 2, '.', '');
    })->implode(' ');
    $expenseMixMax = max(1, collect($expenseMix)->max('value') ?? 1);
    $expenseMixBreakdownMax = max(1, collect($expenseMix)->flatMap(fn ($item) => $item['children'] ?? [])->max('value') ?? 1);
    $activityMixMax = max(1, collect($activityMix)->max('value') ?? 1);
@endphp

<div class="min-h-screen bg-[radial-gradient(circle_at_top,#dbeafe_0%,#eff6ff_18%,#f8fafc_44%,#e0f2fe_72%,#ecfeff_100%)] py-4">
    <div class="mx-auto max-w-7xl px-4">
        <div class="mb-6 flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-blue-50 to-cyan-50 px-4 py-2 text-sm font-semibold text-blue-700 ring-1 ring-blue-100 shadow-sm">
                    <i class="fas fa-gauge-high"></i>
                    Executive Overview
                </div>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900">{{ $dashboardTitle }}</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-500">{{ $dashboardSubtitle }}</p>
            </div>

            <form method="GET" action="{{ route('dashboard') }}" class="grid gap-2.5 rounded-3xl border border-white/70 bg-white/90 p-3.5 shadow-sm backdrop-blur sm:grid-cols-2 xl:min-w-[620px] xl:grid-cols-5">
                <div class="sm:col-span-2 xl:col-span-1">
                    <label for="month" class="mb-1 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Month</label>
                    <input type="month" id="month" name="month" value="{{ request('month', now()->format('Y-m')) }}" class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                </div>
                <div class="sm:col-span-1 xl:col-span-2">
                    <label for="start_date" class="mb-1 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Start Date</label>
                    <input type="date" id="start_date" name="start_date" value="{{ request('start_date', now()->startOfMonth()->toDateString()) }}" class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                </div>
                <div class="sm:col-span-1 xl:col-span-2">
                    <label for="end_date" class="mb-1 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">End Date</label>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <input type="date" id="end_date" name="end_date" value="{{ request('end_date', now()->toDateString()) }}" class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                        <button type="submit" class="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 hover:shadow-blue-600/30 sm:w-auto">
                            <i class="fas fa-filter text-xs"></i>
                            Apply
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="mb-6 space-y-4">
            @foreach ([$primaryMetricCards, $secondaryMetricCards] as $metricRow)
                @php $metricColumns = count($metricRow) > 4 ? 'xl:grid-cols-5' : 'xl:grid-cols-4'; @endphp
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 {{ $metricColumns }}">
                    @foreach ($metricRow as $metric)
                        <div class="rounded-3xl border {{ $metric['border'] }} bg-gradient-to-br from-white via-white {{ $metric['tone'] }} p-4 shadow-[0_18px_45px_rgba(15,23,42,0.06)] backdrop-blur transition hover:-translate-y-0.5 hover:shadow-[0_20px_45px_rgba(37,99,235,0.10)]">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500 sm:text-xs sm:tracking-[0.16em]">{{ $metric['label'] }}</p>
                                    <p class="mt-2 text-xl font-bold leading-tight {{ $metric['valueText'] }} sm:mt-2.5 sm:text-2xl">
                                        @if ($metric['isMoney'])
                                            PHP {{ number_format($metric['value'], 2) }}
                                        @else
                                            {{ number_format($metric['value']) }}
                                        @endif
                                    </p>
                                    <p class="mt-1 text-[11px] text-slate-400 sm:text-xs">Period {{ $periodLabel }}</p>
                                </div>
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br {{ $metric['iconBg'] }} {{ $metric['iconText'] }} shadow-sm ring-1 ring-white/80 sm:h-12 sm:w-12">
                                    <i class="fas {{ $metric['icon'] }} text-sm sm:text-base"></i>
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        <div class="mb-6 grid gap-4 xl:grid-cols-[minmax(0,1fr),360px]">
            <div class="rounded-3xl border border-white/70 bg-gradient-to-br from-white via-white to-emerald-50/70 p-5 shadow-[0_24px_70px_rgba(15,23,42,0.08)] backdrop-blur">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-100 to-lime-100 text-emerald-700 ring-1 ring-emerald-200 shadow-sm">
                            <i class="fas fa-sitemap"></i>
                        </span>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Activity Mix</h2>
                            <p class="text-sm text-slate-500">Quick visual of the current operational workload.</p>
                        </div>
                    </div>

                <div class="space-y-3">
                    @foreach ($activityMix as $item)
                            @php $width = min(100, (($item['value'] ?? 0) / $activityMixMax) * 100); @endphp
                            <div class="rounded-2xl bg-white/70 px-3 py-3 ring-1 ring-white/80">
                                <div class="mb-1.5 flex items-center justify-between gap-3 text-sm">
                                    <span class="inline-flex items-center gap-2 font-medium text-slate-600">
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500 ring-1 ring-slate-200">
                                            <i class="fas fa-chart-pie text-[11px]"></i>
                                        </span>
                                        {{ $item['label'] }}
                                    </span>
                                    <span class="font-semibold {{ $item['text'] }}">{{ number_format($item['value']) }}</span>
                                </div>
                                <div class="h-3 rounded-full bg-slate-100">
                                    <div class="h-3 rounded-full {{ $item['color'] }}" style="width: {{ $width }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
            </div>
        </div>

        <div class="mb-6">
            <div class="rounded-3xl border border-white/70 bg-gradient-to-br from-white via-white to-amber-50/70 p-5 shadow-[0_24px_70px_rgba(15,23,42,0.08)] backdrop-blur">
                <div class="mb-4 flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-100 to-orange-100 text-amber-700 ring-1 ring-amber-200 shadow-sm">
                        <i class="fas fa-coins"></i>
                    </span>
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Income vs Expense Mix</h2>
                        <p class="text-sm text-slate-500">Selected-period financial weighting from your database records.</p>
                    </div>
                </div>

                <div class="space-y-3">
                    @foreach ($expenseMix as $item)
                        @php
                            $width = min(100, (($item['value'] ?? 0) / $expenseMixMax) * 100);
                            $children = $item['children'] ?? [];
                            $icon = $item['icon'] ?? 'fa-coins';
                        @endphp

                        @if (!empty($children))
                            <details class="group rounded-2xl bg-white/80 px-3 py-3 ring-1 ring-white/80">
                                <summary class="cursor-pointer list-none">
                                    <div class="mb-1.5 flex items-center justify-between gap-3 text-sm">
                                        <span class="inline-flex items-center gap-2 font-medium text-slate-600">
                                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500 ring-1 ring-slate-200">
                                                <i class="fas {{ $icon }} text-[11px]"></i>
                                            </span>
                                            {{ $item['label'] }}
                                        </span>
                                        <span class="flex items-center gap-3">
                                            <span class="font-semibold {{ $item['text'] }}">PHP {{ number_format($item['value'], 2) }}</span>
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-100 text-slate-500 ring-1 ring-slate-200 transition group-open:rotate-180">
                                                <i class="fas fa-chevron-down text-[10px]"></i>
                                            </span>
                                        </span>
                                    </div>
                                    <div class="h-3 rounded-full bg-slate-100">
                                        <div class="h-3 rounded-full {{ $item['color'] }}" style="width: {{ $width }}%"></div>
                                    </div>
                                </summary>

                                <div class="mt-3 space-y-2 border-t border-slate-200/70 pt-3">
                                    @foreach ($children as $child)
                                        @php
                                            $childWidth = min(100, (($child['value'] ?? 0) / $expenseMixBreakdownMax) * 100);
                                            $childIcon = $child['icon'] ?? 'fa-coins';
                                        @endphp
                                        <div class="rounded-2xl bg-slate-50/90 px-3 py-3 ring-1 ring-slate-100">
                                            <div class="mb-1.5 flex items-center justify-between gap-3 text-sm">
                                                <span class="inline-flex items-center gap-2 font-medium text-slate-600">
                                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-500 ring-1 ring-slate-200">
                                                        <i class="fas {{ $childIcon }} text-[11px]"></i>
                                                    </span>
                                                    {{ $child['label'] }}
                                                </span>
                                                <span class="font-semibold {{ $child['text'] }}">PHP {{ number_format($child['value'], 2) }}</span>
                                            </div>
                                            <div class="h-3 rounded-full bg-white">
                                                <div class="h-3 rounded-full {{ $child['color'] }}" style="width: {{ $childWidth }}%"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </details>
                        @else
                            <div class="rounded-2xl bg-white/70 px-3 py-3 ring-1 ring-white/80">
                                <div class="mb-1.5 flex items-center justify-between gap-3 text-sm">
                                    <span class="inline-flex items-center gap-2 font-medium text-slate-600">
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500 ring-1 ring-slate-200">
                                            <i class="fas {{ $icon }} text-[11px]"></i>
                                        </span>
                                        {{ $item['label'] }}
                                    </span>
                                    <span class="font-semibold {{ $item['text'] }}">PHP {{ number_format($item['value'], 2) }}</span>
                                </div>
                                <div class="h-3 rounded-full bg-slate-100">
                                    <div class="h-3 rounded-full {{ $item['color'] }}" style="width: {{ $width }}%"></div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mb-6 grid gap-4 xl:grid-cols-[1.25fr,0.95fr]">
            <div class="rounded-3xl border border-white/70 bg-gradient-to-br from-white via-white to-sky-50/70 p-5 shadow-[0_24px_70px_rgba(15,23,42,0.08)] backdrop-blur">
                <div class="mb-4 flex items-center gap-3">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-100 to-blue-100 text-sky-700 ring-1 ring-sky-200 shadow-sm">
                        <i class="fas fa-chart-column"></i>
                    </span>
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Financial Split</h2>
                        <p class="text-sm text-slate-500">Profit versus expense breakdown for the selected period.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    @foreach ($financialSplit as $bar)
                        @php
                            $width = min(100, ($bar['value'] / $chartMax) * 100);
                            $children = $bar['children'] ?? [];
                        @endphp

                        @if (!empty($children))
                            <details class="group rounded-2xl bg-slate-50/80 px-3 py-3 ring-1 ring-slate-100">
                                <summary class="cursor-pointer list-none">
                                    <div class="mb-1.5 flex items-center justify-between gap-3 text-sm">
                                        <span class="font-medium text-slate-600">{{ $bar['label'] }}</span>
                                        <span class="flex items-center gap-3">
                                            <span class="font-semibold {{ $bar['text'] }}">PHP {{ number_format($bar['value'], 2) }}</span>
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white text-slate-500 ring-1 ring-slate-200 transition group-open:rotate-180">
                                                <i class="fas fa-chevron-down text-[10px]"></i>
                                            </span>
                                        </span>
                                    </div>
                                    <div class="h-3 rounded-full bg-slate-100">
                                        <div class="h-3 rounded-full bg-gradient-to-r {{ $bar['bar'] }}" style="width: {{ $width }}%"></div>
                                    </div>
                                </summary>

                                <div class="mt-3 space-y-2 border-t border-slate-200/70 pt-3">
                                    @foreach ($children as $child)
                                        @php $childWidth = min(100, (($child['value'] ?? 0) / $expenseMixBreakdownMax) * 100); @endphp
                                        <div class="rounded-2xl bg-white px-3 py-3 ring-1 ring-slate-100">
                                            <div class="mb-1.5 flex items-center justify-between gap-3 text-sm">
                                                <span class="inline-flex items-center gap-2 font-medium text-slate-600">
                                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-50 text-slate-500 ring-1 ring-slate-200">
                                                        <i class="fas {{ $child['icon'] ?? 'fa-coins' }} text-[11px]"></i>
                                                    </span>
                                                    {{ $child['label'] }}
                                                </span>
                                                <span class="font-semibold {{ $child['text'] }}">PHP {{ number_format($child['value'], 2) }}</span>
                                            </div>
                                            <div class="h-3 rounded-full bg-slate-100">
                                                <div class="h-3 rounded-full {{ $child['color'] }}" style="width: {{ $childWidth }}%"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </details>
                        @else
                            <div>
                                <div class="mb-1.5 flex items-center justify-between text-sm">
                                    <span class="font-medium text-slate-600">{{ $bar['label'] }}</span>
                                    <span class="font-semibold {{ $bar['text'] }}">PHP {{ number_format($bar['value'], 2) }}</span>
                                </div>
                                <div class="h-3 rounded-full bg-slate-100">
                                    <div class="h-3 rounded-full bg-gradient-to-r {{ $bar['bar'] }}" style="width: {{ $width }}%"></div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="rounded-3xl border border-white/70 bg-gradient-to-br from-white via-white to-fuchsia-50/60 p-5 shadow-[0_24px_70px_rgba(15,23,42,0.08)] backdrop-blur">
                <div class="mb-4 flex items-center gap-3">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-gradient-to-br from-fuchsia-100 to-violet-100 text-fuchsia-700 ring-1 ring-fuchsia-200 shadow-sm">
                        <i class="fas fa-wallet"></i>
                    </span>
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Approver Snapshot</h2>
                        <p class="text-sm text-slate-500">Top running totals and uncollected balances.</p>
                    </div>
                </div>

                <div class="space-y-2.5">
                    @forelse ($approvers as $approver)
                        <div class="rounded-2xl border border-slate-200 bg-gradient-to-r from-white via-slate-50/90 to-violet-50/60 p-4 transition hover:border-blue-200 hover:bg-white hover:shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-100 to-fuchsia-100 text-violet-700 ring-1 ring-violet-200 shadow-sm">
                                        <i class="fas fa-user-shield text-sm"></i>
                                    </span>
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $approver->name }}</p>
                                        <p class="mt-1 text-xs uppercase tracking-[0.16em] text-slate-400">Approver</p>
                                    </div>
                                </div>
                                <span class="inline-flex min-h-[2.5rem] min-w-[2.5rem] items-center justify-center rounded-2xl bg-gradient-to-br from-amber-100 to-orange-100 px-3 text-sm font-bold text-orange-700 ring-1 ring-orange-200 shadow-sm">
                                    {{ $loop->iteration }}
                                </span>
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-3">
                                <div class="rounded-2xl bg-emerald-50/80 px-3 py-2.5 ring-1 ring-emerald-100">
                                    <p class="text-[11px] uppercase tracking-[0.14em] text-emerald-500">Running</p>
                                    <p class="mt-1 text-sm font-semibold text-emerald-600">PHP {{ number_format($runningTotalsByApprover[$approver->id] ?? 0, 2) }}</p>
                                </div>
                                <div class="rounded-2xl bg-rose-50/80 px-3 py-2.5 ring-1 ring-rose-100">
                                    <p class="text-[11px] uppercase tracking-[0.14em] text-rose-500">Uncollected</p>
                                    <p class="mt-1 text-sm font-semibold text-rose-600">PHP {{ number_format($uncollectedByApprover[$approver->id] ?? 0, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center text-slate-500">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white text-slate-400 ring-1 ring-slate-200">
                                <i class="fas fa-chart-line text-lg"></i>
                            </div>
                            <p class="mt-3 font-medium text-slate-700">No approver data available</p>
                            <p class="mt-1 text-sm">Approver balances will appear here once records exist.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
