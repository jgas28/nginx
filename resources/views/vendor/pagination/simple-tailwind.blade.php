@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="app-pagination flex w-full flex-col gap-3">
        <p class="text-sm leading-6 text-slate-500">
            Page <span class="font-semibold text-slate-800">{{ $paginator->currentPage() }}</span>
        </p>

        <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:items-center">
            @if ($paginator->onFirstPage())
                <span class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-400">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-400">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>
    </nav>
@endif
