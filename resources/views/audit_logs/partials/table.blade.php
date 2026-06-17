<div class="overflow-x-auto">
    <table class="min-w-full text-sm text-left text-slate-700">
        <thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500">
            <tr>
                <th class="px-4 py-3 font-semibold sm:px-6">Date</th>
                <th class="px-4 py-3 font-semibold sm:px-6">Actor</th>
                <th class="px-4 py-3 font-semibold sm:px-6">Event</th>
                <th class="px-4 py-3 font-semibold sm:px-6">Record</th>
                <th class="px-4 py-3 font-semibold sm:px-6">Description</th>
                <th class="px-4 py-3 font-semibold sm:px-6">IP Address</th>
                <th class="px-4 py-3 font-semibold sm:px-6 text-right">Diff</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 bg-white">
            @forelse($logs as $log)
                <tr class="hover:bg-slate-50/80">
                    <td class="px-4 py-3.5 text-slate-500 sm:px-6 whitespace-nowrap">{{ optional($log->created_at)->format('M d, Y h:i A') }}</td>
                    <td class="px-4 py-3.5 sm:px-6">{{ $log->actor_name ?? 'System' }}</td>
                    <td class="px-4 py-3.5 sm:px-6">
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-700">{{ $log->event }}</span>
                    </td>
                    <td class="px-4 py-3.5 sm:px-6">{{ $log->auditable_type ? class_basename($log->auditable_type) . ' #' . $log->auditable_id : '—' }}</td>
                    <td class="px-4 py-3.5 sm:px-6">{{ $log->description }}</td>
                    <td class="px-4 py-3.5 text-slate-500 sm:px-6">{{ $log->ip_address }}</td>
                    <td class="px-4 py-3.5 sm:px-6 text-right">
                        @if($log->old_values || $log->new_values)
                            <button type="button" onclick="document.getElementById('diff-{{ $log->id }}').classList.toggle('hidden')" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100">
                                <i class="fas fa-code-compare text-[10px]"></i>
                                View
                            </button>
                        @endif
                    </td>
                </tr>
                @if($log->old_values || $log->new_values)
                    <tr id="diff-{{ $log->id }}" class="hidden bg-slate-50/60">
                        <td colspan="7" class="px-4 py-3 sm:px-6">
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div>
                                    <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Before</p>
                                    <pre class="overflow-x-auto rounded-xl bg-white p-3 text-xs text-slate-700 ring-1 ring-slate-200">{{ $log->old_values ? json_encode($log->old_values, JSON_PRETTY_PRINT) : '—' }}</pre>
                                </div>
                                <div>
                                    <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">After</p>
                                    <pre class="overflow-x-auto rounded-xl bg-white p-3 text-xs text-slate-700 ring-1 ring-slate-200">{{ $log->new_values ? json_encode($log->new_values, JSON_PRETTY_PRINT) : '—' }}</pre>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-slate-500 sm:px-6">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400"><i class="fas fa-clipboard-list text-lg"></i></div>
                        <p class="mt-3 font-medium text-slate-700">No audit log entries found</p>
                        <p class="mt-1 text-sm">Try widening your filters.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="flex flex-col gap-3 border-t border-slate-200 px-4 py-4 text-sm text-slate-500 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
    <p>Showing <span class="font-semibold text-slate-700">{{ $logs->firstItem() ?? 0 }}</span> to <span class="font-semibold text-slate-700">{{ $logs->lastItem() ?? 0 }}</span> of <span class="font-semibold text-slate-700">{{ $logs->total() }}</span> entries</p>
    <div class="audit-logs-pagination">
        {{ $logs->links('pagination::tailwind') }}
    </div>
</div>
