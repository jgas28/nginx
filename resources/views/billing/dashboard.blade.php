@extends('layouts.app')

@section('title', 'Billing Report')

@section('content')
<div class="min-h-screen bg-gray-100 py-6 sm:py-8">
    <div class="mx-auto max-w-7xl px-4">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Billing Report Dashboard</h1>
            <p class="mt-2 text-sm text-gray-600 sm:text-base">Track and manage all billing records and statements of account</p>
        </div>

        <!-- Dashboard Stats -->
        <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <!-- Total Billings -->
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Total Billings</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 sm:text-3xl">{{ $stats['total_billings'] ?? 0 }}</p>
                    </div>
                    <div class="rounded-full bg-blue-100 p-3 sm:p-4">
                        <i class="fas fa-file-invoice text-xl text-blue-600 sm:text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Amount -->
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Total Amount</p>
                        <p class="text-3xl font-bold text-green-600 mt-2">₱{{ number_format($stats['total_amount'] ?? 0, 2) }}</p>
                    </div>
                    <div class="rounded-full bg-green-100 p-3 sm:p-4">
                        <i class="fas fa-peso-sign text-xl text-green-600 sm:text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Paid Amount -->
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Paid Amount</p>
                        <p class="text-3xl font-bold text-blue-600 mt-2">₱{{ number_format($stats['paid_amount'] ?? 0, 2) }}</p>
                    </div>
                    <div class="rounded-full bg-blue-100 p-3 sm:p-4">
                        <i class="fas fa-check-circle text-xl text-blue-600 sm:text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Outstanding Amount -->
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Outstanding Amount</p>
                        <p class="text-3xl font-bold text-red-600 mt-2">₱{{ number_format($stats['outstanding_amount'] ?? 0, 2) }}</p>
                    </div>
                    <div class="rounded-full bg-red-100 p-3 sm:p-4">
                        <i class="fas fa-exclamation-circle text-xl text-red-600 sm:text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Actions -->
        <div class="mb-8 rounded-2xl bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Filter & Search</h2>
            
            <form method="GET" action="{{ route('billing.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Company</label>
                        <input type="text" name="company" placeholder="Search by company..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                        <input type="date" name="date_from"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                        <input type="date" name="date_to"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-3 font-medium text-white transition hover:bg-blue-700">
                        <i class="fas fa-search mr-2"></i>Search
                    </button>
                    <a href="{{ route('billing.index') }}" class="inline-flex items-center justify-center rounded-xl bg-gray-300 px-6 py-3 font-medium text-gray-800 transition hover:bg-gray-400">
                        <i class="fas fa-redo mr-2"></i>Reset
                    </a>
                    <a href="{{ route('billing.createSOA.form') }}" class="inline-flex items-center justify-center rounded-xl bg-green-600 px-6 py-3 font-medium text-white transition hover:bg-green-700 sm:ml-auto">
                        <i class="fas fa-plus mr-2"></i>Create SOA
                    </a>
                </div>
            </form>
        </div>

        <!-- Billing Table -->
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="bg-gray-100 text-gray-900 font-semibold">
                        <tr>
                            <th class="px-6 py-3">Statement Date</th>
                            <th class="px-6 py-3">Company</th>
                            <th class="px-6 py-3">Period</th>
                            <th class="px-6 py-3">Total Amount</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr class="hover:bg-gray-50">
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 sm:px-6">
                                <i class="fas fa-inbox text-3xl mb-2 block"></i>
                                <p>No billing records found</p>
                                <p class="text-sm mt-2">Start by creating a new SOA (Statement of Account)</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Information Cards -->
        <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- About Billing Report -->
            <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5 sm:p-6">
                <h3 class="text-lg font-semibold text-blue-900 mb-4">
                    <i class="fas fa-info-circle mr-2"></i>About Billing Report
                </h3>
                <ul class="space-y-2 text-blue-800 text-sm">
                    <li><i class="fas fa-check mr-2 text-green-600"></i>Create and manage Statements of Account (SOA)</li>
                    <li><i class="fas fa-check mr-2 text-green-600"></i>Track billing amounts and customer payments</li>
                    <li><i class="fas fa-check mr-2 text-green-600"></i>Generate detailed billing reports by date range</li>
                    <li><i class="fas fa-check mr-2 text-green-600"></i>View and print SOA documents</li>
                    <li><i class="fas fa-check mr-2 text-green-600"></i>Monitor outstanding amounts and payment status</li>
                </ul>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-2xl border border-green-200 bg-green-50 p-5 sm:p-6">
                <h3 class="text-lg font-semibold text-green-900 mb-4">
                    <i class="fas fa-lightning-bolt mr-2"></i>Quick Actions
                </h3>
                <div class="space-y-2">
                    <a href="{{ route('billing.createSOA.form') }}" class="flex items-center rounded-xl bg-green-600 px-4 py-3 text-sm font-medium text-white transition hover:bg-green-700">
                        <i class="fas fa-plus mr-2"></i>Create New SOA
                    </a>
                    <a href="{{ route('billing.index') }}" class="flex items-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-medium text-white transition hover:bg-blue-700">
                        <i class="fas fa-list mr-2"></i>View All Billings
                    </a>
                    <a href="{{ route('billing.createSOA-acc.form') }}" class="flex items-center rounded-xl bg-purple-600 px-4 py-3 text-sm font-medium text-white transition hover:bg-purple-700">
                        <i class="fas fa-plus mr-2"></i>Create Accessorial SOA
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
