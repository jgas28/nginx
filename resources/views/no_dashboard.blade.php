@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @auth
        <div class="max-w-4xl mx-auto bg-white shadow-md rounded-md p-6 mt-6">
            <h1 class="text-3xl font-extrabold text-gray-800 mb-2">Welcome 👋</h1>

            <div class="mb-4 text-gray-600">
                <p class="text-lg font-semibold">
                    {{ auth()->user()->fname }} {{ auth()->user()->lname }}
                    <span class="text-sm text-gray-500">({{ auth()->user()->employee_code }})</span>
                </p>

                <p class="text-sm mt-1">
                    <span class="font-medium text-gray-700">User ID:</span> {{ auth()->user()->id }}<br>
                    <span class="font-medium text-gray-700">Role ID:</span> {{ auth()->user()->role_id }}
                </p>
            </div>

            <div class="bg-yellow-100 border-l-4 border-yellow-400 text-yellow-800 p-4 rounded">
                <p class="font-medium">You currently don’t have an assigned dashboard role.</p>
                <p>Please contact your system administrator for access.</p>
            </div>
        </div>
    @endauth
@endsection
