@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $dashboardTitle = 'Executive Dashboard';
        $dashboardSubtitle = 'Track profitability, expenses, delivery activity, and approver balances at a glance.';
        $showExtended = false;
    @endphp
    @include('dashboards.partials.owner-dashboard')
@endsection
