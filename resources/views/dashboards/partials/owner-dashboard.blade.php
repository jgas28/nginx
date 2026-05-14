@php
    $showExtended      = $showExtended ?? false;
    $dashboardTitle    = $dashboardTitle ?? 'Owner Dashboard';
    $dashboardSubtitle = $dashboardSubtitle ?? 'Track finances, delivery health, and approver balances in one place.';
    $periodLabel       = request('month', now()->format('Y-m'));
    $analyticsSeries   = $analyticsSeries ?? [];
    $analyticsMax      = max(1, $analyticsMax ?? 1);
    $activityMix       = $activityMix ?? [];
    $expenseMix        = $expenseMix ?? [];

    $income    = ($totalDeliveryRates ?? 0) + ($totalAccessorialRates ?? 0);
    $opExp     = $totals->operation_total    ?? 0;
    $adminExp  = $totals->admin_total        ?? 0;
    $rpmExp    = $totals->rpm_total          ?? 0;
    $accExp    = $totals->accessorial_total  ?? 0;
    $netProfit = $income - $adminExp - $rpmExp - $opExp - $accExp;

    // ── All palette colors as hex to prevent Tailwind purge ──────────────
    $pal = [
        'emerald' => ['bg'=>'#d1fae5','border'=>'#6ee7b7','icon'=>'#059669','val'=>'#047857','bar'=>'#10b981'],
        'teal'    => ['bg'=>'#ccfbf1','border'=>'#5eead4','icon'=>'#0d9488','val'=>'#0f766e','bar'=>'#14b8a6'],
        'orange'  => ['bg'=>'#ffedd5','border'=>'#fdba74','icon'=>'#ea580c','val'=>'#c2410c','bar'=>'#f97316'],
        'blue'    => ['bg'=>'#dbeafe','border'=>'#93c5fd','icon'=>'#2563eb','val'=>'#1d4ed8','bar'=>'#3b82f6'],
        'amber'   => ['bg'=>'#fef3c7','border'=>'#fcd34d','icon'=>'#d97706','val'=>'#b45309','bar'=>'#f59e0b'],
        'sky'     => ['bg'=>'#e0f2fe','border'=>'#7dd3fc','icon'=>'#0284c7','val'=>'#0369a1','bar'=>'#0ea5e9'],
        'rose'    => ['bg'=>'#ffe4e6','border'=>'#fda4af','icon'=>'#e11d48','val'=>'#be123c','bar'=>'#f43f5e'],
        'violet'  => ['bg'=>'#ede9fe','border'=>'#c4b5fd','icon'=>'#7c3aed','val'=>'#6d28d9','bar'=>'#8b5cf6'],
        'fuchsia' => ['bg'=>'#fae8ff','border'=>'#f0abfc','icon'=>'#c026d3','val'=>'#a21caf','bar'=>'#d946ef'],
        'slate'   => ['bg'=>'#f1f5f9','border'=>'#cbd5e1','icon'=>'#475569','val'=>'#334155','bar'=>'#64748b'],
    ];

    // ── Exactly 9 cards → 3 columns × 3 rows ──────────────────────
    // Row 1 – Revenue KPIs
    // Row 2 – Expense detail
    // Row 3 – Operational activity
    $statCards = [
        // Row 1
        ['label'=>'Revenue',         'value'=>$income,             'fmt'=>true,  'sub'=>'Total income',        'icon'=>'fa-sack-dollar',        'p'=>$pal['emerald']],
        ['label'=>'Net Profit',      'value'=>$netProfit,          'fmt'=>true,  'sub'=>'After all expenses',  'icon'=>'fa-chart-line',         'p'=>$netProfit>=0?$pal['teal']:$pal['rose']],
        ['label'=>'Total Expenses',  'value'=>$adminExp+$rpmExp+$opExp+$accExp, 'fmt'=>true, 'sub'=>'All cost types', 'icon'=>'fa-coins','p'=>$pal['orange']],
        // Row 2
        ['label'=>'Operations',      'value'=>$opExp,              'fmt'=>true,  'sub'=>'Diesel · Toll · Freight','icon'=>'fa-gears',           'p'=>$pal['amber']],
        ['label'=>'Admin + RPM',     'value'=>$adminExp+$rpmExp,   'fmt'=>true,  'sub'=>'Admin & maintenance', 'icon'=>'fa-user-shield',        'p'=>$pal['blue']],
        ['label'=>'Accessorial Exp.','value'=>$accExp,             'fmt'=>true,  'sub'=>'Manpower · Hauling',  'icon'=>'fa-truck-ramp-box',     'p'=>$pal['teal']],
        // Row 3
        ['label'=>'Pending',         'value'=>$totalPendingDeliveries??0, 'fmt'=>false,'sub'=>'Awaiting delivery',  'icon'=>'fa-hourglass-half',    'p'=>$pal['rose']],
        ['label'=>'CVR Approvals',   'value'=>$totalCVRapproval??0,       'fmt'=>false,'sub'=>'Cash vouchers',      'icon'=>'fa-file-signature',    'p'=>$pal['fuchsia']],
        ['label'=>'Liquidations',    'value'=>$totalLiquidation??0,       'fmt'=>false,'sub'=>'Awaiting settlement','icon'=>'fa-file-invoice-dollar','p'=>$pal['violet']],
    ];

    // Activity mix config: icon + full hex palette
    $activityHex = [
        'Delivered Today'    => ['icon'=>'fa-circle-check',        ...$pal['sky']],
        'Pending Deliveries' => ['icon'=>'fa-hourglass-half',      ...$pal['rose']],
        'Truck Allocated'    => ['icon'=>'fa-truck',               ...$pal['violet']],
        'Liquidations'       => ['icon'=>'fa-file-invoice-dollar', ...$pal['amber']],
        'CVR Approvals'      => ['icon'=>'fa-file-signature',      ...$pal['fuchsia']],
    ];

    // Financial split with hex colors
    $financialSplit = [
        ['label'=>'Revenue',        'value'=>$income,   'barA'=>'#10b981','barB'=>'#4ade80','textHex'=>'#059669','icon'=>'fa-sack-dollar'],
        ['label'=>'Admin Expenses', 'value'=>$adminExp, 'barA'=>'#3b82f6','barB'=>'#22d3ee','textHex'=>'#2563eb','icon'=>'fa-user-shield','children'=>$totals->admin_breakdown??[]],
        ['label'=>'RPM Expenses',   'value'=>$rpmExp,   'barA'=>'#f59e0b','barB'=>'#facc15','textHex'=>'#d97706','icon'=>'fa-wrench',     'children'=>$totals->rpm_breakdown??[]],
        ['label'=>'Op. Expenses',   'value'=>$opExp,    'barA'=>'#f97316','barB'=>'#f87171','textHex'=>'#ea580c','icon'=>'fa-gears',      'children'=>$totals->operation_breakdown??[]],
    ];
    $chartMax = max($income, $adminExp, $rpmExp, $opExp, 1);
    $expenseMixBreakdownMax = max(1, collect($expenseMix)->flatMap(fn($i)=>$i['children']??[])->max('value')??1);
