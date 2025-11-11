<x-filament::page>
    <div class="space-y-6">
        <!-- Maintenance Request Details Section -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-6">Maintenance Request Details</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Tenant -->
                <div>
                    <dt class="text-sm font-medium text-gray-500 mb-1">Tenant</dt>
                    <dd class="text-sm text-gray-900">
                        @if($record->tenant)
                            {{ $record->tenant->first_name }} {{ $record->tenant->last_name }}
                        @else
                            <span class="text-gray-400">No tenant assigned</span>
                        @endif
                    </dd>
                </div>

                <!-- Room/Area -->
                <div>
                    <dt class="text-sm font-medium text-gray-500 mb-1">{{ $record->is_common_area ? 'Common Area' : 'Room' }}</dt>
                    <dd class="text-sm text-gray-900">
                        @if($record->is_common_area)
                            @php
                                $commonAreas = [
                                    'common_bathroom' => 'Common Bathroom',
                                    'common_kitchen' => 'Common Kitchen',
                                    'hallway' => 'Hallway',
                                    'lobby' => 'Lobby',
                                    'study_area' => 'Study Area',
                                    'laundry_area' => 'Laundry Area',
                                    'water_pump_room' => 'Water Pump Room',
                                    'electrical_room' => 'Electrical Room',
                                    'outdoor_area' => 'Outdoor Area',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                {{ $commonAreas[$record->room_id] ?? 'Unknown' }}
                            </span>
                        @else
                            @if($record->room)
                                {{ $record->room->room_number }}
                            @else
                                <span class="text-gray-400">No room specified</span>
                            @endif
                        @endif
                    </dd>
                </div>

                <!-- Specific Area -->
                <div>
                    <dt class="text-sm font-medium text-gray-500 mb-1">Specific Area/Item</dt>
                    <dd class="text-sm text-gray-900">{{ $record->area }}</dd>
                </div>

                <!-- Priority -->
                <div>
                    <dt class="text-sm font-medium text-gray-500 mb-1">Priority</dt>
                    <dd class="text-sm text-gray-900">
                        @php
                            $priorityColors = [
                                'low' => 'bg-gray-100 text-gray-800',
                                'medium' => 'bg-blue-100 text-blue-800',
                                'high' => 'bg-yellow-100 text-yellow-800',
                                'urgent' => 'bg-red-100 text-red-800'
                            ];
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $priorityColors[$record->priority] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($record->priority) }}
                        </span>
                    </dd>
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 mb-1">Description</dt>
                    <dd class="text-sm text-gray-900 bg-gray-50 p-3 rounded-md">{{ $record->description }}</dd>
                </div>
            </div>
        </div>

        <!-- Status & Assignment Section -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-6">Status & Assignment</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Status -->
                <div>
                    <dt class="text-sm font-medium text-gray-500 mb-1">Status</dt>
                    <dd class="text-sm text-gray-900">
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'in_progress' => 'bg-blue-100 text-blue-800',
                                'completed' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800'
                            ];
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$record->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                        </span>
                    </dd>
                </div>

                <!-- Assigned To -->
                <div>
                    <dt class="text-sm font-medium text-gray-500 mb-1">Assigned To</dt>
                    <dd class="text-sm text-gray-900">
                        @if($record->assignee)
                            {{ $record->assignee->name }}
                            <span class="text-xs text-gray-500">({{ ucfirst($record->assignee->role) }})</span>
                        @else
                            <span class="text-gray-400">Not assigned</span>
                        @endif
                    </dd>
                </div>

                <!-- Completion Notes -->
                @if($record->completion_notes)
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 mb-1">Completion Notes</dt>
                        <dd class="text-sm text-gray-900 bg-green-50 p-3 rounded-md border border-green-200">{{ $record->completion_notes }}</dd>
                    </div>
                @endif

                <!-- Cancel Reason -->
                @if($record->cancel_reason)
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 mb-1">Cancellation Reason</dt>
                        <dd class="text-sm text-gray-900 bg-red-50 p-3 rounded-md border border-red-200">{{ $record->cancel_reason }}</dd>
                    </div>
                @endif

                <!-- Completed At -->
                @if($record->completed_at)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Completed At</dt>
                        <dd class="text-sm text-gray-900">{{ $record->completed_at->format('M d, Y g:i A') }}</dd>
                    </div>
                @endif
            </div>
        </div>

        <!-- Timestamps Section -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-6">Timeline</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Created -->
                <div>
                    <dt class="text-sm font-medium text-gray-500 mb-1">Submitted</dt>
                    <dd class="text-sm text-gray-900">{{ $record->created_at->format('M d, Y g:i A') }}</dd>
                </div>

                <!-- Last Updated -->
                <div>
                    <dt class="text-sm font-medium text-gray-500 mb-1">Last Updated</dt>
                    <dd class="text-sm text-gray-900">{{ $record->updated_at->format('M d, Y g:i A') }}</dd>
                </div>

                <!-- Time Since Submission -->
                <div>
                    <dt class="text-sm font-medium text-gray-500 mb-1">Time Since Submission</dt>
                    <dd class="text-sm text-gray-900">{{ $record->created_at->diffForHumans() }}</dd>
                </div>
            </div>

            @if($record->status === 'completed' && $record->completed_at)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex items-center text-sm text-green-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium">Completed in {{ $record->created_at->diffInDays($record->completed_at) }} days</span>
                    </div>
                </div>
            @elseif($record->status === 'pending')
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex items-center text-sm text-yellow-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium">Pending for {{ $record->created_at->diffInDays() }} days</span>
                    </div>
                </div>
            @elseif($record->status === 'in_progress')
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex items-center text-sm text-blue-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium">In progress for {{ $record->created_at->diffInDays() }} days</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament::page>
