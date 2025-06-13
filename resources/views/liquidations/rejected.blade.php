@extends('layouts.app')

@section('content')
    <h1>Rejected Liquidations</h1>

    @dump($rejectedLiquidations) <!-- Use this instead of dd() -->

    @if ($rejectedLiquidations->isEmpty())
        <p>No rejected liquidations found.</p>
    @else
        <ul>
            @foreach ($rejectedLiquidations as $liquidation)
                <li>
                    <a href="{{ route('liquidations.show', $liquidation->id) }}">
                        Liquidation #{{ $liquidation->id }} - {{ $liquidation->title ?? 'No Title' }}
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
