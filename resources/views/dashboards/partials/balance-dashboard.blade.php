@php
    $dashboardTitle    = $dashboardTitle ?? 'Dashboard';
    $dashboardSubtitle = $dashboardSubtitle ?? 'Monitor running balances and uncollected amounts per approver.';
    $approvers         = $approvers ?? collect();
    $runTotals         = $runningTotalsByApprover ?? [];
    $uncTotals         = $uncollectedByApprover ?? [];

    $totalRunning     = collect($runTotals)->sum();
    $totalUncollected = collect($uncTotals)->sum();
    $approverCount    = collect($approvers)->count();
    $highestRunning   = collect($runTotals)->max() ?? 0;
    $maxBalance       = max($totalRunning, $totalUncollected, $highestRunning, 1);

    $summaryCards = [
        ['label'=>'Approvers',       'value'=>$approverCount, 'fmt'=>false, 'icon'=>'fa-users',           'bg'=>'bg-blue-50',   'iconCls'=>'text-blue-600',   'ring'=>'ring-blue-200',   'valCls'=>'text-slate-800'],
        ['label'=>'Running Total',   'value'=>$totalRunning,  'fmt'=>true,  'icon'=>'fa-circle-check',    'bg'=>'bg-emerald-50','iconCls'=>'text-emerald-600','ring'=>'ring-emerald-200','valCls'=>'text-emerald-700'],
        ['label'=>'Uncollected',     'value'=>$totalUncollected,'fmt'=>true,'icon'=>'fa-triangle-exclamation','bg'=>'bg-rose-50','iconCls'=>'text-rose-600', 'ring'=>'ring-rose-200',   'valCls'=>'text-rose-700'],
        ['label'=>'Highest Balance', 'value'=>$highestRunning,'fmt'=>true,  'icon'=>'fa-trophy',          'bg'=>'bg-amber-50',  'iconCls'=>'text-amber-600',  'ring'=>'ring-amber-200',  'valCls'=>'text-sky-700'],
    ];
