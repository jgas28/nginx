<div class="bg-white rounded-lg shadow p-6 mb-8" id="compensation-setup-card">
    @php
        $missingCompensationColumns = collect($availableUserColumns ?? [])
            ->filter(fn ($isAvailable) => !$isAvailable)
            ->keys()
            ->values();
    @endphp

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Compensation Setup</h2>
            <p class="text-sm text-gray-600 mt-1">Set daily rate, fixed monthly salary, and mandatory government numbers per employee.</p>
        </div>
        <form method="GET" action="{{ route('hr.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center" data-compensation-search-form>
            <input type="hidden" name="search" value="{{ $search }}">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="partial_compensation" value="1">
            <input
                type="text"
                name="comp_search"
                value="{{ $compensationSearch }}"
                placeholder="Search employee, code, or position..."
                class="w-full sm:w-80 px-4 py-2 border border-gray-300 rounded-lg"
            >
            <div class="flex items-center gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
                <a href="{{ route('hr.index', ['search' => $search, 'status' => $status, 'month' => $month, 'partial_compensation' => 1]) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg font-medium" data-compensation-link>
                    <i class="fas fa-redo"></i>
                </a>
            </div>
        </form>
    </div>

    @if($missingCompensationColumns->isNotEmpty())
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <p class="font-medium">Compensation fields are not ready in the database yet.</p>
            <p class="mt-1">
                Missing columns:
                {{ $missingCompensationColumns->implode(', ') }}.
                Save will stay disabled until these fields exist in the <code>users</code> table.
            </p>
        </div>
    @endif

    <p class="text-sm text-gray-500 mb-4">Showing {{ $employees->count() }} of {{ $employees->total() }} employee{{ $employees->total() === 1 ? '' : 's' }} in compensation setup.</p>

    <form method="POST" action="{{ route('hr.updateDailyRates') }}">
        @csrf
        <input type="hidden" name="search" value="{{ $search }}">
        <input type="hidden" name="status" value="{{ $status }}">
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="comp_search" value="{{ $compensationSearch }}">
        <input type="hidden" name="employees_page" value="{{ $employees->currentPage() }}">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-100 text-gray-900 font-semibold">
                    <tr>
                        <th class="px-4 py-3">Employee</th>
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Position</th>
                        <th class="px-4 py-3">Employment Status</th>
                        <th class="px-4 py-3">Daily Rate</th>
                        <th class="px-4 py-3">Monthly Salary</th>
                        <th class="px-4 py-3">SSS</th>
                        <th class="px-4 py-3">PhilHealth</th>
                        <th class="px-4 py-3">TIN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($employees as $employee)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $employee->fname }} {{ $employee->lname }}</td>
                            <td class="px-4 py-3">{{ $employee->employee_code }}</td>
                            <td class="px-4 py-3">{{ $employee->position ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ ucfirst($employee->employment_status ?? 'N/A') }}</td>
                            <td class="px-4 py-3">
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="daily_rates[{{ $employee->id }}]"
                                    value="{{ old('daily_rates.' . $employee->id, $employee->daily_rate ?? 0) }}"
                                    class="w-40 px-3 py-2 border border-gray-300 rounded-lg"
                                >
                            </td>
                            <td class="px-4 py-3">
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="monthly_salaries[{{ $employee->id }}]"
                                    value="{{ old('monthly_salaries.' . $employee->id, $employee->monthly_salary ?? 0) }}"
                                    class="w-40 px-3 py-2 border border-gray-300 rounded-lg"
                                >
                            </td>
                            <td class="px-4 py-3">
                                <input
                                    type="text"
                                    name="sss_nos[{{ $employee->id }}]"
                                    value="{{ old('sss_nos.' . $employee->id, $employee->sss_no) }}"
                                    class="w-40 px-3 py-2 border border-gray-300 rounded-lg"
                                >
                            </td>
                            <td class="px-4 py-3">
                                <input
                                    type="text"
                                    name="philhealth_nos[{{ $employee->id }}]"
                                    value="{{ old('philhealth_nos.' . $employee->id, $employee->philhealth_no) }}"
                                    class="w-40 px-3 py-2 border border-gray-300 rounded-lg"
                                >
                            </td>
                            <td class="px-4 py-3">
                                <input
                                    type="text"
                                    name="tin_nos[{{ $employee->id }}]"
                                    value="{{ old('tin_nos.' . $employee->id, $employee->tin_no) }}"
                                    class="w-40 px-3 py-2 border border-gray-300 rounded-lg"
                                >
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-gray-500">No employees found for the current search.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            <button
                type="submit"
                class="bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-6 py-2 rounded-lg font-medium"
                @disabled($missingCompensationColumns->isNotEmpty())
            >
                <i class="fas fa-save mr-2"></i>Save Compensation Setup
            </button>
        </div>
    </form>

    <div class="mt-4" data-compensation-pagination>
        {{ $employees->appends(['partial_compensation' => 1])->links() }}
    </div>
</div>
