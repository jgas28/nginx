@extends('layouts.app')

@section('title', 'Attendance Management')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Attendance Management</h1>
            <p class="text-gray-600 mt-2">Track and manage employee attendance records</p>
        </div>

        <!-- Dashboard Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Records Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Total Records</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalRecords }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-4">
                        <i class="fas fa-chart-bar text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Present Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Present</p>
                        <p class="text-3xl font-bold text-green-600 mt-2">{{ $presentCount }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-4">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Late Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Late</p>
                        <p class="text-3xl font-bold text-yellow-600 mt-2">{{ $lateCount }}</p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-4">
                        <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Absent Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Absent</p>
                        <p class="text-3xl font-bold text-red-600 mt-2">{{ $absentCount }}</p>
                    </div>
                    <div class="bg-red-100 rounded-full p-4">
                        <i class="fas fa-times-circle text-red-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Month Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">This Month</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Present:</span>
                        <span class="font-bold text-green-600">{{ $monthlyPresent }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Late:</span>
                        <span class="font-bold text-yellow-600">{{ $monthlyLate }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Absent:</span>
                        <span class="font-bold text-red-600">{{ $monthlyAbsent }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Actions -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Filter & Search</h2>
            
            <form method="GET" action="{{ route('attendance.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Employee Name</label>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or code..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Status</option>
                            <option value="Present" {{ $status === 'Present' ? 'selected' : '' }}>Present</option>
                            <option value="Late" {{ $status === 'Late' ? 'selected' : '' }}>Late</option>
                            <option value="Absent" {{ $status === 'Absent' ? 'selected' : '' }}>Absent</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium">
                        <i class="fas fa-search mr-2"></i>Search
                    </button>
                    <a href="{{ route('attendance.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-medium">
                        <i class="fas fa-redo mr-2"></i>Reset
                    </a>
                    <a href="{{ route('attendance.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium ml-auto">
                        <i class="fas fa-plus mr-2"></i>Add Attendance
                    </a>
                </div>
            </form>
        </div>

        <!-- Attendance Groups Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Attendance by Employee</h2>
                <p class="mt-1 text-sm text-gray-500">Each row shows one employee. Click View Summary to open the detailed attendance modal.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="bg-gray-100 text-gray-900 font-semibold">
                        <tr>
                            <th class="px-6 py-3">Employee</th>
                            <th class="px-6 py-3">Employee Code</th>
                            <th class="px-6 py-3">Position</th>
                            <th class="px-6 py-3">Records</th>
                            <th class="px-6 py-3">Present</th>
                            <th class="px-6 py-3">Late</th>
                            <th class="px-6 py-3">Absent</th>
                            <th class="px-6 py-3">Total Hours</th>
                            <th class="px-6 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($attendanceGroups as $employee)
                            @php
                                $employeeRecords = $employee->attendances;
                                $presentRecords = $employeeRecords->where('status', 'Present')->count();
                                $lateRecords = $employeeRecords->where('status', 'Late')->count();
                                $absentRecords = $employeeRecords->where('status', 'Absent')->count();
                                $totalHours = number_format($employeeRecords->sum(function ($record) {
                                    if (!is_null($record->total_hours)) {
                                        return (float) $record->total_hours;
                                    }

                                    return in_array($record->status, ['Present', 'Late'], true) ? 8.0 : 0.0;
                                }), 2);
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $employee->fname }} {{ $employee->lname }}</td>
                                <td class="px-6 py-4">{{ $employee->employee_code }}</td>
                                <td class="px-6 py-4">{{ $employee->position ?? 'N/A' }}</td>
                                <td class="px-6 py-4">{{ $employeeRecords->count() }}</td>
                                <td class="px-6 py-4"><span class="font-semibold text-green-600">{{ $presentRecords }}</span></td>
                                <td class="px-6 py-4"><span class="font-semibold text-yellow-600">{{ $lateRecords }}</span></td>
                                <td class="px-6 py-4"><span class="font-semibold text-red-600">{{ $absentRecords }}</span></td>
                                <td class="px-6 py-4 font-semibold text-blue-700">{{ $totalHours }} hrs</td>
                                <td class="px-6 py-4">
                                    <button type="button" onclick="openAttendanceSummaryModal({{ $employee->id }})" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                                        <i class="fas fa-eye mr-2"></i>View Summary
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-3xl mb-2"></i>
                                    <p>No attendance records found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $attendanceGroups->links() }}
            </div>
        </div>
    </div>
