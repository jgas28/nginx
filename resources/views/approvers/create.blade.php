@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto py-10 px-4">
    <div class="bg-white shadow-lg rounded-xl p-8">
        <form action="{{ route('approvers.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    placeholder="Enter full name" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                >
            </div>

            <div>
                <label for="site" class="block text-sm font-medium text-gray-700 mb-1">Site</label>
                <input 
                    type="text" 
                    name="site" 
                    id="site" 
                    placeholder="Enter site name" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                >
            </div>

            <button 
                type="submit" 
                class="w-full bg-blue-600 text-white font-semibold py-2 px-4 rounded-md hover:bg-blue-700 transition duration-200"
            >
                Create Approver
            </button>
        </form>
    </div>
</div>
@endsection
