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

        <!-- Attendance Records Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="bg-gray-100 text-gray-900 font-semibold">
                        <tr>
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3">Employee</th>
                            <th class="px-6 py-3">Employee Code</th>
                            <th class="px-6 py-3">Time In</th>
                            <th class="px-6 py-3">Time Out</th>
                            <th class="px-6 py-3">Total Hours</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($attendanceRecords as $record)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">{{ $record->date->format('M d, Y') }}</td>
                                <td class="px-6 py-4 font-medium">{{ $record->user->fname }} {{ $record->user->lname }}</td>
                                <td class="px-6 py-4">{{ $record->user->employee_code }}</td>
                                <td class="px-6 py-4">{{ $record->time_in ? $record->time_in->format('H:i') : '-' }}</td>
                                <td class="px-6 py-4">{{ $record->time_out ? $record->time_out->format('H:i') : '-' }}</td>
                                <td class="px-6 py-4">{{ $record->total_hours ? number_format($record->total_hours, 2) : '-' }} hrs</td>
                                <td class="px-6 py-4">
                                    @if($record->status === 'Present')
                                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Present</span>
                                    @elseif($record->status === 'Late')
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">Late</span>
                                    @else
                                        <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">Absent</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">{{ $record->remarks ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-3xl mb-2"></i>
                                    <p>No attendance records found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $attendanceRecords->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