@endphp

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-cyan-50/30 to-blue-50/20 py-4 sm:py-6">
    <div class="mx-auto max-w-7xl px-3 sm:px-4 lg:px-6">

        {{-- ── Header ───────────────────────────────────── --}}
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="mb-2 inline-flex items-center gap-2 rounded-full bg-cyan-600 px-3 py-1 text-xs font-bold text-white shadow-sm shadow-cyan-500/30">
                    <i class="fas fa-chart-pie"></i>
                    Balance Overview
                </div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ $dashboardTitle }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $dashboardSubtitle }}</p>
            </div>
            <div class="shrink-0 flex items-center gap-1.5 rounded-xl bg-white px-3 py-2 text-xs text-slate-500 shadow-sm ring-1 ring-slate-200">
                <i class="fas fa-clock-rotate-left text-slate-400"></i>
                Updated {{ now()->format('M d, Y · h:i A') }}
            </div>
        </div>

        {{-- ── Summary stat cards ────────────────────────── --}}
        <div class="mb-5 grid grid-cols-2 gap-2 sm:grid-cols-4">
            @foreach($summaryCards as $c)
            <div class="flex flex-col gap-2 rounded-2xl border border-white bg-white p-3.5 shadow-sm ring-1 {{ $c['ring'] }} transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl {{ $c['bg'] }} {{ $c['iconCls'] }} ring-1 {{ $c['ring'] }}">
                        <i class="fas {{ $c['icon'] }} text-xs"></i>
                    </span>
                    <span class="text-[9px] font-bold uppercase tracking-widest text-slate-400 text-right leading-tight">{{ $c['label'] }}</span>
                </div>
                <p class="text-lg font-extrabold leading-none {{ $c['valCls'] }} sm:text-xl">
                    @if($c['fmt'])
                        <span class="text-[9px] font-semibold text-slate-500">PHP</span>
                        {{ number_format($c['value'], 0) }}
                    @else
                        {{ number_format($c['value']) }}
                    @endif
                </p>
            </div>
            @endforeach
        </div>

        {{-- ── Collection Health Bar ──────────────────────── --}}
        @if($totalRunning > 0 || $totalUncollected > 0)
        <div class="mb-5 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-sky-50 text-sky-600 ring-1 ring-sky-200">
                        <i class="fas fa-wave-square text-xs"></i>
                    </span>
                    <div>
                        <p class="text-sm font-bold text-slate-800">Collection Health</p>
                        <p class="text-xs text-slate-400">Running vs uncollected overall</p>
                    </div>
                </div>
                @php
                    $collectionRate = $totalRunning > 0 ? round((($totalRunning - $totalUncollected) / $totalRunning) * 100) : 0;
                @endphp
                <span class="rounded-full px-3 py-1 text-xs font-bold
                    {{ $collectionRate >= 75 ? 'bg-emerald-100 text-emerald-700' : ($collectionRate >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">
                    {{ $collectionRate }}% Collected
                </span>
            </div>
            <div class="space-y-2">
                <div>
                    <div class="mb-1 flex justify-between text-xs text-slate-500">
                        <span class="flex items-center gap-1.5"><i class="fas fa-circle-check text-emerald-500 text-[9px]"></i> Running Total</span>
                        <span class="font-bold text-emerald-600">PHP {{ number_format($totalRunning, 2) }}</span>
                    </div>
                    <div class="h-2.5 rounded-full bg-slate-100">
                        <div class="h-2.5 rounded-full bg-gradient-to-r from-emerald-500 to-green-400 transition-all duration-700"
                             style="width: {{ min(100, ($totalRunning / $maxBalance) * 100) }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="mb-1 flex justify-between text-xs text-slate-500">
                        <span class="flex items-center gap-1.5"><i class="fas fa-triangle-exclamation text-rose-500 text-[9px]"></i> Uncollected</span>
                        <span class="font-bold text-rose-600">PHP {{ number_format($totalUncollected, 2) }}</span>
                    </div>
                    <div class="h-2.5 rounded-full bg-slate-100">
                        <div class="h-2.5 rounded-full bg-gradient-to-r from-rose-500 to-red-400 transition-all duration-700"
                             style="width: {{ min(100, ($totalUncollected / $maxBalance) * 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ── Approver Cards ────────────────────────────── --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-violet-50 text-violet-600 ring-1 ring-violet-200">
                        <i class="fas fa-user-check text-xs"></i>
                    </span>
                    <div>
                        <p class="text-sm font-bold text-slate-800">Approver Balances</p>
                        <p class="text-xs text-slate-400">Individual running and uncollected amounts</p>
                    </div>
                </div>
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">
                    {{ $approverCount }} total
                </span>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @forelse($approvers as $approver)
                    @php
                        $run = (float)($runTotals[$approver->id] ?? 0);
                        $unc = (float)($uncTotals[$approver->id] ?? 0);
                        $rw  = min(100, ($run / $maxBalance) * 100);
                        $uw  = min(100, ($unc / $maxBalance) * 100);
                        $net = $run - $unc;
                    @endphp
                    <div class="group rounded-xl border border-slate-200 bg-gradient-to-br from-white to-slate-50/60 p-4 transition hover:border-blue-300 hover:shadow-md hover:-translate-y-0.5">

                        {{-- Approver header --}}
                        <div class="mb-3 flex items-center gap-3">
                            <span class="relative inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-100 to-fuchsia-100 text-violet-700 ring-1 ring-violet-200 font-bold text-sm shadow-sm">
                                {{ $loop->iteration }}
                                <span class="absolute -top-1 -right-1 h-3 w-3 rounded-full bg-emerald-400 ring-2 ring-white"></span>
                            </span>
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-slate-900 text-sm">{{ $approver->name }}</p>
                                <p class="text-[10px] uppercase tracking-widest text-slate-400">Approver</p>
                            </div>
                        </div>

                        {{-- Balance bars --}}
                        <div class="space-y-2.5 mb-3">
                            <div>
                                <div class="mb-1 flex items-center justify-between text-xs">
                                    <span class="text-slate-500 font-medium">Running</span>
                                    <span class="font-bold text-emerald-600">PHP {{ number_format($run, 2) }}</span>
                                </div>
                                <div class="h-2 rounded-full bg-emerald-100">
                                    <div class="h-2 rounded-full bg-gradient-to-r from-emerald-500 to-green-400 transition-all duration-700" style="width:{{ $rw }}%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="mb-1 flex items-center justify-between text-xs">
                                    <span class="text-slate-500 font-medium">Uncollected</span>
                                    <span class="font-bold text-rose-600">PHP {{ number_format($unc, 2) }}</span>
                                </div>
                                <div class="h-2 rounded-full bg-rose-100">
                                    <div class="h-2 rounded-full bg-gradient-to-r from-rose-500 to-red-400 transition-all duration-700" style="width:{{ $uw }}%"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Net collected badge --}}
                        <div class="flex items-center justify-between rounded-lg {{ $net >= 0 ? 'bg-emerald-50' : 'bg-rose-50' }} px-3 py-2">
                            <span class="text-[10px] font-bold uppercase tracking-widest {{ $net >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                Net Balance
                            </span>
                            <span class="text-xs font-extrabold {{ $net >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $net >= 0 ? '+' : '' }}PHP {{ number_format($net, 2) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="sm:col-span-2 xl:col-span-3 rounded-xl border border-dashed border-slate-300 bg-slate-50 py-14 text-center">
                        <i class="fas fa-users-slash text-4xl text-slate-300 block mb-3"></i>
                        <p class="text-sm font-semibold text-slate-500">No approver balance data available</p>
                        <p class="text-xs text-slate-400 mt-1">Once running balance records exist, they will appear here.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>