</div>

<div id="attendanceSummaryModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4 py-6 backdrop-blur-sm">
    <div class="w-full max-w-3xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
        <div class="flex items-start justify-between border-b border-gray-200 bg-gradient-to-r from-slate-50 to-blue-50 px-6 py-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-600">Attendance Summary</p>
                <h3 id="attendanceModalEmployeeName" class="mt-2 text-2xl font-semibold text-gray-900">Employee Summary</h3>
                <p id="attendanceModalEmployeeMeta" class="mt-1 text-sm text-gray-500">Attendance details</p>
            </div>
            <button type="button" onclick="closeAttendanceSummaryModal()" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-400 transition hover:border-gray-300 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <div class="px-6 py-5">
            <div class="mb-4 overflow-x-auto pb-1">
                <div class="flex min-w-max flex-nowrap gap-1.5">
                    <div class="flex min-w-[118px] items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 shadow-sm">
                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-white text-slate-600 shadow-sm">
                            <i class="fas fa-clipboard-list text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[9px] font-semibold uppercase tracking-[0.14em] text-slate-500">Records</p>
                            <p id="attendanceModalTotalRecords" class="mt-0.5 text-sm font-bold leading-none text-slate-900">0</p>
                        </div>
                    </div>
                    <div class="flex min-w-[118px] items-center gap-2 rounded-lg border border-green-100 bg-green-50 px-2 py-1.5 shadow-sm">
                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-white text-green-600 shadow-sm">
                            <i class="fas fa-check-circle text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[9px] font-semibold uppercase tracking-[0.14em] text-green-600">Present</p>
                            <p id="attendanceModalPresentCount" class="mt-0.5 text-sm font-bold leading-none text-green-700">0</p>
                        </div>
                    </div>
                    <div class="flex min-w-[118px] items-center gap-2 rounded-lg border border-yellow-100 bg-yellow-50 px-2 py-1.5 shadow-sm">
                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-white text-yellow-600 shadow-sm">
                            <i class="fas fa-clock text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[9px] font-semibold uppercase tracking-[0.14em] text-yellow-700">Late</p>
                            <p id="attendanceModalLateCount" class="mt-0.5 text-sm font-bold leading-none text-yellow-700">0</p>
                        </div>
                    </div>
                    <div class="flex min-w-[118px] items-center gap-2 rounded-lg border border-red-100 bg-red-50 px-2 py-1.5 shadow-sm">
                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-white text-red-600 shadow-sm">
                            <i class="fas fa-times-circle text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[9px] font-semibold uppercase tracking-[0.14em] text-red-600">Absent</p>
                            <p id="attendanceModalAbsentCount" class="mt-0.5 text-sm font-bold leading-none text-red-700">0</p>
                        </div>
                    </div>
                    <div class="flex min-w-[130px] items-center gap-2 rounded-lg border border-blue-100 bg-blue-50 px-2 py-1.5 shadow-sm">
                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-white text-blue-600 shadow-sm">
                            <i class="fas fa-hourglass-half text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[9px] font-semibold uppercase tracking-[0.14em] text-blue-600">Hours</p>
                            <p id="attendanceModalTotalHours" class="mt-0.5 text-sm font-bold leading-none text-blue-700">0.00</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3 rounded-xl border border-gray-200 bg-gray-50 px-3 py-3">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div class="min-w-0">
                        <h4 class="text-base font-semibold text-gray-900">Daily Attendance Log</h4>
                        <p class="text-xs text-gray-500">Review time in, time out, status, and rendered hours.</p>
                    </div>
                    <div class="flex w-full flex-col gap-2 sm:flex-row md:w-auto md:items-center">
                        <div class="relative w-full sm:w-64">
                            <i class="fas fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="text" id="attendanceModalSearch" placeholder="Search date or status..." class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div class="relative w-full sm:w-28">
                            <select id="attendanceModalPageSize" class="w-full appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 pr-8 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                <option value="5" selected>5 rows</option>
                                <option value="10">10 rows</option>
                                <option value="15">15 rows</option>
                            </select>
                            <i class="fas fa-chevron-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm">
                <div class="max-h-[18rem] overflow-auto">
                <table class="min-w-full text-sm">
                    <thead class="sticky top-0 z-10 bg-gray-100 text-left text-gray-700">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Date</th>
                            <th class="px-4 py-3 font-semibold">Time In</th>
                            <th class="px-4 py-3 font-semibold">Time Out</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold">Total Hours</th>
                            <th class="px-4 py-3 font-semibold">Remarks</th>
                        </tr>
                    </thead>
                    <tbody id="attendanceModalRows" class="divide-y divide-gray-200 text-gray-700"></tbody>
                </table>
                </div>
                <div class="flex flex-col gap-3 border-t border-gray-200 bg-white px-4 py-3 text-sm text-gray-500 sm:flex-row sm:items-center sm:justify-between">
                    <p id="attendanceModalPaginationText">Showing 0 to 0 of 0 rows</p>
                    <div class="flex items-center gap-2">
                        <button type="button" id="attendanceModalPrev" class="rounded-lg border border-gray-300 px-3 py-1.5 text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50">
                            Previous
                        </button>
                        <span id="attendanceModalPageIndicator" class="min-w-[88px] text-center font-medium text-gray-700">Page 1 of 1</span>
                        <button type="button" id="attendanceModalNext" class="rounded-lg border border-gray-300 px-3 py-1.5 text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between border-t border-gray-200 bg-gray-50 px-6 py-4">
            <p class="text-sm text-gray-500">Tip: use the search box to quickly filter this employee's attendance rows.</p>
            <button type="button" onclick="closeAttendanceSummaryModal()" class="rounded-xl bg-slate-900 px-4 py-2 text-white transition hover:bg-slate-700">
                Close Summary
            </button>
        </div>
    </div>
