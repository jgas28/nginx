@php
    $dashboardTitle = $dashboardTitle ?? 'Dashboard';
    $dashboardSubtitle = $dashboardSubtitle ?? 'Monitor running balances and uncollected amounts per approver.';
    $maxBalance = max(
        collect($runningTotalsByApprover ?? [])->max() ?? 0,
        collect($uncollectedByApprover ?? [])->max() ?? 0,
        1
    );
@endphp

<div class="min-h-screen bg-[radial-gradient(circle_at_top,#dbeafe_0%,#eff6ff_18%,#f8fafc_44%,#ecfeff_72%,#f0fdf4_100%)] py-4">
    <div class="mx-auto max-w-7xl px-4">
        <div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-cyan-50 to-blue-50 px-4 py-2 text-sm font-semibold text-cyan-700 ring-1 ring-cyan-100 shadow-sm">
                    <i class="fas fa-chart-pie"></i>
                    Balance Overview
                </div>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900">{{ $dashboardTitle }}</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-500">{{ $dashboardSubtitle }}</p>
            </div>

            <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-4">
                <div class="rounded-2xl border border-white/70 bg-gradient-to-br from-white via-white to-sky-50/70 px-4 py-3 shadow-sm backdrop-blur transition hover:-translate-y-0.5 hover:shadow-md">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Approvers</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900">{{ collect($approvers)->count() }}</p>
                </div>
                <div class="rounded-2xl border border-white/70 bg-gradient-to-br from-white via-white to-emerald-50/70 px-4 py-3 shadow-sm backdrop-blur transition hover:-translate-y-0.5 hover:shadow-md">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Running Total</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-600">PHP {{ number_format(collect($runningTotalsByApprover ?? [])->sum(), 2) }}</p>
                </div>
                <div class="rounded-2xl border border-white/70 bg-gradient-to-br from-white via-white to-rose-50/70 px-4 py-3 shadow-sm backdrop-blur transition hover:-translate-y-0.5 hover:shadow-md">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Uncollected</p>
                    <p class="mt-2 text-2xl font-bold text-rose-600">PHP {{ number_format(collect($uncollectedByApprover ?? [])->sum(), 2) }}</p>
                </div>
                <div class="rounded-2xl border border-white/70 bg-gradient-to-br from-white via-white to-violet-50/70 px-4 py-3 shadow-sm backdrop-blur transition hover:-translate-y-0.5 hover:shadow-md">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Highest Balance</p>
                    <p class="mt-2 text-2xl font-bold text-sky-600">PHP {{ number_format(collect($runningTotalsByApprover ?? [])->max() ?? 0, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="mb-6 rounded-3xl border border-white/70 bg-gradient-to-br from-white via-white to-cyan-50/60 p-5 shadow-[0_24px_70px_rgba(15,23,42,0.08)] backdrop-blur">
            <div class="mb-4 flex items-center gap-3">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-100 to-cyan-100 text-sky-700 ring-1 ring-sky-200 shadow-sm">
                    <i class="fas fa-wave-square"></i>
                </span>
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Balance Snapshot</h2>
                    <p class="text-sm text-slate-500">Quick visual comparison of running and uncollected totals.</p>
                </div>
            </div>

            <div class="grid gap-3 lg:grid-cols-2">
                @forelse ($approvers as $approver)
                    @php
                        $running = (float) ($runningTotalsByApprover[$approver->id] ?? 0);
                        $uncollected = (float) ($uncollectedByApprover[$approver->id] ?? 0);
                        $runningWidth = min(100, ($running / $maxBalance) * 100);
                        $uncollectedWidth = min(100, ($uncollected / $maxBalance) * 100);
                    @endphp
                    <div class="rounded-2xl border border-slate-200 bg-gradient-to-r from-white via-slate-50/90 to-cyan-50/60 p-4 transition hover:border-blue-200 hover:bg-white hover:shadow-sm">
                        <div class="mb-3 flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-100 to-cyan-100 text-blue-700 ring-1 ring-blue-200 shadow-sm">
                                    <i class="fas fa-user-check text-sm"></i>
                                </span>
                                <div>
                                    <h3 class="text-base font-semibold text-slate-900">{{ $approver->name }}</h3>
                                    <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Approver</p>
                                </div>
                            </div>
                            <span class="inline-flex min-h-[2.5rem] min-w-[2.5rem] items-center justify-center rounded-2xl bg-gradient-to-br from-amber-100 to-orange-100 px-3 text-sm font-bold text-orange-700 ring-1 ring-orange-200 shadow-sm">
                                {{ $loop->iteration }}
                            </span>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <div class="mb-1 flex items-center justify-between text-sm">
                                    <span class="font-medium text-slate-600">Running Total</span>
                                    <span class="font-semibold text-emerald-600">PHP {{ number_format($running, 2) }}</span>
                                </div>
                                <div class="h-2.5 rounded-full bg-emerald-100">
                                    <div class="h-2.5 rounded-full bg-gradient-to-r from-emerald-500 to-green-500" style="width: {{ $runningWidth }}%"></div>
                                </div>
                            </div>

                            <div>
                                <div class="mb-1 flex items-center justify-between text-sm">
                                    <span class="font-medium text-slate-600">Uncollected</span>
                                    <span class="font-semibold text-rose-600">PHP {{ number_format($uncollected, 2) }}</span>
                                </div>
                                <div class="h-2.5 rounded-full bg-rose-100">
                                    <div class="h-2.5 rounded-full bg-gradient-to-r from-rose-500 to-red-500" style="width: {{ $uncollectedWidth }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="lg:col-span-2 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center text-slate-500">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white text-slate-400 ring-1 ring-slate-200">
                            <i class="fas fa-chart-column text-lg"></i>
                        </div>
                        <p class="mt-3 font-medium text-slate-700">No approver balance data available</p>
                        <p class="mt-1 text-sm">Once running balance records exist, they will appear here.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
