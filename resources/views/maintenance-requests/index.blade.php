@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">My Maintenance Requests</h2>
        <a href="{{ route('maintenance-requests.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> New Request
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($requests->count())
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Room</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $req)
                        <tr>
                            <td>{{ $req->created_at->format('M d, Y') }}</td>
                            <td>{{ $req->room->room_number }}</td>
                            <td>{{ ucfirst($req->priority) }}</td>
                            <td>{{ ucfirst(str_replace('_',' ',$req->status)) }}</td>
                            <td class="text-end">
                                <a href="{{ route('maintenance-requests.show', $req) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end">{{ $requests->links() }}</div>
    @else
        <p class="text-center">No requests yet.</p>
    @endif
</div>
@endsection
