@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow rounded-4">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Edit Approver</h2>

                    <form action="{{ route('approvers.update', $approver) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $approver->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="site" class="form-label">Site</label>
                            <input type="text" name="site" id="site" class="form-control" value="{{ old('site', $approver->site) }}" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-3">Update Approver</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
