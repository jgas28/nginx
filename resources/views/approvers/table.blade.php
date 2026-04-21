<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200 text-sm text-left text-slate-700">
        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
            <tr>
                <th class="px-4 py-4 sm:px-6">Name</th>
                <th class="px-4 py-4 sm:px-6">Site</th>
                <th class="px-4 py-4 text-right sm:px-6">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
            @forelse($approvers as $approver)
                <tr class="transition hover:bg-slate-50/80">
                    <td class="px-4 py-4 font-medium text-slate-900 sm:px-6">{{ $approver->name }}</td>
                    <td class="px-4 py-4 text-slate-600 sm:px-6">{{ $approver->site }}</td>
                    <td class="px-4 py-4 sm:px-6">
                        <div class="flex flex-col items-stretch justify-end gap-2 sm:flex-row">
                            <a
                                href="{{ route('approvers.edit', $approver) }}"
                                class="inline-flex items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-4 py-2 font-semibold text-amber-700 transition hover:border-amber-300 hover:bg-amber-100"
                            >
                                Edit
                            </a>
                            <form action="{{ route('approvers.destroy', $approver) }}" method="POST" class="w-full sm:w-auto">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="inline-flex w-full items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 py-2 font-semibold text-red-700 transition hover:border-red-300 hover:bg-red-100"
                                >
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-4 py-10 text-center text-sm text-slate-500 sm:px-6">
                        No approvers found for the current search.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="approvers-pagination border-t border-slate-200 px-4 py-4 sm:px-6">
    {{ $approvers->links('pagination::tailwind') }}
</div>
