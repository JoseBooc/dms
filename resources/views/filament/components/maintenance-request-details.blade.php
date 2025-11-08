<div class="space-y-6">
    <!-- Request Details -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Tenant</label>
            <p class="mt-1 text-sm text-gray-900">{{ $request->tenant->name ?? 'N/A' }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Room</label>
            <p class="mt-1 text-sm text-gray-900">{{ $request->room->number ?? 'N/A' }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Area</label>
            <p class="mt-1 text-sm text-gray-900">{{ $request->area }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Priority</label>
            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                @if($request->priority === 'high') bg-red-100 text-red-800
                @elseif($request->priority === 'medium') bg-yellow-100 text-yellow-800
                @else bg-green-100 text-green-800
                @endif">
                {{ ucfirst($request->priority) }}
            </span>
        </div>
    </div>
    
    <div>
        <label class="block text-sm font-medium text-gray-700">Description</label>
        <p class="mt-1 text-sm text-gray-900">{{ $request->description }}</p>
    </div>

    @if($request->photos)
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Photos</label>
            <div class="grid grid-cols-2 gap-2">
                @foreach($request->photos as $photo)
                    <img src="{{ Storage::url($photo) }}" alt="Request photo" class="w-full h-32 object-cover rounded-lg border">
                @endforeach
            </div>
        </div>
    @endif

    @if($request->status === 'completed' && $request->completion_notes)
        <div class="border-t pt-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Completion Notes</label>
            <p class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded">{{ $request->completion_notes }}</p>
        </div>
    @endif

    <div class="text-xs text-gray-500">
        Created: {{ $request->created_at->format('M d, Y g:i A') }}
        @if($request->updated_at->ne($request->created_at))
            • Updated: {{ $request->updated_at->format('M d, Y g:i A') }}
        @endif
    </div>
</div>