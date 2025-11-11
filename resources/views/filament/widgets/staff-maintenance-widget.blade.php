<x-filament::widget>
    <x-filament::card>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">My Maintenance Tasks</h3>
                <div class="flex space-x-2">
                    @php $stats = $this->getStats(); @endphp
                    <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">
                        {{ $stats['pending'] }} Pending
                    </span>
                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                        {{ $stats['in_progress'] }} In Progress
                    </span>
                </div>
            </div>

            @php $requests = $this->getMaintenanceRequests(); @endphp

            @if($requests->count() > 0)
                <div class="space-y-3">
                    @foreach($requests as $request)
                        <div class="border rounded-lg p-3 hover:bg-gray-50 transition">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                            @if($request->priority === 'high') bg-red-100 text-red-800
                                            @elseif($request->priority === 'medium') bg-yellow-100 text-yellow-800
                                            @else bg-green-100 text-green-800
                                            @endif">
                                            {{ ucfirst($request->priority) }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                            @if($request->status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($request->status === 'in_progress') bg-blue-100 text-blue-800
                                            @else bg-green-100 text-green-800
                                            @endif">
                                            {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                        </span>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">
                                        Room {{ $request->room->room_number ?? 'N/A' }} - {{ $request->area }}
                                    </p>
                                    <p class="text-xs text-gray-600 mt-1">{{ Str::limit($request->description, 80) }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Requested {{ $request->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m0 0h6a2 2 0 002-2V7a2 2 0 00-2-2h-2m0 0V3a2 2 0 00-2-2H9a2 2 0 00-2 2v2z"></path>
                    </svg>
                    <p class="mt-2 text-sm">No maintenance tasks assigned yet</p>
                </div>
            @endif
        </div>
    </x-filament::card>
</x-filament::widget>
