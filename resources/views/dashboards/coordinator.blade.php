@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $dashboardTitle = 'Coordinator Dashboard';
        $dashboardSubtitle = 'Review running balances and uncollected amounts assigned to approvers.';
    @endphp
    @include('dashboards.partials.balance-dashboard')
@endsection
