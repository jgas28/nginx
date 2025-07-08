<button onclick="closeModal()"
    class="absolute top-2 right-2 text-gray-600 hover:text-black text-xl font-bold">&times;</button>

<h2 class="text-lg font-semibold mb-4">MTM: {{ $deliveryRequest->mtm }}</h2>

@if ($deliveryRequest->cashVouchers->isNotEmpty())
    <div class="overflow-x-auto">
        <table class="w-full text-sm border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 border">CVR Number</th>
                    <th class="p-2 border">Type</th>
                    <th class="p-2 border">Amount</th>
                    <th class="p-2 border">Approved Amount</th>
                    <th class="p-2 border">Liquidated Cash</th>
                    <th class="p-2 border">Liquidated Card</th>
                    <th class="p-2 border">Requestor</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($deliveryRequest->cashVouchers as $cvr)
                    <tr>
                        <td class="p-2 border">{{ $cvr->cvr_number }}</td>
                        <td class="p-2 border">{{ ucfirst($cvr->cvr_type) }}</td>
                        <td class="p-2 border">₱{{ number_format($cvr->amount, 2) }}</td>

                        {{-- Approved Amounts with Status --}}
                        <td class="p-2 border">
                            <ul class="list-disc list-inside">
                                @foreach ($cvr->cvrApprovals as $approval)
                                    <li>
                                        ₱{{ number_format($approval->amount, 2) }}
                                        (
                                        @if ($approval->status == 1)
                                            <span class="text-green-600">Approved</span>
                                        @elseif ($approval->status == 3)
                                            <span class="text-red-600">Rejected</span>
                                        @else
                                            <span class="text-gray-600">{{ ucfirst($approval->status) }}</span>
                                        @endif
                                        )
                                    </li>
                                @endforeach
                            </ul>
                        </td>

                        {{-- Liquidated Cash with Status (same style as Approved Amounts) --}}
                        <td class="p-2 border text-sm">
                            <ul class="list-disc list-inside">
                                @foreach ($cvr->liquidations as $liq)
                                    @php
                                        $cashAmount = 0;

                                        $cashAmount += ($liq->allowance ?? 0)
                                                    + ($liq->manpower ?? 0)
                                                    + ($liq->hauling ?? 0)
                                                    + ($liq->right_of_way ?? 0)
                                                    + ($liq->roro_expense ?? 0)
                                                    + ($liq->cash_charge ?? 0);

                                        if (is_array($liq->others)) {
                                            foreach ($liq->others as $item) {
                                                $cashAmount += $item['amount'] ?? 0;
                                            }
                                        }

                                        if (is_array($liq->gasoline)) {
                                            foreach ($liq->gasoline as $g) {
                                                if (($g['type'] ?? 'cash') === 'cash') {
                                                    $cashAmount += $g['amount'] ?? 0;
                                                }
                                            }
                                        }

                                        if (is_array($liq->rfid)) {
                                            foreach ($liq->rfid as $r) {
                                                if (($r['type'] ?? 'cash') === 'cash') {
                                                    $cashAmount += $r['amount'] ?? 0;
                                                }
                                            }
                                        }

                                        $statusText = match((int) $liq->status) {
                                            1 => 'Validated',
                                            3 => 'For Collection',
                                            4 => 'For Approval',
                                            5 => 'Completed',
                                            10 => 'Rejected',
                                            default => 'N/A',
                                        };

                                        $statusClass = $liq->status == 10 ? 'text-red-600' : 'text-green-600';
                                    @endphp

                                    @if ($cashAmount > 0)
                                        <li>
                                            <span class="text-black">₱{{ number_format($cashAmount, 2) }}</span>
                                            (
                                            <span class="{{ $statusClass }}">{{ $statusText }}</span>
                                            )
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </td>

                        {{-- Liquidated Card with Status --}}
                        <td class="p-2 border text-blue-700 text-sm">
                            <ul class="list-disc list-inside">
                                @foreach ($cvr->liquidations as $liq)
                                    @php
                                        $cardAmount = 0;

                                        if (is_array($liq->gasoline)) {
                                            foreach ($liq->gasoline as $g) {
                                                if (($g['type'] ?? '') === 'card') {
                                                    $cardAmount += $g['amount'] ?? 0;
                                                }
                                            }
                                        }

                                        if (is_array($liq->rfid)) {
                                            foreach ($liq->rfid as $r) {
                                                if (($r['type'] ?? '') === 'card') {
                                                    $cardAmount += $r['amount'] ?? 0;
                                                }
                                            }
                                        }
                                    @endphp

                                    @if ($cardAmount > 0)
                                        <li>
                                            ₱{{ number_format($cardAmount, 2) }} -
                                            <span class="italic text-gray-500">{{ $liq->remarks }}</span><br>
                                            <span class="text-xs text-gray-600">Status: {{ ucfirst($liq->status ?? 'N/A') }}</span>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </td>

                        {{-- Requestor --}}
                        <td class="p-2 border">
                            {{ optional($cvr->employee)->fname }} {{ optional($cvr->employee)->lname }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @php
        $totalApprovedAmount = 0;
        $totalLiquidatedCash = 0;
        $totalLiquidatedCard = 0;

        foreach ($deliveryRequest->cashVouchers as $cvr) {
            // Approved Amounts
            foreach ($cvr->cvrApprovals as $approval) {
                if ($approval->status != 3) { // Exclude rejected
                    $totalApprovedAmount += $approval->amount;
                }
            }

            foreach ($cvr->liquidations as $liq) {
                if ($liq->status != 10) { // Exclude rejected
                    // Liquidated Cash
                    $cashAmount = ($liq->allowance ?? 0)
                                + ($liq->manpower ?? 0)
                                + ($liq->hauling ?? 0)
                                + ($liq->right_of_way ?? 0)
                                + ($liq->roro_expense ?? 0)
                                + ($liq->cash_charge ?? 0);

                    if (is_array($liq->others)) {
                        foreach ($liq->others as $item) {
                            $cashAmount += $item['amount'] ?? 0;
                        }
                    }

                    if (is_array($liq->gasoline)) {
                        foreach ($liq->gasoline as $g) {
                            if (($g['type'] ?? 'cash') === 'cash') {
                                $cashAmount += $g['amount'] ?? 0;
                            }
                        }
                    }

                    if (is_array($liq->rfid)) {
                        foreach ($liq->rfid as $r) {
                            if (($r['type'] ?? 'cash') === 'cash') {
                                $cashAmount += $r['amount'] ?? 0;
                            }
                        }
                    }

                    $totalLiquidatedCash += $cashAmount;

                    // Liquidated Card
                    $cardAmount = 0;
                    if (is_array($liq->gasoline)) {
                        foreach ($liq->gasoline as $g) {
                            if (($g['type'] ?? '') === 'card') {
                                $cardAmount += $g['amount'] ?? 0;
                            }
                        }
                    }

                    if (is_array($liq->rfid)) {
                        foreach ($liq->rfid as $r) {
                            if (($r['type'] ?? '') === 'card') {
                                $cardAmount += $r['amount'] ?? 0;
                            }
                        }
                    }

                    $totalLiquidatedCard += $cardAmount;
                }
            }
        }
        $accessorialTotal = $deliveryRequest->lineItems->sum('accessorial_rate');

        $remainingBalance = ($deliveryRequest->delivery_rate + $accessorialTotal) 
                    - ($totalLiquidatedCash + $totalLiquidatedCard);
    @endphp

    {{-- Summary Table --}}
    <div class="mt-6">
        <h3 class="text-md font-semibold mb-2">Summary</h3>
        <table class="w-full text-sm border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 border">Delivery Rate</th>
                    <th class="p-2 border">Accessorial Total</th>
                    <th class="p-2 border">Total Approved Amount</th>
                    <th class="p-2 border">Total Liquidated (Cash)</th>
                    <th class="p-2 border">Total Liquidated (Card)</th>
                    <th class="p-2 border">Profit</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="p-2 border">₱{{ number_format($deliveryRequest->delivery_rate, 2) }}</td>
                    <td class="p-2 border">₱{{ number_format($accessorialTotal, 2) }}</td>
                    <td class="p-2 border">₱{{ number_format($totalApprovedAmount, 2) }}</td>
                    <td class="p-2 border">₱{{ number_format($totalLiquidatedCash, 2) }}</td>
                    <td class="p-2 border">₱{{ number_format($totalLiquidatedCard, 2) }}</td>
                    <td class="p-2 border font-semibold text-red-600">
                        ₱{{ number_format($remainingBalance, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>



@else
    <p class="text-gray-500 italic">No CVRs found for this MTM.</p>
@endif
