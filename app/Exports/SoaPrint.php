@extends('layouts.app')

@section('content')
<!-- Print Layout -->
<div class="container mx-auto p-6">
    <!-- Company Information -->
    <div class="text-center mb-8">
        <div class="font-bold text-xl">FCBOIS TRUCKING SERVICES</div>
        <div class="text-sm">VAT REG. TIN # 246-451-785-000</div>
        <div class="text-sm">MENINA ROMMEL L. PROP</div>
        <div class="text-sm">405 BIANCA D BLGD. AMAIA STEP NUVALI BRGY, CANLUBANG CALAMBA CITY, LAGUNA</div>
    </div>

    <!-- SOA Number and Date -->
    <div class="flex justify-between mb-4">
        <div class="font-bold text-lg">SOA Number: {{ $soa->soa_number }}</div>
        <div class="font-bold text-lg">Date: {{ date('F j, Y') }}</div>
    </div>

    <!-- Table for Delivery Request Details -->
    <div class="overflow-x-auto bg-white shadow-md rounded-lg mb-6">
        <table class="table-auto w-full border-collapse border">
            <thead>
                <tr>
                    <th class="px-4 py-2 border">PROJECT</th>
                    <th class="px-4 py-2 border">SITE ID</th>
                    <th class="px-4 py-2 border">SITE /WHSE. ORIGIN</th>
                    <th class="px-4 py-2 border">WSHE / STAGING DSTN</th>
                    <th class="px-4 py-2 border">REGION</th>
                    <th class="px-4 py-2 border">DN ID</th>
                    <th class="px-4 py-2 border">RECEIVED DATE</th>
                    <th class="px-4 py-2 border">Plate Number</th>
                    <th class="px-4 py-2 border">TRUCK</th>
                    <th class="px-4 py-2 border">TRUCK RATE</th>
                    <th class="px-4 py-2 border">ADD ON</th>
                    <th class="px-4 py-2 border">TOTAL TRUCK RATE</th>
                </tr>
            </thead>
            <tbody>
                @foreach($soa->deliveryRequests as $deliveryRequest)
                <tr>
                    <td class="px-4 py-2 border">{{ $deliveryRequest->project_name }}</td>
                    <td class="px-4 py-2 border">{{ $deliveryRequest->site_id }}</td>
                    <td class="px-4 py-2 border">{{ $deliveryRequest->site_origin }}</td>
                    <td class="px-4 py-2 border">{{ $deliveryRequest->wshe_staging_dstn }}</td>
                    <td class="px-4 py-2 border">{{ $deliveryRequest->region }}</td>
                    <td class="px-4 py-2 border">{{ $deliveryRequest->dn_id }}</td>
                    <td class="px-4 py-2 border">{{ $deliveryRequest->received_date }}</td>
                    <td class="px-4 py-2 border">{{ $deliveryRequest->plate_number }}</td>
                    <td class="px-4 py-2 border">{{ $deliveryRequest->truck }}</td>
                    <td class="px-4 py-2 border">{{ number_format($deliveryRequest->truck_rate, 2) }}</td>
                    <td class="px-4 py-2 border">{{ number_format($deliveryRequest->add_on, 2) }}</td>
                    <td class="px-4 py-2 border">{{ number_format($deliveryRequest->total_truck_rate, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Prepared By (Footer) -->
    <div class="mt-8 text-center">
        <div class="font-bold">Prepared By:</div>
        <div class="font-bold">{{ $soa->prepared_by }}</div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 100%;
            padding: 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            padding: 8px 12px;
            border: 1px solid #000;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .text-lg {
            font-size: 1.25rem;
        }

        .text-sm {
            font-size: 0.875rem;
        }
    }
</style>
@endpush
