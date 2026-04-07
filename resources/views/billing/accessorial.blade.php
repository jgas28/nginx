@extends('layouts.app')

@section('title', 'Accessorial Billing')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-4xl mx-auto px-4">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Accessorial Billing</h1>
                    <p class="text-gray-600 mt-2">Manage accessorial services and billing</p>
                </div>
                <a href="{{ route('billing.dashboard') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Coming Soon Message -->
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <i class="fas fa-tools text-6xl text-gray-400 mb-4"></i>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">Coming Soon</h2>
            <p class="text-gray-600 mb-6">
                The Accessorial Billing module is currently under development. This will provide comprehensive
                management of accessorial services, rates, and billing.
            </p>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-left">
                <h3 class="text-lg font-semibold text-green-900 mb-2">What to expect:</h3>
                <ul class="text-green-800 space-y-1">
                    <li>• Accessorial type management</li>
                    <li>• Rate configuration and updates</li>
                    <li>• Automated calculation of additional charges</li>
                    <li>• Integration with delivery requests</li>
                    <li>• Separate SOA generation for accessorial services</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection