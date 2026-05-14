@php
    $showExtended      = $showExtended ?? false;
    $dashboardTitle    = $dashboardTitle ?? 'Owner Dashboard';
    $dashboardSubtitle = $dashboardSubtitle ?? 'Track finances, delivery health, and approver balances in one place.';
    $periodLabel       = request('month', now()->format('Y-m'));
    $analyticsSeries   = $analyticsSeries ?? [];
    $analyticsMax      = max(1, $analyticsMax ?? 1);
    $activityMix       = $activityMix ?? [];
    $expenseMix        = $expenseMix ?? [];

    $income       = ($totalDeliveryRates ?? 0) + ($totalAccessorialRates ?? 0);
    $opExp        = $totals->operation_total ?? 0;
    $adminExp     = $totals->admin_total ?? 0;
    $rpmExp       = $totals->rpm_total ?? 0;
    $accExp       = $totals->accessorial_total ?? 0;
    $netProfit    = $income - $adminExp - $rpmExp - $opExp - $accExp;

    $statCards = [
        ['label'=>'Revenue',             'value'=>$income,    'fmt'=>true, 'icon'=>'fa-sack-dollar',    'ring'=>'ring-emerald-200', 'bg'=>'bg-emerald-50', 'iconCls'=>'text-emerald-600', 'valCls'=>'text-emerald-700', 'trend'=>null],
        ['label'=>'Net Profit',          'value'=>$netProfit, 'fmt'=>true, 'icon'=>'fa-chart-line',     'ring'=>'ring-teal-200',   'bg'=>'bg-teal-50',    'iconCls'=>'text-teal-600',    'valCls'=>$netProfit>=0?'text-teal-700':'text-rose-700', 'trend'=>null],
        ['label'=>'Operations',          'value'=>$opExp,     'fmt'=>true, 'icon'=>'fa-gears',          'ring'=>'ring-orange-200', 'bg'=>'bg-orange-50',  'iconCls'=>'text-orange-600',  'valCls'=>'text-orange-700', 'trend'=>null],
        ['label'=>'Accessorial Exp.',    'value'=>$accExp,    'fmt'=>true, 'icon'=>'fa-truck-ramp-box', 'ring'=>'ring-teal-200',   'bg'=>'bg-teal-50',    'iconCls'=>'text-teal-600',    'valCls'=>'text-teal-700',   'trend'=>null],
        ['label'=>'Admin + RPM',         'value'=>$adminExp+$rpmExp,'fmt'=>true,'icon'=>'fa-user-shield','ring'=>'ring-blue-200',  'bg'=>'bg-blue-50',    'iconCls'=>'text-blue-600',    'valCls'=>'text-blue-700',   'trend'=>null],
        ['label'=>'Delivered Today',   'value'=>$totalDelivered ?? 0,             'fmt'=>false, 'icon'=>'fa-circle-check',       'ring'=>'ring-sky-200',     'bg'=>'bg-sky-50',      'iconCls'=>'text-sky-600',     'valCls'=>'text-sky-700',     'trend'=>null],
        ['label'=>'Pending',           'value'=>$totalPendingDeliveries ?? 0,     'fmt'=>false, 'icon'=>'fa-hourglass-half',     'ring'=>'ring-rose-200',    'bg'=>'bg-rose-50',     'iconCls'=>'text-rose-600',    'valCls'=>'text-rose-700',    'trend'=>null],
        ['label'=>'Trucks Allocated',  'value'=>$totalTruckAllocated ?? 0,        'fmt'=>false, 'icon'=>'fa-truck',              'ring'=>'ring-violet-200',  'bg'=>'bg-violet-50',   'iconCls'=>'text-violet-600',  'valCls'=>'text-violet-700',  'trend'=>null],
        ['label'=>'CVR Approvals',     'value'=>$totalCVRapproval ?? 0,           'fmt'=>false, 'icon'=>'fa-file-signature',     'ring'=>'ring-fuchsia-200', 'bg'=>'bg-fuchsia-50',  'iconCls'=>'text-fuchsia-600', 'valCls'=>'text-fuchsia-700', 'trend'=>null],
        ['label'=>'Liquidations',      'value'=>$totalLiquidation ?? 0,           'fmt'=>false, 'icon'=>'fa-file-invoice-dollar','ring'=>'ring-teal-200',    'bg'=>'bg-teal-50',     'iconCls'=>'text-teal-600',    'valCls'=>'text-teal-700',    'trend'=>null],
    ];

    $activityIconMap = [
        'Delivered Today'    => 'fa-circle-check',
        'Pending Deliveries' => 'fa-hourglass-half',
        'Truck Allocated'    => 'fa-truck',
        'Liquidations'       => 'fa-file-invoice-dollar',
        'CVR Approvals'      => 'fa-file-signature',
    ];

    $financialSplit = [
        ['label'=>'Revenue',        'value'=>$income,   'bar'=>'from-emerald-500 to-green-400', 'text'=>'text-emerald-600', 'icon'=>'fa-sack-dollar'],
        ['label'=>'Admin Expenses', 'value'=>$adminExp, 'bar'=>'from-blue-500 to-cyan-400',     'text'=>'text-blue-600',   'icon'=>'fa-user-shield', 'children'=>$totals->admin_breakdown ?? []],
        ['label'=>'RPM Expenses',   'value'=>$rpmExp,   'bar'=>'from-amber-500 to-yellow-400',  'text'=>'text-amber-600',  'icon'=>'fa-gas-pump',    'children'=>$totals->rpm_breakdown ?? []],
        ['label'=>'Op. Expenses',   'value'=>$opExp,    'bar'=>'from-orange-500 to-red-400',    'text'=>'text-orange-600', 'icon'=>'fa-gears',       'children'=>$totals->operation_breakdown ?? []],
    ];
    $chartMax = max($income, $adminExp, $rpmExp, $opExp, 1);
    $expenseMixBreakdownMax = max(1, collect($expenseMix)->flatMap(fn($i)=>$i['children']??[])->max('value')??1);
