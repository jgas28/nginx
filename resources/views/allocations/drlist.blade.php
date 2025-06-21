@extends('layouts.app')

@section('title', 'Delivery Requests List')

@section('content')
    <form method="GET" class="mb-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 items-end">
        <div>
            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="w-full border rounded px-3 py-2" placeholder="From date">
        </div>

        <div>
            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
            <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="w-full border rounded px-3 py-2" placeholder="To date">
        </div>

        <div>
            <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Month</label>
            <select name="month" id="month" class="w-full border rounded px-3 py-2">
                <option value="">Select Month</option>
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                    </option>
                @endfor
            </select>
        </div>

        <div>
            <label for="company_id" class="block text-sm font-medium text-gray-700 mb-1">Company</label>
            <select name="company_id" id="company_id" class="w-full border rounded px-3 py-2">
                <option value="">Select Company</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                        {{ $company->company_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="area_id" class="block text-sm font-medium text-gray-700 mb-1">Area</label>
            <select name="area_id" id="area_id" class="w-full border rounded px-3 py-2">
                <option value="">Select Area</option>
                @foreach ($areas as $area)
                    <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>
                        {{ $area->area_code }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="region_id" class="block text-sm font-medium text-gray-700 mb-1">Region</label>
            <select name="region_id" id="region_id" class="w-full border rounded px-3 py-2">
                <option value="">Select Region</option>
                @foreach ($regions as $region)
                    <option value="{{ $region->id }}" {{ request('region_id') == $region->id ? 'selected' : '' }}>
                        {{ $region->province }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="created_by" class="block text-sm font-medium text-gray-700 mb-1">Created By</label>
            <select name="created_by" id="created_by" class="w-full border rounded px-3 py-2">
                <option value="">Select Creator</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" {{ request('created_by') == $user->id ? 'selected' : '' }}>
                        {{ $user->fname }} {{ $user->lname }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="lg:col-span-6 flex space-x-3 justify-start">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded shadow">Filter</button>
            <a href="{{ route('allocation.drlist') }}" class="text-gray-700 hover:text-gray-900 self-center">Reset</a>
        </div>
    </form>


    <div class="bg-white rounded-lg shadow-md p-6 overflow-x-auto">
        <table class="min-w-full border">
            <thead>
                <tr class="bg-gray-200 text-left text-sm">
                    <th class="p-2 border">MTM</th>
                    <th class="p-2 border">Delivery Rate</th>
                    <th class="p-2 border">Accessorial Total</th>
                    <th class="p-2 border">Delivery Date</th>
                    <th class="p-2 border">Created At</th>
                    <th class="p-2 border">Created By</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($drList as $dr)
                    <tr class="text-sm hover:bg-gray-100">
                        <td class="p-2 border">{{ $dr['mtm'] }}</td>
                        <td class="p-2 border">₱{{ number_format($dr['delivery_rate'], 2) }}</td>
                        <td class="p-2 border">₱{{ number_format($dr['accessorial_total'], 2) }}</td>
                        <td class="p-2 border">{{ \Carbon\Carbon::parse($dr['delivery_date'])->format('Y-m-d') }}</td>
                        <td class="p-2 border">{{ \Carbon\Carbon::parse($dr['created_at'])->format('Y-m-d') }}</td>
                        <td class="p-2 border">{{ $dr['creator_name'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@endsection
