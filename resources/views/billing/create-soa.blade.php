@extends('layouts.app')

@section('title', 'Create Statement of Account')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-6xl mx-auto px-4">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Create Statement of Account</h1>
                    <p class="text-gray-600 mt-2">Generate a new SOA for billing purposes</p>
                </div>
                <a href="{{ route('billing.dashboard') }}" class="inline-flex w-full items-center justify-center rounded-lg bg-gray-500 px-4 py-2 text-white hover:bg-gray-600 sm:w-auto">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>
        </div>

        <!-- SOA Form -->
        <form action="{{ route('billing.createSOA') }}" method="POST" id="soaForm">
            @csrf
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">SOA Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Company Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Company <span class="text-red-500">*</span>
                        </label>
                        <!-- Hidden native select for form submission -->
                        <select id="company_id" name="company_id" class="hidden" onchange="onCompanyChange()">
                            <option value="">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>{{ $company->company_name }}</option>
                            @endforeach
                        </select>
                        <!-- Custom styled dropdown -->
                        <div class="relative" id="company-select-wrapper">
                            <button type="button" id="company-trigger"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white text-left flex items-center justify-between gap-2"
                                    onclick="toggleCustomSelect('company')">
                                <span id="company-display" class="text-gray-400 text-sm truncate">Select Company</span>
                                <i class="fas fa-chevron-down text-gray-400 text-xs flex-shrink-0 transition-transform duration-200" id="company-chevron"></i>
                            </button>
                            <div id="company-dropdown"
                                 class="hidden absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-64 overflow-y-auto">
                                <div class="p-1.5">
                                    <div class="py-2 px-3 text-gray-400 text-sm cursor-pointer hover:bg-gray-50 rounded-lg"
                                         onclick="selectCompanyOption('', 'Select Company', 0)">
                                        Select Company
                                    </div>
                                    @foreach($companies as $company)
                                        @php $pendingCount = $companyItemCounts[$company->id] ?? 0; @endphp
                                        <div class="py-2 px-3 flex items-center justify-between gap-2 cursor-pointer hover:bg-blue-50 rounded-lg transition-colors"
                                             onclick="selectCompanyOption('{{ $company->id }}', {{ json_encode($company->company_name) }}, {{ $pendingCount }})">
                                            <span class="text-gray-900 text-sm truncate">{{ $company->company_name }}</span>
                                            @if($pendingCount > 0)
                                                <span class="flex-shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-200 whitespace-nowrap">
                                                    <i class="fas fa-clock"></i>{{ $pendingCount }}
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div id="company-pending-hint" class="hidden mt-2 flex items-center gap-1.5 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-1.5">
                            <i class="fas fa-clock"></i>
                            <span id="company-pending-hint-text"></span>
                        </div>
                        @if($companies->isEmpty())
                            <p class="mt-1 text-sm text-red-600">No companies found in database</p>
                        @endif
                        @error('company_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Customer Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Customer <span class="text-red-500">*</span>
                        </label>
                        <!-- Hidden native select for form submission -->
                        <select id="customer_id" name="customer_id" class="hidden" onchange="onCustomerChange()">
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                            @endforeach
                        </select>
                        <!-- Custom styled dropdown -->
                        <div class="relative" id="customer-select-wrapper">
                            <button type="button" id="customer-trigger"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white text-left flex items-center justify-between gap-2"
                                    onclick="toggleCustomSelect('customer')">
                                <span id="customer-display" class="text-gray-400 text-sm truncate">Select Customer</span>
                                <i class="fas fa-chevron-down text-gray-400 text-xs flex-shrink-0 transition-transform duration-200" id="customer-chevron"></i>
                            </button>
                            <div id="customer-dropdown"
                                 class="hidden absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-64 overflow-y-auto">
                                <div class="p-1.5">
                                    <div class="py-2 px-3 text-gray-400 text-sm cursor-pointer hover:bg-gray-50 rounded-lg"
                                         onclick="selectCustomerOption('', 'Select Customer', 0)">
                                        Select Customer
                                    </div>
                                    @foreach($customers as $customer)
                                        @php $pendingCount = $customerItemCounts[$customer->id] ?? 0; @endphp
                                        <div class="py-2 px-3 flex items-center justify-between gap-2 cursor-pointer hover:bg-blue-50 rounded-lg transition-colors"
                                             onclick="selectCustomerOption('{{ $customer->id }}', {{ json_encode($customer->name) }}, {{ $pendingCount }})">
                                            <span class="text-gray-900 text-sm truncate">{{ $customer->name }}</span>
                                            @if($pendingCount > 0)
                                                <span class="flex-shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-200 whitespace-nowrap">
                                                    <i class="fas fa-clock"></i>{{ $pendingCount }}
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div id="customer-pending-hint" class="hidden mt-2 flex items-center gap-1.5 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-1.5">
                            <i class="fas fa-clock"></i>
                            <span id="customer-pending-hint-text"></span>
                        </div>
                        @if($customers->isEmpty())
                            <p class="mt-1 text-sm text-red-600">No customers found in database</p>
                        @endif
                        @error('customer_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Billing Period From -->
                    <div>
                        <label for="billing_period_from" class="block text-sm font-medium text-gray-700 mb-2">
                            Billing Period From <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="billing_period_from" name="billing_period_from"
                               value="{{ old('billing_period_from') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               onchange="onFromDateChange()">
                        @error('billing_period_from')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Billing Period To -->
                    <div>
                        <label for="billing_period_to" class="block text-sm font-medium text-gray-700 mb-2">
                            Billing Period To <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="billing_period_to" name="billing_period_to"
                               value="{{ old('billing_period_to') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               onchange="onToDateChange()">
                        @error('billing_period_to')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Booking Date -->
                    <div>
                        <label for="booking_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Booking Date
                        </label>
                        <input type="date" id="booking_date" name="booking_date"
                               value="{{ old('booking_date', '2026-04-07') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('booking_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Notes -->
                <div class="mt-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Notes
                    </label>
                    <textarea id="notes" name="notes" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Additional notes for this SOA...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Delivery Requests Selection -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <div class="flex flex-col gap-3 md:flex-row md:justify-between md:items-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">Select Delivery Requests</h2>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="selectAllItems()" class="inline-flex items-center justify-center px-3 py-2 text-sm bg-blue-500 text-white rounded hover:bg-blue-600 whitespace-nowrap">
                            Select All
                        </button>
                        <button type="button" onclick="selectNoneItems()" class="inline-flex items-center justify-center px-3 py-2 text-sm bg-gray-500 text-white rounded hover:bg-gray-600 whitespace-nowrap">
                            Unselect All
                        </button>
                    </div>
                </div>

                <!-- Search and Filter Controls -->
                <div class="mb-4 flex flex-col gap-4 lg:flex-row">
                    <div class="flex-1">
                        <input type="text" id="searchDeliveryRequests" placeholder="Search by MTM, site, or company..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               onkeyup="filterBySearch()">
                    </div>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:w-auto">
                        <select id="filterByCompany" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="filterByCompany()">
                            <option value="">All Companies</option>
                        </select>
                        <select id="filterByCustomer" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="filterByCustomer()">
                            <option value="">All Customers</option>
                        </select>
                    </div>
                </div>

                <!-- Results Summary -->
                <div class="mb-4 text-sm text-gray-600">
                    <span id="resultsCount">0</span> delivery requests found
                </div>

                <div id="deliveryRequestsContainer" class="max-h-96 overflow-y-auto border border-gray-200 rounded-xl bg-white">
                    <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                        <div class="w-16 h-16 rounded-full bg-blue-50 flex items-center justify-center mb-4">
                            <i class="fas fa-truck-loading text-blue-400 text-2xl"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-500">Loading delivery requests&hellip;</p>
                    </div>
                </div>

                @error('delivery_request_ids')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Summary and Actions -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">SOA Summary</h3>
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">

                    <div class="flex-1 min-w-0 space-y-4">

                        <!-- 1. Adjustments (first) -->
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-sliders-h text-gray-400"></i> Adjustments
                            </h4>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 mb-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                                    <select id="card_discount_type" onchange="syncAdjustments()"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                        <option value="">None</option>
                                        <option value="discount">Discount</option>
                                        <option value="dispute">Dispute</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Amount (₱)</label>
                                    <input type="number" id="card_discount_amount" min="0" step="0.01" value="0"
                                           onchange="syncAdjustments()" oninput="syncAdjustments()"
                                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Remarks / Reason</label>
                                    <input type="text" id="card_discount_remarks" maxlength="1000"
                                           placeholder="Why this discount/dispute?"
                                           onchange="syncAdjustments()" oninput="syncAdjustments()"
                                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Manual Adjustment (₱)</label>
                                    <input type="number" id="card_adjustment_amount" step="0.01" value="0"
                                           onchange="syncAdjustments()" oninput="syncAdjustments()"
                                           placeholder="+ or −"
                                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    <p class="mt-1 text-xs text-gray-400">Negative to deduct, positive to add</p>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Adjustment Remarks</label>
                                    <input type="text" id="card_adjustment_remarks" maxlength="1000"
                                           placeholder="Reason for manual adjustment"
                                           onchange="syncAdjustments()" oninput="syncAdjustments()"
                                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                </div>
                            </div>
                        </div>

                        <!-- 2. Live breakdown (second) -->
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm space-y-2">
                            <div class="flex justify-between text-gray-600">
                                <span class="flex items-center gap-1.5"><i class="fas fa-boxes text-gray-400 text-xs"></i> Selected Line Items</span>
                                <span id="selectedRequests" class="font-semibold text-gray-800">0</span>
                            </div>

                            <!-- Rate source breakdown (delivery + accessorial) -->
                            <div id="summaryRateBreakdown" class="hidden flex-col gap-1 pl-1">
                                <div class="flex justify-between text-sky-600 text-xs">
                                    <span class="flex items-center gap-1"><i class="fas fa-truck text-xs w-3"></i> Delivery Rate</span>
                                    <span id="summaryDeliveryRateAmt" class="font-medium">₱0.00</span>
                                </div>
                                <div class="flex justify-between text-purple-600 text-xs">
                                    <span class="flex items-center gap-1"><i class="fas fa-tags text-xs w-3"></i> Accessorial</span>
                                    <span id="summaryAccessorialAmt" class="font-medium">₱0.00</span>
                                </div>
                            </div>

                            <div class="flex justify-between text-gray-600">
                                <span class="flex items-center gap-1.5"><i class="fas fa-receipt text-gray-400 text-xs"></i> Subtotal</span>
                                <span id="summarySubtotal" class="font-semibold text-gray-800">₱0.00</span>
                            </div>
                            <div id="summaryDiscountRow" class="hidden flex-col gap-0.5">
                                <div class="flex justify-between text-red-600">
                                    <span class="flex items-center gap-1.5"><i class="fas fa-tag text-xs"></i> <span id="summaryDiscountLabel">Discount</span></span>
                                    <span id="summaryDiscountAmt" class="font-semibold">-₱0.00</span>
                                </div>
                                <p id="summaryDiscountRemarks" class="text-xs text-gray-400 italic pl-5 hidden"></p>
                            </div>
                            <div id="summaryAdjustmentRow" class="hidden flex-col gap-0.5">
                                <div class="flex justify-between text-blue-600">
                                    <span class="flex items-center gap-1.5"><i class="fas fa-sliders-h text-xs"></i> Manual Adjustment</span>
                                    <span id="summaryAdjustmentAmt" class="font-semibold">₱0.00</span>
                                </div>
                                <p id="summaryAdjustmentRemarks" class="text-xs text-gray-400 italic pl-5 hidden"></p>
                            </div>
                            <div class="flex justify-between font-bold text-gray-900 border-t border-gray-300 pt-2">
                                <span class="flex items-center gap-1.5"><i class="fas fa-check-circle text-emerald-500 text-xs"></i> Final Total</span>
                                <span id="totalAmount" class="text-emerald-700 text-base">₱0.00</span>
                            </div>

                            <!-- Who is affected -->
                            <div id="summaryAffectedInfo" class="hidden pt-1 border-t border-gray-100 text-xs text-gray-400 space-y-0.5">
                                <div id="summaryAffectedCompany" class="flex items-center gap-1.5">
                                    <i class="fas fa-building w-3 text-gray-300"></i>
                                    <span></span>
                                </div>
                                <div id="summaryAffectedCustomer" class="flex items-center gap-1.5">
                                    <i class="fas fa-user w-3 text-gray-300"></i>
                                    <span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="flex flex-col gap-3 sm:flex-row lg:flex-col lg:w-44">
                        <button type="button" onclick="calculateTotal()" class="inline-flex w-full items-center justify-center rounded-lg bg-blue-500 px-6 py-2.5 text-white hover:bg-blue-600">
                            <i class="fas fa-calculator mr-2"></i>Show Summary
                        </button>
                        <button type="button" onclick="validateAndSubmit()" class="inline-flex w-full items-center justify-center rounded-lg bg-green-600 px-6 py-2.5 text-white hover:bg-green-700">
                            <i class="fas fa-save mr-2"></i>Create SOA
                        </button>
                    </div>
                </div>
            </div>

            <!-- Hidden inputs for adjustments (synced from modal) -->
            <input type="hidden" name="discount_type" id="hidden_discount_type">
            <input type="hidden" name="discount_amount" id="hidden_discount_amount" value="0">
            <input type="hidden" name="discount_remarks" id="hidden_discount_remarks">
            <input type="hidden" name="adjustment_amount" id="hidden_adjustment_amount" value="0">
            <input type="hidden" name="adjustment_remarks" id="hidden_adjustment_remarks">
        </form>
    </div>
</div>

<!-- Validation Modal -->
<div id="validationModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200/80 overflow-hidden">
        <div class="flex items-center gap-4 px-6 pt-6 pb-4">
            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Missing Required Fields</h3>
                <p class="text-sm text-gray-400 mt-0.5">Please complete the fields below before creating your SOA.</p>
            </div>
        </div>
        <div class="px-6 pb-2" id="validationModalErrors"></div>
        <div class="px-6 pb-6 pt-4 flex flex-col gap-2 sm:flex-row">
            <button onclick="closeValidationModal()" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
                <i class="fas fa-times mr-2"></i>Dismiss
            </button>
            <button onclick="closeValidationModal()" class="w-full rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 transition">
                <i class="fas fa-arrow-left mr-2"></i>Go back and fix
            </button>
        </div>
    </div>
</div>

<div id="calculateTotalModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/65 px-4 backdrop-blur-[2px]">
    <div class="w-full max-w-4xl rounded-xl bg-white shadow-[0_30px_80px_rgba(15,23,42,0.35)] ring-1 ring-slate-200/80">
        <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-4 py-4 sm:px-6">
            <div>
                <h3 class="text-xl font-semibold text-gray-900">Selected Delivery Request Summary</h3>
                <p class="mt-1 text-sm text-gray-500">Review the selected delivery requests and total amount before creating the SOA.</p>
            </div>
            <button type="button" onclick="closeCalculateTotalModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="max-h-[70vh] overflow-y-auto px-4 py-4 sm:px-6">
            <div id="calculateTotalModalEmpty" class="hidden rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-10 text-center text-gray-500">
                <i class="fas fa-inbox text-3xl mb-3"></i>
                <p class="font-medium">No delivery requests selected yet.</p>
                <p class="mt-1 text-sm">Select at least one delivery request, then click Calculate Total again.</p>
            </div>

            <div id="calculateTotalModalContent" class="hidden">
                <div class="mb-4 grid grid-cols-2 gap-3 md:grid-cols-4">
                    <div class="rounded-lg bg-blue-50 px-4 py-3">
                        <p class="text-xs text-blue-600 font-medium">Selected</p>
                        <p id="modalSelectedCount" class="mt-1 text-2xl font-bold text-blue-900">0</p>
                    </div>
                    <div class="rounded-lg bg-sky-50 px-4 py-3">
                        <p class="text-xs text-sky-600 font-medium flex items-center gap-1"><i class="fas fa-truck text-xs"></i> Delivery Rate</p>
                        <p id="modalDeliveryRateTotal" class="mt-1 text-lg font-bold text-sky-700">PHP 0.00</p>
                    </div>
                    <div class="rounded-lg bg-purple-50 px-4 py-3">
                        <p class="text-xs text-purple-600 font-medium flex items-center gap-1"><i class="fas fa-tags text-xs"></i> Accessorial</p>
                        <p id="modalAccessorialTotal" class="mt-1 text-lg font-bold text-purple-700">PHP 0.00</p>
                    </div>
                    <div class="rounded-lg bg-emerald-50 px-4 py-3">
                        <p class="text-xs text-emerald-600 font-medium">Grand Subtotal</p>
                        <p id="modalGrandTotal" class="mt-1 text-lg font-bold text-emerald-700">PHP 0.00</p>
                    </div>
                </div>


                <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p id="calculateTotalModalPaginationText" class="text-sm text-gray-500">Showing 0 to 0 of 0 rows</p>
                    <div class="flex items-center gap-2 self-start sm:self-auto">
                        <span class="text-sm text-gray-500">Rows</span>
                        <select id="calculateTotalModalPageSize" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            <option value="5" selected>5</option>
                            <option value="10">10</option>
                            <option value="15">15</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100 text-left text-gray-700">
                            <tr>
                                <th class="px-4 py-3 font-semibold">MTM / Line Item</th>
                                <th class="px-4 py-3 font-semibold">Booking</th>
                                <th class="px-4 py-3 font-semibold">Delivery</th>
                                <th class="px-4 py-3 font-semibold">Company</th>
                                <th class="px-4 py-3 font-semibold">Customer</th>
                                <th class="px-4 py-3 font-semibold">Billed For</th>
                                <th class="px-4 py-3 font-semibold text-sky-700">Delivery Rate</th>
                                <th class="px-4 py-3 font-semibold text-purple-700">Accessorial</th>
                                <th class="px-4 py-3 font-semibold">Total</th>
                            </tr>
                        </thead>
                        <tbody id="calculateTotalModalRows" class="divide-y divide-gray-200 text-gray-700"></tbody>
                    </table>
                </div>

                <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
                    <button type="button" id="calculateTotalModalPrev" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50">
                        Previous
                    </button>
                    <span id="calculateTotalModalPageIndicator" class="min-w-[88px] text-center text-sm font-medium text-gray-700">Page 1 of 1</span>
                    <button type="button" id="calculateTotalModalNext" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50">
                        Next
                    </button>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-gray-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-end sm:px-6">
            <button type="button" onclick="closeCalculateTotalModal()" class="inline-flex w-full items-center justify-center rounded-lg bg-gray-100 px-4 py-2 text-gray-700 hover:bg-gray-200 sm:w-auto">
                Close
            </button>
            <button type="button" onclick="closeCalculateTotalModal(); document.querySelector('#soaForm button[type=&quot;submit&quot;]')?.focus();" class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 sm:w-auto">
                Continue to Create SOA
            </button>
        </div>
    </div>
</div>

<script>
// Delivery line items data
const deliveryLineItems = [
    @foreach($deliveryLineItems as $lineItem)
    {
        id: {{ $lineItem->id }},
        mtm: '{{ addslashes($lineItem->mtm) }}',
        accessorialRate: @if(is_array($lineItem->accessorial_rate)) {{ array_sum($lineItem->accessorial_rate) }} @else {{ $lineItem->accessorial_rate ?? 0 }} @endif,
        addOnRate: @if(is_array($lineItem->add_on_rate)) {{ array_sum($lineItem->add_on_rate) }} @else {{ $lineItem->add_on_rate ?? 0 }} @endif,
        siteName: '{{ addslashes(is_array($lineItem->site_name) ? implode(', ', $lineItem->site_name) : ($lineItem->site_name ?? 'N/A')) }}',
        bookingDate: '{{ $lineItem->delivery_request_booking_date ?? '' }}',
        requestAmount: {{ $lineItem->delivery_request_amount ?? 0 }},
        requestStatus: '{{ addslashes($lineItem->joined_delivery_status_name ?? ($lineItem->delivery_request_delivery_status ?? $lineItem->delivery_request_status ?? 'N/A')) }}',
        deliveryRequest: {
            deliveryDate: '{{ $lineItem->delivery_request_delivery_date ?? ($lineItem->deliveryRequest ? $lineItem->deliveryRequest->delivery_date : '') }}',
            companyId: '{{ $lineItem->delivery_request_company_id ?? ($lineItem->deliveryRequest ? $lineItem->deliveryRequest->company_id : '') }}',
            customerId: '{{ $lineItem->delivery_request_customer_id ?? ($lineItem->deliveryRequest ? $lineItem->deliveryRequest->customer_id : '') }}',
            companyName: '{{ addslashes($lineItem->joined_company_name ?? ($lineItem->deliveryRequest && $lineItem->deliveryRequest->company ? $lineItem->deliveryRequest->company->company_name : 'N/A')) }}',
            customerName: '{{ addslashes($lineItem->joined_customer_name ?? ($lineItem->deliveryRequest && $lineItem->deliveryRequest->customer ? $lineItem->deliveryRequest->customer->name : 'N/A')) }}'
        },
        deliveryStatusId: '{{ $lineItem->delivery_status ?? '' }}',
        deliveryStatusName: '{{ addslashes(optional($lineItem->deliveryStatus)->status_name ?? 'N/A') }}'
    }@if(!$loop->last),@endif
    @endforeach
];

console.log('Delivery line items loaded:', deliveryLineItems.length);
let calculateTotalModalItems = [];
let calculateTotalModalCurrentPage = 1;

// ── Skeleton loader ───────────────────────────────────────────
function buildSkeletonCards(n) {
    const card = `
        <div class="border border-gray-100 rounded-xl p-4 animate-pulse">
            <div class="flex items-start gap-3">
                <div class="mt-0.5 h-5 w-5 rounded bg-gray-200 flex-shrink-0"></div>
                <div class="flex-1 space-y-2 min-w-0">
                    <div class="flex gap-2 items-center">
                        <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                        <div class="h-4 bg-gray-100 rounded w-16"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-2 pt-1">
                        <div class="h-3 bg-gray-100 rounded"></div>
                        <div class="h-3 bg-gray-100 rounded w-4/5"></div>
                        <div class="h-3 bg-gray-100 rounded w-3/4"></div>
                        <div class="h-3 bg-gray-100 rounded"></div>
                        <div class="h-3 bg-gray-100 rounded col-span-2 w-2/3"></div>
                    </div>
                    <div class="flex gap-4 pt-1 border-t border-gray-100">
                        <div class="h-3 bg-gray-100 rounded w-24"></div>
                        <div class="h-3 bg-gray-100 rounded w-20"></div>
                        <div class="h-4 bg-gray-200 rounded w-16 ml-auto"></div>
                    </div>
                </div>
            </div>
        </div>`;
    return `<div class="space-y-3 p-3">${card.repeat(n)}</div>`;
}

// ── Status badge ──────────────────────────────────────────────
function getStatusBadge(status) {
    const s = (status || 'N/A').toLowerCase();
    if (s.includes('deliver') || s.includes('complet')) return { cls: 'bg-green-100 text-green-700',  text: status || 'Delivered' };
    if (s.includes('transit') || s.includes('progress'))return { cls: 'bg-blue-100 text-blue-700',   text: status };
    if (s.includes('pending') || s.includes('wait'))    return { cls: 'bg-yellow-100 text-yellow-700',text: status };
    if (s.includes('cancel')  || s.includes('fail'))    return { cls: 'bg-red-100 text-red-700',      text: status };
    return { cls: 'bg-gray-100 text-gray-500', text: status || 'N/A' };
}

// ── Billing selections Map: itemId → {delivery: bool, accessorial: bool} ──
const billingSelections = new Map();

function getBillingType(itemId) {
    const li = deliveryLineItems.find(i => i.id === itemId);
    const sel = billingSelections.get(itemId) || {};
    const hasDR = Number(li?.requestAmount || 0) > 0 && sel.delivery === true;
    const hasAC = (Number(li?.accessorialRate || 0) + Number(li?.addOnRate || 0)) > 0 && sel.accessorial === true;
    if (hasDR && hasAC) return 'both';
    if (hasDR) return 'delivery_only';
    if (hasAC) return 'accessorial_only';
    return 'both';
}

function onBillingToggle(itemId, type, checked, event) {
    if (event) event.stopPropagation();
    const sel = billingSelections.get(itemId) || { delivery: true, accessorial: true };
    sel[type] = checked;
    billingSelections.set(itemId, sel);

    // Update label styling
    const label = document.querySelector(`.bill-toggle-label[data-item="${itemId}"][data-type="${type}"]`);
    if (label) {
        const isDelivery = type === 'delivery';
        const activeOn  = isDelivery
            ? ['border-blue-600',  'bg-blue-600',  'text-white', 'shadow-md', 'shadow-blue-200']
            : ['border-emerald-600','bg-emerald-600','text-white','shadow-md', 'shadow-emerald-200'];
        const activeOff = ['border-gray-300','bg-gray-100','text-gray-400','opacity-60'];

        if (checked) {
            label.classList.remove(...activeOff);
            label.classList.add(...activeOn);
        } else {
            label.classList.remove(...activeOn);
            label.classList.add(...activeOff);
        }
        // Toggle the icon inside the circle: check when active, type-icon when inactive
        const iconEl = label.querySelector('span > i');
        if (iconEl) {
            if (checked) {
                iconEl.classList.remove(isDelivery ? 'fa-truck' : 'fa-tags');
                iconEl.classList.add('fa-check');
            } else {
                iconEl.classList.remove('fa-check');
                iconEl.classList.add(isDelivery ? 'fa-truck' : 'fa-tags');
            }
        }
    }

    // Recalculate this card's billed total
    const li = deliveryLineItems.find(i => i.id === itemId);
    if (li) {
        const drAmt = Number(li.requestAmount || 0);
        const acAmt = Number(li.accessorialRate || 0) + Number(li.addOnRate || 0);
        const newSel = billingSelections.get(itemId);
        const billed = (newSel.delivery !== false && drAmt > 0 ? drAmt : 0)
                     + (newSel.accessorial !== false && acAmt > 0 ? acAmt : 0);
        const el = document.getElementById(`item-billed-${itemId}`);
        if (el) el.textContent = `₱${billed.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}`;
    }
    updateSummary();
}

// ── Card interactivity ────────────────────────────────────────
function toggleCard(cardEl) {
    const cb = cardEl.querySelector('.delivery-checkbox');
    cb.checked = !cb.checked;
    toggleCardStyle(cb);
}

function toggleCardStyle(cb) {
    const card = cb.closest('.dr-card');
    if (!card) return;
    if (cb.checked) {
        card.classList.add('border-blue-400', 'bg-blue-50', 'shadow-sm');
        card.classList.remove('border-gray-200');
    } else {
        card.classList.remove('border-blue-400', 'bg-blue-50', 'shadow-sm');
        card.classList.add('border-gray-200');

        // Reset billing selection to unchecked when card is deselected
        const itemId = parseInt(cb.value);
        billingSelections.set(itemId, { delivery: false, accessorial: false });

        // Reset toggle visuals back to gray inactive
        card.querySelectorAll('.bill-toggle-label').forEach(label => {
            const type = label.dataset.type;
            label.classList.remove(
                'border-blue-600','bg-blue-600',
                'border-emerald-600','bg-emerald-600',
                'text-white','shadow-md','shadow-blue-200','shadow-emerald-200','cursor-pointer'
            );
            label.classList.add('border-gray-300','bg-gray-100','text-gray-400');
            // Reset circle icon back to type icon
            const iconEl = label.querySelector('span > i');
            if (iconEl) {
                iconEl.classList.remove('fa-check');
                iconEl.classList.add(type === 'delivery' ? 'fa-truck' : 'fa-tags');
            }
            // Update the item billed total display to ₱0.00
            const totalEl = document.getElementById(`item-billed-${itemId}`);
            if (totalEl) totalEl.textContent = '₱0.00';
        });
    }

    // Enable or disable billing toggles
    card.querySelectorAll('.bill-toggle-label').forEach(label => {
        if (cb.checked) {
            label.classList.remove('pointer-events-none', 'opacity-30', 'cursor-not-allowed');
            label.classList.add('cursor-pointer');
        } else {
            label.classList.add('pointer-events-none', 'opacity-30', 'cursor-not-allowed');
            label.classList.remove('cursor-pointer');
        }
    });
    updateSummary();
}

// ── Validation modal ──────────────────────────────────────────
function validateAndSubmit() {
    const errors = [];
    if (!document.getElementById('company_id').value)
        errors.push({ icon: 'fa-building',      color: 'blue',   field: 'Company',            msg: 'Please select a company for this SOA.' });
    if (!document.getElementById('customer_id').value)
        errors.push({ icon: 'fa-user',           color: 'purple', field: 'Customer',           msg: 'Please select a customer for this SOA.' });
    if (!document.getElementById('billing_period_from').value)
        errors.push({ icon: 'fa-calendar-alt',  color: 'green',  field: 'Billing Period From', msg: 'Set the billing period start date.' });
    if (!document.getElementById('billing_period_to').value)
        errors.push({ icon: 'fa-calendar-check',color: 'green',  field: 'Billing Period To',   msg: 'Set the billing period end date.' });
    if (!document.querySelectorAll('.delivery-checkbox:checked').length)
        errors.push({ icon: 'fa-boxes',         color: 'orange', field: 'Delivery Requests',   msg: 'Select at least one delivery request.' });

    // Ensure every selected item has at least one billing component chosen
    document.querySelectorAll('.delivery-checkbox:checked').forEach(cb => {
        const itemId = parseInt(cb.value);
        const sel = billingSelections.get(itemId) || {};
        if (!sel.delivery && !sel.accessorial) {
            errors.push({ icon: 'fa-tag', color: 'orange', field: `MTM billing not set`,
                msg: `Please select Delivery Rate and/or Accessorial for at least one selected item (Line Item #${itemId}).` });
        }
    });

    if (errors.length) { showValidationModal(errors); return; }

    // Inject billing type hidden inputs for each selected item
    const form = document.getElementById('soaForm');
    form.querySelectorAll('input[name^="item_billing"]').forEach(el => el.remove());
    document.querySelectorAll('.delivery-checkbox:checked').forEach(cb => {
        const itemId = parseInt(cb.value);
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = `item_billing[${itemId}]`;
        input.value = getBillingType(itemId);
        form.appendChild(input);
    });

    form.submit();
}

function showValidationModal(errors) {
    const colorMap = {
        blue:   'bg-blue-100 text-blue-600',
        purple: 'bg-purple-100 text-purple-600',
        green:  'bg-green-100 text-green-600',
        orange: 'bg-orange-100 text-orange-600',
        red:    'bg-red-100 text-red-600',
    };
    document.getElementById('validationModalErrors').innerHTML = errors.map(e => `
        <div class="flex items-start gap-3 py-3 border-b border-gray-100 last:border-0">
            <div class="flex-shrink-0 w-9 h-9 rounded-full ${colorMap[e.color] || 'bg-gray-100 text-gray-500'} flex items-center justify-center">
                <i class="fas ${e.icon} text-sm"></i>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900">${e.field}</p>
                <p class="text-xs text-gray-400 mt-0.5">${e.msg}</p>
            </div>
        </div>`).join('');
    const modal = document.getElementById('validationModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function closeValidationModal() {
    const modal = document.getElementById('validationModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

// ── Card enter animation ──────────────────────────────────────
const _drStyle = document.createElement('style');
_drStyle.textContent = `
@keyframes drFadeIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }
.dr-card-enter { animation: drFadeIn 0.2s ease forwards; }
`;
document.head.appendChild(_drStyle);

function formatPeso(amount) {
    return `PHP ${Number(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function formatDisplayDate(dateValue) {
    if (!dateValue) {
        return 'N/A';
    }

    try {
        const normalizedDate = dateValue.includes('T') ? dateValue : `${dateValue}T00:00:00`;
        const date = new Date(normalizedDate);

        if (isNaN(date.getTime())) {
            return dateValue;
        }

        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    } catch (error) {
        return dateValue;
    }
}

function getLineItemAmount(lineItem) {
    const drAmt = Number(lineItem.requestAmount || 0);
    const acAmt = Number(lineItem.accessorialRate || 0) + Number(lineItem.addOnRate || 0);
    const sel = billingSelections.get(lineItem.id);
    if (sel) {
        return (sel.delivery !== false && drAmt > 0 ? drAmt : 0)
             + (sel.accessorial !== false && acAmt > 0 ? acAmt : 0);
    }
    return drAmt > 0 ? drAmt : acAmt;
}

function getSelectedLineItems() {
    return Array.from(document.querySelectorAll('.delivery-checkbox:checked'))
        .map((checkbox) => deliveryLineItems.find((item) => item.id === parseInt(checkbox.value, 10)))
        .filter(Boolean);
}

function renderCalculateTotalModalRows() {
    const rowsContainer = document.getElementById('calculateTotalModalRows');
    const pageSize = parseInt(document.getElementById('calculateTotalModalPageSize').value, 10);
    const totalRows = calculateTotalModalItems.length;
    const totalPages = Math.max(1, Math.ceil(totalRows / pageSize));

    if (calculateTotalModalCurrentPage > totalPages) {
        calculateTotalModalCurrentPage = totalPages;
    }

    const startIndex = totalRows === 0 ? 0 : (calculateTotalModalCurrentPage - 1) * pageSize;
    const endIndex = Math.min(startIndex + pageSize, totalRows);
    const visibleRows = calculateTotalModalItems.slice(startIndex, endIndex);

    rowsContainer.innerHTML = visibleRows.map((lineItem) => {
        const drAmt  = Number(lineItem.requestAmount || 0);
        const acAmt  = Number(lineItem.accessorialRate || 0) + Number(lineItem.addOnRate || 0);
        const sel    = billingSelections.get(lineItem.id) || {};
        const billedDr = (sel.delivery !== false && drAmt > 0) ? drAmt : 0;
        const billedAc = (sel.accessorial !== false && acAmt > 0) ? acAmt : 0;
        const total    = billedDr + billedAc;
        const typeMap  = {
            both:             ['Both',              'bg-green-100 text-green-700'],
            delivery_only:    ['Delivery Only',     'bg-sky-100 text-sky-700'],
            accessorial_only: ['Accessorial Only',  'bg-purple-100 text-purple-700'],
        };
        const billingType = getBillingType(lineItem.id);
        const [typeLabel, typeCls] = typeMap[billingType] || typeMap.both;
        return `
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 align-top">
                <div class="font-medium text-gray-900">${lineItem.mtm}</div>
                <div class="text-xs text-gray-500">Line Item #${lineItem.id}</div>
            </td>
            <td class="px-4 py-3 align-top text-sm">${formatDisplayDate(lineItem.bookingDate)}</td>
            <td class="px-4 py-3 align-top text-sm">${formatDisplayDate(lineItem.deliveryRequest ? lineItem.deliveryRequest.deliveryDate : '')}</td>
            <td class="px-4 py-3 align-top text-sm">${lineItem.deliveryRequest ? lineItem.deliveryRequest.companyName : 'N/A'}</td>
            <td class="px-4 py-3 align-top text-sm">${lineItem.deliveryRequest ? lineItem.deliveryRequest.customerName : 'N/A'}</td>
            <td class="px-4 py-3 align-top">
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold ${typeCls}">${typeLabel}</span>
            </td>
            <td class="px-4 py-3 align-top text-sm ${billedDr > 0 ? 'text-sky-700 font-medium' : 'text-gray-300'}">${billedDr > 0 ? formatPeso(billedDr) : '—'}</td>
            <td class="px-4 py-3 align-top text-sm ${billedAc > 0 ? 'text-purple-700 font-medium' : 'text-gray-300'}">${billedAc > 0 ? formatPeso(billedAc) : '—'}</td>
            <td class="px-4 py-3 align-top font-bold text-gray-900">${formatPeso(total)}</td>
        </tr>`;
    }).join('');

    const startDisplay = totalRows === 0 ? 0 : startIndex + 1;
    const endDisplay = totalRows === 0 ? 0 : endIndex;

    document.getElementById('calculateTotalModalPaginationText').textContent = `Showing ${startDisplay} to ${endDisplay} of ${totalRows} rows`;
    document.getElementById('calculateTotalModalPageIndicator').textContent = `Page ${calculateTotalModalCurrentPage} of ${totalPages}`;
    document.getElementById('calculateTotalModalPrev').disabled = calculateTotalModalCurrentPage <= 1;
    document.getElementById('calculateTotalModalNext').disabled = calculateTotalModalCurrentPage >= totalPages;
}

function filterDeliveryRequests() {
    try {
        const companyId = document.getElementById('company_id').value;
        const customerId = document.getElementById('customer_id').value;
        const fromDate = document.getElementById('billing_period_from').value;
        const toDate = document.getElementById('billing_period_to').value;

        // Get additional filters
        const searchTerm = document.getElementById('searchDeliveryRequests').value;
        const companyFilter = document.getElementById('filterByCompany').value;
        const customerFilter = document.getElementById('filterByCustomer').value;

        console.log('=== SOA Filter Query ===');
        console.log('Main Filters:', {
            companyId: companyId || 'All',
            customerId: customerId || 'All',
            fromDate: fromDate || 'Not set',
            toDate: toDate || 'Not set'
        });
        console.log('Additional Filters:', {
            searchTerm: searchTerm || 'Empty',
            companyFilter: companyFilter || 'All',
            customerFilter: customerFilter || 'All'
        });
        console.log('Total delivery line items to filter:', deliveryLineItems.length);

        const container = document.getElementById('deliveryRequestsContainer');
        container.innerHTML = buildSkeletonCards(4);

        setTimeout(() => {
            let html = '<div class="space-y-2">'; // Reduced spacing for more compact display
            let hasItems = false;
            let visibleItems = 0;

            // Get current filters
            const searchTerm = document.getElementById('searchDeliveryRequests').value.toLowerCase();
            const companyFilter = document.getElementById('filterByCompany').value;
            const customerFilter = document.getElementById('filterByCustomer').value;

            deliveryLineItems.forEach(function(lineItem) {
                try {
                    let shouldShow = true;

                    // Always extract delivery request data for display purposes
                    const drDate = lineItem.deliveryRequest ? lineItem.deliveryRequest.deliveryDate : null;
                    const drCompanyId = lineItem.deliveryRequest ? lineItem.deliveryRequest.companyId : '';
                    const drCustomerId = lineItem.deliveryRequest ? lineItem.deliveryRequest.customerId : '';

                    // Apply filters only if they are set
                    if (companyId || customerId || (fromDate && toDate)) {
                        console.log('Checking item:', lineItem.id, 'drCompanyId:', drCompanyId, 'drCustomerId:', drCustomerId, 'drDate:', drDate);
                        console.log('Filters: companyId:', companyId, 'customerId:', customerId, 'fromDate:', fromDate, 'toDate:', toDate);

                        const companyMatch = !companyId || drCompanyId == companyId;
                        const customerMatch = !customerId || drCustomerId == customerId;

                        console.log('Matches: companyMatch:', companyMatch, 'customerMatch:', customerMatch);

                        // Parse dates for proper comparison
                        let dateMatch = true;
                        if (drDate && fromDate && toDate) {
                            try {
                                const deliveryDate = new Date(drDate + 'T00:00:00');
                                const from = new Date(fromDate + 'T00:00:00');
                                const to = new Date(toDate + 'T23:59:59');
                                dateMatch = deliveryDate >= from && deliveryDate <= to;
                                console.log('Date check:', drDate, '->', deliveryDate, '>=', from, '&&', deliveryDate, '<=', to, '=', dateMatch);
                            } catch (e) {
                                console.log('Date parsing error for:', drDate, e);
                                dateMatch = false;
                            }
                        }

                        shouldShow = companyMatch && customerMatch && dateMatch;
                        console.log('Should show:', shouldShow, '(company:', companyMatch, 'customer:', customerMatch, 'date:', dateMatch, ')');
                    }

                    // Apply additional filters (search and dropdown filters)
                    if (shouldShow) {
                        const mtm = lineItem.mtm.toLowerCase();
                        const siteName = (lineItem.siteName || '').toString().toLowerCase();
                        const companyName = (lineItem.deliveryRequest ? lineItem.deliveryRequest.companyName : '').toLowerCase();
                        const customerName = (lineItem.deliveryRequest ? lineItem.deliveryRequest.customerName : '').toLowerCase();

                        const searchMatch = !searchTerm ||
                            mtm.includes(searchTerm) ||
                            siteName.includes(searchTerm) ||
                            companyName.includes(searchTerm) ||
                            customerName.includes(searchTerm);

                        const companyDropdownMatch = !companyFilter || (lineItem.deliveryRequest && lineItem.deliveryRequest.companyId == companyFilter);
                        const customerDropdownMatch = !customerFilter || (lineItem.deliveryRequest && lineItem.deliveryRequest.customerId == customerFilter);

                        shouldShow = shouldShow && searchMatch && companyDropdownMatch && customerDropdownMatch;
                    }

                    if (shouldShow) {
                        hasItems = true;
                        visibleItems++;
                        const totalRate = Number(lineItem.requestAmount || 0) > 0
                            ? Number(lineItem.requestAmount || 0)
                            : (lineItem.accessorialRate + lineItem.addOnRate);
                        const amountToDisplay = totalRate;
                        // Properly format the date for display
                        let formattedBookingDate = 'N/A';
                        let formattedDate = 'N/A';
                        if (lineItem.bookingDate) {
                            try {
                                const bookingDateStr = lineItem.bookingDate.includes('T') ? lineItem.bookingDate : lineItem.bookingDate + 'T00:00:00';
                                const bookingDateObj = new Date(bookingDateStr);
                                if (!isNaN(bookingDateObj.getTime())) {
                                    formattedBookingDate = bookingDateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                                }
                            } catch (e) {
                                console.warn('Date formatting error for booking date:', lineItem.bookingDate, e);
                                formattedBookingDate = lineItem.bookingDate;
                            }
                        }
                        if (drDate) {
                            try {
                                // Ensure proper date parsing by adding time component if missing
                                const dateStr = drDate.includes('T') ? drDate : drDate + 'T00:00:00';
                                const dateObj = new Date(dateStr);
                                if (!isNaN(dateObj.getTime())) {
                                    formattedDate = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                                }
                            } catch (e) {
                                console.warn('Date formatting error for:', drDate, e);
                                formattedDate = drDate; // Fallback to raw date if formatting fails
                            }
                        }

                        const badge = getStatusBadge(lineItem.requestStatus || lineItem.deliveryStatusName);
                        const cName = lineItem.deliveryRequest ? lineItem.deliveryRequest.companyName : 'N/A';
                        const cuName = lineItem.deliveryRequest ? lineItem.deliveryRequest.customerName : 'N/A';

                        // Billing components
                        const drAmt  = Number(lineItem.requestAmount || 0);
                        const acAmt  = Number(lineItem.accessorialRate || 0) + Number(lineItem.addOnRate || 0);
                        const hasDelivery     = drAmt > 0;
                        const hasAccessorial  = acAmt > 0;

                        // Init billing selection for this item (default: nothing selected — user must pick)
                        if (!billingSelections.has(lineItem.id)) {
                            billingSelections.set(lineItem.id, { delivery: false, accessorial: false });
                        }
                        const sel = billingSelections.get(lineItem.id);
                        const billedTotal = (sel.delivery && hasDelivery ? drAmt : 0) + (sel.accessorial && hasAccessorial ? acAmt : 0);

                        const drActive  = sel.delivery    && hasDelivery;
                        const acActive  = sel.accessorial && hasAccessorial;

                        html += `
                            <div class="dr-card border border-gray-200 rounded-xl p-4 cursor-pointer hover:border-blue-300 hover:bg-blue-50/40 transition-all duration-150 dr-card-enter"
                                 style="animation-delay:${visibleItems * 35}ms; opacity:0;"
                                 onclick="toggleCard(this)">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 pt-0.5">
                                        <input type="checkbox" name="delivery_line_item_ids[]" value="${lineItem.id}"
                                               id="line_item_${lineItem.id}" class="delivery-checkbox h-5 w-5 rounded border-gray-300 text-blue-600 cursor-pointer accent-blue-600"
                                               onclick="event.stopPropagation()" onchange="toggleCardStyle(this)">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-wrap items-center gap-2 mb-2">
                                            <span class="font-semibold text-gray-900 text-sm">MTM: ${lineItem.mtm}</span>
                                            <span class="text-xs text-gray-400">#${lineItem.id}</span>
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium ${badge.cls}">${badge.text}</span>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1 text-xs text-gray-500">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <i class="fas fa-calendar-alt text-gray-300 w-3 flex-shrink-0"></i>
                                                <span class="truncate">Booking: ${formattedBookingDate}</span>
                                            </div>
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <i class="fas fa-truck text-gray-300 w-3 flex-shrink-0"></i>
                                                <span class="truncate">Delivery: ${formattedDate}</span>
                                            </div>
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <i class="fas fa-building text-gray-300 w-3 flex-shrink-0"></i>
                                                <span class="truncate">${cName}</span>
                                            </div>
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <i class="fas fa-user text-gray-300 w-3 flex-shrink-0"></i>
                                                <span class="truncate">${cuName}</span>
                                            </div>
                                            <div class="flex items-center gap-1.5 min-w-0 sm:col-span-2">
                                                <i class="fas fa-map-marker-alt text-gray-300 w-3 flex-shrink-0"></i>
                                                <span class="truncate">${lineItem.siteName || 'N/A'}</span>
                                            </div>
                                        </div>

                                        <!-- Billing type toggles -->
                                        <div class="mt-2.5 pt-2 border-t border-gray-100">
                                            <p class="text-xs text-gray-500 font-medium mb-2">Bill for:</p>
                                            <div class="flex flex-wrap gap-2">
                                                ${hasDelivery ? `
                                                <label class="bill-toggle-label inline-flex items-center gap-2 rounded-xl px-3 py-2 border-2 text-xs font-bold select-none transition-all duration-150 active:scale-95 pointer-events-none opacity-30 cursor-not-allowed
                                                       ${drActive
                                                           ? 'border-blue-600 bg-blue-600 text-white shadow-md shadow-blue-200'
                                                           : 'border-gray-300 bg-gray-100 text-gray-400'}"
                                                       data-item="${lineItem.id}" data-type="delivery"
                                                       onclick="event.stopPropagation()">
                                                    <input type="checkbox" class="sr-only" ${drActive ? 'checked' : ''}
                                                           onchange="onBillingToggle(${lineItem.id}, 'delivery', this.checked, event)">
                                                    <span class="inline-flex items-center justify-center w-4 h-4 rounded-full ${drActive ? 'bg-white/30' : 'bg-gray-300/50'}">
                                                        <i class="fas ${drActive ? 'fa-check' : 'fa-truck'} text-xs"></i>
                                                    </span>
                                                    <i class="fas fa-truck text-xs ${drActive ? '' : 'hidden'}"></i>
                                                    Delivery Rate
                                                    <span class="font-extrabold tracking-tight">₱${drAmt.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</span>
                                                </label>` : ''}
                                                ${hasAccessorial ? `
                                                <label class="bill-toggle-label inline-flex items-center gap-2 rounded-xl px-3 py-2 border-2 text-xs font-bold select-none transition-all duration-150 active:scale-95 pointer-events-none opacity-30 cursor-not-allowed
                                                       ${acActive
                                                           ? 'border-emerald-600 bg-emerald-600 text-white shadow-md shadow-emerald-200'
                                                           : 'border-gray-300 bg-gray-100 text-gray-400'}"
                                                       data-item="${lineItem.id}" data-type="accessorial"
                                                       onclick="event.stopPropagation()">
                                                    <input type="checkbox" class="sr-only" ${acActive ? 'checked' : ''}
                                                           onchange="onBillingToggle(${lineItem.id}, 'accessorial', this.checked, event)">
                                                    <span class="inline-flex items-center justify-center w-4 h-4 rounded-full ${acActive ? 'bg-white/30' : 'bg-gray-300/50'}">
                                                        <i class="fas ${acActive ? 'fa-check' : 'fa-tags'} text-xs"></i>
                                                    </span>
                                                    Accessorial
                                                    <span class="font-extrabold tracking-tight">₱${acAmt.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</span>
                                                </label>` : ''}
                                                ${!hasDelivery && !hasAccessorial ? `<span class="text-xs text-gray-400 italic">No rate data</span>` : ''}
                                            </div>
                                            <div class="flex justify-end mt-2">
                                                <span class="font-bold text-emerald-700 text-sm" id="item-billed-${lineItem.id}">
                                                    ₱${billedTotal.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                } catch (error) {
                    console.error('Error processing line item:', lineItem, error);
                }
            });

            html += '</div>';

            if (!hasItems) {
                html = `
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-inbox text-3xl mb-4"></i>
                        <p>No delivery requests found for the selected criteria.</p>
                        <p class="text-sm mt-2">Try adjusting your filters or check if delivery requests exist in the system.</p>
                    </div>
                `;
            }

            container.innerHTML = html;
            document.getElementById('resultsCount').textContent = visibleItems + ' delivery requests found';

            console.log('=== SOA Filter Results ===');
            console.log('Items found:', visibleItems);
            console.log('Has items to display:', hasItems);
            console.log('Total items processed:', deliveryLineItems.length);
            console.log('========================');

            // Populate filter dropdowns with available companies/customers
            populateFilterDropdowns();
        }, 250);
    } catch (error) {
        console.error('Error in filterDeliveryRequests:', error);
        document.getElementById('deliveryRequestsContainer').innerHTML = `
            <div class="text-center py-8 text-red-500">
                <i class="fas fa-exclamation-triangle text-3xl mb-4"></i>
                <p>An error occurred while filtering delivery requests.</p>
                <p class="text-sm mt-2">Please check the browser console for details.</p>
            </div>
        `;
    }
}

