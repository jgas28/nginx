@extends('layouts.app')

@section('title', 'Billing Reports')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4">
        <!-- DataTables CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Billing Reports</h1>
            <p class="text-gray-600 mt-2">View and manage all billing records and statements of account</p>
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
                            <option value="draft">Draft</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="paid">Paid</option>
                            <option value="overdue">Overdue</option>
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
                    <a href="{{ route('billing.exportExcel', request()->only(['company', 'date_from', 'date_to', 'status'])) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg font-medium">
                        <i class="fas fa-file-excel mr-2"></i>Export Excel
                    </a>
                    <a href="{{ route('billing.createSOA.form') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium ml-auto">
                        <i class="fas fa-plus mr-2"></i>Create SOA
                    </a>
                </div>
            </form>
        </div>

        <!-- Billing Content Tabs -->
        <div class="bg-white rounded-lg shadow overflow-hidden">

            <!-- Tab Headers -->
            <div class="border-b border-gray-200 px-6 pt-4">
                <nav class="flex gap-1" id="billingTabNav">
                    <button onclick="switchTab('waiting')" id="tab-btn-waiting"
                        class="tab-btn px-5 py-2.5 text-sm font-medium rounded-t-lg border-b-2 border-blue-600 text-blue-600 bg-blue-50 transition">
                        <i class="fas fa-clock mr-2"></i>Waiting to be Paid
                    </button>
                    <button onclick="switchTab('paid')" id="tab-btn-paid"
                        class="tab-btn px-5 py-2.5 text-sm font-medium rounded-t-lg border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition">
                        <i class="fas fa-circle-check mr-2"></i>Paid SOA
                    </button>
                    <button onclick="switchTab('table')" id="tab-btn-table"
                        class="tab-btn px-5 py-2.5 text-sm font-medium rounded-t-lg border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition">
                        <i class="fas fa-table mr-2"></i>All SOA List
                    </button>
                    <button onclick="switchTab('analytics')" id="tab-btn-analytics"
                        class="tab-btn px-5 py-2.5 text-sm font-medium rounded-t-lg border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition">
                        <i class="fas fa-chart-bar mr-2"></i>Analytics
                    </button>
                    <button onclick="switchTab('deliveries')" id="tab-btn-deliveries"
                        class="tab-btn px-5 py-2.5 text-sm font-medium rounded-t-lg border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition">
                        <i class="fas fa-truck mr-2"></i>Billed Deliveries
                        @php $billedCount = count($billedDeliveries ?? []); @endphp
                        @if($billedCount > 0)
                            <span class="ml-1.5 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full bg-emerald-100 text-emerald-700">{{ $billedCount }}</span>
                        @endif
                    </button>
                </nav>
            </div>

            <!-- Tab: Waiting to be Paid (default) -->
            <div id="tab-waiting">
                <div class="overflow-x-auto">
                    <table id="waitingSoaTable" class="w-full text-sm text-left text-gray-600 display">
                        <thead class="bg-gray-100 text-gray-900 font-semibold">
                            <tr>
                                <th class="px-6 py-3">SOA Number</th>
                                <th class="px-6 py-3">Statement Date</th>
                                <th class="px-6 py-3">Company</th>
                                <th class="px-6 py-3">Customer</th>
                                <th class="px-6 py-3">Billing Period</th>
                                <th class="px-6 py-3">Total Amount</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($waitingSoas ?? [] as $soa)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium" data-order="{{ optional($soa->created_at)->timestamp ?? 0 }}">{{ $soa->soa_number }}</td>
                                    <td class="px-6 py-4" data-order="{{ optional($soa->statement_date)->format('Ymd') ?? '' }}">{{ optional($soa->statement_date)->format('M d, Y') }}</td>
                                    <td class="px-6 py-4">{{ $soa->company->company_name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4">{{ $soa->customer->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4">
                                        {{ optional($soa->billing_period_from)->format('M d') ?? 'N/A' }} - {{ optional($soa->billing_period_to)->format('M d, Y') ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 font-semibold">â‚±{{ number_format($soa->total_amount, 2) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full
                                            @if($soa->status == 'paid') bg-green-100 text-green-800
                                            @elseif($soa->status == 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($soa->status == 'approved') bg-blue-100 text-blue-800
                                            @elseif($soa->status == 'overdue') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst($soa->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex gap-2 items-center">
                                            <a href="{{ route('billing.showSoa', $soa->id) }}" class="text-blue-600 hover:text-blue-800" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('billing.editSoa', $soa->id) }}" class="text-indigo-600 hover:text-indigo-800" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('billing.markPaid', $soa->id) }}" class="inline js-mark-paid-form" data-soa-number="{{ $soa->soa_number }}">
                                                @csrf
                                                <button type="submit" class="text-emerald-600 hover:text-emerald-800" title="Mark as Paid">
                                                    <i class="fas fa-circle-check"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('billing.destroySoa', $soa->id) }}" onsubmit="return confirm('Delete this SOA?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            <a href="{{ route('soa.print', $soa->id) }}" class="text-green-600 hover:text-green-800" target="_blank" title="Print">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <a href="{{ route('soa.downloadPdf', $soa->id) }}" class="text-red-600 hover:text-red-800" title="Download PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab: Paid SOA -->
            <div id="tab-paid" class="hidden">
                <div class="overflow-x-auto">
                    <table id="paidSoaTable" class="w-full text-sm text-left text-gray-600 display">
                        <thead class="bg-gray-100 text-gray-900 font-semibold">
                            <tr>
                                <th class="px-6 py-3">SOA Number</th>
                                <th class="px-6 py-3">Statement Date</th>
                                <th class="px-6 py-3">Company</th>
                                <th class="px-6 py-3">Customer</th>
                                <th class="px-6 py-3">Billing Period</th>
                                <th class="px-6 py-3">Total Amount</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($paidSoas ?? [] as $soa)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium" data-order="{{ optional($soa->created_at)->timestamp ?? 0 }}">{{ $soa->soa_number }}</td>
                                    <td class="px-6 py-4" data-order="{{ optional($soa->statement_date)->format('Ymd') ?? '' }}">{{ optional($soa->statement_date)->format('M d, Y') }}</td>
                                    <td class="px-6 py-4">{{ $soa->company->company_name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4">{{ $soa->customer->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4">
                                        {{ optional($soa->billing_period_from)->format('M d') ?? 'N/A' }} - {{ optional($soa->billing_period_to)->format('M d, Y') ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 font-semibold">Ã¢â€šÂ±{{ number_format($soa->total_amount, 2) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full
                                            @if($soa->status == 'paid') bg-green-100 text-green-800
                                            @elseif($soa->status == 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($soa->status == 'approved') bg-blue-100 text-blue-800
                                            @elseif($soa->status == 'overdue') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst($soa->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex gap-2 items-center">
                                            <a href="{{ route('billing.showSoa', $soa->id) }}" class="text-blue-600 hover:text-blue-800" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('billing.editSoa', $soa->id) }}" class="text-indigo-600 hover:text-indigo-800" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('billing.destroySoa', $soa->id) }}" onsubmit="return confirm('Delete this SOA?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            <a href="{{ route('soa.print', $soa->id) }}" class="text-green-600 hover:text-green-800" target="_blank" title="Print">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <a href="{{ route('soa.downloadPdf', $soa->id) }}" class="text-red-600 hover:text-red-800" title="Download PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab: All SOA List -->
            <div id="tab-table" class="hidden">
                <style>
                    #paidSoaTable_wrapper .billing-table-toolbar,
                    #waitingSoaTable_wrapper .billing-table-toolbar,
                    #soaTable_wrapper .billing-table-toolbar {
                        display: flex;
                        flex-wrap: wrap;
                        align-items: center;
                        justify-content: space-between;
                        gap: 1rem;
                        padding: 1.25rem 1.5rem 0.75rem;
                    }

                    #paidSoaTable_wrapper .billing-table-toolbar .dataTables_length,
                    #paidSoaTable_wrapper .billing-table-toolbar .dataTables_filter,
                    #waitingSoaTable_wrapper .billing-table-toolbar .dataTables_length,
                    #waitingSoaTable_wrapper .billing-table-toolbar .dataTables_filter,
                    #soaTable_wrapper .billing-table-toolbar .dataTables_length,
                    #soaTable_wrapper .billing-table-toolbar .dataTables_filter {
                        float: none;
                        margin: 0;
                    }

                    #paidSoaTable_wrapper .billing-table-toolbar .dataTables_length,
                    #waitingSoaTable_wrapper .billing-table-toolbar .dataTables_length,
                    #soaTable_wrapper .billing-table-toolbar .dataTables_length {
                        display: flex;
                        align-items: center;
                        gap: 0.75rem;
                        color: #334155;
                        font-size: 0.95rem;
                    }

                    #paidSoaTable_wrapper .billing-table-toolbar .dataTables_length label,
                    #paidSoaTable_wrapper .billing-table-toolbar .dataTables_filter label,
                    #waitingSoaTable_wrapper .billing-table-toolbar .dataTables_length label,
                    #waitingSoaTable_wrapper .billing-table-toolbar .dataTables_filter label,
                    #soaTable_wrapper .billing-table-toolbar .dataTables_length label,
                    #soaTable_wrapper .billing-table-toolbar .dataTables_filter label {
                        display: flex;
                        align-items: center;
                        gap: 0.75rem;
                        margin: 0;
                        font-weight: 500;
                        color: #334155;
                    }

                    #paidSoaTable_wrapper .billing-table-toolbar .dataTables_filter,
                    #waitingSoaTable_wrapper .billing-table-toolbar .dataTables_filter,
                    #soaTable_wrapper .billing-table-toolbar .dataTables_filter {
                        margin-left: auto;
                    }

                    #paidSoaTable_wrapper .billing-table-toolbar .dataTables_filter input,
                    #paidSoaTable_wrapper .billing-table-toolbar .dataTables_length select,
                    #waitingSoaTable_wrapper .billing-table-toolbar .dataTables_filter input,
                    #waitingSoaTable_wrapper .billing-table-toolbar .dataTables_length select,
                    #soaTable_wrapper .billing-table-toolbar .dataTables_filter input,
                    #soaTable_wrapper .billing-table-toolbar .dataTables_length select {
                        margin: 0;
                        min-height: 3.1rem;
                        border-radius: 1rem;
                        border: 1px solid #cbd5e1;
                        background: #fff;
                        padding: 0.75rem 1rem;
                        font-size: 0.95rem;
                        color: #0f172a;
                        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
                    }

                    #paidSoaTable_wrapper .billing-table-toolbar .dataTables_filter input,
                    #waitingSoaTable_wrapper .billing-table-toolbar .dataTables_filter input,
                    #soaTable_wrapper .billing-table-toolbar .dataTables_filter input {
                        min-width: 320px;
                    }

                    #paidSoaTable_wrapper .billing-table-toolbar .dataTables_length select,
                    #waitingSoaTable_wrapper .billing-table-toolbar .dataTables_length select,
                    #soaTable_wrapper .billing-table-toolbar .dataTables_length select {
                        min-width: 88px;
                        padding-right: 2.5rem;
                    }

                    #paidSoaTable_wrapper .billing-table-toolbar .dataTables_filter input:focus,
                    #paidSoaTable_wrapper .billing-table-toolbar .dataTables_length select:focus,
                    #waitingSoaTable_wrapper .billing-table-toolbar .dataTables_filter input:focus,
                    #waitingSoaTable_wrapper .billing-table-toolbar .dataTables_length select:focus,
                    #soaTable_wrapper .billing-table-toolbar .dataTables_filter input:focus,
                    #soaTable_wrapper .billing-table-toolbar .dataTables_length select:focus {
                        border-color: #3b82f6;
                        outline: none;
                        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.14);
                    }

                    #paidSoaTable_wrapper .billing-table-footer,
                    #waitingSoaTable_wrapper .billing-table-footer,
                    #soaTable_wrapper .billing-table-footer {
                        display: flex;
                        flex-wrap: wrap;
                        align-items: center;
                        justify-content: space-between;
                        gap: 1rem;
                        padding: 0.75rem 1.5rem 1.25rem;
                    }

                    #paidSoaTable_wrapper .billing-table-footer .dataTables_info,
                    #paidSoaTable_wrapper .billing-table-footer .dataTables_paginate,
                    #waitingSoaTable_wrapper .billing-table-footer .dataTables_info,
                    #waitingSoaTable_wrapper .billing-table-footer .dataTables_paginate,
                    #soaTable_wrapper .billing-table-footer .dataTables_info,
                    #soaTable_wrapper .billing-table-footer .dataTables_paginate {
                        float: none;
                        margin: 0;
                    }

                    @media (max-width: 767px) {
                        #paidSoaTable_wrapper .billing-table-toolbar,
                        #paidSoaTable_wrapper .billing-table-footer,
                        #waitingSoaTable_wrapper .billing-table-toolbar,
                        #waitingSoaTable_wrapper .billing-table-footer,
                        #soaTable_wrapper .billing-table-toolbar,
                        #soaTable_wrapper .billing-table-footer {
                            padding-left: 1rem;
                            padding-right: 1rem;
                        }

                        #paidSoaTable_wrapper .billing-table-toolbar,
                        #waitingSoaTable_wrapper .billing-table-toolbar,
                        #soaTable_wrapper .billing-table-toolbar { align-items: stretch; }

                        #paidSoaTable_wrapper .billing-table-toolbar .dataTables_filter,
                        #paidSoaTable_wrapper .billing-table-toolbar .dataTables_filter label,
                        #paidSoaTable_wrapper .billing-table-toolbar .dataTables_filter input,
                        #paidSoaTable_wrapper .billing-table-toolbar .dataTables_length,
                        #paidSoaTable_wrapper .billing-table-toolbar .dataTables_length label,
                        #waitingSoaTable_wrapper .billing-table-toolbar .dataTables_filter,
                        #waitingSoaTable_wrapper .billing-table-toolbar .dataTables_filter label,
                        #waitingSoaTable_wrapper .billing-table-toolbar .dataTables_filter input,
                        #waitingSoaTable_wrapper .billing-table-toolbar .dataTables_length,
                        #waitingSoaTable_wrapper .billing-table-toolbar .dataTables_length label,
                        #soaTable_wrapper .billing-table-toolbar .dataTables_filter,
                        #soaTable_wrapper .billing-table-toolbar .dataTables_filter label,
                        #soaTable_wrapper .billing-table-toolbar .dataTables_filter input,
                        #soaTable_wrapper .billing-table-toolbar .dataTables_length,
                        #soaTable_wrapper .billing-table-toolbar .dataTables_length label {
                            width: 100%;
                        }

                        #paidSoaTable_wrapper .billing-table-toolbar .dataTables_filter,
                        #waitingSoaTable_wrapper .billing-table-toolbar .dataTables_filter,
                        #soaTable_wrapper .billing-table-toolbar .dataTables_filter { margin-left: 0; }

                        #paidSoaTable_wrapper .billing-table-toolbar .dataTables_filter label,
                        #paidSoaTable_wrapper .billing-table-toolbar .dataTables_length label,
                        #waitingSoaTable_wrapper .billing-table-toolbar .dataTables_filter label,
                        #waitingSoaTable_wrapper .billing-table-toolbar .dataTables_length label,
                        #soaTable_wrapper .billing-table-toolbar .dataTables_filter label,
                        #soaTable_wrapper .billing-table-toolbar .dataTables_length label {
                            justify-content: space-between;
                        }

                        #paidSoaTable_wrapper .billing-table-toolbar .dataTables_filter input,
                        #waitingSoaTable_wrapper .billing-table-toolbar .dataTables_filter input,
                        #soaTable_wrapper .billing-table-toolbar .dataTables_filter input {
                            min-width: 0;
                            width: 100%;
                        }
                    }
                </style>

                <div class="overflow-x-auto">
                    <table id="soaTable" class="w-full text-sm text-left text-gray-600 display">
                        <thead class="bg-gray-100 text-gray-900 font-semibold">
                            <tr>
                                <th class="px-6 py-3">SOA Number</th>
                                <th class="px-6 py-3">Statement Date</th>
                                <th class="px-6 py-3">Company</th>
                                <th class="px-6 py-3">Customer</th>
                                <th class="px-6 py-3">Billing Period</th>
                                <th class="px-6 py-3">Total Amount</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($soas ?? [] as $soa)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium" data-order="{{ optional($soa->created_at)->timestamp ?? 0 }}">{{ $soa->soa_number }}</td>
                                    <td class="px-6 py-4" data-order="{{ optional($soa->statement_date)->format('Ymd') ?? '' }}">{{ optional($soa->statement_date)->format('M d, Y') }}</td>
                                    <td class="px-6 py-4">{{ $soa->company->company_name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4">{{ $soa->customer->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4">
                                        {{ optional($soa->billing_period_from)->format('M d') ?? 'N/A' }} - {{ optional($soa->billing_period_to)->format('M d, Y') ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 font-semibold">₱{{ number_format($soa->total_amount, 2) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full
                                            @if($soa->status == 'paid') bg-green-100 text-green-800
                                            @elseif($soa->status == 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($soa->status == 'approved') bg-blue-100 text-blue-800
                                            @elseif($soa->status == 'overdue') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst($soa->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex gap-2 items-center">
                                            <a href="{{ route('billing.showSoa', $soa->id) }}" class="text-blue-600 hover:text-blue-800" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('billing.editSoa', $soa->id) }}" class="text-indigo-600 hover:text-indigo-800" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($soa->status !== 'paid')
                                                <form method="POST" action="{{ route('billing.markPaid', $soa->id) }}" class="inline js-mark-paid-form" data-soa-number="{{ $soa->soa_number }}">
                                                    @csrf
                                                    <button type="submit" class="text-emerald-600 hover:text-emerald-800" title="Mark as Paid">
                                                        <i class="fas fa-circle-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('billing.destroySoa', $soa->id) }}" onsubmit="return confirm('Delete this SOA?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            <a href="{{ route('soa.print', $soa->id) }}" class="text-green-600 hover:text-green-800" target="_blank" title="Print">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <a href="{{ route('soa.downloadPdf', $soa->id) }}" class="text-red-600 hover:text-red-800" title="Download PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab: Billed Deliveries -->
            <div id="tab-deliveries" class="hidden">
                <style>
                    #billedTable_wrapper .billed-table-toolbar {
                        display: flex;
                        flex-wrap: wrap;
                        align-items: center;
                        justify-content: space-between;
                        gap: 1rem;
                        padding: 1.25rem 1.5rem 0.75rem;
                    }
                    #billedTable_wrapper .billed-table-toolbar .dataTables_length,
                    #billedTable_wrapper .billed-table-toolbar .dataTables_filter {
                        float: none; margin: 0;
                    }
                    #billedTable_wrapper .billed-table-toolbar .dataTables_length {
                        display: flex; align-items: center; gap: 0.75rem; color: #334155; font-size: 0.95rem;
                    }
                    #billedTable_wrapper .billed-table-toolbar .dataTables_length label,
                    #billedTable_wrapper .billed-table-toolbar .dataTables_filter label {
                        display: flex; align-items: center; gap: 0.75rem; margin: 0; font-weight: 500; color: #334155;
                    }
                    #billedTable_wrapper .billed-table-toolbar .dataTables_filter { margin-left: auto; }
                    #billedTable_wrapper .billed-table-toolbar .dataTables_filter input,
                    #billedTable_wrapper .billed-table-toolbar .dataTables_length select {
                        margin: 0; min-height: 2.8rem; border-radius: 0.75rem; border: 1px solid #cbd5e1;
                        background: #fff; padding: 0.5rem 1rem; font-size: 0.9rem; color: #0f172a;
                    }
                    #billedTable_wrapper .billed-table-toolbar .dataTables_filter input { min-width: 280px; }
                    #billedTable_wrapper .billed-table-footer {
                        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
                        gap: 1rem; padding: 0.75rem 1.5rem 1.25rem;
                    }
                    #billedTable_wrapper .billed-table-footer .dataTables_info,
                    #billedTable_wrapper .billed-table-footer .dataTables_paginate { float: none; margin: 0; }
                </style>

                {{-- Info banner --}}
                <div class="mx-6 mt-4 mb-2 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    <i class="fas fa-info-circle mt-0.5 shrink-0 text-emerald-500"></i>
                    <span>All delivery requests that have been included in an SOA are listed here. Use the <strong>SOA Status</strong> badge to track whether payment has been received for each delivery.</span>
                </div>

                <div class="overflow-x-auto">
                    <table id="billedTable" class="w-full text-sm text-left text-gray-600 display">
                        <thead class="bg-gray-100 text-gray-900 font-semibold text-xs uppercase tracking-wide">
                            <tr>
                                <th class="px-4 py-3">MTM</th>
                                <th class="px-4 py-3">Booking Date</th>
                                <th class="px-4 py-3">Delivery Date</th>
                                <th class="px-4 py-3">Company</th>
                                <th class="px-4 py-3">Customer</th>
                                <th class="px-4 py-3">SOA Reference</th>
                                <th class="px-4 py-3">Billing Period</th>
                                <th class="px-4 py-3">Billed For</th>
                                <th class="px-4 py-3 text-right">Amount Billed</th>
                                <th class="px-4 py-3">Payment Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($billedDeliveries ?? [] as $bd)
                                @php
                                    $billingType  = $bd->billing_type ?? 'both';
                                    $drAmt        = (float)($bd->delivery_rate_amount ?? 0);
                                    $acAmt        = (float)($bd->accessorial_rate_amount ?? 0);
                                    $totalAmt     = (float)($bd->amount ?? 0);
                                    $soaStatus    = $bd->soa_status ?? 'draft';

                                    $billingLabel = match($billingType) {
                                        'delivery_only'    => ['text' => 'Delivery Only',    'cls' => 'bg-blue-100 text-blue-700'],
                                        'accessorial_only' => ['text' => 'Accessorial Only', 'cls' => 'bg-purple-100 text-purple-700'],
                                        default            => ['text' => 'Both',              'cls' => 'bg-teal-100 text-teal-700'],
                                    };

                                    $statusConfig = match($soaStatus) {
                                        'paid'     => ['text' => 'Paid',     'cls' => 'bg-green-100 text-green-800',  'icon' => 'fa-circle-check',  'dot' => 'bg-green-500'],
                                        'approved' => ['text' => 'Approved', 'cls' => 'bg-blue-100 text-blue-800',   'icon' => 'fa-thumbs-up',     'dot' => 'bg-blue-500'],
                                        'pending'  => ['text' => 'Pending',  'cls' => 'bg-yellow-100 text-yellow-800','icon' => 'fa-clock',         'dot' => 'bg-yellow-500'],
                                        'overdue'  => ['text' => 'Overdue',  'cls' => 'bg-red-100 text-red-800',     'icon' => 'fa-exclamation-circle','dot' => 'bg-red-500'],
                                        default    => ['text' => 'Draft',    'cls' => 'bg-gray-100 text-gray-700',   'icon' => 'fa-pencil',        'dot' => 'bg-gray-400'],
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $bd->mtm ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-gray-600" data-order="{{ $bd->booking_date ?? '' }}">
                                        {{ $bd->booking_date ? \Carbon\Carbon::parse($bd->booking_date)->format('M d, Y') : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600" data-order="{{ $bd->delivery_date ?? '' }}">
                                        {{ $bd->delivery_date ? \Carbon\Carbon::parse($bd->delivery_date)->format('M d, Y') : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ $bd->company_name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $bd->customer_name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('billing.showSoa', $bd->soa_id) }}"
                                           class="inline-flex items-center gap-1.5 font-semibold text-blue-600 hover:text-blue-800 hover:underline">
                                            <i class="fas fa-file-invoice text-xs"></i>{{ $bd->soa_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 text-xs">
                                        {{ $bd->billing_period_from ? \Carbon\Carbon::parse($bd->billing_period_from)->format('M d') : '—' }}
                                        –
                                        {{ $bd->billing_period_to ? \Carbon\Carbon::parse($bd->billing_period_to)->format('M d, Y') : '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $billingLabel['cls'] }}">{{ $billingLabel['text'] }}</span>
                                        @if($billingType !== 'accessorial_only' && $drAmt > 0)
                                            <div class="text-xs text-gray-400 mt-0.5">DR: ₱{{ number_format($drAmt, 2) }}</div>
                                        @endif
                                        @if($billingType !== 'delivery_only' && $acAmt > 0)
                                            <div class="text-xs text-gray-400">AC: ₱{{ number_format($acAmt, 2) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-bold text-gray-900 text-right">₱{{ number_format($totalAmt, 2) }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusConfig['cls'] }}">
                                            <span class="inline-block w-1.5 h-1.5 rounded-full {{ $statusConfig['dot'] }}"></span>
                                            <i class="fas {{ $statusConfig['icon'] }} text-[10px]"></i>
                                            {{ $statusConfig['text'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab: Analytics -->
            <div id="tab-analytics" class="hidden p-6">

                <!-- KPI Row -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 rounded-lg p-4 text-center">
                        <p class="text-xs text-blue-600 font-semibold uppercase tracking-wide">Collection Rate</p>
                        <p class="text-3xl font-bold text-blue-700 mt-1" id="kpiCollectionRate">—</p>
                        <p class="text-xs text-blue-500 mt-1">Paid / Total</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4 text-center">
                        <p class="text-xs text-green-600 font-semibold uppercase tracking-wide">Avg per SOA</p>
                        <p class="text-2xl font-bold text-green-700 mt-1" id="kpiAvgPerSoa">—</p>
                        <p class="text-xs text-green-500 mt-1">Average billing amount</p>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4 text-center">
                        <p class="text-xs text-yellow-600 font-semibold uppercase tracking-wide">Pending SOAs</p>
                        <p class="text-3xl font-bold text-yellow-700 mt-1" id="kpiPending">—</p>
                        <p class="text-xs text-yellow-500 mt-1">Awaiting action</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-4 text-center">
                        <p class="text-xs text-red-600 font-semibold uppercase tracking-wide">Overdue SOAs</p>
                        <p class="text-3xl font-bold text-red-700 mt-1" id="kpiOverdue">—</p>
                        <p class="text-xs text-red-500 mt-1">Requires attention</p>
                    </div>
                </div>

                <!-- Charts Row 1 -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Status Distribution -->
                    <div class="bg-gray-50 rounded-lg p-5">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">SOA Status Distribution</h3>
                        <div class="flex justify-center" style="height:240px;">
                            <canvas id="chartStatus"></canvas>
                        </div>
                    </div>
                    <!-- Monthly Billing Trend -->
                    <div class="bg-gray-50 rounded-lg p-5">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">Monthly Billing Trend</h3>
                        <div style="height:240px;">
                            <canvas id="chartMonthly"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 2 -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Top Companies -->
                    <div class="bg-gray-50 rounded-lg p-5">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">Top Companies by Billed Amount</h3>
                        <div style="height:240px;">
                            <canvas id="chartCompanies"></canvas>
                        </div>
                    </div>
                    <!-- Paid vs Outstanding breakdown -->
                    <div class="bg-gray-50 rounded-lg p-5">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">Paid vs Outstanding by Status</h3>
                        <div style="height:240px;">
                            <canvas id="chartPaidOutstanding"></canvas>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // ── Tab switching ─────────────────────────────────────────────
    let analyticsInitialized = false;
    let billedTableInitialized = false;

    function switchTab(name) {
        ['waiting', 'paid', 'table', 'analytics', 'deliveries'].forEach(t => {
            document.getElementById('tab-' + t).classList.toggle('hidden', t !== name);
            const btn = document.getElementById('tab-btn-' + t);
            if (t === name) {
                btn.classList.add('border-blue-600', 'text-blue-600', 'bg-blue-50');
                btn.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:bg-gray-50');
            } else {
                btn.classList.remove('border-blue-600', 'text-blue-600', 'bg-blue-50');
                btn.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:bg-gray-50');
            }
        });
        if (name === 'analytics' && !analyticsInitialized) {
            initAnalytics();
            analyticsInitialized = true;
        }
        if (name === 'deliveries' && !billedTableInitialized) {
            $('#billedTable').DataTable({
                pageLength: 25,
                order: [[5, 'desc']],
                autoWidth: false,
                dom: '<"billed-table-toolbar"lf>t<"billed-table-footer"ip>',
                columns: [
                    { width: '10%' },
                    { width: '10%' },
                    { width: '10%' },
                    { width: '13%' },
                    { width: '13%' },
                    { width: '12%' },
                    { width: '12%' },
                    { width: '10%' },
                    { width: '8%', className: 'text-right' },
                    { width: '10%', orderable: false }
                ],
                language: {
                    search: 'Search deliveries:',
                    emptyTable: 'No billed deliveries found. Deliveries will appear here once they are added to an SOA.'
                }
            });
            billedTableInitialized = true;
        }
    }

    // ── DataTable ────────────────────────────────────────────────
    $(document).ready(function () {
        $('#waitingSoaTable').DataTable({
            pageLength: 10,
            order: [[0, 'desc']],
            autoWidth: false,
            dom: '<"billing-table-toolbar"lf>t<"billing-table-footer"ip>',
            columns: [
                { width: '18%' },
                { width: '12%' },
                { width: '18%' },
                { width: '18%' },
                { width: '18%' },
                { width: '10%', className: 'text-right' },
                { width: '8%' },
                { orderable: false, searchable: false, width: '8%' }
            ],
            columnDefs: [{ orderable: false, targets: 7 }],
            language: {
                search: "Search waiting SOA:",
                emptyTable: "No SOA records waiting to be paid"
            }
        });

        $('#paidSoaTable').DataTable({
            pageLength: 10,
            order: [[0, 'desc']],
            autoWidth: false,
            dom: '<"billing-table-toolbar"lf>t<"billing-table-footer"ip>',
            columns: [
                { width: '18%' },
                { width: '12%' },
                { width: '18%' },
                { width: '18%' },
                { width: '18%' },
                { width: '10%', className: 'text-right' },
                { width: '8%' },
                { orderable: false, searchable: false, width: '8%' }
            ],
            columnDefs: [{ orderable: false, targets: 7 }],
            language: {
                search: "Search paid SOA:",
                emptyTable: "No paid SOA records available"
            }
        });

        $('#soaTable').DataTable({
            pageLength: 10,
            order: [[0, 'desc']],
            autoWidth: false,
            dom: '<"billing-table-toolbar"lf>t<"billing-table-footer"ip>',
            columns: [
                { width: '18%' },
                { width: '12%' },
                { width: '18%' },
                { width: '18%' },
                { width: '18%' },
                { width: '10%', className: 'text-right' },
                { width: '8%' },
                { orderable: false, searchable: false, width: '8%' }
            ],
            columnDefs: [{ orderable: false, targets: 7 }],
            language: {
                search: "Search all SOA:",
                emptyTable: "No SOA records available"
            }
        });
    });

    document.addEventListener('submit', async function (event) {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || !form.classList.contains('js-mark-paid-form')) {
            return;
        }

        if (form.dataset.confirmed === 'true') {
            return;
        }

        event.preventDefault();

        if (typeof Swal === 'undefined') {
            form.dataset.confirmed = 'true';
            form.submit();
            return;
        }

        const soaNumber = form.dataset.soaNumber || 'this SOA';

        const result = await Swal.fire({
            title: 'Mark SOA as Paid?',
            html: `<p class="text-sm text-slate-600">You are about to update <strong>${soaNumber}</strong> to <strong>Paid</strong>.</p><p class="mt-2 text-sm text-slate-500">This will set the paid amount to the full total and clear the outstanding amount.</p>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, mark as paid',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            focusCancel: true,
            customClass: {
                popup: 'rounded-[24px]',
                confirmButton: 'swal-success-btn',
                cancelButton: 'swal-cancel-btn'
            },
            buttonsStyling: false
        });

        if (!result.isConfirmed) {
            return;
        }

        form.dataset.confirmed = 'true';
        form.submit();
    }, true);

    // ── SOA data from server ──────────────────────────────────────
    @php
        $soaChartData = ($soas ?? collect())->map(function($s) {
            return [
                'status'       => $s->status,
                'total_amount' => (float) $s->total_amount,
                'paid_amount'  => (float) $s->paid_amount,
                'outstanding'  => (float) $s->outstanding_amount,
                'company'      => $s->company->company_name ?? 'N/A',
                'month'        => optional($s->statement_date)->format('Y-m'),
            ];
        })->values()->all();
    @endphp
    const soaData = @json($soaChartData);

    // ── Analytics init ────────────────────────────────────────────
    function fmt(n) {
        return '₱' + Number(n).toLocaleString('en-PH', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function initAnalytics() {
        const total     = soaData.reduce((s, r) => s + r.total_amount, 0);
        const paid      = soaData.reduce((s, r) => s + r.paid_amount, 0);
        const count     = soaData.length;

        // KPI cards
        document.getElementById('kpiCollectionRate').textContent =
            count ? (total > 0 ? (paid / total * 100).toFixed(1) + '%' : '0%') : '—';
        document.getElementById('kpiAvgPerSoa').textContent =
            count ? fmt(total / count) : '—';
        document.getElementById('kpiPending').textContent =
            soaData.filter(r => r.status === 'pending').length;
        document.getElementById('kpiOverdue').textContent =
            soaData.filter(r => r.status === 'overdue').length;

        // ── Chart 1: Status doughnut ──
        const statusLabels = ['Draft', 'Pending', 'Approved', 'Paid', 'Overdue'];
        const statusKeys   = ['draft', 'pending', 'approved', 'paid', 'overdue'];
        const statusColors = ['#94a3b8','#fbbf24','#3b82f6','#22c55e','#ef4444'];
        const statusCounts = statusKeys.map(k => soaData.filter(r => r.status === k).length);

        new Chart(document.getElementById('chartStatus'), {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{ data: statusCounts, backgroundColor: statusColors, borderWidth: 2, borderColor: '#f9fafb' }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } } }
            }
        });

        // ── Chart 2: Monthly trend ──
        const monthMap = {};
        soaData.forEach(r => {
            if (!r.month) return;
            monthMap[r.month] = (monthMap[r.month] || 0) + r.total_amount;
        });
        const months = Object.keys(monthMap).sort();
        const monthLabels = months.map(m => {
            const [y, mo] = m.split('-');
            return new Date(y, mo - 1).toLocaleString('en-US', { month: 'short', year: '2-digit' });
        });

        new Chart(document.getElementById('chartMonthly'), {
            type: 'bar',
            data: {
                labels: monthLabels.length ? monthLabels : ['No data'],
                datasets: [{
                    label: 'Total Billed',
                    data: months.map(m => monthMap[m]),
                    backgroundColor: 'rgba(59,130,246,0.7)',
                    borderColor: '#3b82f6',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { ticks: { callback: v => fmt(v), font: { size: 10 } }, grid: { color: '#e5e7eb' } },
                    x: { ticks: { font: { size: 10 } }, grid: { display: false } }
                }
            }
        });

        // ── Chart 3: Top companies ──
        const companyMap = {};
        soaData.forEach(r => { companyMap[r.company] = (companyMap[r.company] || 0) + r.total_amount; });
        const topCompanies = Object.entries(companyMap)
            .sort((a, b) => b[1] - a[1])
            .slice(0, 6);

        new Chart(document.getElementById('chartCompanies'), {
            type: 'bar',
            data: {
                labels: topCompanies.map(c => c[0]),
                datasets: [{
                    label: 'Billed Amount',
                    data: topCompanies.map(c => c[1]),
                    backgroundColor: 'rgba(16,185,129,0.7)',
                    borderColor: '#10b981',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { callback: v => fmt(v), font: { size: 10 } }, grid: { color: '#e5e7eb' } },
                    y: { ticks: { font: { size: 10 } }, grid: { display: false } }
                }
            }
        });

        // ── Chart 4: Paid vs Outstanding stacked by status ──
        const activeStatuses = statusKeys.filter(k => soaData.some(r => r.status === k));
        const activeLabels   = activeStatuses.map(k => k.charAt(0).toUpperCase() + k.slice(1));
        const paidByStatus   = activeStatuses.map(k => soaData.filter(r => r.status === k).reduce((s,r) => s + r.paid_amount, 0));
        const outsByStatus   = activeStatuses.map(k => soaData.filter(r => r.status === k).reduce((s,r) => s + r.outstanding, 0));

        new Chart(document.getElementById('chartPaidOutstanding'), {
            type: 'bar',
            data: {
                labels: activeLabels.length ? activeLabels : ['No data'],
                datasets: [
                    { label: 'Paid', data: paidByStatus, backgroundColor: 'rgba(34,197,94,0.7)', borderColor: '#22c55e', borderWidth: 1, borderRadius: 4 },
                    { label: 'Outstanding', data: outsByStatus, backgroundColor: 'rgba(239,68,68,0.65)', borderColor: '#ef4444', borderWidth: 1, borderRadius: 4 }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { labels: { boxWidth: 12, font: { size: 11 } } } },
                scales: {
                    x: { stacked: true, ticks: { font: { size: 10 } }, grid: { display: false } },
                    y: { stacked: true, ticks: { callback: v => fmt(v), font: { size: 10 } }, grid: { color: '#e5e7eb' } }
                }
            }
        });
    }
</script>
@endsection
