@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow rounded-4">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Edit Request Type</h2>

                    <form action="{{ route('cvr_request_types.update', $cvr_request_type) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="request_code" class="form-label">Request Type Code</label>
                            <input type="text" name="request_code" id="request_code" class="form-control @error('request_code') is-invalid @enderror" value="{{ old('request_code', $cvr_request_type->request_code) }}" required>
                            @error('request_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="request_type" class="form-label">Request Type Name</label>
                            <input type="text" name="request_type" id="request_type" class="form-control @error('request_type') is-invalid @enderror" value="{{ old('request_type', $cvr_request_type->request_type) }}" required>
                            @error('request_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="group_type" class="form-label">Group</label>
                            <select name="group_type" id="group_type" class="form-select form-control @error('group_type') is-invalid @enderror" required>
                                <option value="" disabled {{ old('group_type', $cvr_request_type->group_type) ? '' : 'selected' }}>Select Group</option>
                                <option value="Admin" {{ old('group_type', $cvr_request_type->group_type) == 'Admin' ? 'selected' : '' }}>Admin</option>
                                <option value="Operations" {{ old('group_type', $cvr_request_type->group_type) == 'Operations' ? 'selected' : '' }}>Operations</option>
                            </select>
                            @error('group_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-3">Update Request Type</button>

                        <a href="{{ route('cvr_request_types.index') }}" class="btn btn-outline-secondary w-100 mt-2">Back to List</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
