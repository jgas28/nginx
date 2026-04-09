@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $dashboardTitle = 'Admin Dashboard';
        $dashboardSubtitle = 'Keep an eye on running balances, liabilities, and approver collections.';
    @endphp
    @include('dashboards.partials.balance-dashboard')
@endsection
