@extends('layouts.app')

@section('title', 'Edit Statement of Account')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-6xl mx-auto px-4">
        <div class="mb-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Statement of Account</h1>
                    <p class="text-gray-600 mt-2">Update SOA {{ $soa->soa_number ?? 'N/A' }} with the same flow used in SOA creation.</p>
                </div>
                <a href="{{ route('billing.index') }}" class="inline-flex w-full items-center justify-center rounded-lg bg-gray-500 px-4 py-2 text-white hover:bg-gray-600 sm:w-auto">
                    <i class="fas fa-arrow-left mr-2"></i>Back to SOA List
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('billing.updateSoa', $soa->id) }}" id="editSoaForm">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">SOA Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="company_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Company <span class="text-red-500">*</span>
                        </label>
                        <select id="company_id" name="company_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                onchange="filterEditDeliveryRequests()">
                            <option value="">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ (string) old('company_id', $soa->company_id) === (string) $company->id ? 'selected' : '' }}>
                                    {{ $company->company_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('company_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Customer <span class="text-red-500">*</span>
                        </label>
                        <select id="customer_id" name="customer_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                onchange="filterEditDeliveryRequests()">
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ (string) old('customer_id', $soa->customer_id) === (string) $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="billing_period_from" class="block text-sm font-medium text-gray-700 mb-2">
                            Billing Period From <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="billing_period_from" name="billing_period_from"
                               value="{{ old('billing_period_from', $soa->billing_period_from?->format('Y-m-d')) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('billing_period_from')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="billing_period_to" class="block text-sm font-medium text-gray-700 mb-2">
                            Billing Period To <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="billing_period_to" name="billing_period_to"
                               value="{{ old('billing_period_to', $soa->billing_period_to?->format('Y-m-d')) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('billing_period_to')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="booking_date" class="block text-sm font-medium text-gray-700 mb-2">Booking Date</label>
                        <input type="date" id="booking_date" name="booking_date"
                               value="{{ old('booking_date', $soa->due_date?->format('Y-m-d')) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('booking_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="status" name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @foreach(['draft', 'pending', 'approved', 'paid', 'overdue'] as $status)
                                <option value="{{ $status }}" {{ old('status', $soa->status) === $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea id="notes" name="notes" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Additional notes for this SOA...">{{ old('notes', $soa->notes) }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <div class="flex flex-col gap-3 md:flex-row md:justify-between md:items-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">Select Delivery Requests</h2>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="selectAllRequests()" class="inline-flex items-center justify-center px-3 py-2 text-sm bg-blue-500 text-white rounded hover:bg-blue-600 whitespace-nowrap">
                            Select All
                        </button>
                        <button type="button" onclick="unselectAllRequests()" class="inline-flex items-center justify-center px-3 py-2 text-sm bg-gray-500 text-white rounded hover:bg-gray-600 whitespace-nowrap">
                            Unselect All
                        </button>
                    </div>
                </div>

                <div class="mb-4 flex flex-col gap-4 lg:flex-row">
                    <div class="flex-1">
                        <input type="text" id="editSearchDeliveryRequests" placeholder="Search by MTM, site, or company..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               oninput="filterEditDeliveryRequests()">
                    </div>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:w-auto">
                        <select id="editFilterByCompany" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="filterEditDeliveryRequests()">
                            <option value="">All Companies</option>
                        </select>
                        <select id="editFilterByCustomer" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="filterEditDeliveryRequests()">
                            <option value="">All Customers</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4 flex flex-col gap-3 text-sm text-gray-600 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-2">
                        <span>Show</span>
                        <select id="editDeliveryPageSize" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            <option value="5" selected>5</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span>entries</span>
                    </div>
                    <div>
                        <span id="editResultsCount">{{ count($editableDeliveryRequests) }}</span> delivery requests found
                    </div>
                </div>

                <div id="editDeliveryRequestsContainer" class="max-h-[72vh] overflow-y-auto border border-gray-200 rounded-xl bg-white">
                    @php
                        $selectedIdLookup = array_fill_keys(
                            collect(old('delivery_request_ids', $selectedDeliveryRequestIds))
                                ->map(fn ($id) => (int) $id)
                                ->all(),
                            true
                        );
                    @endphp
                    @forelse($editableDeliveryRequests as $deliveryRequest)
                        @php
                            $requestId = (int) $deliveryRequest->id;
                            $isSelected = isset($selectedIdLookup[$requestId]);
                            $hasCurrentBillingSelection = array_key_exists($requestId, $currentBillingSelections);
                            $billingType = old('item_billing.' . $deliveryRequest->id, $hasCurrentBillingSelection ? $currentBillingSelections[$requestId] : ($deliveryRequest->current_billing_type ?? null));
                            $alreadyBilled = $deliveryRequest->already_billed ?? null;
                            $deliveryLocked = $alreadyBilled === 'delivery_only';
                            $accessorialLocked = $alreadyBilled === 'accessorial_only';
                            $deliverySelected = in_array($billingType, ['delivery_only', 'both'], true) && !$deliveryLocked;
                            $accessorialSelected = in_array($billingType, ['accessorial_only', 'both'], true) && !$accessorialLocked;
                            $partiallyBilled = filled($alreadyBilled);
                        @endphp
                        <div class="edit-delivery-item dr-card dr-card-enter border {{ $isSelected ? 'border-blue-400 bg-blue-50 shadow-sm' : ($partiallyBilled ? 'border-amber-200 bg-amber-50/30' : 'border-gray-200') }} rounded-lg px-3 py-2.5 cursor-pointer hover:border-blue-300 hover:bg-blue-50/40 transition-all duration-150 mb-2 last:mb-0"
                             style="animation-delay:{{ $loop->iteration * 35 }}ms; opacity:0;"
                             data-id="{{ $deliveryRequest->id }}"
                             data-mtm="{{ strtolower($deliveryRequest->mtm ?? '') }}"
                             data-site="{{ strtolower($deliveryRequest->site_name ?? '') }}"
                             data-company-id="{{ $deliveryRequest->company_id ?? '' }}"
                             data-company-name="{{ strtolower($deliveryRequest->company_name ?? '') }}"
                             data-customer-id="{{ $deliveryRequest->customer_id ?? '' }}"
                             data-customer-name="{{ strtolower($deliveryRequest->customer_name ?? '') }}"
                             data-mtm-display="{{ $deliveryRequest->mtm ?? 'N/A' }}"
                             data-site-display="{{ $deliveryRequest->site_name ?? '' }}"
                             data-booking-date="{{ $deliveryRequest->booking_date ?? '' }}"
                             data-delivery-date="{{ $deliveryRequest->delivery_date ?? '' }}"
                             data-delivery-rate="{{ (float) ($deliveryRequest->delivery_rate ?? 0) }}"
                             data-accessorial-total="{{ (float) ($deliveryRequest->accessorial_total ?? 0) }}"
                             data-company-display="{{ $deliveryRequest->company_name ?? 'N/A' }}"
                             data-customer-display="{{ $deliveryRequest->customer_name ?? 'N/A' }}"
                             data-already-billed="{{ $alreadyBilled ?? '' }}"
                             data-current-billing-type="{{ $billingType ?? '' }}"
                             data-partially-billed="{{ $partiallyBilled ? '1' : '0' }}"
                             onclick="toggleEditCard(this)">
                            <div class="flex items-start gap-2.5">
                                <div class="flex-shrink-0 pt-0">
                                    <input type="checkbox"
                                           name="delivery_request_ids[]"
                                           value="{{ $deliveryRequest->id }}"
                                           class="edit-delivery-checkbox h-5 w-5 rounded border-gray-300 text-blue-600 cursor-pointer accent-blue-600"
                                           {{ $isSelected ? 'checked' : '' }}
                                           onclick="event.stopPropagation()"
                                           onchange="toggleEditCardStyle(this)">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-1.5 mb-1.5">
                                        <span class="font-semibold text-[13px] text-gray-900">MTM: {{ $deliveryRequest->mtm ?? 'N/A' }}</span>
                                        <span class="text-[11px] text-gray-400">#{{ $deliveryRequest->id }}</span>
                                        <span class="px-1.5 py-0.5 rounded-full text-[11px] font-medium bg-green-100 text-green-700">{{ $deliveryRequest->status_name ?? 'Delivered' }}</span>
                                        @if($alreadyBilled === 'delivery_only')
                                            <span class="px-1.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-700 border border-amber-200">
                                                <i class="fas fa-truck mr-1 text-[10px]"></i>Delivery billed
                                            </span>
                                        @elseif($alreadyBilled === 'accessorial_only')
                                            <span class="px-1.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-700 border border-amber-200">
                                                <i class="fas fa-tags mr-1 text-[10px]"></i>Accessorial billed
                                            </span>
                                        @elseif($alreadyBilled === 'both')
                                            <span class="px-1.5 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 text-red-700 border border-red-200">
                                                <i class="fas fa-check-circle mr-1 text-[10px]"></i>Fully billed
                                            </span>
                                        @endif
                                        @if($partiallyBilled && $alreadyBilled !== 'both')
                                            <span class="px-1.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-700 border border-amber-200">
                                                <i class="fas fa-exclamation-circle mr-1"></i>Partially billed
                                            </span>
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-x-3 gap-y-0.5 text-[10px] text-gray-500">
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <i class="fas fa-calendar-alt text-gray-300 w-3 flex-shrink-0"></i>
                                            <span class="truncate">Booking: {{ $deliveryRequest->booking_date ? \Carbon\Carbon::parse($deliveryRequest->booking_date)->format('M d, Y') : 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <i class="fas fa-truck text-gray-300 w-3 flex-shrink-0"></i>
                                            <span class="truncate">Delivery: {{ $deliveryRequest->delivery_date ? \Carbon\Carbon::parse($deliveryRequest->delivery_date)->format('M d, Y') : 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <i class="fas fa-building text-gray-300 w-3 flex-shrink-0"></i>
                                            <span class="truncate">{{ $deliveryRequest->company_name ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <i class="fas fa-user text-gray-300 w-3 flex-shrink-0"></i>
                                            <span class="truncate">{{ $deliveryRequest->customer_name ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 min-w-0 sm:col-span-2 xl:col-span-1">
                                            <i class="fas fa-map-marker-alt text-gray-300 w-3 flex-shrink-0"></i>
                                            <span class="truncate">{{ $deliveryRequest->site_name ?: 'N/A' }}</span>
                                        </div>
                                    </div>

                                    <div class="mt-2 pt-2 border-t border-gray-100">
                                        <p class="text-[11px] text-gray-500 font-medium mb-1">Bill for:</p>
                                        <div class="flex flex-wrap gap-1.5">
                                            @if($deliveryLocked)
                                                <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 border-2 border-amber-200 bg-amber-50 text-[11px] font-bold text-amber-600 select-none cursor-not-allowed">
                                                    <span class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full bg-amber-200">
                                                        <i class="fas fa-check text-[10px] text-amber-700"></i>
                                                    </span>
                                                    <i class="fas fa-truck text-[10px]"></i>
                                                    Delivery Rate
                                                    <span class="font-extrabold tracking-tight">P{{ number_format((float) ($deliveryRequest->delivery_rate ?? 0), 2) }}</span>
                                                    <span class="px-1 py-0.5 rounded bg-amber-200 text-amber-700 text-[10px] font-semibold">Billed</span>
                                                </span>
                                            @elseif((float) ($deliveryRequest->delivery_rate ?? 0) > 0)
                                                <label class="edit-bill-toggle-label inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 border-2 text-[11px] font-bold select-none transition-all duration-150 active:scale-95 {{ $isSelected ? 'cursor-pointer' : 'pointer-events-none opacity-30 cursor-not-allowed' }} {{ $deliverySelected ? 'border-blue-600 bg-blue-600 text-white shadow-md shadow-blue-200' : 'border-gray-300 bg-gray-100 text-gray-400' }}"
                                                       data-item="{{ $deliveryRequest->id }}"
                                                       data-type="delivery"
                                                       onclick="event.stopPropagation()">
                                                    <input type="checkbox" class="sr-only" {{ $deliverySelected ? 'checked' : '' }}
                                                           onchange="onEditBillingToggle({{ $deliveryRequest->id }}, 'delivery', this.checked, event)">
                                                    <span class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full {{ $deliverySelected ? 'bg-white/30' : 'bg-gray-300/50' }}">
                                                        <i class="fas {{ $deliverySelected ? 'fa-check' : 'fa-truck' }} text-[10px]"></i>
                                                    </span>
                                                    <i class="edit-bill-type-icon fas fa-truck text-[10px] {{ $deliverySelected ? '' : 'hidden' }}"></i>
                                                    Delivery Rate
                                                    <span class="font-extrabold tracking-tight">P{{ number_format((float) ($deliveryRequest->delivery_rate ?? 0), 2) }}</span>
                                                </label>
                                            @endif

                                            @if($accessorialLocked)
                                                <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 border-2 border-amber-200 bg-amber-50 text-[11px] font-bold text-amber-600 select-none cursor-not-allowed">
                                                    <span class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full bg-amber-200">
                                                        <i class="fas fa-check text-[10px] text-amber-700"></i>
                                                    </span>
                                                    <i class="fas fa-tags text-[10px]"></i>
                                                    Accessorial
                                                    <span class="font-extrabold tracking-tight">P{{ number_format((float) ($deliveryRequest->accessorial_total ?? 0), 2) }}</span>
                                                    <span class="px-1 py-0.5 rounded bg-amber-200 text-amber-700 text-[10px] font-semibold">Billed</span>
                                                </span>
                                            @elseif((float) ($deliveryRequest->accessorial_total ?? 0) > 0)
                                                <label class="edit-bill-toggle-label inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 border-2 text-[11px] font-bold select-none transition-all duration-150 active:scale-95 {{ $isSelected ? 'cursor-pointer' : 'pointer-events-none opacity-30 cursor-not-allowed' }} {{ $accessorialSelected ? 'border-emerald-600 bg-emerald-600 text-white shadow-md shadow-emerald-200' : 'border-gray-300 bg-gray-100 text-gray-400' }}"
                                                       data-item="{{ $deliveryRequest->id }}"
                                                       data-type="accessorial"
                                                       onclick="event.stopPropagation()">
                                                    <input type="checkbox" class="sr-only" {{ $accessorialSelected ? 'checked' : '' }}
                                                           onchange="onEditBillingToggle({{ $deliveryRequest->id }}, 'accessorial', this.checked, event)">
                                                    <span class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full {{ $accessorialSelected ? 'bg-white/30' : 'bg-gray-300/50' }}">
                                                        <i class="fas {{ $accessorialSelected ? 'fa-check' : 'fa-tags' }} text-[10px]"></i>
                                                    </span>
                                                    <i class="edit-bill-type-icon fas fa-tags text-[10px] {{ $accessorialSelected ? '' : 'hidden' }}"></i>
                                                    Accessorial
                                                    <span class="font-extrabold tracking-tight">P{{ number_format((float) ($deliveryRequest->accessorial_total ?? 0), 2) }}</span>
                                                </label>
                                            @endif

                                            @if((float) ($deliveryRequest->delivery_rate ?? 0) <= 0 && (float) ($deliveryRequest->accessorial_total ?? 0) <= 0)
                                                <span class="text-xs text-gray-400 italic">No rate data</span>
                                            @endif
                                        </div>

                                        <div class="flex justify-end mt-1">
                                            <span class="font-bold text-emerald-700 text-sm" id="edit-item-billed-{{ $deliveryRequest->id }}">P0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-4"></i>
                            <p>No delivery requests available for this SOA.</p>
                        </div>
                    @endforelse

                    @if(count($editableDeliveryRequests) > 0)
                        <div id="editDeliveryRequestsEmptyState" class="hidden text-center py-8 text-gray-500">
                            <i class="fas fa-search text-3xl mb-4"></i>
                            <p>No delivery requests found for the selected criteria.</p>
                        </div>
                    @endif
                </div>

                <div class="mt-4 flex flex-col gap-3 text-sm text-gray-600 sm:flex-row sm:items-center sm:justify-between">
                    <p id="editDeliveryPaginationText">Showing 0 to 0 of 0 entries</p>
                    <div class="flex items-center gap-2">
                        <button type="button" id="editDeliveryPrev" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50">
                            Previous
                        </button>
                        <span id="editDeliveryPageIndicator" class="min-w-[88px] text-center font-medium text-gray-700">Page 1 of 1</span>
                        <button type="button" id="editDeliveryNext" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50">
                            Next
                        </button>
                    </div>
                </div>

                @error('delivery_request_ids')
                    <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">SOA Summary</h3>
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                    <div class="flex-1 min-w-0 space-y-4">
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-sliders-h text-gray-400"></i> Adjustments
                            </h4>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 mb-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                                    <select name="discount_type" id="edit_discount_type" onchange="updateEditSummary()"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                        <option value="" {{ old('discount_type', $soa->discount_type) == '' ? 'selected' : '' }}>None</option>
                                        <option value="discount" {{ old('discount_type', $soa->discount_type) == 'discount' ? 'selected' : '' }}>Discount</option>
                                        <option value="dispute" {{ old('discount_type', $soa->discount_type) == 'dispute' ? 'selected' : '' }}>Dispute</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Amount (P)</label>
                                    <input type="number" name="discount_amount" id="edit_discount_amount"
                                           min="0" step="0.01"
                                           value="{{ old('discount_amount', $soa->discount_amount ?? 0) }}"
                                           onchange="updateEditSummary()" oninput="updateEditSummary()"
                                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Remarks / Reason</label>
                                    <input type="text" name="discount_remarks" id="edit_discount_remarks"
                                           maxlength="1000"
                                           value="{{ old('discount_remarks', $soa->discount_remarks) }}"
                                           placeholder="Why this discount/dispute?"
                                           onchange="updateEditSummary()" oninput="updateEditSummary()"
                                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Manual Adjustment (P)</label>
                                    <input type="number" name="adjustment_amount" id="edit_adjustment_amount"
                                           step="0.01"
                                           value="{{ old('adjustment_amount', $soa->adjustment_amount ?? 0) }}"
                                           onchange="updateEditSummary()" oninput="updateEditSummary()"
                                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    <p class="mt-1 text-xs text-gray-400">Negative to deduct, positive to add</p>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Adjustment Remarks</label>
                                    <input type="text" name="adjustment_remarks" id="edit_adjustment_remarks"
                                           maxlength="1000"
                                           value="{{ old('adjustment_remarks', $soa->adjustment_remarks) }}"
                                           placeholder="Reason for manual adjustment"
                                           onchange="updateEditSummary()" oninput="updateEditSummary()"
                                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                </div>
                            </div>

                            <div class="mt-3 pt-3 border-t border-gray-200 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Withholding Tax</label>
                                    <select name="withholding_tax_rate" id="edit_withholding_tax_rate" onchange="updateEditSummary()"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                        <option value="0" {{ (float) old('withholding_tax_rate', $soa->withholding_tax_rate ?? 0) == 0 ? 'selected' : '' }}>None</option>
                                        <option value="2" {{ (float) old('withholding_tax_rate', $soa->withholding_tax_rate ?? 0) == 2 ? 'selected' : '' }}>2%</option>
                                        <option value="5" {{ (float) old('withholding_tax_rate', $soa->withholding_tax_rate ?? 0) == 5 ? 'selected' : '' }}>5%</option>
                                        <option value="10" {{ (float) old('withholding_tax_rate', $soa->withholding_tax_rate ?? 0) == 10 ? 'selected' : '' }}>10%</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">VAT</label>
                                    <label class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 cursor-pointer hover:bg-gray-50 transition">
                                        <input type="checkbox" id="edit_vat_applied" name="vat_applied" value="1"
                                               {{ old('vat_applied', (float) ($soa->vat_amount ?? 0) > 0 ? '1' : '') == '1' ? 'checked' : '' }}
                                               onchange="updateEditSummary()" class="h-4 w-4 rounded text-orange-500 accent-orange-500">
                                        <span class="text-sm text-gray-700">Apply 12% VAT</span>
                                    </label>
                                </div>
                            </div>

                            <input type="hidden" name="vat_amount" id="edit_hidden_vat_amount" value="{{ old('vat_amount', $soa->vat_amount ?? 0) }}">
                            <input type="hidden" name="withholding_tax_amount" id="edit_hidden_withholding_tax_amount" value="{{ old('withholding_tax_amount', $soa->withholding_tax_amount ?? 0) }}">
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm space-y-2">
                            <div class="flex justify-between text-gray-600">
                                <span class="flex items-center gap-1.5"><i class="fas fa-boxes text-gray-400 text-xs"></i> Selected Requests</span>
                                <span id="editSelectedRequests" class="font-semibold text-gray-800">0</span>
                            </div>
                            <div id="editRateBreakdown" class="hidden flex-col gap-1 pl-1">
                                <div class="flex justify-between text-sky-600 text-xs">
                                    <span class="flex items-center gap-1"><i class="fas fa-truck text-xs w-3"></i> Delivery Rate</span>
                                    <span id="editDeliveryRateAmt" class="font-medium">P0.00</span>
                                </div>
                                <div class="flex justify-between text-purple-600 text-xs">
                                    <span class="flex items-center gap-1"><i class="fas fa-tags text-xs w-3"></i> Accessorial</span>
                                    <span id="editAccessorialAmt" class="font-medium">P0.00</span>
                                </div>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span class="flex items-center gap-1.5"><i class="fas fa-receipt text-gray-400 text-xs"></i> Subtotal</span>
                                <span id="editSubtotal" class="font-semibold text-gray-800">P0.00</span>
                            </div>
                            <div id="editDiscountRow" class="hidden flex-col gap-0.5">
                                <div class="flex justify-between text-red-600">
                                    <span class="flex items-center gap-1.5"><i class="fas fa-tag text-xs"></i> <span id="editDiscountLabel">Discount</span></span>
                                    <span id="editDiscountAmt" class="font-semibold">-P0.00</span>
                                </div>
                                <p id="editDiscountRemarks" class="text-xs text-gray-400 italic pl-5 hidden"></p>
                            </div>
                            <div id="editAdjustmentRow" class="hidden flex-col gap-0.5">
                                <div class="flex justify-between text-blue-600">
                                    <span class="flex items-center gap-1.5"><i class="fas fa-sliders-h text-xs"></i> Manual Adjustment</span>
                                    <span id="editAdjustmentAmt" class="font-semibold">P0.00</span>
                                </div>
                                <p id="editAdjustmentRemarks" class="text-xs text-gray-400 italic pl-5 hidden"></p>
                            </div>
                            <div id="editVatRow" class="hidden justify-between text-orange-600">
                                <span class="flex items-center gap-1.5"><i class="fas fa-percentage text-xs"></i> VAT (12%)</span>
                                <span id="editVatAmt" class="font-semibold">+P0.00</span>
                            </div>
                            <div id="editWtaxRow" class="hidden justify-between text-indigo-600">
                                <span class="flex items-center gap-1.5"><i class="fas fa-minus-circle text-xs"></i> <span id="editWtaxLabel">WHT (2%)</span></span>
                                <span id="editWtaxAmt" class="font-semibold">-P0.00</span>
                            </div>
                            <div class="flex justify-between font-bold text-gray-900 border-t border-gray-300 pt-2">
                                <span class="flex items-center gap-1.5"><i class="fas fa-check-circle text-emerald-500 text-xs"></i> Final Total</span>
                                <span id="editTotalAmount" class="text-emerald-700 text-base">P0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row lg:flex-col lg:w-48">
                        <button type="button" onclick="calculateEditTotal()" class="inline-flex w-full items-center justify-center rounded-lg bg-blue-500 px-6 py-2.5 text-white hover:bg-blue-600">
                            <i class="fas fa-calculator mr-2"></i>Show Summary
                        </button>
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-green-600 px-6 py-2.5 text-white hover:bg-green-700">
                            <i class="fas fa-save mr-2"></i>Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="editValidationModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200/80 overflow-hidden">
        <div class="flex items-center gap-4 px-6 pt-6 pb-4">
            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Missing Required Fields</h3>
                <p class="text-sm text-gray-400 mt-0.5">Please complete the fields below before saving your SOA changes.</p>
            </div>
        </div>
        <div class="px-6 pb-2" id="editValidationModalErrors"></div>
        <div class="px-6 pb-6 pt-4 flex flex-col gap-2 sm:flex-row">
            <button type="button" onclick="closeEditValidationModal()" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
                <i class="fas fa-times mr-2"></i>Dismiss
            </button>
            <button type="button" onclick="closeEditValidationModal()" class="w-full rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 transition">
                <i class="fas fa-arrow-left mr-2"></i>Go back and fix
            </button>
        </div>
    </div>
</div>

<div id="editSummaryModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/65 px-4 backdrop-blur-[2px]">
    <div class="w-full max-w-5xl rounded-xl bg-white shadow-[0_30px_80px_rgba(15,23,42,0.35)] ring-1 ring-slate-200/80">
        <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-4 py-4 sm:px-6">
            <div>
                <h3 class="text-xl font-semibold text-gray-900">Selected Delivery Request Summary</h3>
                <p class="mt-1 text-sm text-gray-500">Review selected delivery requests and totals before saving.</p>
            </div>
            <button type="button" onclick="closeEditSummaryModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="max-h-[70vh] overflow-y-auto px-4 py-4 sm:px-6">
            <div id="editSummaryModalEmpty" class="hidden rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-10 text-center text-gray-500">
                <i class="fas fa-inbox text-3xl mb-3"></i>
                <p class="font-medium">No delivery requests selected yet.</p>
            </div>

            <div id="editSummaryModalContent" class="hidden">
                <div class="mb-4 grid grid-cols-2 gap-3 md:grid-cols-4">
                    <div class="rounded-lg bg-blue-50 px-4 py-3">
                        <p class="text-xs text-blue-600 font-medium">Selected</p>
                        <p id="editModalSelectedCount" class="mt-1 text-2xl font-bold text-blue-900">0</p>
                    </div>
                    <div class="rounded-lg bg-sky-50 px-4 py-3">
                        <p class="text-xs text-sky-600 font-medium flex items-center gap-1"><i class="fas fa-truck text-xs"></i> Delivery Rate</p>
                        <p id="editModalDeliveryRateTotal" class="mt-1 text-lg font-bold text-sky-700">PHP 0.00</p>
                    </div>
                    <div class="rounded-lg bg-purple-50 px-4 py-3">
                        <p class="text-xs text-purple-600 font-medium flex items-center gap-1"><i class="fas fa-tags text-xs"></i> Accessorial</p>
                        <p id="editModalAccessorialTotal" class="mt-1 text-lg font-bold text-purple-700">PHP 0.00</p>
                    </div>
                    <div class="rounded-lg bg-emerald-50 px-4 py-3">
                        <p class="text-xs text-emerald-600 font-medium">Grand Subtotal</p>
                        <p id="editModalGrandTotal" class="mt-1 text-lg font-bold text-emerald-700">PHP 0.00</p>
                    </div>
                </div>

                <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p id="editModalPaginationText" class="text-sm text-gray-500">Showing 0 to 0 of 0 rows</p>
                    <div class="flex items-center gap-2 self-start sm:self-auto">
                        <span class="text-sm text-gray-500">Rows</span>
                        <select id="editModalPageSize" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
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
                                <th class="px-4 py-3 font-semibold">MTM</th>
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
                        <tbody id="editModalRows" class="divide-y divide-gray-200 text-gray-700"></tbody>
                    </table>
                </div>

                <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
                    <button type="button" id="editModalPrev" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50">Previous</button>
                    <span id="editModalPageIndicator" class="min-w-[88px] text-center text-sm font-medium text-gray-700">Page 1 of 1</span>
                    <button type="button" id="editModalNext" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50">Next</button>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-gray-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-end sm:px-6">
            <button type="button" onclick="closeEditSummaryModal()" class="inline-flex w-full items-center justify-center rounded-lg bg-gray-100 px-4 py-2 text-gray-700 hover:bg-gray-200 sm:w-auto">
                Close
            </button>
        </div>
    </div>
</div>

<script>
const editLineItems = Array.from(document.querySelectorAll('.edit-delivery-item')).map((item) => ({
    id: parseInt(item.dataset.id || '0', 10),
    mtm: item.dataset.mtmDisplay || 'N/A',
    siteName: item.dataset.siteDisplay || '',
    bookingDate: item.dataset.bookingDate || '',
    deliveryDate: item.dataset.deliveryDate || '',
    deliveryRate: Number(item.dataset.deliveryRate || 0),
    accessorialTotal: Number(item.dataset.accessorialTotal || 0),
    companyId: item.dataset.companyId || '',
    companyName: item.dataset.companyDisplay || 'N/A',
    customerId: item.dataset.customerId || '',
    customerName: item.dataset.customerDisplay || 'N/A',
    alreadyBilled: item.dataset.alreadyBilled || '',
    currentBillingType: item.dataset.currentBillingType || '',
}));

let editModalItems = [];
let editModalCurrentPage = 1;
const editCheckedItemIds = new Set();
const editBillingSelections = new Map();
let editDeliveryCurrentPage = 1;
let editDeliveryPageSize = 5;

const _editDrStyle = document.createElement('style');
_editDrStyle.textContent = `
@keyframes drFadeIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }
.dr-card-enter { animation: drFadeIn 0.2s ease forwards; }
.edit-delivery-item { content-visibility: auto; contain-intrinsic-size: 170px; }
#editDeliveryRequestsContainer .dr-card { padding: 0.5rem 0.625rem !important; border-radius: 0.5rem !important; margin-bottom: 0.375rem !important; }
#editDeliveryRequestsContainer .dr-card > div { gap: 0.5rem !important; }
#editDeliveryRequestsContainer .edit-delivery-checkbox { width: 1rem !important; height: 1rem !important; }
#editDeliveryRequestsContainer .dr-card .flex.flex-wrap.items-center { gap: 0.25rem !important; margin-bottom: 0.25rem !important; }
#editDeliveryRequestsContainer .dr-card .grid { gap: 0.125rem 0.5rem !important; font-size: 0.675rem !important; line-height: 1.15 !important; }
#editDeliveryRequestsContainer .dr-card .grid i { width: 0.65rem !important; font-size: 0.55rem !important; }
#editDeliveryRequestsContainer .dr-card .edit-bill-toggle-label,
#editDeliveryRequestsContainer .dr-card .select-none { gap: 0.25rem !important; padding: 0.25rem 0.5rem !important; border-radius: 0.375rem !important; font-size: 0.625rem !important; }
#editDeliveryRequestsContainer .dr-card .edit-bill-toggle-label span.inline-flex,
#editDeliveryRequestsContainer .dr-card .select-none span.inline-flex { width: 0.75rem !important; height: 0.75rem !important; }
#editDeliveryRequestsContainer .dr-card .edit-bill-toggle-label i,
#editDeliveryRequestsContainer .dr-card .select-none i { font-size: 0.55rem !important; }
#editDeliveryRequestsContainer .dr-card .font-extrabold { font-size: 0.7rem !important; }
`;
document.head.appendChild(_editDrStyle);

function formatPesoEdit(amount) {
    return `P${Number(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function formatPHP(amount) {
    return `PHP ${Number(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function formatDateEdit(value) {
    if (!value) return 'N/A';
    try {
        const normalized = value.includes('T') ? value : `${value}T00:00:00`;
        const date = new Date(normalized);
        return Number.isNaN(date.getTime())
            ? value
            : date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    } catch (error) {
        return value;
    }
}

function getEditItem(itemId) {
    return editLineItems.find((item) => item.id === itemId);
}

function getEditSelection(itemId) {
    if (!editBillingSelections.has(itemId)) {
        const item = getEditItem(itemId);
        const type = item?.currentBillingType || '';
        const hasSavedSelection = ['delivery_only', 'accessorial_only', 'both'].includes(type);
        editBillingSelections.set(itemId, {
            delivery: hasSavedSelection && ['delivery_only', 'both'].includes(type) && item?.alreadyBilled !== 'delivery_only',
            accessorial: hasSavedSelection && ['accessorial_only', 'both'].includes(type) && item?.alreadyBilled !== 'accessorial_only',
        });
    }

    return editBillingSelections.get(itemId);
}

function getEditBillingType(itemId) {
    const item = getEditItem(itemId);
    const selection = getEditSelection(itemId);
    const deliverySelected = item && item.alreadyBilled !== 'delivery_only' && selection.delivery && Number(item.deliveryRate || 0) > 0;
    const accessorialSelected = item && item.alreadyBilled !== 'accessorial_only' && selection.accessorial && Number(item.accessorialTotal || 0) > 0;

    if (deliverySelected && accessorialSelected) return 'both';
    if (deliverySelected) return 'delivery_only';
    if (accessorialSelected) return 'accessorial_only';
    return '';
}

function getEditBilledAmount(itemId) {
    const item = getEditItem(itemId);
    const selection = getEditSelection(itemId);

    if (!item || !editCheckedItemIds.has(itemId)) {
        return 0;
    }

    let total = 0;

    if (item.alreadyBilled !== 'delivery_only' && selection.delivery && Number(item.deliveryRate || 0) > 0) {
        total += Number(item.deliveryRate || 0);
    }

    if (item.alreadyBilled !== 'accessorial_only' && selection.accessorial && Number(item.accessorialTotal || 0) > 0) {
        total += Number(item.accessorialTotal || 0);
    }

    return total;
}

function syncToggleVisual(itemId, type, checked) {
    const label = document.querySelector(`.edit-bill-toggle-label[data-item="${itemId}"][data-type="${type}"]`);
    if (!label) return;

    const isDelivery = type === 'delivery';
    const activeClasses = isDelivery
        ? ['border-blue-600', 'bg-blue-600', 'text-white', 'shadow-md', 'shadow-blue-200']
        : ['border-emerald-600', 'bg-emerald-600', 'text-white', 'shadow-md', 'shadow-emerald-200'];
    const inactiveClasses = ['border-gray-300', 'bg-gray-100', 'text-gray-400', 'opacity-60'];

    if (checked) {
        label.classList.remove(...inactiveClasses);
        label.classList.add(...activeClasses);
    } else {
        label.classList.remove(...activeClasses);
        label.classList.add(...inactiveClasses);
    }

    const circle = label.querySelector('span');
    if (circle) {
        circle.classList.toggle('bg-white/30', checked);
        circle.classList.toggle('bg-gray-300/50', !checked);
    }

    const icon = label.querySelector('span > i');
    if (icon) {
        icon.classList.remove('fa-check', 'fa-truck', 'fa-tags');
        icon.classList.add(checked ? 'fa-check' : (isDelivery ? 'fa-truck' : 'fa-tags'));
    }

    const typeIcon = label.querySelector('.edit-bill-type-icon');
    if (typeIcon) {
        typeIcon.classList.toggle('hidden', !checked);
    }
}

function onEditBillingToggle(itemId, type, checked, event) {
    if (event) event.stopPropagation();

    const item = getEditItem(itemId);
    if (!item) return;

    if ((type === 'delivery' && item.alreadyBilled === 'delivery_only')
        || (type === 'accessorial' && item.alreadyBilled === 'accessorial_only')) {
        return;
    }

    const selection = getEditSelection(itemId);
    selection[type] = checked;
    editBillingSelections.set(itemId, selection);

    syncToggleVisual(itemId, type, checked);

    const totalEl = document.getElementById(`edit-item-billed-${itemId}`);
    if (totalEl) {
        totalEl.textContent = formatPesoEdit(getEditBilledAmount(itemId));
    }

    updateEditSummary();
}

function toggleEditCard(cardEl) {
    const checkbox = cardEl.querySelector('.edit-delivery-checkbox');
    if (!checkbox) return;

    checkbox.checked = !checkbox.checked;
    toggleEditCardStyle(checkbox);
}

function toggleEditCardStyle(checkbox) {
    const card = checkbox.closest('.dr-card');
    if (!card) return;

    const itemId = parseInt(checkbox.value, 10);

    if (checkbox.checked) {
        editCheckedItemIds.add(itemId);
        card.classList.add('border-blue-400', 'bg-blue-50', 'shadow-sm');
        card.classList.remove('border-gray-200', 'border-amber-200', 'bg-amber-50/30');
    } else {
        editCheckedItemIds.delete(itemId);
        editBillingSelections.set(itemId, { delivery: false, accessorial: false });
        card.classList.remove('border-blue-400', 'bg-blue-50', 'shadow-sm', 'border-amber-200', 'bg-amber-50/30');
        card.classList.add(card.dataset.partiallyBilled === '1' ? 'border-amber-200' : 'border-gray-200');
        if (card.dataset.partiallyBilled === '1') {
            card.classList.add('bg-amber-50/30');
        }
    }

    const selection = getEditSelection(itemId);
    card.querySelectorAll('.edit-bill-toggle-label').forEach((label) => {
        const type = label.dataset.type;

        if (checkbox.checked) {
            label.classList.remove('pointer-events-none', 'opacity-30', 'cursor-not-allowed');
            label.classList.add('cursor-pointer');
            syncToggleVisual(itemId, type, Boolean(selection[type]));
        } else {
            label.classList.add('pointer-events-none', 'opacity-30', 'cursor-not-allowed');
            label.classList.remove('cursor-pointer');
            syncToggleVisual(itemId, type, false);
        }
    });

    const totalEl = document.getElementById(`edit-item-billed-${itemId}`);
    if (totalEl) {
        totalEl.textContent = formatPesoEdit(getEditBilledAmount(itemId));
    }

    updateEditSummary();
}

function populateEditFilterDropdowns() {
    const companySelect = document.getElementById('editFilterByCompany');
    const customerSelect = document.getElementById('editFilterByCustomer');

    if (!companySelect || !customerSelect) return;

    companySelect.innerHTML = '<option value="">All Companies</option>';
    customerSelect.innerHTML = '<option value="">All Customers</option>';

    const companies = new Map();
    const customers = new Map();

    editLineItems.forEach((item) => {
        if (item.companyId && item.companyName) {
            companies.set(String(item.companyId), item.companyName);
        }

        if (item.customerId && item.customerName) {
            customers.set(String(item.customerId), item.customerName);
        }
    });

    Array.from(companies.entries()).sort((a, b) => a[1].localeCompare(b[1])).forEach(([id, name]) => {
        const option = document.createElement('option');
        option.value = id;
        option.textContent = name;
        companySelect.appendChild(option);
    });

    Array.from(customers.entries()).sort((a, b) => a[1].localeCompare(b[1])).forEach(([id, name]) => {
        const option = document.createElement('option');
        option.value = id;
        option.textContent = name;
        customerSelect.appendChild(option);
    });
}

function filterEditDeliveryRequests(resetPage = true) {
    const searchTerm = (document.getElementById('editSearchDeliveryRequests')?.value || '').toLowerCase().trim();
    const companyFilter = document.getElementById('editFilterByCompany')?.value || '';
    const customerFilter = document.getElementById('editFilterByCustomer')?.value || '';
    const resultsCount = document.getElementById('editResultsCount');
    const emptyState = document.getElementById('editDeliveryRequestsEmptyState');
    const items = Array.from(document.querySelectorAll('.edit-delivery-item'));
    const matchedItems = [];

    if (resetPage) {
        editDeliveryCurrentPage = 1;
    }

    items.forEach((item) => {
        const mtm = item.dataset.mtm || '';
        const site = item.dataset.site || '';
        const companyName = item.dataset.companyName || '';
        const customerName = item.dataset.customerName || '';
        const itemCompanyId = item.dataset.companyId || '';
        const itemCustomerId = item.dataset.customerId || '';

        const matchesSearch = !searchTerm
            || mtm.includes(searchTerm)
            || site.includes(searchTerm)
            || companyName.includes(searchTerm)
            || customerName.includes(searchTerm);
        const matchesCompanyFilter = !companyFilter || itemCompanyId === companyFilter;
        const matchesCustomerFilter = !customerFilter || itemCustomerId === customerFilter;

        if (matchesSearch && matchesCompanyFilter && matchesCustomerFilter) {
            matchedItems.push(item);
        }
    });

    const visibleCount = matchedItems.length;
    const totalPages = Math.max(1, Math.ceil(visibleCount / editDeliveryPageSize));

    if (editDeliveryCurrentPage > totalPages) {
        editDeliveryCurrentPage = totalPages;
    }

    const startIndex = visibleCount === 0 ? 0 : (editDeliveryCurrentPage - 1) * editDeliveryPageSize;
    const endIndex = Math.min(startIndex + editDeliveryPageSize, visibleCount);

    items.forEach((item) => {
        item.classList.add('hidden');
    });

    matchedItems.forEach((item, index) => {
        const isOnCurrentPage = index >= startIndex && index < endIndex;
        item.classList.toggle('hidden', !isOnCurrentPage);
    });

    if (resultsCount) {
        resultsCount.textContent = visibleCount;
    }

    if (emptyState) {
        emptyState.classList.toggle('hidden', visibleCount !== 0);
    }

    const paginationText = document.getElementById('editDeliveryPaginationText');
    if (paginationText) {
        paginationText.textContent = `Showing ${visibleCount === 0 ? 0 : startIndex + 1} to ${visibleCount === 0 ? 0 : endIndex} of ${visibleCount} entries`;
    }

    const pageIndicator = document.getElementById('editDeliveryPageIndicator');
    if (pageIndicator) {
        pageIndicator.textContent = `Page ${totalPages === 0 ? 1 : editDeliveryCurrentPage} of ${totalPages}`;
    }

    const prevButton = document.getElementById('editDeliveryPrev');
    if (prevButton) {
        prevButton.disabled = editDeliveryCurrentPage <= 1 || visibleCount === 0;
    }

    const nextButton = document.getElementById('editDeliveryNext');
    if (nextButton) {
        nextButton.disabled = editDeliveryCurrentPage >= totalPages || visibleCount === 0;
    }
}

function changeEditDeliveryPage(page) {
    editDeliveryCurrentPage = Math.max(1, page);
    filterEditDeliveryRequests(false);
}

function changeEditDeliveryPageSize() {
    editDeliveryPageSize = parseInt(document.getElementById('editDeliveryPageSize')?.value || '5', 10);
    editDeliveryCurrentPage = 1;
    filterEditDeliveryRequests(false);
}

function updateEditSummary() {
    let drTotal = 0;
    let acTotal = 0;

    editCheckedItemIds.forEach((itemId) => {
        const item = getEditItem(itemId);
        const selection = getEditSelection(itemId);
        if (!item) return;

        if (item.alreadyBilled !== 'delivery_only' && selection.delivery && Number(item.deliveryRate || 0) > 0) {
            drTotal += Number(item.deliveryRate || 0);
        }

        if (item.alreadyBilled !== 'accessorial_only' && selection.accessorial && Number(item.accessorialTotal || 0) > 0) {
            acTotal += Number(item.accessorialTotal || 0);
        }
    });

    const subtotal = drTotal + acTotal;
    const discountType = document.getElementById('edit_discount_type').value;
    const discountAmt = Math.max(0, parseFloat(document.getElementById('edit_discount_amount').value) || 0);
    const discountRemarks = document.getElementById('edit_discount_remarks').value;
    const adjustmentAmt = parseFloat(document.getElementById('edit_adjustment_amount').value) || 0;
    const adjustmentRemarks = document.getElementById('edit_adjustment_remarks').value;
    const vatApplied = document.getElementById('edit_vat_applied')?.checked || false;
    const withholdingTaxRate = parseFloat(document.getElementById('edit_withholding_tax_rate')?.value || 0);

    const netAmount = subtotal - discountAmt + adjustmentAmt;
    const vatAmount = vatApplied ? netAmount * 0.12 : 0;
    const withholdingTaxAmount = withholdingTaxRate > 0 ? (netAmount * withholdingTaxRate / 100) : 0;
    const finalTotal = Math.max(0, netAmount + vatAmount - withholdingTaxAmount);

    document.getElementById('edit_hidden_vat_amount').value = vatAmount.toFixed(2);
    document.getElementById('edit_hidden_withholding_tax_amount').value = withholdingTaxAmount.toFixed(2);
    document.getElementById('editSelectedRequests').textContent = editCheckedItemIds.size;

    const ratePanel = document.getElementById('editRateBreakdown');
    if (editCheckedItemIds.size > 0 && (drTotal > 0 || acTotal > 0)) {
        document.getElementById('editDeliveryRateAmt').textContent = formatPesoEdit(drTotal);
        document.getElementById('editAccessorialAmt').textContent = formatPesoEdit(acTotal);
        ratePanel.classList.remove('hidden');
        ratePanel.classList.add('flex');
    } else {
        ratePanel.classList.add('hidden');
        ratePanel.classList.remove('flex');
    }

    document.getElementById('editSubtotal').textContent = formatPesoEdit(subtotal);

    const discountRow = document.getElementById('editDiscountRow');
    if (discountType && discountAmt > 0) {
        document.getElementById('editDiscountLabel').textContent = discountType === 'dispute' ? 'Dispute' : 'Discount';
        document.getElementById('editDiscountAmt').textContent = `-${formatPesoEdit(discountAmt)}`;
        const remarks = document.getElementById('editDiscountRemarks');
        remarks.textContent = discountRemarks || '';
        remarks.classList.toggle('hidden', !discountRemarks);
        discountRow.classList.remove('hidden');
        discountRow.classList.add('flex');
    } else {
        discountRow.classList.add('hidden');
        discountRow.classList.remove('flex');
    }

    const adjustmentRow = document.getElementById('editAdjustmentRow');
    if (adjustmentAmt !== 0) {
        document.getElementById('editAdjustmentAmt').textContent = `${adjustmentAmt >= 0 ? '' : '-'}${formatPesoEdit(Math.abs(adjustmentAmt))}`;
        const remarks = document.getElementById('editAdjustmentRemarks');
        remarks.textContent = adjustmentRemarks || '';
        remarks.classList.toggle('hidden', !adjustmentRemarks);
        adjustmentRow.classList.remove('hidden');
        adjustmentRow.classList.add('flex');
    } else {
        adjustmentRow.classList.add('hidden');
        adjustmentRow.classList.remove('flex');
    }

    const vatRow = document.getElementById('editVatRow');
    if (vatApplied && vatAmount > 0) {
        document.getElementById('editVatAmt').textContent = `+${formatPesoEdit(vatAmount)}`;
        vatRow.classList.remove('hidden');
        vatRow.classList.add('flex');
    } else {
        vatRow.classList.add('hidden');
        vatRow.classList.remove('flex');
    }

    const wtaxRow = document.getElementById('editWtaxRow');
    if (withholdingTaxRate > 0 && withholdingTaxAmount > 0) {
        document.getElementById('editWtaxLabel').textContent = `WHT (${withholdingTaxRate}%)`;
        document.getElementById('editWtaxAmt').textContent = `-${formatPesoEdit(withholdingTaxAmount)}`;
        wtaxRow.classList.remove('hidden');
        wtaxRow.classList.add('flex');
    } else {
        wtaxRow.classList.add('hidden');
        wtaxRow.classList.remove('flex');
    }

    document.getElementById('editTotalAmount').textContent = formatPesoEdit(finalTotal);
}

function calculateEditTotal() {
    editModalItems = Array.from(editCheckedItemIds)
        .map((itemId) => {
            const item = getEditItem(itemId);
            if (!item) return null;

            return {
                ...item,
                billedFor: getEditBillingType(itemId),
                billedAmount: getEditBilledAmount(itemId),
            };
        })
        .filter(Boolean);

    const modal = document.getElementById('editSummaryModal');
    const emptyEl = document.getElementById('editSummaryModalEmpty');
    const contentEl = document.getElementById('editSummaryModalContent');

    if (!editModalItems.length) {
        emptyEl.classList.remove('hidden');
        contentEl.classList.add('hidden');
    } else {
        emptyEl.classList.add('hidden');
        contentEl.classList.remove('hidden');

        const deliveryTotal = editModalItems.reduce((sum, item) => {
            const type = item.billedFor;
            return sum + (['delivery_only', 'both'].includes(type) ? Number(item.deliveryRate || 0) : 0);
        }, 0);

        const accessorialTotal = editModalItems.reduce((sum, item) => {
            const type = item.billedFor;
            return sum + (['accessorial_only', 'both'].includes(type) ? Number(item.accessorialTotal || 0) : 0);
        }, 0);

        document.getElementById('editModalSelectedCount').textContent = editModalItems.length;
        document.getElementById('editModalDeliveryRateTotal').textContent = formatPHP(deliveryTotal);
        document.getElementById('editModalAccessorialTotal').textContent = formatPHP(accessorialTotal);
        document.getElementById('editModalGrandTotal').textContent = formatPHP(deliveryTotal + accessorialTotal);

        editModalCurrentPage = 1;
        renderEditModalRows();
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function renderEditModalRows() {
    const pageSize = parseInt(document.getElementById('editModalPageSize').value, 10);
    const total = editModalItems.length;
    const totalPages = Math.max(1, Math.ceil(total / pageSize));
    if (editModalCurrentPage > totalPages) editModalCurrentPage = totalPages;

    const start = total === 0 ? 0 : (editModalCurrentPage - 1) * pageSize;
    const end = Math.min(start + pageSize, total);
    const visible = editModalItems.slice(start, end);

    document.getElementById('editModalRows').innerHTML = visible.map((item) => {
        const typeLabel = ({
            both: 'Delivery + Accessorial',
            delivery_only: 'Delivery Only',
            accessorial_only: 'Accessorial Only',
        })[item.billedFor] || 'Not selected';

        return `<tr class="hover:bg-gray-50">
            <td class="px-4 py-3 align-top">
                <div class="font-medium text-gray-900">${item.mtm}</div>
                <div class="text-xs text-gray-500">DR #${item.id}</div>
            </td>
            <td class="px-4 py-3 align-top text-sm">${formatDateEdit(item.bookingDate)}</td>
            <td class="px-4 py-3 align-top text-sm">${formatDateEdit(item.deliveryDate)}</td>
            <td class="px-4 py-3 align-top text-sm">${item.companyName}</td>
            <td class="px-4 py-3 align-top text-sm">${item.customerName}</td>
            <td class="px-4 py-3 align-top text-sm">${typeLabel}</td>
            <td class="px-4 py-3 align-top text-sm ${item.billedFor === 'accessorial_only' ? 'text-gray-300' : 'text-sky-700 font-medium'}">${item.billedFor === 'accessorial_only' ? '—' : formatPHP(item.deliveryRate)}</td>
            <td class="px-4 py-3 align-top text-sm ${item.billedFor === 'delivery_only' ? 'text-gray-300' : 'text-purple-700 font-medium'}">${item.billedFor === 'delivery_only' ? '—' : formatPHP(item.accessorialTotal)}</td>
            <td class="px-4 py-3 align-top font-bold text-gray-900">${formatPHP(item.billedAmount)}</td>
        </tr>`;
    }).join('');

    const startDisplay = total === 0 ? 0 : start + 1;
    document.getElementById('editModalPaginationText').textContent = `Showing ${startDisplay} to ${total === 0 ? 0 : end} of ${total} rows`;
    document.getElementById('editModalPageIndicator').textContent = `Page ${editModalCurrentPage} of ${totalPages}`;
    document.getElementById('editModalPrev').disabled = editModalCurrentPage <= 1;
    document.getElementById('editModalNext').disabled = editModalCurrentPage >= totalPages;
}

function closeEditSummaryModal() {
    const modal = document.getElementById('editSummaryModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

function showEditValidationModal(errors) {
    const colorMap = {
        blue: 'bg-blue-100 text-blue-600',
        purple: 'bg-purple-100 text-purple-600',
        green: 'bg-green-100 text-green-600',
        orange: 'bg-orange-100 text-orange-600',
        red: 'bg-red-100 text-red-600',
    };

    document.getElementById('editValidationModalErrors').innerHTML = errors.map((error) => `
        <div class="flex items-start gap-3 py-3 border-b border-gray-100 last:border-0">
            <div class="flex-shrink-0 w-9 h-9 rounded-full ${colorMap[error.color] || 'bg-gray-100 text-gray-500'} flex items-center justify-center">
                <i class="fas ${error.icon} text-sm"></i>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900">${error.field}</p>
                <p class="text-xs text-gray-400 mt-0.5">${error.msg}</p>
            </div>
        </div>
    `).join('');

    const modal = document.getElementById('editValidationModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function closeEditValidationModal() {
    const modal = document.getElementById('editValidationModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

function selectAllRequests() {
    document.querySelectorAll('.edit-delivery-item:not(.hidden) .edit-delivery-checkbox').forEach((checkbox) => {
        checkbox.checked = true;
        toggleEditCardStyle(checkbox);
    });
}

function unselectAllRequests() {
    document.querySelectorAll('.edit-delivery-checkbox').forEach((checkbox) => {
        checkbox.checked = false;
        toggleEditCardStyle(checkbox);
    });
}

function validateAndSubmitEditForm(event) {
    const selectedIds = Array.from(editCheckedItemIds);
    const errors = [];

    if (!document.getElementById('company_id').value) {
        errors.push({ icon: 'fa-building', color: 'blue', field: 'Company', msg: 'Please select a company for this SOA.' });
    }
    if (!document.getElementById('customer_id').value) {
        errors.push({ icon: 'fa-user', color: 'purple', field: 'Customer', msg: 'Please select a customer for this SOA.' });
    }
    if (!document.getElementById('billing_period_from').value) {
        errors.push({ icon: 'fa-calendar-alt', color: 'green', field: 'Billing Period From', msg: 'Set the billing period start date.' });
    }
    if (!document.getElementById('billing_period_to').value) {
        errors.push({ icon: 'fa-calendar-check', color: 'green', field: 'Billing Period To', msg: 'Set the billing period end date.' });
    }
    if (!selectedIds.length) {
        errors.push({ icon: 'fa-boxes', color: 'orange', field: 'Delivery Requests', msg: 'Select at least one delivery request.' });
    }

    selectedIds.forEach((itemId) => {
        if (!getEditBillingType(itemId)) {
            errors.push({
                icon: 'fa-tag',
                color: 'orange',
                field: 'MTM billing not set',
                msg: `Please select Delivery Rate and/or Accessorial for Delivery Request #${itemId}.`,
            });
        }
    });

    if (errors.length) {
        event.preventDefault();
        showEditValidationModal(errors);
        return;
    }

    const form = document.getElementById('editSoaForm');
    form.querySelectorAll('input[data-edit-billing-hidden]').forEach((input) => input.remove());

    selectedIds.forEach((itemId) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = `item_billing[${itemId}]`;
        input.value = getEditBillingType(itemId);
        input.dataset.editBillingHidden = '1';
        form.appendChild(input);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    populateEditFilterDropdowns();

    document.querySelectorAll('.edit-delivery-checkbox').forEach((checkbox) => {
        const itemId = parseInt(checkbox.value, 10);
        getEditSelection(itemId);
        if (checkbox.checked) {
            editCheckedItemIds.add(itemId);
        }
        toggleEditCardStyle(checkbox);
    });

    editDeliveryPageSize = parseInt(document.getElementById('editDeliveryPageSize')?.value || '5', 10);
    filterEditDeliveryRequests();
    updateEditSummary();

    document.getElementById('editSoaForm').addEventListener('submit', validateAndSubmitEditForm);
    document.getElementById('editDeliveryPageSize').addEventListener('change', changeEditDeliveryPageSize);
    document.getElementById('editDeliveryPrev').addEventListener('click', function () {
        if (editDeliveryCurrentPage > 1) {
            editDeliveryCurrentPage -= 1;
            filterEditDeliveryRequests(false);
        }
    });
    document.getElementById('editDeliveryNext').addEventListener('click', function () {
        editDeliveryCurrentPage += 1;
        filterEditDeliveryRequests(false);
    });
    document.getElementById('editSummaryModal').addEventListener('click', function (event) {
        if (event.target === this) {
            closeEditSummaryModal();
        }
    });
    document.getElementById('editValidationModal').addEventListener('click', function (event) {
        if (event.target === this) {
            closeEditValidationModal();
        }
    });

    document.getElementById('editModalPageSize').addEventListener('change', function () {
        editModalCurrentPage = 1;
        renderEditModalRows();
    });

    document.getElementById('editModalPrev').addEventListener('click', function () {
        if (editModalCurrentPage > 1) {
            editModalCurrentPage--;
            renderEditModalRows();
        }
    });

    document.getElementById('editModalNext').addEventListener('click', function () {
        const pageSize = parseInt(document.getElementById('editModalPageSize').value, 10);
        const totalPages = Math.max(1, Math.ceil(editModalItems.length / pageSize));
        if (editModalCurrentPage < totalPages) {
            editModalCurrentPage++;
            renderEditModalRows();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeEditSummaryModal();
            closeEditValidationModal();
        }
    });
});
</script>
@endsection
