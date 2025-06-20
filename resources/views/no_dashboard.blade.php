@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @auth
        <h1 class="text-2xl font-bold mb-2">Welcome</h1> 
        <p class="mb-4">
            {{ auth()->user()->id }} {{ auth()->user()->fname }} {{ auth()->user()->lname }} 
            ({{ auth()->user()->employee_code }}) – Role: {{ auth()->user()->role_id }}
        </p>
        <p>
            you have no dashboard role!. contact admin
        </p>
    @endauth

@endsection