function updateSummary() {
    const selectedLineItems = getSelectedLineItems();
    const count = selectedLineItems.length;
    document.getElementById('selectedRequests').textContent = count;

    // Per-component totals
    let drTotal = 0, acTotal = 0;
    selectedLineItems.forEach(li => {
        const drAmt = Number(li.requestAmount || 0);
        const acAmt = Number(li.accessorialRate || 0) + Number(li.addOnRate || 0);
        const sel   = billingSelections.get(li.id) || {};
        if (sel.delivery !== false && drAmt > 0)    drTotal += drAmt;
        if (sel.accessorial !== false && acAmt > 0) acTotal += acAmt;
    });

    // Rate breakdown panel
    const ratePanel = document.getElementById('summaryRateBreakdown');
    if (ratePanel) {
        if (count > 0 && (drTotal > 0 || acTotal > 0)) {
            document.getElementById('summaryDeliveryRateAmt').textContent = `₱${drTotal.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2})}`;
            document.getElementById('summaryAccessorialAmt').textContent  = `₱${acTotal.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2})}`;
            ratePanel.classList.remove('hidden');
            ratePanel.classList.add('flex');
        } else {
            ratePanel.classList.add('hidden');
            ratePanel.classList.remove('flex');
        }
    }

    // Who is affected
    const affectedEl = document.getElementById('summaryAffectedInfo');
    if (affectedEl) {
        const companyDisplay = document.getElementById('company-display')?.textContent?.trim();
        const customerDisplay = document.getElementById('customer-display')?.textContent?.trim();
        const companyValid  = companyDisplay  && companyDisplay  !== 'Select Company';
        const customerValid = customerDisplay && customerDisplay !== 'Select Customer';
        if (count > 0 && (companyValid || customerValid)) {
            if (companyValid)  document.querySelector('#summaryAffectedCompany span').textContent  = companyDisplay;
            if (customerValid) document.querySelector('#summaryAffectedCustomer span').textContent = customerDisplay;
            document.getElementById('summaryAffectedCompany').classList.toggle('hidden', !companyValid);
            document.getElementById('summaryAffectedCustomer').classList.toggle('hidden', !customerValid);
            affectedEl.classList.remove('hidden');
        } else {
            affectedEl.classList.add('hidden');
        }
    }

    const subtotal = drTotal + acTotal;
    const grandTotalEl = document.getElementById('modalGrandTotal');
    if (grandTotalEl) grandTotalEl.dataset.subtotal = subtotal;
    syncAdjustments();
}

