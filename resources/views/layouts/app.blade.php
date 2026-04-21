<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'FCZCNYX'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --app-field-height: 3.25rem;
            --app-field-radius: 0.9rem;
            --app-field-font-size: 0.95rem;
            --app-field-padding-y: 0.75rem;
            --app-field-padding-x: 0.9rem;
            --app-label-font-size: 0.95rem;
            --app-label-color: #334155;
        }

        [data-searchable-select-wrapper] {
            position: relative;
            z-index: 1;
        }

        [data-searchable-select-wrapper][data-open="true"] {
            z-index: 120;
        }

        .searchable-select-panel {
            z-index: 130 !important;
        }

        main form label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: var(--app-label-font-size);
            font-weight: 600;
            color: var(--app-label-color);
        }

        main form input[type="text"],
        main form input[type="date"],
        main form input[type="number"],
        main form input[type="email"],
        main form input[type="time"],
        main form input[type="password"],
        main form input[type="search"],
        main form input[type="url"],
        main form input[type="tel"],
        main form select,
        main form textarea {
            min-height: var(--app-field-height);
            width: 100%;
            border-radius: var(--app-field-radius);
            border: 1px solid #cbd5e1;
            background: #fff;
            padding: var(--app-field-padding-y) var(--app-field-padding-x);
            font-size: var(--app-field-font-size);
            line-height: 1.45;
            color: #1e293b;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
        }

        main form textarea {
            min-height: 6.5rem;
        }

        main form input:focus,
        main form select:focus,
        main form textarea:focus {
            border-color: #4f46e5;
            outline: none;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12);
        }

        main .searchable-select-trigger {
            min-height: var(--app-field-height);
            border-radius: var(--app-field-radius);
            padding: var(--app-field-padding-y) var(--app-field-padding-x);
            font-size: var(--app-field-font-size);
            line-height: 1.45;
        }

        main .searchable-select-source + [data-searchable-select-wrapper] > button,
        main .searchable-select-source + .relative > button {
            min-height: var(--app-field-height) !important;
            border-radius: var(--app-field-radius) !important;
            padding: var(--app-field-padding-y) var(--app-field-padding-x) !important;
            font-size: var(--app-field-font-size) !important;
            line-height: 1.45 !important;
        }

        main .searchable-select-trigger-icon {
            height: 2rem;
            width: 2rem;
            border-radius: 9999px;
        }

        main .searchable-select-source + [data-searchable-select-wrapper] > button > span:first-child,
        main .searchable-select-source + .relative > button > span:first-child {
            height: 2rem !important;
            width: 2rem !important;
            border-radius: 9999px !important;
        }

        main .searchable-select-panel input {
            min-height: 2.75rem;
            border-radius: 0.85rem;
            font-size: var(--app-field-font-size);
        }

        main .searchable-select-option {
            border-radius: 0.85rem;
            padding: 0.6rem 0.75rem;
            font-size: var(--app-field-font-size);
            line-height: 1.4;
        }

        main .searchable-select-panel [data-select-list] > button,
        main .searchable-select-panel [data-searchable-select-list] > button {
            border-radius: 0.85rem !important;
            padding: 0.6rem 0.75rem !important;
            font-size: var(--app-field-font-size) !important;
            line-height: 1.4 !important;
        }

        main .searchable-select-panel [data-select-list] > button > span:first-child,
        main .searchable-select-panel [data-searchable-select-list] > button > span:first-child {
            height: 1.9rem !important;
            width: 1.9rem !important;
            border-radius: 9999px !important;
        }

        main .searchable-select-option-icon {
            height: 1.9rem;
            width: 1.9rem;
            border-radius: 9999px;
        }

        main .dataTables_wrapper .dataTables_filter input,
        main .dataTables_wrapper .dataTables_length select,
        main [data-fast-table] input[id$="-search"],
        main [data-fast-table] select[id$="-per-page"] {
            min-height: var(--app-field-height) !important;
            border-radius: var(--app-field-radius) !important;
            border: 1px solid #cbd5e1 !important;
            background: #fff !important;
            padding: var(--app-field-padding-y) var(--app-field-padding-x) !important;
            font-size: var(--app-field-font-size) !important;
            line-height: 1.45 !important;
            color: #1e293b !important;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
            transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease !important;
        }

        main .dataTables_wrapper .dataTables_filter input:focus,
        main .dataTables_wrapper .dataTables_length select:focus,
        main [data-fast-table] input[id$="-search"]:focus,
        main [data-fast-table] select[id$="-per-page"]:focus {
            border-color: #4f46e5 !important;
            outline: none !important;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12) !important;
        }

        main .dataTables_wrapper .dataTables_filter input {
            min-width: 260px;
        }

        main .dataTables_wrapper .dataTables_length select,
        main [data-fast-table] select[id$="-per-page"] {
            min-width: 88px;
            padding-right: 2.25rem !important;
        }

        select:focus {
            position: relative;
            z-index: 20;
        }

        [x-cloak] {
            display: none !important;
        }

        @media (max-width: 1023px) {
            body {
                overflow: hidden;
            }
        }

        @media (max-width: 639px) {
            :root {
                --app-field-height: 3rem;
                --app-field-radius: 0.8rem;
                --app-field-font-size: 0.9rem;
                --app-field-padding-y: 0.7rem;
                --app-field-padding-x: 0.8rem;
                --app-label-font-size: 0.9rem;
            }

            main .dataTables_wrapper .dataTables_filter input {
                min-width: 0;
                width: 100%;
            }

            main form [class*="min-w-[220px]"] {
                min-width: 0 !important;
                width: 100%;
            }

            main form .border-t.border-slate-200 .inline-flex,
            main form .border-t.border-gray-200 .inline-flex {
                width: 100%;
            }

        }
    </style>
</head>
<body
    class="bg-gray-100 text-gray-900"
    x-data="{
        sidebarExpanded: window.innerWidth >= 1024,
        mobileSidebarOpen: false,
        isMobile: window.innerWidth < 1024,
        init() {
            const syncLayout = () => {
                const mobile = window.innerWidth < 1024;
                this.isMobile = mobile;

                if (mobile) {
                    this.mobileSidebarOpen = false;
                }
            };

            syncLayout();
            window.addEventListener('resize', syncLayout);
        },
        toggleSidebar() {
            if (this.isMobile) {
                this.mobileSidebarOpen = !this.mobileSidebarOpen;
                return;
            }

            this.sidebarExpanded = !this.sidebarExpanded;
        }
    }"
>

