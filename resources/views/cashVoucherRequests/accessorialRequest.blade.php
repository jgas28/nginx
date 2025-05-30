@extends('layouts.app')

@section('title', 'FCZCNYX')

@section('content')

<div class="border bg-white p-2">
    <div class="row">
        <!-- Display MTM (if it exists) -->
        <div class="col-md-4 form-group">
            <label><strong>MTM Number:</strong> {{$deliveryLineItems->first()->mtm}}</label>
        </div>
        <div class="col-md-4 form-group">
            <label><strong>Company:</strong> {{$deliveryLineItems->first()->company->company_name}}</label>
        </div>
        <div class="col-md-4 form-group">
            <label><strong>Delivery Type:</strong> {{$deliveryLineItems->first()->delivery_type}}</label>
        </div>
    </div>

    <!-- Loop through deliveryLineItems and display site_name, delivery_number, and delivery_address -->
    @foreach($deliveryLineItems as $deliveryLineItem)
        <div class="row">
            <div class="col-md-2 form-group">
                <label><strong>Site Name:</strong> {{ str_replace(['"'], '', $deliveryLineItem->site_name) }}</label>
            </div>
        
            <div class="col-md-3 form-group">
                <label><strong>Delivery Number:</strong> {{ str_replace(['"'], '', $deliveryLineItem->delivery_number) }}</label>
            </div>
            <div class="col-md-3 form-group">
                <label><strong>Delivery Address:</strong> {{ str_replace(['"'], '', $deliveryLineItem->delivery_address) }}</label>
            </div>
            <div class="col-md-2 form-group">
                <label><strong>Accessorial Type:</strong> {{ str_replace(['"'], '', $deliveryLineItem->accessorial_type_name) }}</label>
            </div>
            <div class="col-md-2 form-group">
                <label><strong>Accessorial Rate:</strong> {{ str_replace(['"'], '', $deliveryLineItem->accessorial_rate) }}</label>
            </div>
        </div>
    @endforeach
</div>

<form action="{{ route('cashVoucherRequests.store_accessorial') }}" method="POST">
    @csrf
    <input type="hidden" name="mtm" id="mtm" class="form-control" value="{{$deliveryLineItems->first()->mtm}}">
    <input type="hidden" name="cvr_type" id="cvr_type" class="form-control" value="accesorial">
    <div class="border bg-white p-2 my-2">
        <div class="row">
            <div class="col-md-3 form-group">
                <label for="cvr_number">CVR Number</label>
                <input type="text" name="cvr_number" id="cvr_number" class="form-control" required>
            </div>
            <div class="col-md-3 form-group">
                <label for="amount">Amount</label>
                <input type="text" name="amount" id="amount" class="form-control" required>
            </div>
            <div class="col-md-3 form-group">
                <label for="request_type">Request Type</label>
                <select name="request_type" id="request_type" class="form-control" required>
                    <option value="">Select Delivery Type</option>
                    @foreach($requestType as $requestTypes)
                        <option value="{{ $requestTypes->id }}" data-type="{{ $requestTypes->request_code }}">{{ $requestTypes->request_type }}</option>
                    @endforeach
                </select>
                    @error('request_type')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
            </div>
            <div class="col-md-3 form-group">
                <label for="requestor">Requestor</label>
                <select name="requestor" id="requestor" class="form-control" required>
                    <option value="">Select Employee</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" data-type="{{ $employee->employee_code }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                    @endforeach
                </select>
                    @error('requestor')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
            </div>
        </div>

        <!-- Add fields for driver, fleet card, and helper -->
        <div class="row">
            <div class="col-md-3 form-group">
            <label for="driver">Driver</label>
                <select name="driver" id="driver" class="form-control" required>
                    <option value="">Select Employee</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" data-type="{{ $employee->employee_code }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                    @endforeach
                </select>
                    @error('driver')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
            </div>

            <div class="col-md-3 form-group">
            <label for="fleet_card">Fleet Card</label>
                <select name="fleet_card" id="fleet_card" class="form-control" required>
                    <option value="">Select Fleet Card</option>
                    @foreach($fleetCards as $fleetCard)
                        <option value="{{ $fleetCard->id }}" data-type="{{ $fleetCard->account }}">{{ $fleetCard->account_name }}-{{ $fleetCard->account_number }}</option>
                    @endforeach
                </select>
                    @error('fleet_card')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
            </div>

            <!-- Helper fields -->
            <div class="col-md-4 form-group">
                <label for="helper">Helper</label>
                <div id="helper_fields">
                    <!-- <div class="input-group mb-2 gap-4">
                        <input type="text" name="helper[]" class="form-control" placeholder="Enter helper value">
                        <button type="button" class="btn btn-danger btn-sm remove_helper">Remove</button>
                    </div> -->
                </div>
                <button type="button" id="add_helper" class="btn btn-primary btn-sm">Add Helper</button>
                <!-- <small class="form-text text-muted">Add multiple helpers by clicking "Add Helper".</small> -->
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary mt-4 float-end">Submit to Approver</button>
</form>

<script>
    // Add a new helper input field
    document.getElementById('add_helper').addEventListener('click', function() {
        const newHelperField = document.createElement('div');
        newHelperField.classList.add('input-group', 'mb-2');
        newHelperField.innerHTML = `
            <input type="text" name="helper[]" class="form-control" placeholder="Enter helper value">
            <button type="button" class="btn btn-danger btn-sm remove_helper">Remove</button>
        `;
        document.getElementById('helper_fields').appendChild(newHelperField);
    });

    // Remove a helper input field
    document.getElementById('helper_fields').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove_helper')) {
            e.target.parentElement.remove();
        }
    });
</script>

@endsection
