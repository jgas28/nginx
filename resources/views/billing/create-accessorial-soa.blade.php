@extends('layouts.app')

@section('title', 'Accessorial SOA')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-4xl mx-auto px-4">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Accessorial SOA</h1>
                    <p class="text-gray-600 mt-2">Create Statement of Account for accessorial services</p>
                </div>
                <a href="{{ route('billing.dashboard') }}" class="inline-flex w-full items-center justify-center rounded-lg bg-gray-500 px-4 py-2 text-white hover:bg-gray-600 sm:w-auto">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Coming Soon Message -->
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <i class="fas fa-tools text-6xl text-gray-400 mb-4"></i>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">Coming Soon</h2>
            <p class="text-gray-600 mb-6">
                The Accessorial SOA feature is currently under development. This will allow you to create statements of account
                specifically for accessorial services and charges.
            </p>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-left">
                <h3 class="text-lg font-semibold text-blue-900 mb-2">Planned Features:</h3>
                <ul class="text-blue-800 space-y-1">
                    <li>• Accessorial rate calculations</li>
                    <li>• Additional service charges</li>
                    <li>• Automated billing for extra services</li>
                    <li>• Integration with delivery request line items</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
