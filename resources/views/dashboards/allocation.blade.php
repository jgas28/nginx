@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $dashboardTitle = 'Allocation Dashboard';
        $dashboardSubtitle = 'Track balance movement and uncollected amounts while planning delivery allocations.';
    @endphp
    @include('dashboards.partials.balance-dashboard')
@endsection
