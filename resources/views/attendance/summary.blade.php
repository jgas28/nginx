@extends('layouts.app')

@section('title', 'Attendance Summary')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Attendance Summary</h1>
            <p class="text-gray-600 mt-2">Monthly employee attendance summary with time in and time out details.</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Filter Summary</h2>

            <form method="GET" action="{{ route('attendance.summary') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                        <input type="month" name="month" value="{{ $selectedMonth }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                        <div class="relative">
                            <input
                                type="text"
                                id="summary_employee_search"
                                name="employee"
                                value="{{ $employee }}"
                                placeholder="Search name or code..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                                autocomplete="off"
                            >
                            <div
                                id="summary_employee_suggestions"
                                class="absolute z-20 mt-1 hidden max-h-64 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg"
                            >
                                @foreach($employees as $employeeOption)
                                    @php
                                        $employeeLabel = trim($employeeOption->fname . ' ' . $employeeOption->lname) . ' - ' . $employeeOption->employee_code;
                                    @endphp
                                    <button
                                        type="button"
                                        class="summary-employee-suggestion flex w-full items-center justify-between px-4 py-3 text-left text-sm text-gray-700 hover:bg-blue-50"
                                        data-label="{{ $employeeLabel }}"
                                        data-user-id="{{ $employeeOption->id }}"
                                    >
                                        <span>{{ $employeeOption->fname }} {{ $employeeOption->lname }}</span>
                                        <span class="text-xs text-gray-500">{{ $employeeOption->employee_code }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <input type="hidden" id="summary_employee_id" name="user_id" value="{{ $employeeId }}">
                        <p class="mt-2 text-xs text-gray-500">Type the employee name or code, then pick one from the suggestion list to load the summary.</p>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium">
                            <i class="fas fa-search mr-2"></i>Search
                        </button>
                        <a href="{{ route('attendance.summary') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-medium">
                            <i class="fas fa-redo mr-2"></i>Reset
                        </a>
                    </div>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('attendance.summary.excel', request()->only(['month', 'employee', 'user_id'])) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg font-medium {{ !$hasEmployeeFilter ? 'pointer-events-none opacity-50' : '' }}">
                        <i class="fas fa-file-excel mr-2"></i>Export Excel
                    </a>
                    <a href="{{ route('attendance.summary.pdf', request()->only(['month', 'employee', 'user_id'])) }}" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium {{ !$hasEmployeeFilter ? 'pointer-events-none opacity-50' : '' }}">
                        <i class="fas fa-file-pdf mr-2"></i>Download PDF
                    </a>
                </div>
            </form>
        </div>

        @if(!$hasEmployeeFilter)
            <div class="bg-white rounded-lg shadow p-10 text-center text-gray-500">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                    <i class="fas fa-search text-2xl"></i>
                </div>
                <p class="text-lg font-semibold text-gray-800">Search an employee to view attendance summary.</p>
                <p class="mt-2 text-sm text-gray-500">Choose a month, type an employee name or code, then select the employee from the suggestion list.</p>
            </div>
        @else
        @forelse($employeeSummaries as $summary)
            <div class="bg-white rounded-lg shadow mb-6 overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">{{ $summary->employee_name }}</h2>
                            <p class="text-sm text-gray-600">{{ $summary->employee_code }} | {{ $summary->position }}</p>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                            <div class="bg-green-50 rounded-lg px-4 py-3">
                                <div class="text-gray-600">Present</div>
                                <div class="text-lg font-bold text-green-700">{{ $summary->present_count }}</div>
                            </div>
                            <div class="bg-yellow-50 rounded-lg px-4 py-3">
                                <div class="text-gray-600">Late</div>
                                <div class="text-lg font-bold text-yellow-700">{{ $summary->late_count }}</div>
                            </div>
                            <div class="bg-red-50 rounded-lg px-4 py-3">
                                <div class="text-gray-600">Absent</div>
                                <div class="text-lg font-bold text-red-700">{{ $summary->absent_count }}</div>
                            </div>
                            <div class="bg-blue-50 rounded-lg px-4 py-3">
                                <div class="text-gray-600">Hours</div>
                                <div class="text-lg font-bold text-blue-700">{{ number_format($summary->total_hours, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Schedule Table</h3>
                                    <p class="text-sm text-gray-500 mt-1">Detailed attendance schedule for the selected month.</p>
                                </div>
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                <div>
                                    <label class="sr-only" for="schedule-search-{{ $summary->user_id }}">Search Schedule</label>
                                    <input
                                        type="text"
                                        id="schedule-search-{{ $summary->user_id }}"
                                        placeholder="Search date, status, time, remarks..."
                                        class="schedule-search-input w-full sm:w-80 px-4 py-2 border border-gray-300 rounded-lg"
                                        data-target-table="schedule-table-{{ $summary->user_id }}"
                                        data-target-count="schedule-count-{{ $summary->user_id }}"
                                        data-target-pagination="schedule-pagination-{{ $summary->user_id }}"
                                        data-target-page-indicator="schedule-page-indicator-{{ $summary->user_id }}"
                                        data-target-prev="schedule-prev-{{ $summary->user_id }}"
                                        data-target-next="schedule-next-{{ $summary->user_id }}"
                                    >
                                </div>
                                <span id="schedule-count-{{ $summary->user_id }}" class="inline-flex items-center rounded-full bg-white px-3 py-2 text-sm text-gray-600 border border-gray-200">
                                    {{ min(5, $summary->records->count()) }} of {{ $summary->records->count() }} rows
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="max-h-[420px] overflow-auto">
                    <table id="schedule-table-{{ $summary->user_id }}" class="w-full text-sm text-left text-gray-600">
                        <thead class="sticky top-0 z-10 bg-gray-100 text-gray-900 font-semibold shadow-sm">
                            <tr>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Time In</th>
                                <th class="px-4 py-3">Time Out</th>
                                <th class="px-4 py-3">Total Hours</th>
                                <th class="px-4 py-3">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($summary->records as $record)
                                @php
                                    $displayTimeIn = $record->time_in
                                        ? $record->time_in->format('h:i A')
                                        : ($record->status === 'Present' ? '08:00 AM' : '-');
                                    $displayTimeOut = $record->time_out
                                        ? $record->time_out->format('h:i A')
                                        : ($record->status === 'Present' ? '05:00 PM' : '-');
                                    $displayTotalHours = $record->total_hours
                                        ? number_format($record->total_hours, 2)
                                        : ($record->status === 'Present' ? '8.00' : '-');
                                @endphp
                                <tr class="schedule-row hover:bg-gray-50" data-row-index="{{ $loop->index }}">
                                    <td class="px-4 py-3">{{ $record->date->format('M d, Y') }}</td>
                                    <td class="px-4 py-3">{{ $record->status }}</td>
                                    <td class="px-4 py-3">{{ $displayTimeIn }}</td>
                                    <td class="px-4 py-3">{{ $displayTimeOut }}</td>
                                    <td class="px-4 py-3">{{ $displayTotalHours }}</td>
                                    <td class="px-4 py-3">{{ $record->remarks ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                    <div id="schedule-pagination-{{ $summary->user_id }}" class="flex items-center justify-end gap-2 px-6 py-4 border-t border-gray-200 bg-white">
                        <button type="button" id="schedule-prev-{{ $summary->user_id }}" class="schedule-prev rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50" data-target-table="schedule-table-{{ $summary->user_id }}" data-target-count="schedule-count-{{ $summary->user_id }}" data-target-page-indicator="schedule-page-indicator-{{ $summary->user_id }}">
                            Previous
                        </button>
                        <span id="schedule-page-indicator-{{ $summary->user_id }}" class="min-w-[88px] text-center text-sm font-medium text-gray-700">Page 1 of {{ max(1, (int) ceil($summary->records->count() / 5)) }}</span>
                        <button type="button" id="schedule-next-{{ $summary->user_id }}" class="schedule-next rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50" data-target-table="schedule-table-{{ $summary->user_id }}" data-target-count="schedule-count-{{ $summary->user_id }}" data-target-page-indicator="schedule-page-indicator-{{ $summary->user_id }}">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-10 text-center text-gray-500">
                <i class="fas fa-inbox text-3xl mb-3"></i>
                <p>No attendance summary found for the selected filters.</p>
            </div>
        @endforelse
        @endif
    </div>
</div>

<script>
const summaryEmployeeSearchInput = document.getElementById('summary_employee_search');
const summaryEmployeeIdInput = document.getElementById('summary_employee_id');
const summaryEmployeeSuggestions = document.getElementById('summary_employee_suggestions');
const summaryEmployeeSuggestionItems = Array.from(document.querySelectorAll('.summary-employee-suggestion'));
const scheduleSearchInputs = Array.from(document.querySelectorAll('.schedule-search-input'));
const scheduleTableStates = {};

function hideSummaryEmployeeSuggestions() {
    summaryEmployeeSuggestions?.classList.add('hidden');
}

function showSummaryEmployeeSuggestions() {
    if (!summaryEmployeeSuggestions) {
        return;
    }

    summaryEmployeeSuggestions.classList.remove('hidden');
}

function filterSummaryEmployeeSuggestions() {
    if (!summaryEmployeeSearchInput || !summaryEmployeeSuggestions) {
        return;
    }

    const keyword = summaryEmployeeSearchInput.value.trim().toLowerCase();
    let visibleCount = 0;

    if (summaryEmployeeIdInput) {
        summaryEmployeeIdInput.value = '';
    }

    summaryEmployeeSuggestionItems.forEach((item) => {
        const label = (item.dataset.label || '').toLowerCase();
        const shouldShow = keyword === '' || label.includes(keyword);
        item.classList.toggle('hidden', !shouldShow);

        if (shouldShow) {
            visibleCount += 1;
        }
    });

    if (visibleCount > 0) {
        showSummaryEmployeeSuggestions();
    } else {
        hideSummaryEmployeeSuggestions();
    }
}

summaryEmployeeSearchInput?.addEventListener('focus', filterSummaryEmployeeSuggestions);
summaryEmployeeSearchInput?.addEventListener('input', filterSummaryEmployeeSuggestions);

summaryEmployeeSuggestionItems.forEach((item) => {
    item.addEventListener('click', function () {
        if (!summaryEmployeeSearchInput) {
            return;
        }

        summaryEmployeeSearchInput.value = item.dataset.label || '';
        if (summaryEmployeeIdInput) {
            summaryEmployeeIdInput.value = item.dataset.userId || '';
        }
        hideSummaryEmployeeSuggestions();
    });
});

document.addEventListener('click', function (event) {
    if (!summaryEmployeeSearchInput || !summaryEmployeeSuggestions) {
        return;
    }

    if (!summaryEmployeeSearchInput.closest('.relative')?.contains(event.target)) {
        hideSummaryEmployeeSuggestions();
    }
});

scheduleSearchInputs.forEach((input) => {
    input.addEventListener('input', function () {
        const tableId = input.dataset.targetTable;
        const countId = input.dataset.targetCount;
        const state = scheduleTableStates[tableId];

        if (!state) {
            return;
        }

        const keyword = input.value.trim().toLowerCase();
        state.filteredRows = state.allRows.filter((row) => keyword === '' || row.textContent.toLowerCase().includes(keyword));
        state.currentPage = 1;
        renderScheduleTablePage(tableId, countId);
    });
});

function renderScheduleTablePage(tableId, countId) {
    const state = scheduleTableStates[tableId];
    const count = document.getElementById(countId);

    if (!state || !count) {
        return;
    }

    const pageSize = 5;
    const totalRows = state.filteredRows.length;
    const totalPages = Math.max(1, Math.ceil(totalRows / pageSize));

    if (state.currentPage > totalPages) {
        state.currentPage = totalPages;
    }

    const startIndex = totalRows === 0 ? 0 : (state.currentPage - 1) * pageSize;
    const endIndex = Math.min(startIndex + pageSize, totalRows);
    const visibleRows = new Set(state.filteredRows.slice(startIndex, endIndex));

    state.allRows.forEach((row) => {
        row.classList.toggle('hidden', !visibleRows.has(row));
    });

    count.textContent = `${totalRows === 0 ? 0 : startIndex + 1} to ${endIndex} of ${totalRows} rows`;
    document.getElementById(state.pageIndicatorId).textContent = `Page ${state.currentPage} of ${totalPages}`;
    document.getElementById(state.prevButtonId).disabled = state.currentPage <= 1;
    document.getElementById(state.nextButtonId).disabled = state.currentPage >= totalPages;
}

document.querySelectorAll('[id^="schedule-table-"]').forEach((table) => {
    const tableId = table.id;
    const countId = tableId.replace('schedule-table-', 'schedule-count-');
    const pageIndicatorId = tableId.replace('schedule-table-', 'schedule-page-indicator-');
    const prevButtonId = tableId.replace('schedule-table-', 'schedule-prev-');
    const nextButtonId = tableId.replace('schedule-table-', 'schedule-next-');

    scheduleTableStates[tableId] = {
        allRows: Array.from(table.querySelectorAll('.schedule-row')),
        filteredRows: Array.from(table.querySelectorAll('.schedule-row')),
        currentPage: 1,
        pageIndicatorId,
        prevButtonId,
        nextButtonId,
    };

    document.getElementById(prevButtonId)?.addEventListener('click', function () {
        const state = scheduleTableStates[tableId];
        if (!state || state.currentPage <= 1) {
            return;
        }

        state.currentPage -= 1;
        renderScheduleTablePage(tableId, countId);
    });

    document.getElementById(nextButtonId)?.addEventListener('click', function () {
        const state = scheduleTableStates[tableId];
        if (!state) {
            return;
        }

        const totalPages = Math.max(1, Math.ceil(state.filteredRows.length / 5));
        if (state.currentPage >= totalPages) {
            return;
        }

        state.currentPage += 1;
        renderScheduleTablePage(tableId, countId);
    });

    renderScheduleTablePage(tableId, countId);
});
</script>
@endsection
