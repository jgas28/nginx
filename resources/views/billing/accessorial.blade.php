@extends('layouts.app')

@section('title', 'Accessorial Billing')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="mx-auto max-w-4xl px-4">
        <div class="mb-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Accessorial Billing</h1>
                    <p class="mt-2 text-gray-600">Manage accessorial services and billing</p>
                </div>
                <a href="{{ route('billing.dashboard') }}" class="inline-flex w-full items-center justify-center rounded-lg bg-gray-500 px-4 py-2 text-white hover:bg-gray-600 sm:w-auto">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>
        </div>

        <div class="rounded-lg bg-white p-8 text-center shadow-lg">
            <i class="fas fa-tools mb-4 text-6xl text-gray-400"></i>
            <h2 class="mb-4 text-2xl font-semibold text-gray-900">Coming Soon</h2>
            <p class="mb-6 text-gray-600">
                The Accessorial Billing module is currently under development. This will provide comprehensive
                management of accessorial services, rates, and billing.
            </p>
            <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-left">
                <h3 class="mb-2 text-lg font-semibold text-green-900">What to expect:</h3>
                <ul class="space-y-1 text-green-800">
                    <li>&bull; Accessorial type management</li>
                    <li>&bull; Rate configuration and updates</li>
                    <li>&bull; Automated calculation of additional charges</li>
                    <li>&bull; Integration with delivery requests</li>
                    <li>&bull; Separate SOA generation for accessorial services</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