@endphp

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50/30 to-cyan-50/20 py-4 sm:py-6">
    <div class="mx-auto max-w-7xl px-3 sm:px-4 lg:px-6">

        {{-- ── Header ─────────────────────────────── --}}
        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <div class="mb-2 inline-flex items-center gap-2 rounded-full bg-blue-600 px-3 py-1 text-xs font-bold text-white shadow-sm">
                    <i class="fas fa-gauge-high"></i> Live Dashboard
                </div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ $dashboardTitle }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $dashboardSubtitle }}</p>
            </div>
            <form method="GET" action="{{ route('dashboard') }}"
                  class="flex flex-wrap items-end gap-2 rounded-2xl border border-slate-200 bg-white/90 p-3 shadow-sm lg:shrink-0">
                <div>
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Month</label>
                    <input type="month" name="month" value="{{ request('month', now()->format('Y-m')) }}"
                           class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-widest text-slate-400">From</label>
                    <input type="date" name="start_date" value="{{ request('start_date', now()->startOfMonth()->toDateString()) }}"
                           class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-widest text-slate-400">To</label>
                    <input type="date" name="end_date" value="{{ request('end_date', now()->toDateString()) }}"
                           class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                </div>
                <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 active:scale-95">
                    <i class="fas fa-filter text-xs"></i> Apply
                </button>
            </form>
        </div>

        {{-- ── 3 × 3 KPI Grid — compact white cards ──── --}}
        <div class="mb-5 grid grid-cols-2 gap-2.5 sm:grid-cols-3">
            @foreach($statCards as $c)
            <div class="relative overflow-hidden rounded-xl bg-white px-3.5 py-3 shadow-sm ring-1 ring-slate-100 transition hover:-translate-y-0.5 hover:shadow-md"
                 style="border-top: 3px solid {{ $c['p']['bar'] }}">

                {{-- Label row: icon left, label right --}}
                <div class="flex items-center justify-between gap-2 mb-2.5">
                    <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-xs"
                          style="background-color:{{ $c['p']['bg'] }};color:{{ $c['p']['icon'] }}">
                        <i class="fas {{ $c['icon'] }}"></i>
                    </span>
                    <span class="text-right text-[9px] font-bold uppercase leading-tight tracking-[0.12em] text-slate-400">
                        {{ $c['label'] }}
                    </span>
                </div>

                {{-- Value --}}
                <p class="text-xl font-extrabold leading-none tracking-tight sm:text-2xl" style="color:{{ $c['p']['val'] }}">
                    @if($c['fmt'])
                        <span class="text-[9px] font-semibold text-slate-400 mr-0.5">PHP</span>{{ number_format($c['value'], 0) }}
                    @else
                        {{ number_format($c['value']) }}
                    @endif
                </p>

                {{-- Subtitle --}}
                <p class="mt-1 text-[10px] text-slate-400 leading-tight">{{ $c['sub'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- ── Expense Breakdown by Category ──────── --}}
        @php
            $accessorialTotal = $totals->accessorial_total ?? 0;
            $expCategories = [
                ['key'=>'admin',      'label'=>'Admin',       'subtitle'=>'Administrative expenses',          'total'=>$adminExp,        'icon'=>'fa-user-shield',    'hdrHex'=>'#1d4ed8','barHex'=>'#3b82f6','lightHex'=>'#dbeafe', 'items'=>collect($totals->admin_breakdown??[])->filter(fn($i)=>($i['value']??0)>0)->values()->all()],
                ['key'=>'rpm',        'label'=>'RPM',         'subtitle'=>'Repairs · Parts · Maintenance',    'total'=>$rpmExp,          'icon'=>'fa-wrench',         'hdrHex'=>'#b45309','barHex'=>'#f59e0b','lightHex'=>'#fef3c7', 'items'=>collect($totals->rpm_breakdown??[])->filter(fn($i)=>($i['value']??0)>0)->values()->all()],
                ['key'=>'operations', 'label'=>'Operations',  'subtitle'=>'Diesel · Toll · Freight · Lodging','total'=>$opExp,           'icon'=>'fa-gears',          'hdrHex'=>'#c2410c','barHex'=>'#f97316','lightHex'=>'#ffedd5', 'items'=>collect($totals->operation_breakdown??[])->filter(fn($i)=>($i['value']??0)>0)->values()->all()],
                ['key'=>'accessorial','label'=>'Accessorial', 'subtitle'=>'Manpower · Hauling · Equipment',  'total'=>$accessorialTotal,'icon'=>'fa-truck-ramp-box','hdrHex'=>'#0f766e','barHex'=>'#14b8a6','lightHex'=>'#ccfbf1', 'items'=>collect($totals->accessorial_breakdown??[])->filter(fn($i)=>($i['value']??0)>0)->values()->all()],
            ];
            $catMax = max(collect($expCategories)->max('total'), 1);
        @endphp

        <div class="mb-5 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            {{-- Header --}}
            <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-4 py-3">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-800 text-white">
                        <i class="fas fa-table-columns text-xs"></i>
                    </span>
                    <div>
                        <p class="text-sm font-bold text-slate-800">Expense Breakdown by Category</p>
                        <p class="text-xs text-slate-400">Admin · RPM · Operations · Accessorial — tap to expand items</p>
                    </div>
                </div>
                <div class="hidden items-center gap-1.5 rounded-xl bg-slate-50 px-3 py-1.5 text-xs ring-1 ring-slate-200 sm:flex">
                    <i class="fas fa-peso-sign text-[10px] text-slate-400"></i>
                    <span class="text-slate-500">Total:</span>
                    <span class="font-bold text-slate-800">PHP {{ number_format($adminExp + $rpmExp + $opExp + $accessorialTotal, 2) }}</span>
                </div>
            </div>

            {{-- Category columns --}}
            <div class="grid grid-cols-1 divide-y divide-slate-100 sm:grid-cols-2 sm:divide-x sm:divide-y-0 xl:grid-cols-4">
                @foreach($expCategories as $cat)
                    @php
                        $catPct  = min(100, ($cat['total'] / $catMax) * 100);
                        $itemMax = max(collect($cat['items'])->max('value') ?? 1, 1);
                    @endphp
                    <details class="group/cat" @if($loop->first) open @endif>
                        <summary class="cursor-pointer list-none select-none">
                            {{-- Colored category header --}}
                            <div class="flex items-center justify-between gap-3 px-4 py-3"
                                 style="background-color:{{ $cat['hdrHex'] }}">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-white text-xs"
                                          style="background-color:rgba(255,255,255,0.2)">
                                        <i class="fas {{ $cat['icon'] }}"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold leading-none text-white">{{ $cat['label'] }}</p>
                                        <p class="mt-0.5 truncate text-[9px] text-white/70">{{ $cat['subtitle'] }}</p>
                                    </div>
                                </div>
                                <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-white transition group-open/cat:rotate-180"
                                      style="background-color:rgba(255,255,255,0.2)">
                                    <i class="fas fa-chevron-down text-[9px]"></i>
                                </span>
                            </div>

                            {{-- Total bar --}}
                            <div class="px-4 pb-2 pt-3">
                                <div class="mb-1.5 flex items-baseline justify-between">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Total</span>
                                    <span class="text-sm font-extrabold text-slate-800">PHP {{ number_format($cat['total'], 2) }}</span>
                                </div>
                                <div class="h-2 rounded-full" style="background-color:{{ $cat['lightHex'] }}">
                                    <div class="h-2 rounded-full transition-all duration-700"
                                         style="width:{{ $catPct }}%;background-color:{{ $cat['barHex'] }}"></div>
                                </div>
                            </div>
                        </summary>

                        {{-- Item list --}}
                        <div class="space-y-1.5 border-t border-slate-100 px-3 pb-3 pt-2">
                            @forelse($cat['items'] as $item)
                                @php
                                    $iPct     = min(100, (($item['value'] ?? 0) / $itemMax) * 100);
                                    $subItems = $item['children'] ?? [];
                                @endphp
                                @if(!empty($subItems))
                                <details class="group/sub overflow-hidden rounded-lg ring-1 ring-slate-200">
                                    <summary class="cursor-pointer list-none select-none px-2.5 py-2"
                                             style="background-color:{{ $cat['lightHex'] }}">
                                        <div class="flex items-center justify-between gap-2 text-xs">
                                            <span class="flex items-center gap-1.5 font-medium text-slate-700 min-w-0">
                                                <i class="fas {{ $item['icon'] ?? 'fa-coins' }} shrink-0 text-[10px]"
                                                   style="color:{{ $cat['barHex'] }}"></i>
                                                <span class="truncate">{{ $item['label'] }}</span>
                                            </span>
                                            <span class="flex shrink-0 items-center gap-1.5">
                                                <span class="font-bold text-slate-800">PHP {{ number_format($item['value'] ?? 0, 2) }}</span>
                                                <span class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-white/80 text-slate-400 transition group-open/sub:rotate-180">
                                                    <i class="fas fa-chevron-down text-[8px]"></i>
                                                </span>
                                            </span>
                                        </div>
                                        <div class="mt-1.5 h-1.5 rounded-full bg-white/60">
                                            <div class="h-1.5 rounded-full" style="width:{{ $iPct }}%;background-color:{{ $cat['barHex'] }}"></div>
                                        </div>
                                    </summary>
                                    <div class="space-y-1 border-t border-slate-200/60 px-2.5 pb-2 pt-1">
                                        @foreach($subItems as $sub)
                                        <div class="flex items-center justify-between px-1 text-[11px] text-slate-600">
                                            <span class="flex items-center gap-1 truncate">
                                                <i class="fas fa-arrow-right shrink-0 text-[8px] text-slate-400"></i>
                                                {{ $sub['label'] }}
                                            </span>
                                            <span class="ml-2 shrink-0 font-semibold">PHP {{ number_format($sub['value'], 2) }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </details>
                                @else
                                <div class="flex items-center gap-2 rounded-lg bg-slate-50/60 px-2.5 py-2 ring-1 ring-slate-100 transition hover:bg-white hover:shadow-sm">
                                    <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-[10px]"
                                          style="background-color:{{ $cat['lightHex'] }};color:{{ $cat['barHex'] }}">
                                        <i class="fas {{ $item['icon'] ?? 'fa-coins' }}"></i>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="truncate font-medium text-slate-600">{{ $item['label'] }}</span>
                                            <span class="ml-2 shrink-0 font-bold text-slate-800">PHP {{ number_format($item['value'] ?? 0, 2) }}</span>
                                        </div>
                                        <div class="mt-1 h-1 rounded-full" style="background-color:{{ $cat['lightHex'] }}">
                                            <div class="h-1 rounded-full" style="width:{{ $iPct }}%;background-color:{{ $cat['barHex'] }}"></div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @empty
                                <p class="py-4 text-center text-xs italic text-slate-400">No expenses recorded this period.</p>
                            @endforelse
                        </div>
                    </details>
                @endforeach
            </div>
        </div>

        {{-- ── Chart + Activity Mix ────────────────── --}}
        <div class="mb-5 grid gap-4 lg:grid-cols-[1fr,300px]">

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
                        <span class="flex items-center gap-1.5"><span class="inline-block h-2 w-5 rounded-full" style="background:#10b981"></span>Income</span>
                        <span class="flex items-center gap-1.5"><span class="inline-block h-2 w-5 rounded-full" style="background:#f43f5e"></span>Expenses</span>
                        <span class="flex items-center gap-1.5"><span class="inline-block h-2 w-5 rounded-full" style="background:#3b82f6"></span>Profit</span>
                    </div>
                </div>
                <div class="relative h-52 sm:h-64">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            {{-- Activity Mix ─ all colors via inline style --}}
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
                            $pct = min(100, (($item['value'] ?? 0) / $actMax) * 100);
                            $ac  = $activityHex[$item['label']] ?? ['icon'=>'fa-circle-dot', ...$pal['slate']];
                        @endphp
                        <div class="flex items-center gap-3 rounded-xl bg-slate-50/80 px-3 py-2.5 ring-1 ring-slate-100 transition hover:bg-white hover:shadow-sm">
                            {{-- Icon badge: always uses inline style so it's never purged --}}
                            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl text-white text-xs shadow-sm"
                                  style="background-color:{{ $ac['bar'] }}">
                                <i class="fas {{ $ac['icon'] }}"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="truncate font-semibold text-slate-700">{{ $item['label'] }}</span>
                                    <span class="ml-2 shrink-0 text-sm font-extrabold" style="color:{{ $ac['val'] }}">
                                        {{ number_format($item['value']) }}
                                    </span>
                                </div>
                                <div class="mt-1.5 h-1.5 rounded-full" style="background-color:{{ $ac['bg'] }}">
                                    <div class="h-1.5 rounded-full transition-all duration-700"
                                         style="width:{{ $pct }}%;background-color:{{ $ac['bar'] }}"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if(empty($activityMix))
                        <p class="py-6 text-center text-sm text-slate-400">No activity data available.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Financial Split + Approver Snapshot ─── --}}
        <div class="grid gap-4 lg:grid-cols-[1fr,340px]">

            {{-- Financial Split --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <div class="mb-3 flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-amber-50 text-amber-600 ring-1 ring-amber-200">
                        <i class="fas fa-scale-balanced text-xs"></i>
                    </span>
                    <div>
                        <p class="text-sm font-bold text-slate-800">Financial Breakdown</p>
                        <p class="text-xs text-slate-400">Revenue vs expenses — tap to expand</p>
                    </div>
                </div>
                <div class="space-y-2">
                    @foreach($financialSplit as $bar)
                        @php
                            $w    = min(100, ($bar['value'] / $chartMax) * 100);
                            $kids = $bar['children'] ?? [];
                        @endphp
                        @if(!empty($kids))
                        <details class="group overflow-hidden rounded-xl bg-slate-50 ring-1 ring-slate-200">
                            <summary class="cursor-pointer list-none select-none px-3 py-2.5">
                                <div class="flex items-center justify-between gap-3 text-sm">
                                    <span class="flex items-center gap-2 font-medium text-slate-700">
                                        <i class="fas {{ $bar['icon'] }} text-xs" style="color:{{ $bar['textHex'] }}"></i>
                                        {{ $bar['label'] }}
                                    </span>
                                    <span class="flex items-center gap-2">
                                        <span class="text-sm font-bold" style="color:{{ $bar['textHex'] }}">PHP {{ number_format($bar['value'], 2) }}</span>
                                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white text-slate-400 ring-1 ring-slate-300 transition group-open:rotate-180">
                                            <i class="fas fa-chevron-down text-[9px]"></i>
                                        </span>
                                    </span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-slate-200">
                                    <div class="h-2 rounded-full transition-all"
                                         style="width:{{ $w }}%;background:linear-gradient(to right,{{ $bar['barA'] }},{{ $bar['barB'] }})"></div>
                                </div>
                            </summary>
                            <div class="grid grid-cols-1 gap-1.5 border-t border-slate-200 px-3 pb-3 pt-2 sm:grid-cols-2">
                                @foreach($kids as $child)
                                    @php $cw = min(100, (($child['value']??0) / $expenseMixBreakdownMax) * 100); @endphp
                                    <div class="rounded-lg bg-white px-3 py-2 ring-1 ring-slate-100">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="flex items-center gap-1.5 font-medium text-slate-600 truncate">
                                                <i class="fas {{ $child['icon'] ?? 'fa-coins' }} text-[10px]" style="color:{{ $bar['textHex'] }}"></i>
                                                {{ $child['label'] }}
                                            </span>
                                            <span class="ml-1 shrink-0 font-semibold" style="color:{{ $bar['textHex'] }}">
                                                PHP {{ number_format($child['value']??0, 2) }}
                                            </span>
                                        </div>
                                        <div class="mt-1.5 h-1 rounded-full bg-slate-100">
                                            <div class="h-1 rounded-full" style="width:{{ $cw }}%;background-color:{{ $bar['barA'] }}"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </details>
                        @else
                        <div class="rounded-xl bg-slate-50 px-3 py-2.5 ring-1 ring-slate-200">
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <span class="flex items-center gap-2 font-medium text-slate-700">
                                    <i class="fas {{ $bar['icon'] }} text-xs" style="color:{{ $bar['textHex'] }}"></i>
                                    {{ $bar['label'] }}
                                </span>
                                <span class="font-bold" style="color:{{ $bar['textHex'] }}">PHP {{ number_format($bar['value'], 2) }}</span>
                            </div>
                            <div class="mt-2 h-2 rounded-full bg-slate-200">
                                <div class="h-2 rounded-full" style="width:{{ $w }}%;background:linear-gradient(to right,{{ $bar['barA'] }},{{ $bar['barB'] }})"></div>
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
                        {{ count($approvers ?? []) }} total
                    </span>
                </div>
                <div class="space-y-2 overflow-y-auto" style="max-height:320px">
                    @forelse($approvers ?? [] as $approver)
                        @php
                            $run = (float)($runningTotalsByApprover[$approver->id] ?? 0);
                            $unc = (float)($uncollectedByApprover[$approver->id] ?? 0);
                        @endphp
                        <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-3 transition hover:border-blue-200 hover:bg-white hover:shadow-sm">
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl text-xs font-bold text-white"
                                          style="background:linear-gradient(135deg,#8b5cf6,#d946ef)">
                                        {{ $loop->iteration }}
                                    </span>
                                    <p class="truncate text-sm font-semibold text-slate-800">{{ $approver->name }}</p>
                                </div>
                                <span class="shrink-0 rounded-full bg-white px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-400 ring-1 ring-slate-200">
                                    Approver
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div class="rounded-lg px-2.5 py-2" style="background:#d1fae5">
                                    <p class="text-[9px] font-bold uppercase tracking-widest" style="color:#059669">Running</p>
                                    <p class="mt-0.5 font-bold" style="color:#047857">PHP {{ number_format($run, 2) }}</p>
                                </div>
                                <div class="rounded-lg px-2.5 py-2" style="background:#ffe4e6">
                                    <p class="text-[9px] font-bold uppercase tracking-widest" style="color:#e11d48">Uncollected</p>
                                    <p class="mt-0.5 font-bold" style="color:#be123c">PHP {{ number_format($unc, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-10 text-center">
                            <i class="fas fa-chart-line mb-2 block text-3xl text-slate-300"></i>
                            <p class="text-sm font-medium text-slate-500">No approver data yet</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ── Chart.js Trend ─────────────────────────── --}}
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
                { label:'Income',   data:{!! $chartIncome !!},  backgroundColor:'rgba(16,185,129,0.75)', borderColor:'#10b981', borderWidth:1, borderRadius:6, order:2 },
                { label:'Expenses', data:{!! $chartExpense !!}, backgroundColor:'rgba(244,63,94,0.65)',  borderColor:'#f43f5e', borderWidth:1, borderRadius:6, order:2 },
                { label:'Profit',   data:{!! $chartProfit !!},  type:'line', borderColor:'#3b82f6', backgroundColor:'rgba(59,130,246,0.10)', borderWidth:2.5, pointRadius:4, pointBackgroundColor:'#3b82f6', fill:true, tension:0.35, order:1 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode:'index', intersect:false },
            plugins: {
                legend: { display:false },
                tooltip: { backgroundColor:'#0f172a', titleColor:'#94a3b8', bodyColor:'#e2e8f0', padding:10,
                    callbacks: { label: ctx => ' PHP ' + Number(ctx.raw).toLocaleString('en-PH',{minimumFractionDigits:0}) } }
            },
            scales: {
                x: { grid:{display:false}, ticks:{font:{size:10},color:'#94a3b8'} },
                y: { grid:{color:'#f1f5f9'}, beginAtZero:true,
                     ticks:{font:{size:10},color:'#94a3b8',callback:v=>'PHP '+(v>=1000?(v/1000).toFixed(0)+'k':v)} }
            }
        }
    });
})();
</script>
