@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Request #{{ $maintenance_request->id }}</h2>
        <a href="{{ route('maintenance-requests.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Room</dt>
                <dd class="col-sm-9">{{ $maintenance_request->room->room_number }}</dd>

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">{{ ucfirst(str_replace('_',' ', $maintenance_request->status)) }}</dd>

                <dt class="col-sm-3">Priority</dt>
                <dd class="col-sm-9">{{ ucfirst($maintenance_request->priority) }}</dd>

                @if($maintenance_request->area)
                    <dt class="col-sm-3">Area</dt>
                    <dd class="col-sm-9">{{ $maintenance_request->area }}</dd>
                @endif

                <dt class="col-sm-3">Description</dt>
                <dd class="col-sm-9">{{ $maintenance_request->description }}</dd>
            </dl>
        </div>
    </div>

    @if($maintenance_request->photos)
        <div class="card">
            <div class="card-header">Photos</div>
            <div class="card-body">
                <div class="row">
                    @foreach($maintenance_request->photos as $photo)
                        <div class="col-6 col-md-3 mb-3">
                            <img src="{{ asset('storage/' . $photo) }}" class="img-fluid rounded">
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
