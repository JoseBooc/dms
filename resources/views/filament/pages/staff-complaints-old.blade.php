<x-filament::page>
    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-500">Open</p>
                    <p class="text-2xl font-semibold text-yellow-600">{{ $this->openCount }}</p>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-500">In Progress</p>
                    <p class="text-2xl font-semibold text-blue-600">{{ $this->inProgressCount }}</p>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-500">Resolved</p>
                    <p class="text-2xl font-semibold text-green-600">{{ $this->resolvedCount }}</p>
                </div>
            </div>
        </div>

        <!-- Complaints Table -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">My Assigned Complaints ({{ $this->complaints->count() }})</h3>
            
            @if($this->complaints->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($this->complaints as $complaint)
                                <tr class="hover:bg-gray-50 cursor-pointer" 
                                    wire:click="openDetailsModal({{ $complaint->id }})">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $complaint->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $complaint->tenant->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $complaint->room->room_number ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">{{ $complaint->title }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ucfirst($complaint->category) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @php
                                            $priorityColors = [
                                                'low' => 'bg-green-100 text-green-800',
                                                'medium' => 'bg-yellow-100 text-yellow-800',
                                                'high' => 'bg-red-100 text-red-800',
                                                'urgent' => 'bg-purple-100 text-purple-800'
                                            ];
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $priorityColors[$complaint->priority] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($complaint->priority) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @php
                                            $statusColors = [
                                                'open' => 'bg-yellow-100 text-yellow-800',
                                                'in_progress' => 'bg-blue-100 text-blue-800',
                                                'resolved' => 'bg-green-100 text-green-800',
                                                'closed' => 'bg-gray-100 text-gray-800'
                                            ];
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$complaint->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $complaint->created_at->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        @if($complaint->status === 'open')
                                            <button 
                                                wire:click.stop="updateStatus({{ $complaint->id }}, 'in_progress')"
                                                class="text-blue-600 hover:text-blue-900 text-xs bg-blue-100 px-2 py-1 rounded"
                                            >
                                                Start Work
                                            </button>
                                        @elseif($complaint->status === 'in_progress')
                                            <button 
                                                wire:click.stop="updateStatus({{ $complaint->id }}, 'resolved')"
                                                class="text-green-600 hover:text-green-900 text-xs bg-green-100 px-2 py-1 rounded"
                                            >
                                                Resolve
                                            </button>
                                        @else
                                            <span class="text-gray-400 text-xs">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($complaint->status !== 'resolved' && $complaint->status !== 'closed')
                                    <tr class="bg-gray-50">
                                        <td colspan="9" class="px-6 py-2">
                                            <div class="text-sm">
                                                <strong>Description:</strong> {{ $complaint->description }}
                                                @if($complaint->resolution)
                                                    <br><strong>Resolution:</strong> {{ $complaint->resolution }}
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No complaints assigned to you.</p>
            @endif
        </div>
    </div>

    <!-- Complaint Details Modal -->
    @if($showModal && $selectedComplaint)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                    Complaint #{{ $selectedComplaint->id }}
                                </h3>
                                
                                <!-- Complaint Details -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Tenant</p>
                                        <p class="text-sm text-gray-900">{{ $selectedComplaint->tenant->name ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Room</p>
                                        <p class="text-sm text-gray-900">{{ $selectedComplaint->room->number ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Category</p>
                                        <p class="text-sm text-gray-900">{{ ucfirst($selectedComplaint->category) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Priority</p>
                                        <p class="text-sm text-gray-900">{{ ucfirst($selectedComplaint->priority) }}</p>
                                    </div>
                                </div>
                                
                                <div class="mb-6">
                                    <p class="text-sm font-medium text-gray-500">Title</p>
                                    <p class="text-sm text-gray-900">{{ $selectedComplaint->title }}</p>
                                </div>

                                <div class="mb-6">
                                    <p class="text-sm font-medium text-gray-500">Description</p>
                                    <p class="text-sm text-gray-900">{{ $selectedComplaint->description }}</p>
                                </div>

                                @if($selectedComplaint->resolution)
                                    <div class="mb-6">
                                        <p class="text-sm font-medium text-gray-500">Resolution</p>
                                        <p class="text-sm text-gray-900">{{ $selectedComplaint->resolution }}</p>
                                    </div>
                                @endif

                                <!-- Status and Action buttons for in-progress complaints -->
                                @if($selectedComplaint->status === 'open')
                                    <div class="border-t pt-6">
                                        <button 
                                            wire:click="updateStatus({{ $selectedComplaint->id }}, 'in_progress')"
                                            class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-3">
                                            Start Investigation
                                        </button>
                                    </div>
                                @elseif($selectedComplaint->status === 'in_progress')
                                    <div class="border-t pt-6">
                                        <button 
                                            wire:click="updateStatus({{ $selectedComplaint->id }}, 'resolved')"
                                            class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mb-3">
                                            Mark as Resolved
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="closeModal" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-gray-600 text-base font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament::page>
