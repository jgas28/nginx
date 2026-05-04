@extends('layouts.app')

@section('title', 'Delivery Requests List')

@section('content')
    {{-- Trigger Button --}}
    <div class="mb-4 flex justify-between items-end">
        <form method="GET" class="flex items-end gap-4">
            <div>
                <label for="mtm" class="block text-sm font-medium text-gray-700">MTM</label>
                <input type="text" name="mtm" id="mtm" value="{{ request('mtm') }}"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm text-sm px-2 py-1">
            </div>

            <div class="pb-[2px]">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded shadow">
                    Filter
                </button>
                <a href="{{ route('allocation.drlist') }}"
                    class="ml-2 text-gray-700 hover:text-gray-900 underline text-sm">Reset</a>
            </div>
        </form>

        <div>
            <button onclick="openFilterModal()"
                class="bg-gray-800 hover:bg-gray-900 text-white font-semibold px-4 py-2 rounded shadow">
                Filter Options
            </button>
        </div>
    </div>

    {{-- Filter Modal --}}
    <div id="filter-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-start justify-center pt-10 z-50">
        <div onclick="closeFilterModal()" class="absolute inset-0 cursor-pointer"></div>
        <div class="relative bg-white rounded-lg w-[95vw] max-w-6xl max-h-[90vh] overflow-y-auto shadow-lg p-6 sm:p-8 z-10">
            <h2 class="text-lg font-semibold mb-4">Filter Delivery Requests</h2>

            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 items-end">
                {{-- Date Range --}}
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700">Date From</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm text-sm">
                </div>

                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700">Date To</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm text-sm">
                </div>

                {{-- Month --}}
                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700">Month</label>
                    <select name="month" id="month"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">All</option>
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Company --}}
                <div>
                    <label for="company_id" class="block text-sm font-medium text-gray-700">Company</label>
                    <select name="company_id" id="company_id"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">All</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                {{ $company->company_code }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Area --}}
                <div>
                    <label for="area_id" class="block text-sm font-medium text-gray-700">Area</label>
                    <select name="area_id" id="area_id"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">All</option>
                        @foreach ($areas as $area)
                            <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>
                                {{ $area->area_code }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Region --}}
                <div>
                    <label for="region_id" class="block text-sm font-medium text-gray-700">Region</label>
                    <select name="region_id" id="region_id"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">All</option>
                        @foreach ($regions as $region)
                            <option value="{{ $region->id }}" {{ request('region_id') == $region->id ? 'selected' : '' }}>
                                {{ $region->province }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Created By --}}
                <div>
                    <label for="created_by" class="block text-sm font-medium text-gray-700">Created By</label>
                    <select name="created_by" id="created_by"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">All</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ request('created_by') == $user->id ? 'selected' : '' }}>
                                {{ $user->fname }} {{ $user->lname }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="col-span-full flex justify-between items-center mt-4">
                    <div class="flex gap-4">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded shadow">
                            Apply Filters
                        </button>
                        <a href="{{ route('allocation.drlist') }}"
                            class="text-gray-700 hover:text-gray-900 self-center">Reset</a>
                    </div>
                    <button type="button" onclick="closeFilterModal()" class="text-sm text-red-600 hover:underline">
                        Close
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delivery Table --}}
    <div class="bg-white rounded-lg shadow-md p-6 overflow-x-auto">
        <table class="min-w-full border text-sm">
            <thead class="bg-gray-200 text-left">
                <tr>
                    <th class="p-2 border">MTM</th>
                    <th class="p-2 border">Delivery Rate</th>
                    <th class="p-2 border">Accessorial Total</th>
                    <th class="p-2 border">Delivery Date</th>
                    <th class="p-2 border">Created At</th>
                    <th class="p-2 border">Created By</th>
                    <th class="p-2 border">Status</th>
                    <th class="p-2 border">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($drList as $dr)
                    <tr class="hover:bg-gray-100">
                        <td class="p-2 border">{{ $dr->mtm }}</td>
                        <td class="p-2 border">₱{{ number_format($dr->delivery_rate, 2) }}</td>
                        <td class="p-2 border">₱{{ number_format($dr->accessorial_total, 2) }}</td>
                        <td class="p-2 border">{{ \Carbon\Carbon::parse($dr->delivery_date)->format('Y-m-d') }}</td>
                        <td class="p-2 border">{{ \Carbon\Carbon::parse($dr->created_at)->format('Y-m-d') }}</td>
                        <td class="p-2 border">{{ $dr->creator_name }}</td>
                        <td class="p-2 border">
                            @switch($dr->status)
                                @case(1)
                                    Active
                                    @break

                                @case(0)
                                    Inactive
                                    @break

                                @default
                                    Unknown
                            @endswitch
                        </td>
                        <td class="p-2 border">
                            <button 
                                onclick="fetchDRDetails({{ $dr->id }}, {{ $dr->delivery_rate }}, {{ $dr->accessorial_total }})"
                                class="bg-blue-500 text-white text-xs px-3 py-1 rounded hover:bg-blue-600">
                                View
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- View Modal --}}
    <div id="dr-modal" class="transition-opacity duration-300 fixed inset-0 bg-black bg-opacity-50 hidden items-start justify-center pt-10 z-50">
        <div onclick="closeModal()" class="absolute inset-0"></div>
        <div id="dr-modal-content"
            class="relative bg-white rounded-lg w-[95vw] max-w-7xl max-h-[90vh] overflow-y-auto shadow-lg p-6 sm:p-8 z-10">
            {{-- AJAX content will be injected here --}}
        </div>
    </div>

    {{-- Scripts --}}
    <script>
        function fetchDRDetails(drId, deliveryRate = 0, accessorialTotal = 0) {
            fetch(`/allocation/show/${drId}`)
                .then(response => {
                    if (!response.ok) throw new Error("Network response was not ok");
                    return response.text();
                })
                .then(html => {
                    const container = document.getElementById('dr-modal-content');
                    container.innerHTML = html;

                    openModal();
                })
                .catch(error => {
                    console.error("Error fetching DR details:", error);
                    alert("Failed to load DR details. Please try again.");
                });
        }

        function openModal() {
            const modal = document.getElementById('dr-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            const modal = document.getElementById('dr-modal');
            modal.classList.remove('flex');
            modal.classList.add('hidden'); 
        }

        function openFilterModal() {
            const modal = document.getElementById('filter-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeFilterModal() {
            const modal = document.getElementById('filter-modal');
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === "Escape") {
                closeModal();
                closeFilterModal();
            }
        });
    </script>
@endsection
