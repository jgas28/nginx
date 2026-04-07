@extends('layouts.app')

@section('title', 'Add Attendance')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Add Attendance</h1>
                <p class="text-gray-600 mt-2">Select one employee, then check only the dates that employee is present.</p>
            </div>
            <a href="{{ route('attendance.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>Back to Attendance
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <form method="GET" action="{{ route('attendance.create') }}" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2">
                    @php
                        $selectedEmployeeLabel = $selectedUser
                            ? trim($selectedUser->fname . ' ' . $selectedUser->lname) . ' - ' . $selectedUser->employee_code
                            : '';
                    @endphp
                    <label for="employee_search" class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                    <div class="relative">
                        <input
                            type="text"
                            id="employee_search"
                            value="{{ old('employee_search', $selectedEmployeeLabel) }}"
                            placeholder="Search employee name or code..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            autocomplete="off"
                            required
                        >
                        <div
                            id="employee_suggestions"
                            class="absolute z-20 mt-1 hidden max-h-64 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg"
                        >
                            @foreach($employees as $employee)
                                <button
                                    type="button"
                                    class="employee-suggestion-item flex w-full items-center justify-between px-4 py-3 text-left text-sm text-gray-700 hover:bg-blue-50"
                                    data-user-id="{{ $employee->id }}"
                                    data-label="{{ $employee->fname }} {{ $employee->lname }} - {{ $employee->employee_code }}"
                                >
                                    <span>{{ $employee->fname }} {{ $employee->lname }}</span>
                                    <span class="text-xs text-gray-500">{{ $employee->employee_code }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <input type="hidden" id="user_id" name="user_id" value="{{ old('user_id', request('user_id')) }}">
                </div>

                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                    <input
                        type="month"
                        id="month"
                        name="month"
                        value="{{ $selectedMonth }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required
                    >
                </div>

                <div class="md:col-span-3">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                        <i class="fas fa-search mr-2"></i>Load Attendance Dates
                    </button>
                </div>
            </form>
        </div>

        @if($selectedUser)
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Present Dates</h2>
                        <p class="text-sm text-gray-600 mt-1">
                            Employee: <strong>{{ $selectedUser->fname }} {{ $selectedUser->lname }}</strong>
                            | Month: <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y') }}</strong>
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-sm font-medium text-blue-700">
                            Present Dates: <span id="present-dates-count" class="ml-1">{{ count($selectedPresentDates) }}</span>
                        </span>
                        <button type="button" id="check-all-dates" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-check-square mr-2"></i>Check All
                        </button>
                        <button type="button" id="uncheck-all-dates" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-square mr-2"></i>Uncheck All
                        </button>
                    </div>
                </div>

                <form method="POST" action="{{ route('attendance.store') }}" class="p-6">
                    @csrf
                    <input type="hidden" name="mode" value="employee_dates">
                    <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                    <input type="hidden" name="month" value="{{ $selectedMonth }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach($employeeDateOptions as $dateOption)
                            <label class="flex items-center justify-between gap-4 rounded-lg border border-gray-200 px-4 py-3 hover:border-blue-300 hover:bg-blue-50/40">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $dateOption->date->format('M d, Y') }}</div>
                                    <div class="text-sm text-gray-500">{{ $dateOption->date->format('l') }}</div>
                                </div>
                                <input
                                    type="checkbox"
                                    class="present-date-checkbox h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    name="present_dates[]"
                                    value="{{ $dateOption->date_key }}"
                                    {{ in_array($dateOption->date_key, old('present_dates', $selectedPresentDates), true) ? 'checked' : '' }}
                                >
                            </label>
                        @endforeach
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">
                            <i class="fas fa-save mr-2"></i>Save Present Dates
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</div>

<script>
const employeeSearchInput = document.getElementById('employee_search');
const employeeIdInput = document.getElementById('user_id');
const employeeSuggestions = document.getElementById('employee_suggestions');
const employeeSuggestionItems = Array.from(document.querySelectorAll('.employee-suggestion-item'));
const presentDateCheckboxes = Array.from(document.querySelectorAll('.present-date-checkbox'));
const presentDatesCount = document.getElementById('present-dates-count');
const checkAllDatesButton = document.getElementById('check-all-dates');
const uncheckAllDatesButton = document.getElementById('uncheck-all-dates');

function hideEmployeeSuggestions() {
    employeeSuggestions?.classList.add('hidden');
}

function showEmployeeSuggestions() {
    if (!employeeSuggestions) {
        return;
    }

    employeeSuggestions.classList.remove('hidden');
}

function filterEmployeeSuggestions() {
    if (!employeeSearchInput || !employeeSuggestions || !employeeIdInput) {
        return;
    }

    const keyword = employeeSearchInput.value.trim().toLowerCase();
    let visibleCount = 0;

    employeeSuggestionItems.forEach((item) => {
        const label = (item.dataset.label || '').toLowerCase();
        const shouldShow = keyword === '' || label.includes(keyword);
        item.classList.toggle('hidden', !shouldShow);

        if (shouldShow) {
            visibleCount += 1;
        }
    });

    employeeIdInput.value = '';

    if (visibleCount > 0) {
        showEmployeeSuggestions();
    } else {
        hideEmployeeSuggestions();
    }
}

function updatePresentDatesCount() {
    if (!presentDatesCount) {
        return;
    }

    presentDatesCount.textContent = presentDateCheckboxes.filter((checkbox) => checkbox.checked).length;
}

presentDateCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener('change', updatePresentDatesCount);
});

checkAllDatesButton?.addEventListener('click', function () {
    presentDateCheckboxes.forEach((checkbox) => {
        checkbox.checked = true;
    });

    updatePresentDatesCount();
});

uncheckAllDatesButton?.addEventListener('click', function () {
    presentDateCheckboxes.forEach((checkbox) => {
        checkbox.checked = false;
    });

    updatePresentDatesCount();
});

employeeSearchInput?.addEventListener('focus', filterEmployeeSuggestions);
employeeSearchInput?.addEventListener('input', filterEmployeeSuggestions);

employeeSuggestionItems.forEach((item) => {
    item.addEventListener('click', function () {
        if (!employeeSearchInput || !employeeIdInput) {
            return;
        }

        employeeSearchInput.value = item.dataset.label || '';
        employeeIdInput.value = item.dataset.userId || '';
        hideEmployeeSuggestions();
    });
});

document.addEventListener('click', function (event) {
    if (!employeeSearchInput || !employeeSuggestions) {
        return;
    }

    if (!employeeSearchInput.closest('.relative')?.contains(event.target)) {
        hideEmployeeSuggestions();
    }
});

if (employeeIdInput?.value) {
    const selectedItem = employeeSuggestionItems.find((item) => item.dataset.userId === employeeIdInput.value);

    if (selectedItem && employeeSearchInput) {
        employeeSearchInput.value = selectedItem.dataset.label || '';
    }
}

updatePresentDatesCount();
</script>
@endsection