@endphp

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50/30 to-cyan-50/20 py-4 sm:py-6">
    <div class="mx-auto max-w-7xl px-3 sm:px-4 lg:px-6">

        {{-- ── Header ───────────────────────────────────── --}}
        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <div class="mb-2 inline-flex items-center gap-2 rounded-full bg-blue-600 px-3 py-1 text-xs font-bold text-white shadow-sm shadow-blue-500/30">
                    <i class="fas fa-gauge-high"></i>
                    Live Dashboard
                </div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ $dashboardTitle }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $dashboardSubtitle }}</p>
            </div>

            {{-- Period filter --}}
            <form method="GET" action="{{ route('dashboard') }}"
                  class="flex flex-wrap items-end gap-2 rounded-2xl border border-slate-200 bg-white/90 p-3 shadow-sm backdrop-blur-sm lg:shrink-0">
                <div class="min-w-0">
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Month</label>
                    <input type="month" name="month" value="{{ request('month', now()->format('Y-m')) }}"
                           class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                </div>
                <div class="min-w-0">
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-widest text-slate-400">From</label>
                    <input type="date" name="start_date" value="{{ request('start_date', now()->startOfMonth()->toDateString()) }}"
                           class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                </div>
                <div class="min-w-0">
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-widest text-slate-400">To</label>
                    <input type="date" name="end_date" value="{{ request('end_date', now()->toDateString()) }}"
                           class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                </div>
                <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-blue-500/25 transition hover:bg-blue-700 active:scale-95">
                    <i class="fas fa-filter text-xs"></i> Apply
                </button>
            </form>
        </div>

        {{-- ── KPI Stat Cards ────────────────────────────── --}}
        <div class="mb-5 grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-5 xl:grid-cols-5">
            @foreach(array_slice($statCards, 0, 5) as $c)
            <div class="group rounded-2xl border border-white bg-white px-3 py-3 shadow-sm ring-1 {{ $c['ring'] }} transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between gap-2">
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl {{ $c['bg'] }} {{ $c['iconCls'] }} ring-1 {{ $c['ring'] }}">
                        <i class="fas {{ $c['icon'] }} text-xs"></i>
                    </span>
                    <span class="text-[9px] font-bold uppercase tracking-widest text-slate-400 text-right leading-tight">{{ $c['label'] }}</span>
                </div>
                <p class="mt-2 text-base font-extrabold leading-none {{ $c['valCls'] }} sm:text-lg">
                    @if($c['fmt']) <span class="text-[9px] font-semibold">PHP</span> {{ number_format($c['value'], 0) }}
                    @else {{ number_format($c['value']) }}
                    @endif
                </p>
                <p class="mt-0.5 text-[9px] text-slate-400">Period {{ $periodLabel }}</p>
            </div>
            @endforeach
        </div>

        {{-- Operational Stat Cards --}}
        <div class="mb-5 grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-5 xl:grid-cols-5">
            @foreach(array_slice($statCards, 5) as $c)
            <div class="group rounded-2xl border border-white bg-white px-3 py-3 shadow-sm ring-1 {{ $c['ring'] }} transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between gap-2">
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl {{ $c['bg'] }} {{ $c['iconCls'] }} ring-1 {{ $c['ring'] }}">
                        <i class="fas {{ $c['icon'] }} text-xs"></i>
                    </span>
                    <span class="text-[9px] font-bold uppercase tracking-widest text-slate-400 text-right leading-tight">{{ $c['label'] }}</span>
                </div>
                <p class="mt-2 text-2xl font-extrabold leading-none {{ $c['valCls'] }}">{{ number_format($c['value']) }}</p>
                <p class="mt-0.5 text-[9px] text-slate-400">Total count</p>
            </div>
            @endforeach
        </div>

        {{-- ── Expense Breakdown by Category ───────────── --}}
        @php
            $accessorialTotal = $totals->accessorial_total ?? 0;
            $netProfit2       = $income - $adminExp - $rpmExp - $opExp - $accessorialTotal;

            $expCategories = [
                [
                    'key'      => 'admin',
                    'label'    => 'Admin',
                    'subtitle' => 'Administrative expenses',
                    'total'    => $adminExp,
                    'icon'     => 'fa-user-shield',
                    'hdr'      => 'bg-blue-700',
                    'badge'    => 'bg-blue-100 text-blue-800',
                    'bar'      => 'bg-blue-500',
                    'barBg'    => 'bg-blue-100',
                    'items'    => collect($totals->admin_breakdown ?? [])->filter(fn($i)=>($i['value']??0)>0)->values()->all(),
                ],
                [
                    'key'      => 'rpm',
                    'label'    => 'RPM',
                    'subtitle' => 'Repairs, Parts & Maintenance',
                    'total'    => $rpmExp,
                    'icon'     => 'fa-wrench',
                    'hdr'      => 'bg-amber-600',
                    'badge'    => 'bg-amber-100 text-amber-800',
                    'bar'      => 'bg-amber-500',
                    'barBg'    => 'bg-amber-100',
                    'items'    => collect($totals->rpm_breakdown ?? [])->filter(fn($i)=>($i['value']??0)>0)->values()->all(),
                ],
                [
                    'key'      => 'operations',
                    'label'    => 'Operations',
                    'subtitle' => 'Diesel · Toll · Freight · Lodging',
                    'total'    => $opExp,
                    'icon'     => 'fa-gears',
                    'hdr'      => 'bg-orange-600',
                    'badge'    => 'bg-orange-100 text-orange-800',
                    'bar'      => 'bg-orange-500',
                    'barBg'    => 'bg-orange-100',
                    'items'    => collect($totals->operation_breakdown ?? [])->filter(fn($i)=>($i['value']??0)>0)->values()->all(),
                ],
                [
                    'key'      => 'accessorial',
                    'label'    => 'Accessorial',
                    'subtitle' => 'Manpower · Hauling · Equipment',
                    'total'    => $accessorialTotal,
                    'icon'     => 'fa-truck-ramp-box',
                    'hdr'      => 'bg-teal-700',
                    'badge'    => 'bg-teal-100 text-teal-800',
                    'bar'      => 'bg-teal-500',
                    'barBg'    => 'bg-teal-100',
                    'items'    => collect($totals->accessorial_breakdown ?? [])->filter(fn($i)=>($i['value']??0)>0)->values()->all(),
                ],
            ];
            $catMax = max(collect($expCategories)->max('total'), 1);
        @endphp

        <div class="mb-5 rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
            {{-- Section header --}}
            <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-4 py-3">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-800 text-white">
                        <i class="fas fa-table-columns text-xs"></i>
                    </span>
                    <div>
                        <p class="text-sm font-bold text-slate-800">Expense Breakdown by Category</p>
                        <p class="text-xs text-slate-400">Admin · RPM · Operations · Accessorial — tap any card to expand items</p>
                    </div>
                </div>
                <div class="hidden sm:flex items-center gap-1.5 rounded-xl bg-slate-50 px-3 py-1.5 ring-1 ring-slate-200 text-xs">
                    <i class="fas fa-peso-sign text-slate-400 text-[10px]"></i>
                    <span class="text-slate-500">Total expenses:</span>
                    <span class="font-bold text-slate-800">PHP {{ number_format($adminExp + $rpmExp + $opExp + $accessorialTotal, 2) }}</span>
                </div>
            </div>

            {{-- Category columns --}}
            <div class="grid grid-cols-1 gap-0 sm:grid-cols-2 xl:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
                @foreach($expCategories as $cat)
                    @php
                        $catPct = min(100, ($cat['total'] / $catMax) * 100);
                        $itemMax = max(collect($cat['items'])->max('value') ?? 1, 1);
                    @endphp
                    <details class="group/cat peer/cat" @if($loop->first) open @endif>
                        <summary class="cursor-pointer list-none select-none">
                            {{-- Category header --}}
                            <div class="{{ $cat['hdr'] }} px-4 py-3 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white/20 text-white text-xs">
                                        <i class="fas {{ $cat['icon'] }}"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-white leading-none">{{ $cat['label'] }}</p>
                                        <p class="text-[9px] text-white/70 mt-0.5 truncate">{{ $cat['subtitle'] }}</p>
                                    </div>
                                </div>
                                <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white/20 text-white transition group-open/cat:rotate-180">
                                    <i class="fas fa-chevron-down text-[9px]"></i>
                                </span>
                            </div>

                            {{-- Total bar --}}
                            <div class="px-4 pt-3 pb-2">
                                <div class="flex items-baseline justify-between mb-1.5">
                                    <span class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Total</span>
                                    <span class="text-sm font-extrabold text-slate-800">PHP {{ number_format($cat['total'], 2) }}</span>
                                </div>
                                <div class="h-2 rounded-full {{ $cat['barBg'] }}">
                                    <div class="h-2 rounded-full {{ $cat['bar'] }} transition-all duration-700" style="width:{{ $catPct }}%"></div>
                                </div>
                            </div>
                        </summary>

                        {{-- Item breakdown (shown when open) --}}
                        <div class="px-3 pb-3 space-y-1.5 border-t border-slate-100 pt-2">
                            @forelse($cat['items'] as $item)
                                @php
                                    $iPct = min(100, (($item['value'] ?? 0) / $itemMax) * 100);
                                    $subItems = $item['children'] ?? [];
                                @endphp
                                @if(!empty($subItems))
                                <details class="group/sub rounded-lg {{ $cat['barBg'] }}/60 ring-1 ring-slate-100 overflow-hidden">
                                    <summary class="cursor-pointer list-none select-none px-2.5 py-2">
                                        <div class="flex items-center justify-between gap-2 text-xs">
                                            <span class="flex items-center gap-1.5 font-medium text-slate-700 min-w-0">
                                                <i class="fas {{ $item['icon'] ?? 'fa-coins' }} text-[10px] {{ $item['text'] ?? '' }} shrink-0"></i>
                                                <span class="truncate">{{ $item['label'] }}</span>
                                            </span>
                                            <span class="flex items-center gap-1.5 shrink-0">
                                                <span class="font-bold text-slate-800">PHP {{ number_format($item['value'] ?? 0, 2) }}</span>
                                                <span class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-white/80 text-slate-400 transition group-open/sub:rotate-180">
                                                    <i class="fas fa-chevron-down text-[8px]"></i>
                                                </span>
                                            </span>
                                        </div>
                                        <div class="mt-1.5 h-1.5 rounded-full bg-white/60">
                                            <div class="h-1.5 rounded-full {{ $cat['bar'] }}" style="width:{{ $iPct }}%"></div>
                                        </div>
                                    </summary>
                                    <div class="px-2.5 pb-2 pt-1 space-y-1 border-t border-slate-200/60">
                                        @foreach($subItems as $sub)
                                        <div class="flex items-center justify-between text-[11px] text-slate-600 px-1">
                                            <span class="flex items-center gap-1 truncate">
                                                <i class="fas fa-arrow-right text-[8px] text-slate-400 shrink-0"></i>
                                                {{ $sub['label'] }}
                                            </span>
                                            <span class="font-semibold shrink-0 ml-2">PHP {{ number_format($sub['value'], 2) }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </details>
                                @else
                                <div class="flex items-center gap-2 rounded-lg px-2.5 py-2 ring-1 ring-slate-100 bg-slate-50/60 hover:bg-white hover:shadow-sm transition">
                                    <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-lg {{ $cat['barBg'] }} {{ str_replace('bg-', 'text-', $cat['bar']) }} text-[10px]">
                                        <i class="fas {{ $item['icon'] ?? 'fa-coins' }}"></i>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="font-medium text-slate-600 truncate">{{ $item['label'] }}</span>
                                            <span class="font-bold text-slate-800 ml-2 shrink-0">PHP {{ number_format($item['value'] ?? 0, 2) }}</span>
                                        </div>
                                        <div class="mt-1 h-1 rounded-full {{ $cat['barBg'] }}">
                                            <div class="h-1 rounded-full {{ $cat['bar'] }}" style="width:{{ $iPct }}%"></div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @empty
                                <p class="py-4 text-center text-xs text-slate-400 italic">No expenses recorded for this period.</p>
                            @endforelse
                        </div>
                    </details>
                @endforeach
            </div>
        </div>

        {{-- ── Chart + Activity Mix ─────────────────────── --}}
        <div class="mb-5 grid gap-4 lg:grid-cols-[1fr,320px]">

            {{-- Monthly Trend Chart --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-sky-50 text-sky-600 ring-1 ring-sky-200">
                            <i class="fas fa-chart-area text-xs"></i>
                        </span>
                        <div>
                            <p class="text-sm font-bold text-slate-800">Monthly Trend</p>
                            <p class="text-xs text-slate-400">6-month income vs expenses vs profit</p>
                        </div>
                    </div>
                    <div class="hidden items-center gap-3 text-xs sm:flex">
                        <span class="flex items-center gap-1.5"><span class="h-2 w-5 rounded-full bg-emerald-500 inline-block"></span>Income</span>
                        <span class="flex items-center gap-1.5"><span class="h-2 w-5 rounded-full bg-rose-400 inline-block"></span>Expenses</span>
                        <span class="flex items-center gap-1.5"><span class="h-2 w-5 rounded-full bg-blue-500 inline-block"></span>Profit</span>
                    </div>
                </div>
                <div class="relative h-52 sm:h-64">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            {{-- Activity Mix --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <div class="mb-3 flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-violet-50 text-violet-600 ring-1 ring-violet-200">
                        <i class="fas fa-layer-group text-xs"></i>
                    </span>
                    <div>
                        <p class="text-sm font-bold text-slate-800">Operational Activity</p>
                        <p class="text-xs text-slate-400">Current workload at a glance</p>
                    </div>
                </div>
                @php $actMax = max(1, collect($activityMix)->max('value') ?? 1); @endphp
                <div class="space-y-2">
                    @foreach($activityMix as $item)
                        @php
                            $pct   = min(100, (($item['value'] ?? 0) / $actMax) * 100);
                            $icon  = $activityIconMap[$item['label']] ?? 'fa-circle-dot';
                        @endphp
                        <div class="flex items-center gap-3 rounded-xl bg-slate-50/80 px-3 py-2.5 ring-1 ring-slate-100 transition hover:bg-white hover:shadow-sm">
                            <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg {{ $item['color'] }} text-white shadow-sm text-[10px]">
                                <i class="fas {{ $icon }}"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-medium text-slate-600 truncate">{{ $item['label'] }}</span>
                                    <span class="ml-2 shrink-0 font-bold {{ $item['text'] }}">{{ number_format($item['value']) }}</span>
                                </div>
                                <div class="mt-1 h-1.5 rounded-full bg-slate-200">
                                    <div class="h-1.5 rounded-full {{ $item['color'] }} transition-all duration-700" style="width:{{ $pct }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── Financial Split + Approver Snapshot ─────── --}}
        <div class="grid gap-4 lg:grid-cols-[1fr,340px]">

            {{-- Financial Split (expandable) --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <div class="mb-3 flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-amber-50 text-amber-600 ring-1 ring-amber-200">
                        <i class="fas fa-scale-balanced text-xs"></i>
                    </span>
                    <div>
                        <p class="text-sm font-bold text-slate-800">Financial Breakdown</p>
                        <p class="text-xs text-slate-400">Revenue vs expenses — tap to expand details</p>
                    </div>
                </div>
                <div class="space-y-2">
                    @foreach($financialSplit as $bar)
                        @php
                            $w = min(100, ($bar['value'] / $chartMax) * 100);
                            $kids = $bar['children'] ?? [];
                        @endphp
                        @if(!empty($kids))
                        <details class="group rounded-xl bg-slate-50 ring-1 ring-slate-200 overflow-hidden">
                            <summary class="cursor-pointer list-none select-none px-3 py-2.5">
                                <div class="flex items-center justify-between gap-3 text-sm">
                                    <span class="flex items-center gap-2 font-medium text-slate-700">
                                        <i class="fas {{ $bar['icon'] }} text-xs {{ $bar['text'] }}"></i>
                                        {{ $bar['label'] }}
                                    </span>
                                    <span class="flex items-center gap-2">
                                        <span class="font-bold {{ $bar['text'] }} text-sm">PHP {{ number_format($bar['value'], 2) }}</span>
                                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white ring-1 ring-slate-300 text-slate-400 transition group-open:rotate-180">
                                            <i class="fas fa-chevron-down text-[9px]"></i>
                                        </span>
                                    </span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-slate-200">
                                    <div class="h-2 rounded-full bg-gradient-to-r {{ $bar['bar'] }}" style="width:{{ $w }}%"></div>
                                </div>
                            </summary>
                            <div class="px-3 pb-3 pt-1 grid grid-cols-1 sm:grid-cols-2 gap-1.5 border-t border-slate-200">
                                @foreach($kids as $child)
                                    @php $cw = min(100, (($child['value']??0) / $expenseMixBreakdownMax) * 100); @endphp
                                    <div class="rounded-lg bg-white px-3 py-2 ring-1 ring-slate-100">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="flex items-center gap-1.5 font-medium text-slate-600">
                                                <i class="fas {{ $child['icon'] ?? 'fa-coins' }} text-[10px] {{ $child['text'] ?? '' }}"></i>
                                                {{ $child['label'] }}
                                            </span>
                                            <span class="font-semibold {{ $child['text'] ?? '' }}">PHP {{ number_format($child['value']??0, 2) }}</span>
                                        </div>
                                        <div class="mt-1.5 h-1 rounded-full bg-slate-100">
                                            <div class="h-1 rounded-full {{ $child['color'] ?? 'bg-slate-400' }}" style="width:{{ $cw }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </details>
                        @else
                        <div class="rounded-xl bg-slate-50 px-3 py-2.5 ring-1 ring-slate-200">
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <span class="flex items-center gap-2 font-medium text-slate-700">
                                    <i class="fas {{ $bar['icon'] }} text-xs {{ $bar['text'] }}"></i>
                                    {{ $bar['label'] }}
                                </span>
                                <span class="font-bold {{ $bar['text'] }}">PHP {{ number_format($bar['value'], 2) }}</span>
                            </div>
                            <div class="mt-2 h-2 rounded-full bg-slate-200">
                                <div class="h-2 rounded-full bg-gradient-to-r {{ $bar['bar'] }}" style="width:{{ $w }}%"></div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Approver Snapshot --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <div class="mb-3 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-fuchsia-50 text-fuchsia-600 ring-1 ring-fuchsia-200">
                            <i class="fas fa-wallet text-xs"></i>
                        </span>
                        <div>
                            <p class="text-sm font-bold text-slate-800">Approver Snapshot</p>
                            <p class="text-xs text-slate-400">Running &amp; uncollected balances</p>
                        </div>
                    </div>
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500">
                        {{ count($approvers ?? []) }} approver{{ count($approvers ?? []) !== 1 ? 's' : '' }}
                    </span>
                </div>
                <div class="space-y-2 overflow-y-auto" style="max-height:340px;">
                    @forelse($approvers ?? [] as $approver)
                        @php
                            $run  = (float)($runningTotalsByApprover[$approver->id] ?? 0);
                            $unc  = (float)($uncollectedByApprover[$approver->id] ?? 0);
                            $maxV = max($run, $unc, 1);
                        @endphp
                        <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-3 transition hover:border-blue-200 hover:bg-white hover:shadow-sm">
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-100 to-fuchsia-100 text-violet-700 ring-1 ring-violet-200 text-xs font-bold">
                                        {{ $loop->iteration }}
                                    </span>
                                    <p class="truncate text-sm font-semibold text-slate-800">{{ $approver->name }}</p>
                                </div>
                                <span class="shrink-0 rounded-full bg-white px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-400 ring-1 ring-slate-200">
                                    Approver
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div class="rounded-lg bg-emerald-50 px-2.5 py-2 ring-1 ring-emerald-100">
                                    <p class="text-[9px] font-bold uppercase tracking-widest text-emerald-500">Running</p>
                                    <p class="mt-0.5 font-bold text-emerald-700 text-xs">PHP {{ number_format($run, 2) }}</p>
                                </div>
                                <div class="rounded-lg bg-rose-50 px-2.5 py-2 ring-1 ring-rose-100">
                                    <p class="text-[9px] font-bold uppercase tracking-widest text-rose-500">Uncollected</p>
                                    <p class="mt-0.5 font-bold text-rose-700 text-xs">PHP {{ number_format($unc, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-10 text-center">
                            <i class="fas fa-chart-line text-3xl text-slate-300 mb-2 block"></i>
                            <p class="text-sm font-medium text-slate-500">No approver data yet</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ── Chart.js Trend ──────────────────────────────── --}}
@php
    $chartLabels  = collect($analyticsSeries)->pluck('label')->toJson();
    $chartIncome  = collect($analyticsSeries)->pluck('income')->toJson();
    $chartExpense = collect($analyticsSeries)->pluck('expenses')->toJson();
    $chartProfit  = collect($analyticsSeries)->pluck('profit')->toJson();
@endphp
<script>
(function() {
    const ctx = document.getElementById('trendChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! $chartLabels !!},
            datasets: [
                {
                    label: 'Income',
                    data: {!! $chartIncome !!},
                    backgroundColor: 'rgba(16,185,129,0.75)',
                    borderColor: '#10b981',
                    borderWidth: 1,
                    borderRadius: 6,
                    order: 2
                },
                {
                    label: 'Expenses',
                    data: {!! $chartExpense !!},
                    backgroundColor: 'rgba(251,113,133,0.65)',
                    borderColor: '#fb7185',
                    borderWidth: 1,
                    borderRadius: 6,
                    order: 2
                },
                {
                    label: 'Profit',
                    data: {!! $chartProfit !!},
                    type: 'line',
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.10)',
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#3b82f6',
                    fill: true,
                    tension: 0.35,
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleColor: '#94a3b8',
                    bodyColor: '#e2e8f0',
                    padding: 10,
                    callbacks: {
                        label: ctx => ' PHP ' + Number(ctx.raw).toLocaleString('en-PH', {minimumFractionDigits:0})
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10 }, color: '#94a3b8' }
                },
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        font: { size: 10 },
                        color: '#94a3b8',
                        callback: v => 'PHP ' + (v >= 1000 ? (v/1000).toFixed(0)+'k' : v)
                    },
                    beginAtZero: true
                }
            }
        }
    });
})();
</script>
