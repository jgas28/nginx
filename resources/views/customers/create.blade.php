@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto py-10 px-4">
    <div class="bg-white shadow-md rounded-xl p-8">
        <h2 class="text-2xl font-bold text-center mb-6">Create Customer</h2>

        <form action="{{ route('customers.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Customer Name</label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    value="{{ old('name') }}"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror"
                >
                @error('name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <button 
                    type="submit" 
                    class="w-full bg-green-600 text-white font-semibold py-2 px-4 rounded-md hover:bg-green-700 transition"
                >
                    Create Customer
                </button>

                <a 
                    href="{{ route('customers.index') }}" 
                    class="w-full inline-block text-center border border-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-100 transition"
                >
                    Back to List
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
