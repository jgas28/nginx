@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white rounded-lg shadow">
    <h4 class="text-xl font-semibold mb-6">Edit Payment Reference</h4>

    <form action="{{ route('cvr.updateReference', $cvrApprovals->id) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')

        <!-- Payment Type -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
            <label class="text-sm font-medium text-gray-700">
                Payment Type
            </label>
            <div class="md:col-span-2">
                <input
                    type="text"
                    value="{{ 
                        [
                            'cash' => 'Cash',
                            'bank_transfer' => 'Bank Transfer',
                            'outlet_transfer' => 'Outlet Transfer',
                            'cheque' => 'Cheque',
                        ][$cvrApprovals->payment_type] ?? ucfirst(str_replace('_', ' ', $cvrApprovals->payment_type))
                    }}"
                    readonly
                    class="w-full rounded-md border-gray-300 bg-gray-100 text-gray-700 shadow-sm focus:outline-none"
                >
            </div>
        </div>

        <!-- Payment Name -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
            <label class="text-sm font-medium text-gray-700">
                Payment Name
            </label>
            <div class="md:col-span-2">
                <input
                    type="text"
                    value="{{ $cvrApprovals->payment_name }}"
                    readonly
                    class="w-full rounded-md border-gray-300 bg-gray-100 text-gray-700 shadow-sm focus:outline-none"
                >
            </div>
        </div>

        <!-- Reference Number -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
            <label class="text-sm font-medium text-gray-700">
                Reference Number
            </label>
            <div class="md:col-span-2">
                <input
                    type="text"
                    name="reference_number"
                    value="{{ old('reference_number', $cvrApprovals->reference_number) }}"
                    required
                    class="w-full rounded-md border @error('reference_number') border-red-500 @else border-gray-300 @enderror shadow-sm focus:ring-blue-500 focus:border-blue-500"
                >
                @error('reference_number')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>
        </div>

        <!-- Amount -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
            <label class="text-sm font-medium text-gray-700">
                Amount
            </label>
            <div class="md:col-span-2">
                <input
                    type="text"
                    value="{{ number_format($cvrApprovals->amount, 2) }}"
                    readonly
                    class="w-full rounded-md border-gray-300 bg-gray-100 text-gray-700 shadow-sm focus:outline-none"
                >
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-3 pt-4">
            <a
                href="{{ url()->previous() }}"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition"
            >
                Cancel
            </a>

            <button
                type="submit"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition"
            >
                Update Reference
            </button>
        </div>
    </form>
</div>
@endsection
