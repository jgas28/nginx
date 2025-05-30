@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @auth
        <h1 class="text-2xl font-bold mb-2">Welcome</h1> 
        <p class="mb-4">
            {{ auth()->user()->id }} {{ auth()->user()->fname }} {{ auth()->user()->lname }} 
            ({{ auth()->user()->employee_code }}) – Role: {{ auth()->user()->role_id }}
        </p>
    @endauth

    <form action="{{ route('logout') }}" method="POST" class="mb-4">
        @csrf
        <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded">Logout</button>
    </form>
@endsection
