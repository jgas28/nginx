@extends('layouts.app')

@section('title', 'Delivery Request Report')

@section('content')
{{-- reports/cashVoucher.blade.php --}}
<table>
    <thead>
        <tr>
            <th>Voucher ID</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($voucherStatuses as $voucher)
            <tr>
                <td>{{ $voucher->id }}</td>
                <td>{{ $voucher->status_text }}</td>
            </tr>
        @endforeach
    </tbody>
</table>


@endsection
