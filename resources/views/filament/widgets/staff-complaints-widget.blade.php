<x-filament::widget>
    <x-filament::card>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">My Assigned Complaints</h3>
                <div class="flex space-x-2">
                    @php $stats = $this->getStats(); @endphp
                    <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">
                        {{ $stats['pending'] }} Pending
                    </span>
                    <span class="px-2 py-1 text-xs bg-indigo-100 text-indigo-800 rounded-full">
                        {{ $stats['in_progress'] }} In Progress
                    </span>
                </div>
            </div>

            @php $complaints = $this->getComplaints(); @endphp

            @if($complaints->count() > 0)
                <div class="space-y-3">
                    @foreach($complaints as $complaint)
                        <div class="border rounded-lg p-3 hover:bg-gray-50 transition">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                            @if($complaint->priority === 'high') bg-red-100 text-red-800
                                            @elseif($complaint->priority === 'medium') bg-yellow-100 text-yellow-800
                                            @else bg-green-100 text-green-800
                                            @endif">
                                            {{ ucfirst($complaint->priority) }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                            @if($complaint->status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($complaint->status === 'in_progress') bg-blue-100 text-blue-800
                                            @else bg-green-100 text-green-800
                                            @endif">
                                            {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            {{ ucfirst($complaint->category) }}
                                        </span>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">{{ $complaint->title }}</p>
                                    <p class="text-xs text-gray-600 mt-1">{{ Str::limit($complaint->description, 80) }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Room {{ $complaint->room->room_number ?? 'N/A' }} • Filed {{ $complaint->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <p class="mt-2 text-sm">No complaints assigned yet</p>
                </div>
            @endif
        </div>
    </x-filament::card>
</x-filament::widget>