<div class="relative flex h-screen overflow-hidden">
    <div
        x-cloak
        x-show="isMobile && mobileSidebarOpen"
        x-transition.opacity
        @click="mobileSidebarOpen = false"
        class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"
    ></div>

    {{-- Sidebar --}}
    <aside
        :class="isMobile
            ? (mobileSidebarOpen ? 'translate-x-0 w-[18rem] max-w-[85vw]' : '-translate-x-full w-[18rem] max-w-[85vw]')
            : (sidebarExpanded ? 'w-64' : 'w-16')"
        class="fixed inset-y-0 left-0 z-40 flex flex-col bg-gray-800 text-white transition-all duration-300 ease-in-out lg:static lg:translate-x-0"
    >
        <!-- Toggle + Logo -->
        <div class="flex items-center px-4 py-3 border-b border-gray-700">
            <button 
                @click="toggleSidebar()"
                class="focus:outline-none text-white hover:text-gray-300 transition"
                title="Toggle Sidebar"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <a href="{{ route('dashboard') }}"
            class="text-lg font-semibold text-white ml-2"
            x-show="isMobile || sidebarExpanded"
            x-transition
            >
                FCZCNYX
            </a>
        </div>

        @php
            $user = auth()->user();
        @endphp

        <!-- Navigation -->
        <nav class="flex-1 px-2 py-4 overflow-y-auto space-y-0.5">

            {{-- Show Dashboard or No Access --}}
            @php
                $hasDashboardRole = $user->hasAnyRoleId([37, 38, 39, 40, 41]);
            @endphp

            @if($hasDashboardRole)
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-gray-100 transition hover:bg-gray-700/80 hover:text-white">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-sky-500/15 text-sky-300">
                        <i class="fas fa-tachometer-alt text-xs"></i>
                    </span>
                    <span x-show="isMobile || sidebarExpanded" x-transition class="font-medium">Dashboard</span>
                </a>
            @else
                <a href="{{ route('no.dashboard') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-red-300 transition hover:bg-gray-700/80 hover:text-red-200">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-red-500/15 text-red-300">
                        <i class="fas fa-exclamation-triangle text-xs"></i>
                    </span>
                    <span x-show="isMobile || sidebarExpanded" x-transition class="font-medium">No Dashboard Assigned</span>
                </a>
            @endif


            @if(in_array($user->id, [1, 97]))
                <a href="{{ route('delivery.details') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-gray-100 transition hover:bg-gray-700/80 hover:text-white">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-violet-500/15 text-violet-300">
                        <i class="fas fa-boxes-stacked text-xs"></i>
                    </span>
                    <span x-show="isMobile || sidebarExpanded" x-transition class="font-medium">Delivery Details</span>
                </a>
            @endif

            {{-- Settings nav (role_id = 28) --}}
            @if($user->hasAnyRoleId([1, 2, 3, 28]))
            <div x-data="{ open: false, basicOpen: false, cvOpen: false, dliOpen: false, allocationOpen: false }">
                <button @click="open = !open; if (!open) { basicOpen = false; cvOpen = false; dliOpen = false; allocationOpen = false; }" class="w-full flex items-center justify-between rounded-xl px-3 py-2 text-left text-gray-100 transition hover:bg-gray-700/80 hover:text-white">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-500/15 text-amber-300">
                            <i class="fas fa-cogs text-xs"></i>
                        </span>
                        <span x-show="isMobile || sidebarExpanded" x-transition class="font-medium">Settings</span>
                    </div>
                    <svg x-show="isMobile || sidebarExpanded" :class="open ? 'rotate-90' : ''" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="open && (isMobile || sidebarExpanded)" x-transition class="ml-5 mt-2 space-y-1.5 border-l border-gray-700 pl-3">
                    @if($user->hasAnyRoleId([1, 2, 3, 34]))
                        <a href="{{ route('password.change') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-sky-500/15 text-sky-300">
                                <i class="fas fa-key text-xs"></i>
                            </span>
                            <span class="font-medium leading-none">Change Password</span>
                        </a>
                    @endif
                    {{-- Basic Settings (role_id = 3) --}}
                    @if($user->hasAnyRoleId([1, 2, 3, 36]))
                    <div>
                        <button @click="basicOpen = !basicOpen" class="w-full flex items-center justify-between rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white">
                            <span class="flex items-center gap-3">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-500/15 text-blue-300">
                                    <i class="fas fa-truck-loading text-xs"></i>
                                </span>
                                <span class="font-medium leading-none">Delivery Request</span>
                            </span>
                            <svg :class="basicOpen ? 'rotate-90' : ''" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        <div x-show="basicOpen" x-transition class="ml-5 mt-2 space-y-1.5 border-l border-gray-700 pl-3">
                            @if($user->hasAnyRoleId([1, 2, 3]))
                                <a href="{{ route('companies.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-building w-4 text-center text-blue-300"></i><span>Companies</span></a>
                            @endif
                            @if($user->hasAnyRoleId([1, 2, 3]))
                                <a href="{{ route('regions.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-map w-4 text-center text-emerald-300"></i><span>Region</span></a>
                            @endif
                            @if($user->hasAnyRoleId([1, 2, 3]))
                                <a href="{{ route('areas.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-draw-polygon w-4 text-center text-cyan-300"></i><span>Areas</span></a>
                            @endif
                            @if($user->hasAnyRoleId([1, 2, 3]))
                                <a href="{{ route('customers.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-users w-4 text-center text-violet-300"></i><span>Customer</span></a>
                            @endif
                            @if($user->hasAnyRoleId([1, 2, 3, 5]))
                                <a href="{{ route('trucks.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-truck w-4 text-center text-amber-300"></i><span>Trucks</span></a>
                            @endif
                            @if($user->hasAnyRoleId([1, 2, 3]))
                                <a href="{{ route('trucksTypes.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-truck-ramp-box w-4 text-center text-orange-300"></i><span>Truck Type</span></a>
                            @endif
                            @if($user->hasAnyRoleId([1, 2, 3]))
                                <a href="{{ route('suppliers.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-industry w-4 text-center text-pink-300"></i><span>Suppliers</span></a>
                            @endif
                            @if($user->hasAnyRoleId([1, 2, 3, 4]))
                                <a href="{{ route('employees.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-id-badge w-4 text-center text-lime-300"></i><span>Employees</span></a>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- DR Items (role_id = 20) --}}
                    @if($user->hasAnyRoleId([1, 2, 3]))
                    <div>
                        <button @click="dliOpen = !dliOpen" class="w-full flex items-center justify-between rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white">
                            <span class="flex items-center gap-3">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-fuchsia-500/15 text-fuchsia-300">
                                    <i class="fas fa-layer-group text-xs"></i>
                                </span>
                                <span class="font-medium leading-none">DR Items</span>
                            </span>
                            <svg :class="dliOpen ? 'rotate-90' : ''" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        <div x-show="dliOpen" x-transition class="ml-5 mt-2 space-y-1.5 border-l border-gray-700 pl-3">
                            <a href="{{ route('accessorialTypes.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-screwdriver-wrench w-4 text-center text-cyan-300"></i><span>Accesorial Type</span></a>
                            <a href="{{ route('addOnRates.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-tags w-4 text-center text-amber-300"></i><span>Add On Rate</span></a>
                            <a href="{{ route('deliveryStatus.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-signal w-4 text-center text-emerald-300"></i><span>Delivery Status</span></a>
                            <a href="{{ route('deliveryTypes.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-shapes w-4 text-center text-violet-300"></i><span>Delivery Types</span></a>
                            <a href="{{ route('distanceTypes.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-route w-4 text-center text-orange-300"></i><span>Distance</span></a>
                            <a href="{{ route('warehouses.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-warehouse w-4 text-center text-blue-300"></i><span>Warehouse</span></a>
                            <a href="{{ route('deliveryRequestType.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-list-check w-4 text-center text-pink-300"></i><span>DR Types</span></a>
                        </div>
                    </div>
                    @endif

                    {{-- Cash Voucher nav (role_id = 12) --}}
                    @if($user->hasAnyRoleId([1, 2, 3]))
                    <div>
                        <button @click="cvOpen = !cvOpen" class="w-full flex items-center justify-between rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white">
                            <span class="flex items-center gap-3">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-300">
                                    <i class="fas fa-wallet text-xs"></i>
                                </span>
                                <span class="font-medium leading-none">Cash Voucher</span>
                            </span>
                            <svg :class="cvOpen ? 'rotate-90' : ''" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        <div x-show="cvOpen" x-transition class="ml-5 mt-2 space-y-1.5 border-l border-gray-700 pl-3">
                            <a href="{{ route('monthly-series.reset.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-rotate w-4 text-center text-sky-300"></i><span>Reset Series</span></a>
                            <a href="{{ route('approvers.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-user-check w-4 text-center text-emerald-300"></i><span>Approver</span></a>
                            <a href="{{ route('taxes.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-percent w-4 text-center text-rose-300"></i><span>Tax</span></a>
                            <a href="{{ route('expenseTypes.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-file-invoice-dollar w-4 text-center text-amber-300"></i><span>Expense Types</span></a>
                            <a href="{{ route('cvr_request_types.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-receipt w-4 text-center text-violet-300"></i><span>CV Types</span></a>
                        </div>
                    </div>
                    @endif

                    {{-- Allocation nav (role_id = 8) --}}
                    @if($user->hasAnyRoleId([1, 2, 3]))
                    <div>
                        <button @click="allocationOpen = !allocationOpen" class="w-full flex items-center justify-between rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white">
                            <span class="flex items-center gap-3">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-orange-500/15 text-orange-300">
                                    <i class="fas fa-credit-card text-xs"></i>
                                </span>
                                <span class="font-medium leading-none">Allocation</span>
                            </span>
                            <svg :class="allocationOpen ? 'rotate-90' : ''" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        <div x-show="allocationOpen" x-transition class="ml-5 mt-2 space-y-1.5 border-l border-gray-700 pl-3">
                            <a href="{{ route('fleetCards.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-id-card w-4 text-center text-orange-300"></i><span>Fleet Card</span></a>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
            @endif

            {{-- Delivery Request nav (role_id = 6 or 7) --}}
            @if($user->hasAnyRoleId([1, 2, 3, 35]))
            <div x-data="{ openBalance: false }">
                <button @click="openBalance = !openBalance" class="w-full flex items-center justify-between rounded-xl px-3 py-2 text-left text-gray-100 transition hover:bg-gray-700/80 hover:text-white">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-cyan-500/15 text-cyan-300">
                            <i class="fas fa-scale-balanced text-xs"></i>
                        </span>
                        <span x-show="isMobile || sidebarExpanded" x-transition class="font-medium">Running Balance</span>
                    </div>
                    <svg x-show="isMobile || sidebarExpanded" :class="openBalance ? 'rotate-90' : ''" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="openBalance && (isMobile || sidebarExpanded)" x-transition class="ml-5 mt-2 space-y-1.5 border-l border-gray-700 pl-3">
                      @if($user->hasAnyRoleId([1, 2, 3, 43]))<a href="{{ route('running_balance.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-wallet w-4 text-center text-cyan-300"></i><span>Running Balance</span></a>@endif
                    @if($user->hasAnyRoleId([1, 2, 3, 44]))<a href="{{ route('running_balance.adminFunds') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-location-dot w-4 text-center text-emerald-300"></i><span>Running Balance - Laguna</span></a>@endif
                    @if($user->hasAnyRoleId([1, 2, 3, 45]))<a href="{{ route('running_balance.davaoFunds') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-location-dot w-4 text-center text-orange-300"></i><span>Running Balance - Davao</span></a>@endif
                </div>
            </div>
            @endif

            {{-- Allocation List nav (role_id = 9) --}}
            @if($user->hasAnyRoleId([1, 2, 3, 30]))
            <div x-data="{ openAllocate: false }">
                <button @click="openAllocate = !openAllocate" class="w-full flex items-center justify-between rounded-xl px-3 py-2 text-left text-gray-100 transition hover:bg-gray-700/80 hover:text-white">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-orange-500/15 text-orange-300">
                            <i class="fas fa-truck text-xs"></i>
                        </span>
                        <span x-show="isMobile || sidebarExpanded" x-transition class="font-medium">Allocation</span>
                    </div>
                    <svg x-show="isMobile || sidebarExpanded" :class="openAllocate ? 'rotate-90' : ''" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="openAllocate && (isMobile || sidebarExpanded)" x-transition class="ml-5 mt-2 space-y-1.5 border-l border-gray-700 pl-3">
                    @if($user->hasAnyRoleId([8]))<a href="{{ route('allocations.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-route w-4 text-center text-orange-300"></i><span>Allocate</span></a>@endif
                    @if($user->hasAnyRoleId([9]))<a href="{{ route('allocation.drlist') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-list-ul w-4 text-center text-amber-300"></i><span>List</span></a>@endif
                </div>
            </div>
            @endif

            {{-- Delivery Request nav (role_id = 6 or 7) --}}
            @if($user->hasAnyRoleId([1, 2, 3,29]))
            <div x-data="{ openDR: false }">
                <button @click="openDR = !openDR" class="w-full flex items-center justify-between rounded-xl px-3 py-2 text-left text-gray-100 transition hover:bg-gray-700/80 hover:text-white">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-500/15 text-blue-300">
                            <i class="fas fa-box text-xs"></i>
                        </span>
                        <span x-show="isMobile || sidebarExpanded" x-transition class="font-medium">Delivery Request</span>
                    </div>
                    <svg x-show="isMobile || sidebarExpanded" :class="openDR ? 'rotate-90' : ''" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="openDR && (isMobile || sidebarExpanded)" x-transition class="ml-5 mt-2 space-y-1.5 border-l border-gray-700 pl-3">
                    @if($user->hasAnyRoleId([1, 2, 3, 6]))<a href="{{ route('deliveryRequest.create') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-plus w-4 text-center text-blue-300"></i><span>Create</span></a>@endif
                    @if($user->hasAnyRoleId([1, 2, 3, 7]))<a href="{{ route('deliveryRequest.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-list-ul w-4 text-center text-cyan-300"></i><span>List</span></a>@endif
                </div>
            </div>
            @endif

            {{-- Coordinator nav (role_id = 10 or 11) --}}
            @if($user->hasAnyRoleId([1, 2, 3, 31]))
            <div x-data="{ openCoordinator: false }">
                <button @click="openCoordinator = !openCoordinator" class="w-full flex items-center justify-between rounded-xl px-3 py-2 text-left text-gray-100 transition hover:bg-gray-700/80 hover:text-white">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-violet-500/15 text-violet-300">
                            <i class="fas fa-project-diagram text-xs"></i>
                        </span>
                        <span x-show="isMobile || sidebarExpanded" x-transition class="font-medium">Coordinator</span>
                    </div>
                    <svg x-show="isMobile || sidebarExpanded" :class="openCoordinator ? 'rotate-90' : ''" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="openCoordinator && (isMobile || sidebarExpanded)" x-transition class="ml-5 mt-2 space-y-1.5 border-l border-gray-700 pl-3">
                    @if($user->hasAnyRoleId([1, 2, 3, 10]))<a href="{{ route('coordinators.create') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-plus w-4 text-center text-violet-300"></i><span>Create Request</span></a>@endif
                    @if($user->hasAnyRoleId([1, 2, 3, 11]))<a href="{{ route('coordinators.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-list-ul w-4 text-center text-fuchsia-300"></i><span>List</span></a>@endif
                    @if($user->hasAnyRoleId([1, 2, 3, 11]))<a href="{{ route('reports.dr') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-chart-column w-4 text-center text-indigo-300"></i><span>Report</span></a>@endif
                </div>
            </div>
            @endif

            {{-- Cash Voucher List Admin (role_id = 14) --}}
            @if($user->hasAnyRoleId([1, 2, 3, 32]))
            <div x-data="{ openCVR: false }">
                <button @click="openCVR = !openCVR" class="w-full flex items-center justify-between rounded-xl px-3 py-2 text-left text-gray-100 transition hover:bg-gray-700/80 hover:text-white">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-300">
                            <i class="fas fa-money-bill text-xs"></i>
                        </span>
                        <span x-show="isMobile || sidebarExpanded" x-transition class="font-medium">Cash Voucher</span>
                    </div>
                    <svg x-show="isMobile || sidebarExpanded" :class="openCVR ? 'rotate-90' : ''" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="openCVR && (isMobile || sidebarExpanded)" x-transition class="ml-5 mt-2 space-y-1.5 border-l border-gray-700 pl-3">
                    {{-- Add CV List Admin links here --}}
                    @if($user->hasAnyRoleId([1, 2, 3, 13]))
                        <a href="{{ route('cashVoucherRequests.approval') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-circle-check w-4 text-center text-emerald-300"></i><span>DR Approval</span></a>
                    @endif
                    @if($user->hasAnyRoleId([1, 2, 3, 12]))
                        <a href="{{ route('adminCV.approval') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-user-check w-4 text-center text-sky-300"></i><span>Admin/RPM Approval</span></a>
                    @endif
                    @if($user->hasAnyRoleId([1, 2, 3, 14]))
                        <a href="{{ route('adminCV.cvrList') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-list-ul w-4 text-center text-violet-300"></i><span>List - Admin</span></a>
                    @endif
                    @if($user->hasAnyRoleId([1, 2, 3, 15]))
                        <a href="{{ route('cashVoucherRequests.cvrList') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-list-ul w-4 text-center text-fuchsia-300"></i><span>List - DR</span></a>
                    @endif
                    @if($user->hasAnyRoleId([1, 2, 3, 16]))
                        <a href="{{ route('cashVoucherRequests.rejectView') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-circle-xmark w-4 text-center text-rose-300"></i><span>Reject - DR</span></a>
                    @endif
                    @if($user->hasAnyRoleId([1, 2, 3, 17]))
                        <a href="{{ route('adminCV.rejectView') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-ban w-4 text-center text-red-300"></i><span>Reject - Admin</span></a>
                    @endif
                    @if($user->hasAnyRoleId([1, 2, 3, 18]))
                        <a href="{{ route('admin.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-file-circle-plus w-4 text-center text-amber-300"></i><span>Admin/RPM Request</span></a>
                    @endif
                    @if($user->hasAnyRoleId([1, 2, 3, 18]))
                        <a href="{{ route('reports.cv') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-chart-column w-4 text-center text-indigo-300"></i><span>Admin Report</span></a>
                    @endif
                    @if($user->hasAnyRoleId([1, 2, 3, 18]))
                        <a href="{{ route('reports.rpm') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-chart-line w-4 text-center text-cyan-300"></i><span>RPM Report</span></a>
                    @endif
                </div>
            </div>
            @endif

            {{-- Liquidation nav (roles 20 to 27) --}}
            @if($user->hasAnyRoleId([1, 2, 3, 33]))
            <div x-data="{ openLiquidation: false }">
                <button @click="openLiquidation = !openLiquidation" class="w-full flex items-center justify-between rounded-xl px-3 py-2 text-left text-gray-100 transition hover:bg-gray-700/80 hover:text-white">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-teal-500/15 text-teal-300">
                            <i class="fas fa-file-invoice-dollar text-xs"></i>
                        </span>
                        <span x-show="isMobile || sidebarExpanded" x-transition class="font-medium">Liquidation</span>
                    </div>
                    <svg x-show="isMobile || sidebarExpanded" :class="openLiquidation ? 'rotate-90' : ''" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="openLiquidation && (isMobile || sidebarExpanded)" x-transition class="ml-5 mt-2 space-y-1.5 border-l border-gray-700 pl-3">
                    @if($user->hasAnyRoleId([1, 2, 3, 20]))
                        <a href="{{ route('liquidations.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-file-circle-plus w-4 text-center text-teal-300"></i><span>Liquidate - DR</span></a>
                    @endif
                    @if($user->hasAnyRoleId([1, 2, 3, 21]))
                        <a href="{{ route('liquidations.indexAdmin') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-user-shield w-4 text-center text-cyan-300"></i><span>Liquidate - Admin</span></a>
                    @endif
                    @if($user->hasAnyRoleId([1, 2, 3, 22]))
                        <a href="{{ route('liquidations.reviewList') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-check-double w-4 text-center text-emerald-300"></i><span>Validate Liquidation</span></a>
                    @endif
                    @if($user->hasAnyRoleId([1, 2, 3, 23]))
                        <a href="{{ route('liquidations.validatedList') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-hand-holding-dollar w-4 text-center text-lime-300"></i><span>Collect Liquidation</span></a>
                    @endif
                    @if($user->hasAnyRoleId([1, 2, 3, 24]))
                        <a href="{{ route('liquidations.approvalList') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-badge-check w-4 text-center text-sky-300"></i><span>Approved Liquidation</span></a>
                    @endif
                    @if($user->hasAnyRoleId([1, 2, 3, 25]))
                        <a href="{{ route('liquidations.liquidationList') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-list-ul w-4 text-center text-violet-300"></i><span>List -DR</span></a>
                    @endif
                    @if($user->hasAnyRoleId([1, 2, 3, 26]))
                        <a href="{{ route('liquidations.overall') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-chart-pie w-4 text-center text-orange-300"></i><span>Overall</span></a>
                    @endif
                    @if($user->hasAnyRoleId([1, 2, 3, 27]))
                        <a href="{{ route('liquidations.rejectedList') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-circle-xmark w-4 text-center text-rose-300"></i><span>Rejected List</span></a>
                    @endif
                </div>
            </div>
            @endif

            {{-- Billing nav --}}
            @if($user->hasAnyRoleId([1, 2, 3]))
            <div x-data="{ openBilling: false }">
                <button @click="openBilling = !openBilling" class="w-full flex items-center justify-between rounded-xl px-3 py-2 text-left text-gray-100 transition hover:bg-gray-700/80 hover:text-white">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-500/15 text-blue-300">
                            <i class="fas fa-receipt text-xs"></i>
                        </span>
                        <span x-show="isMobile || sidebarExpanded" x-transition class="font-medium">Billing</span>
                    </div>
                    <svg x-show="isMobile || sidebarExpanded" :class="openBilling ? 'rotate-90' : ''" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="openBilling && (isMobile || sidebarExpanded)" x-transition class="ml-5 mt-2 space-y-1.5 border-l border-gray-700 pl-3">
                    <a href="{{ route('billing.dashboard') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-chart-line w-4 text-center text-blue-300"></i><span>Dashboard</span></a>
                    <a href="{{ route('billing.createSOA.form') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-file-circle-plus w-4 text-center text-cyan-300"></i><span>Create SOA</span></a>
                    <a href="{{ route('billing.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-list-ul w-4 text-center text-violet-300"></i><span>SOA List</span></a>
                    <a href="{{ route('billing.indexAccessorial') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-file-invoice-dollar w-4 text-center text-amber-300"></i><span>Accessorial</span></a>
                </div>
            </div>
            @endif

            {{-- Attendance nav --}}
            @if($user->hasAnyRoleId([1, 2, 3]))
            <div x-data="{ openAttendance: false }">
                <button @click="openAttendance = !openAttendance" class="w-full flex items-center justify-between rounded-xl px-3 py-2 text-left text-gray-100 transition hover:bg-gray-700/80 hover:text-white">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-300">
                            <i class="fas fa-clipboard-list text-xs"></i>
                        </span>
                        <span x-show="isMobile || sidebarExpanded" x-transition class="font-medium">Attendance</span>
                    </div>
                    <svg x-show="isMobile || sidebarExpanded" :class="openAttendance ? 'rotate-90' : ''" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="openAttendance && (isMobile || sidebarExpanded)" x-transition class="ml-5 mt-2 space-y-1.5 border-l border-gray-700 pl-3">
                    <a href="{{ route('attendance.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-list-ul w-4 text-center text-emerald-300"></i><span>Attendance List</span></a>
                    <a href="{{ route('attendance.create') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-calendar-plus w-4 text-center text-lime-300"></i><span>Add Attendance</span></a>
                    <a href="{{ route('attendance.summary') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-table-columns w-4 text-center text-cyan-300"></i><span>Summary</span></a>
                </div>
            </div>
            @endif

            {{-- Human Resource nav --}}
            @if($user->hasAnyRoleId([1, 2, 3, 47]))
            <div x-data="{ openHr: false }">
                <button @click="openHr = !openHr" class="w-full flex items-center justify-between rounded-xl px-3 py-2 text-left text-gray-100 transition hover:bg-gray-700/80 hover:text-white">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-pink-500/15 text-pink-300">
                            <i class="fas fa-users text-xs"></i>
                        </span>
                        <span x-show="isMobile || sidebarExpanded" x-transition class="font-medium">Human Resource</span>
                    </div>
                    <svg x-show="isMobile || sidebarExpanded" :class="openHr ? 'rotate-90' : ''" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="openHr && (isMobile || sidebarExpanded)" x-transition class="ml-5 mt-2 space-y-1.5 border-l border-gray-700 pl-3">
                    <a href="{{ route('hr.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-chart-line w-4 text-center text-pink-300"></i><span>Payroll Dashboard</span></a>
                    <a href="{{ route('hr.create') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-file-circle-plus w-4 text-center text-fuchsia-300"></i><span>Create Payroll</span></a>
                    <a href="{{ route('hr.payslips.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-gray-700/70 hover:text-white"><i class="fas fa-file-lines w-4 text-center text-rose-300"></i><span>Payslips</span></a>
                </div>
            </div>
            @endif

        </nav>

        <!-- Logout -->
        <form method="POST" action="{{ route('logout') }}" class="p-4 pt-3">
            @csrf
            <button class="flex w-full items-center gap-3 rounded-xl bg-red-500 px-3 py-2 text-left text-white transition hover:bg-red-600">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-white/15 text-red-100">
                    <i class="fas fa-sign-out-alt text-xs"></i>
                </span>
                <span x-show="isMobile || sidebarExpanded" x-transition class="font-medium">Logout</span>
            </button>
        </form>
    </aside>


    {{-- Main Content --}}
    <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
        {{-- Navbar --}}
        <header class="bg-white shadow px-4 py-3 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    @click="toggleSidebar()"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-slate-50 lg:hidden"
                    title="Open Menu"
                >
                    <i class="fas fa-bars text-base"></i>
                </button>
                <h1 class="text-xl font-semibold">@yield('title', 'Dashboard')</h1>
            </div>
            <div class="flex gap-4">
                <a href="#" class="text-gray-600 hover:text-black"><i class="fas fa-bell"></i></a>
                <a href="#" class="text-gray-600 hover:text-black"><i class="fas fa-envelope"></i></a>
                <button id="fullscreen-btn" class="text-gray-600 hover:text-black"><i class="fas fa-expand"></i></button>
            </div>
        </header>

        {{-- Alerts --}}
       <div class="px-4 mt-4">
        @if(session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 text-red-800 px-4 py-2 rounded">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 text-red-800 px-4 py-2 rounded">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

        {{-- Page Content --}}
        <main class="flex-1 overflow-y-auto p-3 sm:p-4 lg:p-6">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="bg-white text-center py-3 shadow">
            <strong>&copy; {{ now()->year }} FCZCNYX. All rights reserved.</strong>
        </footer>
    </div>
</div>

{{-- Fullscreen Script --}}
<script>
    document.getElementById('fullscreen-btn')?.addEventListener('click', () => {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen?.();
        } else {
            document.exitFullscreen?.();
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('submit', async function (event) {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const methodInput = form.querySelector('input[name="_method"]');
        const isDeleteForm = (methodInput && methodInput.value.toUpperCase() === 'DELETE') ||
            form.getAttribute('method')?.toUpperCase() === 'DELETE';

        if (!isDeleteForm || form.dataset.confirmed === 'true') {
            return;
        }

        event.preventDefault();

        const message = form.dataset.confirmMessage || 'Are you sure you want to delete this record?';
        const title = form.dataset.confirmTitle || 'Delete Record?';
        const confirmText = form.dataset.confirmButton || 'Yes, delete it';
        const cancelText = form.dataset.cancelButton || 'Cancel';

        if (typeof Swal === 'undefined') {
            return;
        }

        const result = await Swal.fire({
            title,
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            reverseButtons: true,
            focusCancel: true,
            customClass: {
                popup: 'rounded-[24px]',
                confirmButton: 'swal-confirm-btn',
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
</script>

<style>
    .swal-confirm-btn,
    .swal-cancel-btn {
        border: 0;
        border-radius: 9999px;
        padding: 0.75rem 1.25rem;
        font-size: 0.875rem;
        font-weight: 600;
        transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
    }

    .swal-confirm-btn {
        background: linear-gradient(135deg, #dc2626, #ef4444);
        color: #fff;
        box-shadow: 0 10px 25px rgba(239, 68, 68, 0.25);
    }

    .swal-cancel-btn {
        background: #f8fafc;
        color: #334155;
        border: 1px solid #cbd5e1;
    }

    .swal-confirm-btn:hover,
    .swal-cancel-btn:hover {
        transform: translateY(-1px);
    }

    .balanced-datatable-toolbar {
        display: grid !important;
        grid-template-columns: minmax(0, 720px) auto !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 1rem !important;
    }

    .balanced-datatable-search-column {
        min-width: 0;
        max-width: 720px;
        width: 100%;
        order: 1;
    }

    .balanced-datatable-meta-column {
        order: 2;
        justify-self: end;
        display: flex !important;
        align-items: center !important;
        gap: 0.75rem !important;
        flex-wrap: nowrap !important;
        white-space: nowrap;
    }

    .balanced-datatable-meta-column > .text-sm.text-slate-500 {
        display: none;
    }

    .balanced-datatable-search-column .relative,
    .balanced-datatable-search-column > .relative {
        max-width: 720px;
    }

    .global-edit-action {
        min-width: 104px;
        justify-content: center !important;
        gap: 0.5rem !important;
        border-radius: 1rem !important;
        border: 1px solid #bfdbfe !important;
        background: linear-gradient(135deg, #eff6ff, #dbeafe) !important;
        color: #1d4ed8 !important;
        box-shadow: 0 6px 18px rgba(59, 130, 246, 0.08);
    }

    .global-edit-action:hover {
        background: linear-gradient(135deg, #dbeafe, #bfdbfe) !important;
        color: #1e40af !important;
        border-color: #93c5fd !important;
    }

    .global-edit-action i {
        color: #2563eb !important;
    }

    .global-inline-filter-form {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: flex-end !important;
        justify-content: space-between !important;
        gap: 1rem !important;
    }

    .global-inline-filter-primary {
        width: 100%;
        max-width: 420px;
        min-width: 0;
        flex: 1 1 320px;
    }

    .global-inline-filter-actions {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: flex-end !important;
        gap: 0.75rem !important;
        white-space: nowrap;
    }

    .global-fast-table-export {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 0;
        padding: 0.5rem 1.5rem 0.75rem;
    }

    .global-export-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        border-radius: 1rem;
        border: 1px solid #bfdbfe;
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        padding: 0.7rem 1rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #1d4ed8;
        box-shadow: 0 6px 18px rgba(59, 130, 246, 0.08);
        transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
    }

    .global-export-action:hover {
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        border-color: #93c5fd;
        color: #1e40af;
        transform: translateY(-1px);
    }

    .global-export-action:disabled {
        cursor: wait;
        opacity: 0.7;
        transform: none;
    }

    @media (max-width: 639px) {
        .global-fast-table-export {
            padding: 0.5rem 1rem 0.75rem;
        }

        .global-export-action {
            width: 100%;
        }
    }

    .global-datatable-footer {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: flex-start !important;
        justify-content: space-between !important;
        gap: 1rem !important;
        width: 100% !important;
        flex-direction: column !important;
    }

    .global-datatable-footer > :first-child {
        display: block !important;
        width: 100% !important;
        min-width: 0;
        flex: 1 1 auto;
        text-align: left !important;
        margin: 0 !important;
        justify-self: start !important;
        align-self: center !important;
    }

    .global-datatable-footer > :last-child {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        margin-left: 0 !important;
        flex: 1 1 100%;
        text-align: left !important;
        white-space: normal !important;
        width: 100% !important;
    }

    .global-datatable-footer > :first-child p,
    .global-datatable-footer > :first-child span,
    .global-datatable-footer > :first-child {
        text-align: left !important;
    }

    .global-datatable-footer .app-pagination {
        width: 100% !important;
    }

    .global-datatable-footer .app-pagination-page-list {
        max-width: 100%;
        flex-wrap: wrap !important;
    }

    .global-datatable-footer > :last-child nav {
        margin-left: 0 !important;
        width: 100% !important;
    }

    @media (min-width: 640px) {
        .global-datatable-footer {
            flex-wrap: nowrap !important;
            align-items: center !important;
            flex-direction: row !important;
        }

        .global-datatable-footer > :last-child {
            justify-content: flex-end !important;
            margin-left: auto !important;
            flex: 0 0 auto;
            text-align: right !important;
            white-space: nowrap !important;
            width: auto !important;
        }

        .global-datatable-footer > :last-child nav,
        .global-datatable-footer .app-pagination {
            width: auto !important;
        }

        .global-datatable-footer .app-pagination-page-list {
            flex-wrap: nowrap !important;
        }
    }
</style>

<script>
    (() => {
        const searchTimers = new WeakMap();

        function enhanceDataTableLayout(root = document) {
            const searchInputs = root.querySelectorAll('[data-fast-table-search], input[id$="-search"]');

            searchInputs.forEach((searchInput) => {
                const toolbar = searchInput.closest('.flex, .grid');

                if (!(toolbar instanceof HTMLElement)) {
                    return;
                }

                const perPageSelect = toolbar.querySelector('[data-fast-table-per-page], select[id$="-per-page"]');

                if (!(perPageSelect instanceof HTMLSelectElement)) {
                    return;
                }

                const searchColumn = Array.from(toolbar.children).find((child) => child.contains(searchInput));
                const metaColumn = Array.from(toolbar.children).find((child) => child.contains(perPageSelect));

                if (!(searchColumn instanceof HTMLElement) || !(metaColumn instanceof HTMLElement)) {
                    return;
                }

                toolbar.classList.add('balanced-datatable-toolbar');
                searchColumn.classList.add('balanced-datatable-search-column');
                metaColumn.classList.add('balanced-datatable-meta-column');
            });
        }

        function enhanceEditButtons(root = document) {
            root.querySelectorAll('table a, table button').forEach((element) => {
                const label = (element.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();

                if (label !== 'edit') {
                    return;
                }

                element.classList.add('global-edit-action');
            });
        }

        function enhanceInlineFilterForms(root = document) {
            root.querySelectorAll('form[id$="filter-form"], form#filter-form').forEach((form) => {
                if (form.hasAttribute('data-skip-inline-filter-enhancer')) {
                    return;
                }

                const directChildren = Array.from(form.children).filter((child) => child instanceof HTMLElement);

                if (directChildren.length !== 2) {
                    return;
                }

                const [primaryColumn, actionColumn] = directChildren;
                const hasSearchOrSelect = !!primaryColumn.querySelector('input, select');
                const hasActionButton = !!actionColumn.querySelector('button, a');

                if (!hasSearchOrSelect || !hasActionButton) {
                    return;
                }

                form.classList.add('global-inline-filter-form');
                primaryColumn.classList.add('global-inline-filter-primary');
                actionColumn.classList.add('global-inline-filter-actions');

                const searchableWrapper = primaryColumn.querySelector('.searchable-select-source + .relative');
                if (searchableWrapper instanceof HTMLElement) {
                    searchableWrapper.style.maxWidth = '420px';
                    searchableWrapper.style.width = '100%';
                }
            });
        }

        function enhanceDataTableFooters(root = document) {
            root.querySelectorAll('div').forEach((element) => {
                if (!(element instanceof HTMLElement)) {
                    return;
                }

                const children = Array.from(element.children).filter((child) => child instanceof HTMLElement);
                if (children.length !== 2) {
                    return;
                }

                const [leftChild, rightChild] = children;
                const hasShowingText = /showing\s+\d+/i.test((leftChild.textContent || '').trim());
                const hasPagination = !!rightChild.querySelector('.pagination, nav[role="navigation"], a[href*="page="]');

                if (!hasShowingText || !hasPagination) {
                    return;
                }

                element.classList.add('global-datatable-footer');
            });
        }

        function ensureFastTableId(table) {
            if (!table.id) {
                table.id = `fast-table-${Math.random().toString(36).slice(2, 10)}`;
            }

            return table.id;
        }

        function getExportFilename(table) {
            const tableId = ensureFastTableId(table);
            const heading = table.closest('section, div')?.querySelector('h1, h2, h3');
            const label = (table.dataset.exportName || heading?.textContent || tableId || 'table')
                .trim()
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');

            const date = new Date();
            const stamp = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;

            return `${label || 'table'}-${stamp}.xls`;
        }

        function getCurrentTableElement(table) {
            return table.querySelector('table');
        }

        function extractTableHeaders(tableElement) {
            const headerCells = Array.from(tableElement.querySelectorAll('thead th'));

            if (headerCells.length > 0) {
                return headerCells.map((cell) => (cell.textContent || '').replace(/\s+/g, ' ').trim());
            }

            const firstRowCells = Array.from(tableElement.querySelectorAll('tr:first-child th, tr:first-child td'));
            return firstRowCells.map((cell) => (cell.textContent || '').replace(/\s+/g, ' ').trim());
        }

        function extractTableRows(tableElement) {
            return Array.from(tableElement.querySelectorAll('tbody tr'))
                .map((row) => {
                    const cells = Array.from(row.querySelectorAll('th, td'))
                        .map((cell) => (cell.textContent || '').replace(/\s+/g, ' ').trim());

                    if (cells.length === 0) {
                        return null;
                    }

                    const hasVisibleData = cells.some((value) => value !== '');
                    if (!hasVisibleData) {
                        return null;
                    }

                    if (cells.length === 1) {
                        const colspan = row.querySelector('td, th')?.getAttribute('colspan');
                        if (colspan && /no\s+|try\s+|not\s+found|empty/i.test(cells[0])) {
                            return null;
                        }
                    }

                    return cells;
                })
                .filter(Boolean);
        }

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        async function getTableHtmlFromResponse(response) {
            const contentType = response.headers.get('content-type') || '';

            if (contentType.includes('application/json')) {
                const payload = await response.json();
                return payload.html || '';
            }

            return await response.text();
        }

        function parseHtmlTable(html) {
            const parser = new DOMParser();
            const documentFragment = parser.parseFromString(html, 'text/html');
            return documentFragment.querySelector('table');
        }

        function getTotalEntries(table) {
            const footerText = table.textContent || '';
            const match = footerText.match(/of\s+(\d+)\s+(entries|results)/i);
            return match ? Number.parseInt(match[1], 10) : 0;
        }

        async function collectAllFastTableRows(table) {
            const tableElement = getCurrentTableElement(table);

            if (!(tableElement instanceof HTMLTableElement)) {
                return { headers: [], rows: [] };
            }

            const headers = extractTableHeaders(tableElement);
            const searchValue = getSearchInput(table)?.value || '';
            const perPageValue = Number.parseInt(getPerPageInput(table)?.value || '10', 10) || 10;
            const totalEntries = getTotalEntries(table);
            const totalPages = Math.max(1, Math.ceil(totalEntries / Math.max(perPageValue, 1)));
            const rows = [];

            for (let page = 1; page <= totalPages; page += 1) {
                const url = buildTableUrl(table, {
                    search: searchValue,
                    perPage: perPageValue,
                });
                url.searchParams.set('page', String(page));

                const response = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                const html = await getTableHtmlFromResponse(response);
                const remoteTable = parseHtmlTable(html);

                if (!(remoteTable instanceof HTMLTableElement)) {
                    continue;
                }

                rows.push(...extractTableRows(remoteTable));
            }

            return { headers, rows };
        }

        function downloadExcelFile(filename, headers, rows) {
            const thead = headers.length > 0
                ? `<thead><tr>${headers.map((header) => `<th>${escapeHtml(header)}</th>`).join('')}</tr></thead>`
                : '';
            const tbody = rows.map((row) => `<tr>${row.map((cell) => `<td>${escapeHtml(cell)}</td>`).join('')}</tr>`).join('');
            const documentHtml = `
                <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
                    <head>
                        <meta charset="UTF-8">
                    </head>
                    <body>
                        <table border="1">
                            ${thead}
                            <tbody>${tbody}</tbody>
                        </table>
                    </body>
                </html>
            `;

            const blob = new Blob([documentHtml], { type: 'application/vnd.ms-excel;charset=utf-8;' });
            const downloadUrl = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(downloadUrl);
        }

        function enhanceFastTableExports(root = document) {
            const tables = root.matches?.('[data-fast-table]')
                ? [root]
                : Array.from(root.querySelectorAll('[data-fast-table]'));

            tables.forEach((table) => {
                if (!(table instanceof HTMLElement) || !(table.parentElement instanceof HTMLElement)) {
                    return;
                }

                const tableId = ensureFastTableId(table);
                let exportBar = table.parentElement.querySelector(`[data-fast-table-export-for="${tableId}"]`);

                if (!(exportBar instanceof HTMLElement)) {
                    exportBar = document.createElement('div');
                    exportBar.className = 'global-fast-table-export';
                    exportBar.dataset.fastTableExportFor = tableId;
                    exportBar.innerHTML = `
                        <button type="button" class="global-export-action" data-fast-table-export-button>
                            <i class="fas fa-file-excel"></i>
                            <span>Download Excel</span>
                        </button>
                    `;
                    table.parentElement.insertBefore(exportBar, table);
                }

                const button = exportBar.querySelector('[data-fast-table-export-button]');

                if (!(button instanceof HTMLButtonElement) || button.dataset.bound === 'true') {
                    return;
                }

                button.dataset.bound = 'true';
                button.addEventListener('click', async () => {
                    const originalLabel = button.innerHTML;
                    button.disabled = true;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Preparing Excel...</span>';

                    try {
                        const { headers, rows } = await collectAllFastTableRows(table);

                        if (rows.length === 0) {
                            button.innerHTML = '<i class="fas fa-ban"></i><span>No rows to export</span>';
                            setTimeout(() => {
                                button.disabled = false;
                                button.innerHTML = originalLabel;
                            }, 1600);
                            return;
                        }

                        downloadExcelFile(getExportFilename(table), headers, rows);
                    } catch (error) {
                        console.error('Error exporting fast table:', error);
                    } finally {
                        button.disabled = false;
                        button.innerHTML = originalLabel;
                    }
                });
            });
        }

        function enhancePage(root = document) {
            enhanceDataTableLayout(root);
            enhanceEditButtons(root);
            enhanceInlineFilterForms(root);
            enhanceDataTableFooters(root);
            enhanceFastTableExports(root);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => enhancePage(document), { once: true });
        } else {
            enhancePage(document);
        }

        function getFastTable(target) {
            return target instanceof Element ? target.closest('[data-fast-table]') : null;
        }

        function getTableConfig(table) {
            return {
                endpoint: table.dataset.endpoint || window.location.href,
                searchSelector: table.dataset.searchSelector || '[data-fast-table-search]',
                perPageSelector: table.dataset.perPageSelector || '[data-fast-table-per-page]',
                paginationSelector: table.dataset.paginationSelector || '[data-fast-table-pagination] a',
                history: table.dataset.history !== 'false',
            };
        }

        function getSearchInput(table) {
            const config = getTableConfig(table);
            return table.querySelector(config.searchSelector);
        }

        function getPerPageInput(table) {
            const config = getTableConfig(table);
            return table.querySelector(config.perPageSelector);
        }

        function buildTableUrl(table, overrides = {}) {
            const config = getTableConfig(table);
            const url = new URL(overrides.url || config.endpoint, window.location.origin);
            const searchValue = overrides.search ?? getSearchInput(table)?.value ?? '';
            const perPageValue = overrides.perPage ?? getPerPageInput(table)?.value ?? '';

            if (!overrides.url) {
                if (searchValue !== '') {
                    url.searchParams.set('search', searchValue);
                } else {
                    url.searchParams.delete('search');
                }

                if (perPageValue !== '') {
                    url.searchParams.set('per_page', perPageValue);
                } else {
                    url.searchParams.delete('per_page');
                }
            }

            return url;
        }

        async function loadFastTable(table, overrides = {}) {
            const config = getTableConfig(table);
            const url = buildTableUrl(table, overrides);

            table.classList.add('opacity-60', 'pointer-events-none', 'transition-opacity');

            try {
                const response = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                const contentType = response.headers.get('content-type') || '';
                let html = '';

                if (contentType.includes('application/json')) {
                    const payload = await response.json();
                    html = payload.html || '';
                } else {
                    html = await response.text();
                }

                table.innerHTML = html;

                table.dispatchEvent(new CustomEvent('fast-table:loaded', {
                    bubbles: true,
                    detail: {
                        url: url.toString(),
                    },
                }));

                enhancePage(table);

                if (config.history) {
                    window.history.replaceState({}, '', url.toString());
                }
            } catch (error) {
                console.error('Error loading fast table:', error);
            } finally {
                table.classList.remove('opacity-60', 'pointer-events-none');
            }
        }

        document.addEventListener('input', function (event) {
            const table = getFastTable(event.target);

            if (!table) {
                return;
            }

            const config = getTableConfig(table);

            if (!event.target.matches(config.searchSelector)) {
                return;
            }

            const previousTimer = searchTimers.get(table);

            if (previousTimer) {
                clearTimeout(previousTimer);
            }

            const nextTimer = setTimeout(() => {
                loadFastTable(table, {
                    search: event.target.value,
                    perPage: getPerPageInput(table)?.value || 10,
                });
            }, 250);

            searchTimers.set(table, nextTimer);
        });

        document.addEventListener('change', function (event) {
            const table = getFastTable(event.target);

            if (!table) {
                return;
            }

            const config = getTableConfig(table);

            if (!event.target.matches(config.perPageSelector)) {
                return;
            }

            loadFastTable(table, {
                search: getSearchInput(table)?.value || '',
                perPage: event.target.value,
            });
        });

        document.addEventListener('click', function (event) {
            const link = event.target.closest('a');
            const table = getFastTable(event.target);

            if (!table || !link) {
                return;
            }

            const config = getTableConfig(table);

            if (!link.matches(config.paginationSelector)) {
                return;
            }

            event.preventDefault();
            loadFastTable(table, { url: link.href });
        });
    })();
</script>

{{-- Alpine.js (for dropdowns) --}}
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
@yield('scripts')

</body>
</html>
