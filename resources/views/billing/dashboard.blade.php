@extends('layouts.app')

@section('title', 'Billing Report')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Billing Report Dashboard</h1>
            <p class="text-gray-600 mt-2">Track and manage all billing records and statements of account</p>
        </div>

        <!-- Dashboard Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Billings -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Total Billings</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_billings'] ?? 0 }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-4">
                        <i class="fas fa-file-invoice text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Amount -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Total Amount</p>
                        <p class="text-3xl font-bold text-green-600 mt-2">₱{{ number_format($stats['total_amount'] ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-4">
                        <i class="fas fa-peso-sign text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Paid Amount -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Paid Amount</p>
                        <p class="text-3xl font-bold text-blue-600 mt-2">₱{{ number_format($stats['paid_amount'] ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-4">
                        <i class="fas fa-check-circle text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Outstanding Amount -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Outstanding Amount</p>
                        <p class="text-3xl font-bold text-red-600 mt-2">₱{{ number_format($stats['outstanding_amount'] ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-red-100 rounded-full p-4">
                        <i class="fas fa-exclamation-circle text-red-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Actions -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
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

                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium">
                        <i class="fas fa-search mr-2"></i>Search
                    </button>
                    <a href="{{ route('billing.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-medium">
                        <i class="fas fa-redo mr-2"></i>Reset
                    </a>
                    <a href="{{ route('billing.createSOA.form') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium ml-auto">
                        <i class="fas fa-plus mr-2"></i>Create SOA
                    </a>
                </div>
            </form>
        </div>

        <!-- Billing Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
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
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
            <!-- About Billing Report -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
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
            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-green-900 mb-4">
                    <i class="fas fa-lightning-bolt mr-2"></i>Quick Actions
                </h3>
                <div class="space-y-2">
                    <a href="{{ route('billing.createSOA.form') }}" class="flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition">
                        <i class="fas fa-plus mr-2"></i>Create New SOA
                    </a>
                    <a href="{{ route('billing.index') }}" class="flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                        <i class="fas fa-list mr-2"></i>View All Billings
                    </a>
                    <a href="{{ route('billing.createSOA-acc.form') }}" class="flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-medium transition">
                        <i class="fas fa-plus mr-2"></i>Create Accessorial SOA
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
