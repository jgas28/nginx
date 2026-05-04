@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h2 class="text-xl font-bold mb-4">Delivery Request Summary</h2>
    <div class="overflow-x-auto">
        <table class="table-auto w-full border border-gray-300 text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2">MTM</th>
                    <th class="border p-2">Delivery Rate</th>
                    <th class="border p-2">Accessorial Rate</th>
                    <th class="border p-2">Requested</th>
                    <th class="border p-2">Approved</th>
                    <th class="border p-2">Liquidated (Cash)</th>
                    <th class="border p-2">Liquidated (Card)</th>
                    <th class="border p-2">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deliveryRequests as $dr)
                    @php
                        $accessorialRate = $dr->lineItems->sum(fn($i) => (float) $i->accessorial_rate);

                        $requested = $dr->cashVouchers->sum('amount');
                        $approved = $dr->cvrApprovals->sum('amount');

                        $liquidatedCash = $dr->liquidations->sum(function ($l) {
                            return ($l->allowance ?? 0) + ($l->manpower ?? 0) + ($l->hauling ?? 0) +
                                ($l->right_of_way ?? 0) + ($l->roro_expense ?? 0) +
                                collect($l->gasoline ?? [])
                                        ->filter(fn($g) => ($g['type'] ?? '') === 'cash')
                                        ->sum(fn($g) => (float) ($g['amount'] ?? 0)) +
                                collect($l->rfid ?? [])
                                        ->filter(fn($r) => ($r['type'] ?? '') === 'cash')
                                        ->sum(fn($r) => (float) ($r['amount'] ?? 0)) +
                                collect($l->others ?? [])
                                        ->sum(fn($o) => (float) ($o['amount'] ?? 0));
                        });

                        $liquidatedCard = $dr->liquidations->sum(function ($l) {
                            return collect($l->gasoline ?? [])
                                ->filter(fn($g) => ($g['type'] ?? '') === 'card')
                                ->sum(fn($g) => (float) ($g['amount'] ?? 0)) +
                                collect($l->rfid ?? [])
                                ->filter(fn($r) => ($r['type'] ?? '') === 'card')
                                ->sum(fn($r) => (float) ($r['amount'] ?? 0));
                        });
                    @endphp
                    <tr>
                        <td class="border p-2">{{ $dr->mtm }}</td>
                        <td class="border p-2 text-right">₱{{ number_format($dr->delivery_rate ?? 0, 2) }}</td>
                        <td class="border p-2 text-right">₱{{ number_format($accessorialRate, 2) }}</td>
                        <td class="border p-2 text-right">₱{{ number_format($requested, 2) }}</td>
                        <td class="border p-2 text-right">₱{{ number_format($approved, 2) }}</td>
                        <td class="border p-2 text-right">₱{{ number_format($dr->liquidations_totals['cash'] ?? 0, 2) }}</td>
                        <td class="border p-2 text-right">₱{{ number_format($dr->liquidations_totals['card'] ?? 0, 2) }}</td>
                        <td class="border p-2 text-center">
                            <button 
                                @click="selectedModal = '{{ $dr->id }}'" 
                                class="text-blue-600 hover:underline">
                                View
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