</div>

<script>
const attendanceModalData = @json($attendanceModalData);
let attendanceModalActiveRecords = [];
let attendanceModalFilteredRecords = [];
let attendanceModalCurrentPage = 1;

function getAttendanceStatusBadgeClasses(status) {
    if (status === 'Present') {
        return 'bg-green-100 text-green-800';
    }

    if (status === 'Late') {
        return 'bg-yellow-100 text-yellow-800';
    }

    return 'bg-red-100 text-red-800';
}

function renderAttendanceModalRows() {
    const pageSize = parseInt(document.getElementById('attendanceModalPageSize').value, 10);
    const totalRows = attendanceModalFilteredRecords.length;
    const totalPages = Math.max(1, Math.ceil(totalRows / pageSize));

    if (attendanceModalCurrentPage > totalPages) {
        attendanceModalCurrentPage = totalPages;
    }

    const startIndex = totalRows === 0 ? 0 : (attendanceModalCurrentPage - 1) * pageSize;
    const endIndex = Math.min(startIndex + pageSize, totalRows);
    const visibleRows = attendanceModalFilteredRecords.slice(startIndex, endIndex);

    document.getElementById('attendanceModalRows').innerHTML = visibleRows.length
        ? visibleRows.map((record) => `
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">${record.date}</td>
                <td class="px-4 py-3">${record.time_in}</td>
                <td class="px-4 py-3">${record.time_out}</td>
                <td class="px-4 py-3">
                    <span class="rounded-full px-3 py-1 text-xs font-semibold ${getAttendanceStatusBadgeClasses(record.status)}">${record.status}</span>
                </td>
                <td class="px-4 py-3 font-medium">${record.total_hours} hrs</td>
                <td class="px-4 py-3">${record.remarks}</td>
            </tr>
        `).join('')
        : `
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-gray-500">No attendance rows match the current search.</td>
            </tr>
        `;

    const startDisplay = totalRows === 0 ? 0 : startIndex + 1;
    const endDisplay = totalRows === 0 ? 0 : endIndex;

    document.getElementById('attendanceModalPaginationText').textContent = `Showing ${startDisplay} to ${endDisplay} of ${totalRows} rows`;
    document.getElementById('attendanceModalPageIndicator').textContent = `Page ${attendanceModalCurrentPage} of ${totalPages}`;
    document.getElementById('attendanceModalPrev').disabled = attendanceModalCurrentPage <= 1;
    document.getElementById('attendanceModalNext').disabled = attendanceModalCurrentPage >= totalPages;
}

