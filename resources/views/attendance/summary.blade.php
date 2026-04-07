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
                        <input type="text" name="employee" value="{{ $employee }}" placeholder="Search name or code..." class="w-full px-4 py-2 border border-gray-300 rounded-lg">
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
                    <a href="{{ route('attendance.summary.excel', request()->only(['month', 'employee'])) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg font-medium">
                        <i class="fas fa-file-excel mr-2"></i>Export Excel
                    </a>
                    <a href="{{ route('attendance.summary.pdf', request()->only(['month', 'employee'])) }}" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium">
                        <i class="fas fa-file-pdf mr-2"></i>Download PDF
                    </a>
                </div>
            </form>
        </div>

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

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="bg-gray-100 text-gray-900 font-semibold">
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
                                <tr>
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
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-10 text-center text-gray-500">
                <i class="fas fa-inbox text-3xl mb-3"></i>
                <p>No attendance summary found for the selected filters.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
