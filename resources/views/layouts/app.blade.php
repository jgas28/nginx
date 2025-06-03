<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'FCZCNYX'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900" x-data="{ sidebarOpen: true }">

<div class="flex h-screen">

    {{-- Sidebar --}}
    <aside 
        :class="sidebarOpen ? 'w-64' : 'w-16'" 
        class="bg-gray-800 text-white flex flex-col transition-all duration-300 ease-in-out"
    >
        <!-- Toggle + Logo -->
        <div class="flex items-center px-4 py-3 border-b border-gray-700">
            <button 
                @click="sidebarOpen = !sidebarOpen" 
                class="focus:outline-none text-white hover:text-gray-300 transition"
                title="Toggle Sidebar"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <a href="{{ route('dashboard') }}" 
            class="text-lg font-semibold text-white ml-2" 
            x-show="sidebarOpen" 
            x-transition
            >
                FCZCNYX
            </a>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-2 py-4 overflow-y-auto space-y-1">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 px-3 py-2 rounded hover:bg-gray-700">
                <i class="fas fa-tachometer-alt"></i>
                <span x-show="sidebarOpen" x-transition>Dashboard</span>
            </a>

            <!-- Settings with nested Basic Settings -->
            <div x-data="{ open: false, basicOpen: false, cvOpen: false, dliOpen: false, allocationOpen: false }">
                <!-- Settings Button -->
                <button 
                    @click="open = !open; if (!open) basicOpen = false;" 
                    class="w-full flex items-center justify-between px-3 py-2 rounded hover:bg-gray-700"
                >
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-cogs"></i>
                        <span x-show="sidebarOpen" x-transition>Settings</span>
                    </div>
                    <svg 
                        x-show="sidebarOpen" 
                        :class="open ? 'rotate-90' : ''" 
                        class="w-4 h-4 transform transition-transform" 
                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                <!-- Settings Submenu -->
                <div x-show="open && sidebarOpen" x-transition class="ml-8 mt-1 space-y-1">
                    @if(auth()->user()->role_id != 1)
                        <a href="{{ route('password.change') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">
                            Change Password
                        </a>
                    @endif
                    @if(auth()->user()->role_id = 1)
                        <a href="{{ route('running_balance.index') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">
                            Running Balance
                        </a>
                    @endif
                    <!-- Basic Settings -->
                    <div>
                        <button 
                            @click="basicOpen = !basicOpen" 
                            class="w-full flex items-center justify-between px-3 py-1 rounded hover:bg-gray-700 text-sm"
                        >
                            <div class="flex items-center space-x-2">
                                <span>Delivery Request</span>
                            </div>
                            <svg 
                                :class="basicOpen ? 'rotate-90' : ''" 
                                class="w-4 h-4 transform transition-transform" 
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        
                        <div x-show="basicOpen" x-transition class="ml-6 mt-1 space-y-1">
                            <a href="{{ route('companies.index') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">Companies</a>
                             <a href="{{ route('regions.index') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">Region</a>
                            <a href="{{ route('areas.index') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">Areas</a>
                            <a href="{{ route('customers.index') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">Customer</a>
                            <a href="{{ route('trucks.index') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">Trucks</a>
                            <a href="{{ route('trucksTypes.index') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">Truck Type</a>
                            <a href="{{ route('suppliers.index') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">Suppliers</a>
                            
                        </div>
                    </div>

                    <!-- Delivery Line Items Settings -->
                    <div>
                        <button 
                            @click="dliOpen = !dliOpen" 
                            class="w-full flex items-center justify-between px-3 py-1 rounded hover:bg-gray-700 text-sm"
                        >
                            <div class="flex items-center space-x-2">
                                <span>DR Items</span>
                            </div>
                            <svg 
                                :class="dliOpen ? 'rotate-90' : ''" 
                                class="w-4 h-4 transform transition-transform" 
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        
                        <div x-show="dliOpen" x-transition class="ml-6 mt-1 space-y-1">
                            <a href="{{ route('accessorialTypes.index') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">Accesorial Type</a>
                            <a href="{{ route('addOnRates.index') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">Add On Rate</a>
                            <a href="{{ route('deliveryStatus.index') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">Delivery Status</a>
                            <a href="{{ route('deliveryTypes.index') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">Delivery Types</a>
                            <a href="{{ route('distanceTypes.index') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">Distance</a>
                            <a href="{{ route('warehouses.index') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">Warehouse</a>
                            <a href="{{ route('deliveryRequestType.index') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">DR Types</a>
                            
                        </div>
                    </div>

                    <!-- Cash Voucher Settings -->
                    <div>
                        <button 
                            @click="cvOpen = !cvOpen" 
                            class="w-full flex items-center justify-between px-3 py-1 rounded hover:bg-gray-700 text-sm"
                        >
                            <div class="flex items-center space-x-2">
                                <span>Cash Voucher</span>
                            </div>
                            <svg 
                                :class="cvOpen ? 'rotate-90' : ''" 
                                class="w-4 h-4 transform transition-transform" 
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        
                        <div x-show="cvOpen" x-transition class="ml-6 mt-1 space-y-1">
                            <a href="{{ route('monthly-series.reset.index') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">Reset Series</a>
                            <a href="{{ route('approvers.index') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">Approver</a>
                            <a href="{{ route('taxes.index') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">Tax</a>
                            <a href="{{ route('expenseTypes.index') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">Expense Types</a>
                            <a href="{{ route('cvr_request_types.index') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">CV Types</a>
                            
                        </div>
                    </div>

                    <!-- Allocation -->
                    <div>
                        <button 
                            @click="allocationOpen = !allocationOpen" 
                            class="w-full flex items-center justify-between px-3 py-1 rounded hover:bg-gray-700 text-sm"
                        >
                            <div class="flex items-center space-x-2">
                                <span>Allocation</span>
                            </div>
                            <svg 
                                :class="allocationOpen ? 'rotate-90' : ''" 
                                class="w-4 h-4 transform transition-transform" 
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        
                        <div x-show="allocationOpen" x-transition class="ml-6 mt-1 space-y-1">
                            <a href="{{ route('fleetCards.index') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">Fleet Card</a>
                        </div>
                    </div>
                </div>
            </div>
            @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 2)
                <div x-data="{ openDR: false }">
                    <button 
                        @click="openDR = !openDR" 
                        class="w-full flex items-center justify-between px-3 py-2 rounded hover:bg-gray-700"
                    >
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-box"></i>
                            <span x-show="sidebarOpen" x-transition>Delivery Request</span>
                        </div>
                        <svg 
                            x-show="sidebarOpen" 
                            :class="openDR ? 'rotate-90' : ''" 
                            class="w-4 h-4 transform transition-transform" 
                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <div x-show="openDR && sidebarOpen" x-transition class="ml-8 mt-1 space-y-1">
                        <a href="{{ route('deliveryRequest.create') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">
                            Create
                        </a>
                        <a href="{{ route('deliveryRequest.index') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">
                            List
                        </a>
                    </div>
                </div>
            @endif
            @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 4)
                <div x-data="{ openAllocate: false }">
                    <button 
                        @click="openAllocate = !openAllocate" 
                        class="w-full flex items-center justify-between px-3 py-2 rounded hover:bg-gray-700"
                    >
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-truck"></i>
                            <span x-show="sidebarOpen" x-transition>Allocation</span>
                        </div>
                        <svg 
                            x-show="sidebarOpen" 
                            :class="openAllocate ? 'rotate-90' : ''" 
                            class="w-4 h-4 transform transition-transform" 
                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <div x-show="openAllocate && sidebarOpen" x-transition class="ml-8 mt-1 space-y-1">
                        <a href="{{ route('allocations.index') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">
                            Allocate
                        </a>
                    </div>
                </div>
            @endif
            @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 4)
                <div x-data="{ openCoordinator: false }">
                    <button 
                        @click="openCoordinator = !openCoordinator" 
                        class="w-full flex items-center justify-between px-3 py-2 rounded hover:bg-gray-700"
                    >
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-project-diagram"></i>
                            <span x-show="sidebarOpen" x-transition>Coordinator</span>
                        </div>
                        <svg 
                            x-show="sidebarOpen" 
                            :class="openCoordinator ? 'rotate-90' : ''" 
                            class="w-4 h-4 transform transition-transform" 
                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <div x-show="openCoordinator && sidebarOpen" x-transition class="ml-8 mt-1 space-y-1">
                        <a href="{{ route('coordinators.create') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">
                            Create Request
                        </a>
                        <a href="{{ route('coordinators.index') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">
                            List
                        </a>
                    </div>
                </div>
            @endif
            @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 3)
                <div x-data="{ openCVR: false }">
                    <button 
                        @click="openCVR = !openCVR" 
                        class="w-full flex items-center justify-between px-3 py-2 rounded hover:bg-gray-700"
                    >
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-money-bill"></i>
                            <span x-show="sidebarOpen" x-transition>Cash Voucher</span>
                        </div>
                        <svg 
                            x-show="sidebarOpen" 
                            :class="openCVR ? 'rotate-90' : ''" 
                            class="w-4 h-4 transform transition-transform" 
                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <div x-show="openCVR && sidebarOpen" x-transition class="ml-8 mt-1 space-y-1">
                        <a href="{{ route('cashVoucherRequests.approval') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">
                            DR Approval
                        </a>
                        <a href="{{ route('adminCV.approval') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">
                            Admin/RPM Approval
                        </a>
                        <a href="{{ route('adminCV.cvrList') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">
                            List - Admin
                        </a>
                        <a href="{{ route('cashVoucherRequests.cvrList') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">
                            List - DR
                        </a>
                        <a href="{{ route('cashVoucherRequests.rejectView') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">
                            Reject - DR
                        </a>
                        <a href="{{ route('admin.index') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">
                            Admin/RPM Request
                        </a>
                    </div>
                </div>
            @endif
            @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 4)
                <div x-data="{ openCoordinator: false }">
                    <button 
                        @click="openCoordinator = !openCoordinator" 
                        class="w-full flex items-center justify-between px-3 py-2 rounded hover:bg-gray-700"
                    >
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span x-show="sidebarOpen" x-transition>Liquidation DR</span>
                        </div>
                        <svg 
                            x-show="sidebarOpen" 
                            :class="openCoordinator ? 'rotate-90' : ''" 
                            class="w-4 h-4 transform transition-transform" 
                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <div x-show="openCoordinator && sidebarOpen" x-transition class="ml-8 mt-1 space-y-1">
                        <a href="{{ route('liquidations.index') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">
                            Liquidate
                        </a>
                        <a href="{{ route('liquidations.reviewList') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">
                            Validate Liquidation
                        </a>
                        <a href="{{ route('liquidations.validatedList') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">
                            Collect Liquidation
                        </a>
                        <a href="{{ route('liquidations.approvalList') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">
                            Approved Liquidation
                        </a>
                    </div>
                </div>
            @endif

            @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 4)
                <div x-data="{ openCoordinator: false }">
                    <button 
                        @click="openCoordinator = !openCoordinator" 
                        class="w-full flex items-center justify-between px-3 py-2 rounded hover:bg-gray-700"
                    >
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span x-show="sidebarOpen" x-transition>Liquidation Admin</span>
                        </div>
                        <svg 
                            x-show="sidebarOpen" 
                            :class="openCoordinator ? 'rotate-90' : ''" 
                            class="w-4 h-4 transform transition-transform" 
                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <div x-show="openCoordinator && sidebarOpen" x-transition class="ml-8 mt-1 space-y-1">
                        <a href="{{ route('liquidations.indexAdmin') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">
                            Liquidate
                        </a>
                        <a href="{{ route('liquidations.reviewList') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">
                            Validate Liquidation
                        </a>
                        <a href="{{ route('liquidations.validatedList') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">
                            Collect Liquidation
                        </a>
                        <a href="{{ route('liquidations.approvalList') }}" class="block px-3 py-1 rounded hover:bg-gray-700 text-sm">
                            Approved Liquidation
                        </a>
                    </div>
                </div>
            @endif
        </nav>

        <!-- Logout -->
        <form method="POST" action="{{ route('logout') }}" class="p-4">
            @csrf
            <button class="flex items-center space-x-3 w-full text-left px-3 py-2 rounded bg-red-500 hover:bg-red-600 text-white">
                <i class="fas fa-sign-out-alt"></i>
                <span x-show="sidebarOpen" x-transition>Logout</span>
            </button>
        </form>
    </aside>


    {{-- Main Content --}}
    <div class="flex-1 flex flex-col overflow-hidden">
        {{-- Navbar --}}
        <header class="bg-white shadow px-4 py-3 flex justify-between items-center">
            <div>
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
        <main class="flex-1 overflow-y-auto p-4">
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

{{-- Alpine.js (for dropdowns) --}}
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

</body>
</html>
