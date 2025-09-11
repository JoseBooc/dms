@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Submit Maintenance Request</h2>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('maintenance-requests.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Room</label>
                    <input type="text" class="form-control" value="{{ $assignment->room->room_number }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                        <option value="medium" selected>Medium</option>
                        <option value="low">Low</option>
                        <option value="high">High</option>
                    </select>
                    @error('priority')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Area (optional)</label>
                    <input type="text" name="area" class="form-control @error('area') is-invalid @enderror" placeholder="e.g., Bathroom, Window, Door">
                    @error('area')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Issue Description</label>
                    <textarea name="description" rows="5" class="form-control @error('description') is-invalid @enderror" required></textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Photos (optional)</label>
                    <input type="file" name="photos[]" class="form-control @error('photos.*') is-invalid @enderror" multiple accept="image/*">
                    @error('photos.*')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('maintenance-requests.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
