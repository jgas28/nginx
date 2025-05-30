@extends('layouts.app')

@section('content')
@if(session('success'))
    <div class="bg-green-100 text-green-800 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
@endif
<div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
    @if ($errors->any())
        <div class="mb-4 text-red-600">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('password.change') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label for="current_password" class="block text-gray-700">Current Password</label>
            <input type="password" name="current_password" id="current_password" required
                   class="w-full mt-1 p-2 border rounded">
        </div>

        <div class="mb-4">
            <label for="new_password" class="block text-gray-700">New Password</label>
            <input type="password" name="new_password" id="new_password" required
                   class="w-full mt-1 p-2 border rounded">
        </div>

        <div class="mb-4">
            <label for="new_password_confirmation" class="block text-gray-700">Confirm New Password</label>
            <input type="password" name="new_password_confirmation" id="new_password_confirmation" required
                   class="w-full mt-1 p-2 border rounded">
        </div>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Update Password
        </button>
    </form>
</div>
@endsection
