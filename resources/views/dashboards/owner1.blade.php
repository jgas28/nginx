@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $dashboardTitle = 'Owner Dashboard';
        $dashboardSubtitle = 'View profitability, expenses, delivery health, approvals, and liquidation activity.';
        $showExtended = true;
    @endphp
    @include('dashboards.partials.owner-dashboard')
@endsection