function filterAttendanceModalRows() {
    const searchTerm = document.getElementById('attendanceModalSearch').value.toLowerCase().trim();

    attendanceModalFilteredRecords = attendanceModalActiveRecords.filter((record) => {
        return `${record.date} ${record.time_in} ${record.time_out} ${record.status} ${record.total_hours} ${record.remarks}`
            .toLowerCase()
            .includes(searchTerm);
    });

    attendanceModalCurrentPage = 1;
    renderAttendanceModalRows();
}

function openAttendanceSummaryModal(userId) {
    const employee = attendanceModalData[userId];

    if (!employee) {
        return;
    }

    document.getElementById('attendanceModalEmployeeName').textContent = employee.name;
    document.getElementById('attendanceModalEmployeeMeta').textContent = `${employee.employee_code || 'No Code'} | ${employee.position || 'N/A'}`;
    document.getElementById('attendanceModalTotalRecords').textContent = employee.total_records;
    document.getElementById('attendanceModalPresentCount').textContent = employee.present_count;
    document.getElementById('attendanceModalLateCount').textContent = employee.late_count;
    document.getElementById('attendanceModalAbsentCount').textContent = employee.absent_count;
    document.getElementById('attendanceModalTotalHours').textContent = `${employee.total_hours} hrs`;

    document.getElementById('attendanceModalSearch').value = '';
    attendanceModalActiveRecords = employee.records;
    attendanceModalFilteredRecords = [...employee.records];
    attendanceModalCurrentPage = 1;
    renderAttendanceModalRows();

    const modal = document.getElementById('attendanceSummaryModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function closeAttendanceSummaryModal() {
    const modal = document.getElementById('attendanceSummaryModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('attendanceModalSearch').addEventListener('input', filterAttendanceModalRows);
    document.getElementById('attendanceModalPageSize').addEventListener('change', function () {
        attendanceModalCurrentPage = 1;
        renderAttendanceModalRows();
    });
    document.getElementById('attendanceModalPrev').addEventListener('click', function () {
        if (attendanceModalCurrentPage > 1) {
            attendanceModalCurrentPage -= 1;
            renderAttendanceModalRows();
        }
    });
    document.getElementById('attendanceModalNext').addEventListener('click', function () {
        const pageSize = parseInt(document.getElementById('attendanceModalPageSize').value, 10);
        const totalPages = Math.max(1, Math.ceil(attendanceModalFilteredRecords.length / pageSize));

        if (attendanceModalCurrentPage < totalPages) {
            attendanceModalCurrentPage += 1;
            renderAttendanceModalRows();
        }
    });

    document.getElementById('attendanceSummaryModal').addEventListener('click', function (event) {
        if (event.target === this) {
            closeAttendanceSummaryModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeAttendanceSummaryModal();
        }
    });
});
</script>
@endsection
