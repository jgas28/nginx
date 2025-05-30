@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow rounded-4">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Create Request Type</h2>

                    <form action="{{ route('cvr_request_types.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="request_code" class="form-label">Request Type Code</label>
                            <input type="text" name="request_code" id="request_code" class="form-control" placeholder="e.g. RT-001" required>
                        </div>

                        <div class="mb-3">
                            <label for="request_type" class="form-label">Request Type Name</label>
                            <input type="text" name="request_type" id="request_type" class="form-control" placeholder="e.g. Maintenance Request" required>
                        </div>

                        <div class="mb-3">
                            <label for="group_type" class="form-label">Group Type</label>
                            <select name="group_type" id="group_type" class="form-select form-control" required>
                                <option value="" disabled selected>Select Group</option>
                                <option value="Admin">Admin</option>
                                <option value="Operations">Operations</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-3">Create Request Type</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
