@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Welcome, {{ auth()->user()->name }}!</h2>

    @if($current_assignment)
        <!-- Current Room Information -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Your Room</div>
                    <div class="card-body">
                        <h5 class="card-title">Room {{ $current_assignment->room->room_number }}</h5>
                        <p class="card-text">
                            <strong>Floor:</strong> {{ $current_assignment->room->floor }}<br>
                            <strong>Type:</strong> {{ $current_assignment->room->type }}<br>
                            <strong>Monthly Rent:</strong> ₱{{ number_format($current_assignment->monthly_rent, 2) }}<br>
                            <strong>Start Date:</strong> {{ $current_assignment->start_date->format('M d, Y') }}<br>
                            @if($current_assignment->end_date)
                                <strong>End Date:</strong> {{ $current_assignment->end_date->format('M d, Y') }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Quick Actions</div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="#" class="btn btn-primary">
                                <i class="fas fa-file-invoice"></i> View Bills
                            </a>
                            <a href="{{ route('maintenance-requests.create') }}" class="btn btn-success">
                                <i class="fas fa-tools"></i> Submit Maintenance Request
                            </a>
                            <a href="#" class="btn btn-info">
                                <i class="fas fa-history"></i> Payment History
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info">
            You are not currently assigned to any room.
        </div>
    @endif

    <!-- Maintenance Requests -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">Recent Maintenance Requests</div>
                <div class="card-body">
                    @if($maintenance_requests->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Issue</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($maintenance_requests as $request)
                                        <tr>
                                            <td>{{ $request->created_at->format('M d, Y') }}</td>
                                            <td>{{ Str::limit($request->description, 50) }}</td>
                                            <td>
                                                <span class="badge bg-{{ 
                                                    $request->status === 'completed' ? 'success' : 
                                                    ($request->status === 'in_progress' ? 'warning' : 'secondary')
                                                }}">
                                                    {{ ucfirst($request->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('maintenance-requests.show', $request) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center">No maintenance requests found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
