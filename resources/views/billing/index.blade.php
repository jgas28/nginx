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

        <!-- Billing Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <style>
                #soaTable_wrapper .billing-table-toolbar {
                    display: flex;
                    flex-wrap: wrap;
                    align-items: center;
                    justify-content: space-between;
                    gap: 1rem;
                    padding: 1.25rem 1.5rem 0.75rem;
                }

                #soaTable_wrapper .billing-table-toolbar .dataTables_length,
                #soaTable_wrapper .billing-table-toolbar .dataTables_filter {
                    float: none;
                    margin: 0;
                }

                #soaTable_wrapper .billing-table-toolbar .dataTables_length {
                    display: flex;
                    align-items: center;
                    gap: 0.75rem;
                    color: #334155;
                    font-size: 0.95rem;
                }

                #soaTable_wrapper .billing-table-toolbar .dataTables_length label,
                #soaTable_wrapper .billing-table-toolbar .dataTables_filter label {
                    display: flex;
                    align-items: center;
                    gap: 0.75rem;
                    margin: 0;
                    font-weight: 500;
                    color: #334155;
                }

                #soaTable_wrapper .billing-table-toolbar .dataTables_filter {
                    margin-left: auto;
                }

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

                #soaTable_wrapper .billing-table-toolbar .dataTables_filter input {
                    min-width: 320px;
                }

                #soaTable_wrapper .billing-table-toolbar .dataTables_length select {
                    min-width: 88px;
                    padding-right: 2.5rem;
                }

                #soaTable_wrapper .billing-table-toolbar .dataTables_filter input:focus,
                #soaTable_wrapper .billing-table-toolbar .dataTables_length select:focus {
                    border-color: #3b82f6;
                    outline: none;
                    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.14);
                }

                #soaTable_wrapper .billing-table-footer {
                    display: flex;
                    flex-wrap: wrap;
                    align-items: center;
                    justify-content: space-between;
                    gap: 1rem;
                    padding: 0.75rem 1.5rem 1.25rem;
                }

                #soaTable_wrapper .billing-table-footer .dataTables_info,
                #soaTable_wrapper .billing-table-footer .dataTables_paginate {
                    float: none;
                    margin: 0;
                }

                @media (max-width: 767px) {
                    #soaTable_wrapper .billing-table-toolbar,
                    #soaTable_wrapper .billing-table-footer {
                        padding-left: 1rem;
                        padding-right: 1rem;
                    }

                    #soaTable_wrapper .billing-table-toolbar {
                        align-items: stretch;
                    }

                    #soaTable_wrapper .billing-table-toolbar .dataTables_filter,
                    #soaTable_wrapper .billing-table-toolbar .dataTables_filter label,
                    #soaTable_wrapper .billing-table-toolbar .dataTables_filter input,
                    #soaTable_wrapper .billing-table-toolbar .dataTables_length,
                    #soaTable_wrapper .billing-table-toolbar .dataTables_length label {
                        width: 100%;
                    }

                    #soaTable_wrapper .billing-table-toolbar .dataTables_filter {
                        margin-left: 0;
                    }

                    #soaTable_wrapper .billing-table-toolbar .dataTables_filter label,
                    #soaTable_wrapper .billing-table-toolbar .dataTables_length label {
                        justify-content: space-between;
                    }

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
                                <td class="px-6 py-4 font-medium">{{ $soa->soa_number }}</td>
                                <td class="px-6 py-4">{{ optional($soa->statement_date)->format('M d, Y') }}</td>
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
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#soaTable').DataTable({
            pageLength: 10,
            order: [[1, 'desc']],
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
            columnDefs: [
                { orderable: false, targets: 7 }
            ],
            language: {
                search: "Search SOA:",
                emptyTable: "No SOA records available"
            }
        });
    });
</script>
@endsection
