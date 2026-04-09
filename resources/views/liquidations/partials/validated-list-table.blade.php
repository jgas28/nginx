@php
    $overviewCards = [
        ['label' => 'For Collection', 'value' => number_format($overview['total'] ?? 0), 'icon' => 'fa-hand-holding-dollar', 'bg' => 'from-emerald-100 to-green-100', 'text' => 'text-emerald-700'],
        ['label' => 'Admin', 'value' => number_format($overview['admin'] ?? 0), 'icon' => 'fa-user-shield', 'bg' => 'from-blue-100 to-indigo-100', 'text' => 'text-blue-700'],
        ['label' => 'RPM', 'value' => number_format($overview['rpm'] ?? 0), 'icon' => 'fa-gas-pump', 'bg' => 'from-amber-100 to-yellow-100', 'text' => 'text-amber-700'],
        ['label' => 'Delivery Related', 'value' => number_format($overview['delivery_related'] ?? 0), 'icon' => 'fa-truck-fast', 'bg' => 'from-cyan-100 to-sky-100', 'text' => 'text-cyan-700'],
    ];
@endphp

<div class="space-y-5 p-5">
    <div class="grid gap-3" style="grid-template-columns: repeat(4, minmax(0, 1fr));">
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

    <div class="grid items-center gap-4 rounded-[24px] border border-slate-200 bg-white p-4" style="grid-template-columns: minmax(0, 720px) auto; justify-content: space-between;">
        <div class="relative min-w-0 max-w-[720px]">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <i class="fas fa-magnifying-glass text-sm"></i>
            </span>
            <input
                type="text"
                id="liquidations-validated-search"
                value="{{ $search }}"
                placeholder="Search CVR, company, supplier, collector, expense..."
                class="w-full rounded-2xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100"
            >
        </div>

        <div class="flex items-center justify-end gap-3 whitespace-nowrap">
            <label for="liquidations-validated-per-page" class="text-sm font-medium text-slate-600">Show</label>
            <select id="liquidations-validated-per-page" class="rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                @foreach ([5, 10, 25, 50] as $size)
                    <option value="{{ $size }}" {{ (int) $perPage === $size ? 'selected' : '' }}>{{ $size }}</option>
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
                        <th class="px-6 py-4">CVR Number</th>
                        <th class="px-6 py-4">Voucher Type</th>
                        <th class="px-6 py-4">Prepared / Noted / Collector</th>
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
                                $companyCode = optional($cashVoucher->company)->company_code ?? 'N/A';
                                $expenseCodeVal = optional($cashVoucher->expenseTypes)->expense_code ?? 'N/A';
                                $details = "-$companyCode$expenseCodeVal";
                            } elseif (in_array($cvrType, ['delivery', 'pullout', 'accessorial', 'freight', 'others'])) {
                                $truckName = optional(optional($liquidation->allocation)->truck)->truck_name ?? 'N/A';
                                $companyCode = optional(optional($liquidation->deliveryRequest)->company)->company_code ?? 'N/A';
                                $expenseCodeVal = optional(optional($liquidation->deliveryRequest)->expenseType)->expense_code ?? 'N/A';
                                $details = "-$truckName-$companyCode$expenseCodeVal";
                            }

                            $typeStyle = match (strtolower((string) $cvrType)) {
                                'admin' => 'bg-blue-50 text-blue-700 ring-blue-100',
                                'rpm' => 'bg-amber-50 text-amber-700 ring-amber-100',
                                'delivery', 'pullout', 'accessorial', 'freight', 'others' => 'bg-cyan-50 text-cyan-700 ring-cyan-100',
                                default => 'bg-slate-100 text-slate-700 ring-slate-200',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 align-top">
                                <div class="flex items-start gap-3">
                                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                        <i class="fas fa-file-invoice-dollar text-sm"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-slate-900">{{ $cvrNumber }}{!! $details !!}</div>
                                        <div class="mt-1 text-xs text-slate-500">
                                            {{ optional($cashVoucher->company)->company_name ?? optional(optional($cashVoucher->deliveryRequest)->company)->company_name ?? 'N/A' }}
                                        </div>
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
                                    <div class="flex items-center gap-2 text-sm text-slate-700">
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                                            <i class="fas fa-hand-holding-dollar text-xs"></i>
                                        </span>
                                        <span>{{ trim(($liquidation->collector->fname ?? '') . ' ' . ($liquidation->collector->lname ?? '')) ?: 'N/A' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 align-top text-slate-500">
                                {{ optional($liquidation->created_at)->format('Y-m-d') ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 align-top">
                                <div class="flex justify-end">
                                    <a href="{{ route('liquidations.validated', $liquidation->id) }}" class="inline-flex min-w-[140px] items-center justify-center gap-2 rounded-2xl border px-4 py-2.5 text-xs font-semibold shadow-md transition" style="background-color:#059669;border-color:#047857;color:#ffffff;">
                                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full" style="background-color:#ffffff;color:#047857;">
                                            <i class="fas fa-money-bill-transfer text-[11px]"></i>
                                        </span>
                                        <span style="color:#ffffff;">Collect</span>
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
                                <p class="mt-3 font-medium text-slate-700">No validated liquidations found</p>
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
            <div class="liquidations-validated-pagination">
                {{ $liquidations->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
</div>