function calculateTotal() {
    const checkboxes = document.querySelectorAll('.delivery-checkbox:checked');
    let total = 0;

    checkboxes.forEach(checkbox => {
        const lineItemId = parseInt(checkbox.value);
        // Find the line item in our JavaScript array
        const lineItem = deliveryLineItems.find(item => item.id === lineItemId);
        if (lineItem) {
            const lineItemTotal = Number(lineItem.requestAmount || 0) > 0
                ? Number(lineItem.requestAmount || 0)
                : (Number(lineItem.accessorialRate || 0) + Number(lineItem.addOnRate || 0));
            total += lineItemTotal;
        }
    });

    document.getElementById('totalAmount').textContent = `Total Amount: ₱${total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
}

function openCalculateTotalModal(selectedLineItems, total) {
    const modal = document.getElementById('calculateTotalModal');
    const emptyState = document.getElementById('calculateTotalModalEmpty');
    const content = document.getElementById('calculateTotalModalContent');

    if (selectedLineItems.length === 0) {
        emptyState.classList.remove('hidden');
        content.classList.add('hidden');
        calculateTotalModalItems = [];
        calculateTotalModalCurrentPage = 1;
        document.getElementById('calculateTotalModalRows').innerHTML = '';
        document.getElementById('calculateTotalModalPaginationText').textContent = 'Showing 0 to 0 of 0 rows';
        document.getElementById('calculateTotalModalPageIndicator').textContent = 'Page 1 of 1';
    } else {
        emptyState.classList.add('hidden');
        content.classList.remove('hidden');
        document.getElementById('modalSelectedCount').textContent = selectedLineItems.length;

        // Compute per-component totals
        let drTotal = 0, acTotal = 0;
        selectedLineItems.forEach(li => {
            const drAmt = Number(li.requestAmount || 0);
            const acAmt = Number(li.accessorialRate || 0) + Number(li.addOnRate || 0);
            const sel   = billingSelections.get(li.id) || {};
            if (sel.delivery !== false && drAmt > 0)    drTotal += drAmt;
            if (sel.accessorial !== false && acAmt > 0) acTotal += acAmt;
        });
        document.getElementById('modalDeliveryRateTotal').textContent = formatPeso(drTotal);
        document.getElementById('modalAccessorialTotal').textContent  = formatPeso(acTotal);

        const grandTotalEl = document.getElementById('modalGrandTotal');
        grandTotalEl.textContent = formatPeso(total);
        grandTotalEl.dataset.subtotal = total;
        calculateTotalModalItems = selectedLineItems;
        calculateTotalModalCurrentPage = 1;
        renderCalculateTotalModalRows();
        syncAdjustments();
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function closeCalculateTotalModal() {
    const modal = document.getElementById('calculateTotalModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

function calculateTotal() {
    const selectedLineItems = getSelectedLineItems();
    const total = selectedLineItems.reduce((sum, lineItem) => sum + getLineItemAmount(lineItem), 0);

    document.getElementById('totalAmount').textContent = `Total Amount: ${formatPeso(total)}`;
    openCalculateTotalModal(selectedLineItems, total);
}

// Bulk selection functions
function selectAllItems() {
    document.querySelectorAll('.delivery-checkbox').forEach(cb => {
        cb.checked = true;
        toggleCardStyle(cb);
    });
    updateSummary();
}

function selectNoneItems() {
    document.querySelectorAll('.delivery-checkbox').forEach(cb => {
        cb.checked = false;
        toggleCardStyle(cb);
    });
    updateSummary();
}

// Search and filter functions
function onCompanyChange() {
    const companyValue = document.getElementById('company_id').value;
    console.log('🏢 Main Company filter changed - Selected company ID:', companyValue || 'None');
    filterDeliveryRequests();
}

function onCustomerChange() {
    const customerValue = document.getElementById('customer_id').value;
    console.log('👥 Main Customer filter changed - Selected customer ID:', customerValue || 'None');
    filterDeliveryRequests();
}

function onFromDateChange() {
    const fromDateValue = document.getElementById('billing_period_from').value;
    console.log('📅 From Date filter changed - Selected date:', fromDateValue || 'Not set');
    filterDeliveryRequests();
}

function onToDateChange() {
    const toDateValue = document.getElementById('billing_period_to').value;
    console.log('📅 To Date filter changed - Selected date:', toDateValue || 'Not set');
    filterDeliveryRequests();
}

function filterBySearch() {
    const searchValue = document.getElementById('searchDeliveryRequests').value;
    console.log('🔍 Search filter triggered - Search term:', searchValue || 'Empty');
    filterDeliveryRequests();
}

function filterByCompany() {
    const companyValue = document.getElementById('filterByCompany').value;
    console.log('🏢 Company dropdown filter triggered - Selected company:', companyValue || 'All');
    filterDeliveryRequests();
}

function filterByCustomer() {
    const customerValue = document.getElementById('filterByCustomer').value;
    console.log('👥 Customer dropdown filter triggered - Selected customer:', customerValue || 'All');
    filterDeliveryRequests();
}

// Populate filter dropdowns with available companies/customers
function populateFilterDropdowns() {
    const companySelect = document.getElementById('filterByCompany');
    const customerSelect = document.getElementById('filterByCustomer');

    // Clear existing options except the first one
    companySelect.innerHTML = '<option value="">All Companies</option>';
    customerSelect.innerHTML = '<option value="">All Customers</option>';

    const companies = new Set();
    const customers = new Set();

    // Collect unique companies and customers from ALL items (not just filtered ones)
    deliveryLineItems.forEach(item => {
        if (item.deliveryRequest) {
            if (item.deliveryRequest.companyId && item.deliveryRequest.companyName) {
                companies.add(JSON.stringify({
                    id: item.deliveryRequest.companyId,
                    name: item.deliveryRequest.companyName
                }));
            }
            if (item.deliveryRequest.customerId && item.deliveryRequest.customerName) {
                customers.add(JSON.stringify({
                    id: item.deliveryRequest.customerId,
                    name: item.deliveryRequest.customerName
                }));
            }
        }
    });

    // Add company options
    Array.from(companies).sort((a, b) => {
        const nameA = JSON.parse(a).name;
        const nameB = JSON.parse(b).name;
        return nameA.localeCompare(nameB);
    }).forEach(companyJson => {
        const company = JSON.parse(companyJson);
        const option = document.createElement('option');
        option.value = company.id;
        option.textContent = company.name;
        companySelect.appendChild(option);
    });

    // Add customer options
    Array.from(customers).sort((a, b) => {
        const nameA = JSON.parse(a).name;
        const nameB = JSON.parse(b).name;
        return nameA.localeCompare(nameB);
    }).forEach(customerJson => {
        const customer = JSON.parse(customerJson);
        const option = document.createElement('option');
        option.value = customer.id;
        option.textContent = customer.name;
        customerSelect.appendChild(option);
    });
}

// ── Adjustment sync ───────────────────────────────────────────
function syncAdjustments() {
    const subtotal        = parseFloat(document.getElementById('modalGrandTotal').dataset.subtotal || 0);
    const discountType    = document.getElementById('card_discount_type').value;
    const discountAmt     = Math.max(0, parseFloat(document.getElementById('card_discount_amount').value) || 0);
    const discountRemarks = document.getElementById('card_discount_remarks').value;
    const adjustmentAmt   = parseFloat(document.getElementById('card_adjustment_amount').value) || 0;
    const adjustmentRemarks = document.getElementById('card_adjustment_remarks').value;
    const finalTotal      = Math.max(0, subtotal - discountAmt + adjustmentAmt);

    // Sync hidden form inputs
    document.getElementById('hidden_discount_type').value      = discountType;
    document.getElementById('hidden_discount_amount').value    = discountAmt;
    document.getElementById('hidden_discount_remarks').value   = discountRemarks;
    document.getElementById('hidden_adjustment_amount').value  = adjustmentAmt;
    document.getElementById('hidden_adjustment_remarks').value = adjustmentRemarks;

    // Subtotal
    document.getElementById('summarySubtotal').textContent = formatPeso(subtotal);

    // Discount/Dispute row
    const discRow = document.getElementById('summaryDiscountRow');
    if (discountType && discountAmt > 0) {
        document.getElementById('summaryDiscountLabel').textContent = discountType === 'dispute' ? 'Dispute' : 'Discount';
        document.getElementById('summaryDiscountAmt').textContent = `-${formatPeso(discountAmt)}`;
        const dr = document.getElementById('summaryDiscountRemarks');
        dr.textContent = discountRemarks || '';
        dr.classList.toggle('hidden', !discountRemarks);
        discRow.classList.remove('hidden');
        discRow.classList.add('flex');
    } else {
        discRow.classList.add('hidden');
        discRow.classList.remove('flex');
    }

    // Adjustment row
    const adjRow = document.getElementById('summaryAdjustmentRow');
    if (adjustmentAmt !== 0) {
        const sign = adjustmentAmt >= 0 ? '' : '-';
        document.getElementById('summaryAdjustmentAmt').textContent = `${sign}${formatPeso(Math.abs(adjustmentAmt))}`;
        const ar = document.getElementById('summaryAdjustmentRemarks');
        ar.textContent = adjustmentRemarks || '';
        ar.classList.toggle('hidden', !adjustmentRemarks);
        adjRow.classList.remove('hidden');
        adjRow.classList.add('flex');
    } else {
        adjRow.classList.add('hidden');
        adjRow.classList.remove('flex');
    }

    // Final total
    document.getElementById('totalAmount').textContent = formatPeso(finalTotal);
}

// ── Custom select dropdowns (company & customer) ──────────────
const _csOpen = { company: false, customer: false };

const _companyPendingCounts = {
    @foreach($companies as $company)
    {{ $company->id }}: {{ $companyItemCounts[$company->id] ?? 0 }},
    @endforeach
};
const _customerPendingCounts = {
    @foreach($customers as $customer)
    {{ $customer->id }}: {{ $customerItemCounts[$customer->id] ?? 0 }},
    @endforeach
};

function toggleCustomSelect(type) {
    const wasOpen = _csOpen[type];
    _csCloseAll();
    if (!wasOpen) _csOpen[type] = true, document.getElementById(`${type}-dropdown`).classList.remove('hidden'), document.getElementById(`${type}-chevron`).classList.add('rotate-180');
}

function _csCloseAll() {
    ['company', 'customer'].forEach(t => {
        _csOpen[t] = false;
        document.getElementById(`${t}-dropdown`).classList.add('hidden');
        document.getElementById(`${t}-chevron`).classList.remove('rotate-180');
    });
}

function _applyCustomSelect(type, value, label, pendingCount) {
    const select = document.getElementById(`${type}_id`);
    select.value = value;
    const display = document.getElementById(`${type}-display`);
    if (value) {
        display.textContent = label;
        display.classList.replace('text-gray-400', 'text-gray-900');
    } else {
        display.textContent = type === 'company' ? 'Select Company' : 'Select Customer';
        display.classList.replace('text-gray-900', 'text-gray-400');
    }
    const hint = document.getElementById(`${type}-pending-hint`);
    const hintText = document.getElementById(`${type}-pending-hint-text`);
    if (value && pendingCount > 0) {
        hintText.textContent = `${pendingCount} pending delivery request${pendingCount !== 1 ? 's' : ''} available`;
        hint.classList.remove('hidden');
    } else {
        hint.classList.add('hidden');
    }
    _csCloseAll();
    select.dispatchEvent(new Event('change'));
}

function selectCompanyOption(value, label, pendingCount) { _applyCustomSelect('company', value, label, pendingCount); }
function selectCustomerOption(value, label, pendingCount) { _applyCustomSelect('customer', value, label, pendingCount); }

document.addEventListener('click', function(e) {
    if (!e.target.closest('#company-select-wrapper')) { _csOpen.company = false; const d = document.getElementById('company-dropdown'); if (d) { d.classList.add('hidden'); document.getElementById('company-chevron').classList.remove('rotate-180'); } }
    if (!e.target.closest('#customer-select-wrapper')) { _csOpen.customer = false; const d = document.getElementById('customer-dropdown'); if (d) { d.classList.add('hidden'); document.getElementById('customer-chevron').classList.remove('rotate-180'); } }
});

// ── Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Restore custom select display for old() pre-selections
    ['company', 'customer'].forEach(type => {
        const select = document.getElementById(`${type}_id`);
        if (select && select.value) {
            const opt = select.querySelector(`option[value="${select.value}"]`);
            const counts = type === 'company' ? _companyPendingCounts : _customerPendingCounts;
            if (opt) _applyCustomSelect(type, select.value, opt.textContent.trim(), counts[select.value] || 0);
        }
    });

    // Populate filter dropdowns first
    populateFilterDropdowns();

    // Then filter delivery requests
    filterDeliveryRequests();

    document.getElementById('calculateTotalModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeCalculateTotalModal();
        }
    });

    document.getElementById('calculateTotalModalPageSize').addEventListener('change', function() {
        calculateTotalModalCurrentPage = 1;
        renderCalculateTotalModalRows();
    });

    document.getElementById('calculateTotalModalPrev').addEventListener('click', function() {
        if (calculateTotalModalCurrentPage > 1) {
            calculateTotalModalCurrentPage -= 1;
            renderCalculateTotalModalRows();
        }
    });

    document.getElementById('calculateTotalModalNext').addEventListener('click', function() {
        const pageSize = parseInt(document.getElementById('calculateTotalModalPageSize').value, 10);
        const totalPages = Math.max(1, Math.ceil(calculateTotalModalItems.length / pageSize));

        if (calculateTotalModalCurrentPage < totalPages) {
            calculateTotalModalCurrentPage += 1;
            renderCalculateTotalModalRows();
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeCalculateTotalModal();
        }
    });
});
</script>
@endsection
