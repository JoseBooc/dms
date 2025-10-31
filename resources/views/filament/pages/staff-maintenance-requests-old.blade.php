<x-filament::page>
    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-500">Pending</p>
                    <p class="text-2xl font-semibold text-yellow-600">{{ $this->pendingCount }}</p>
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
                    <p class="text-sm font-medium text-gray-500">Completed</p>
                    <p class="text-2xl font-semibold text-green-600">{{ $this->completedCount }}</p>
                </div>
            </div>
        </div>

        <!-- Maintenance Requests Table -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">My Assigned Maintenance Requests ({{ $this->maintenanceRequests->count() }})</h3>
            
            @if($this->maintenanceRequests->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Area</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($this->maintenanceRequests as $request)
                                <tr class="hover:bg-gray-50 cursor-pointer" 
                                    wire:click="openDetailsModal({{ $request->id }})">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $request->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->tenant->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->room->number ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->area ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">{{ $request->description }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @php
                                            $priorityColors = [
                                                'low' => 'bg-green-100 text-green-800',
                                                'medium' => 'bg-yellow-100 text-yellow-800',
                                                'high' => 'bg-red-100 text-red-800',
                                                'urgent' => 'bg-purple-100 text-purple-800'
                                            ];
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $priorityColors[$request->priority] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($request->priority) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @php
                                            $statusColors = [
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'in_progress' => 'bg-blue-100 text-blue-800',
                                                'completed' => 'bg-green-100 text-green-800',
                                                'cancelled' => 'bg-red-100 text-red-800'
                                            ];
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$request->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->created_at->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        @if($request->status === 'pending')
                                            <button 
                                                wire:click.stop="updateStatus({{ $request->id }}, 'in_progress')"
                                                class="text-blue-600 hover:text-blue-900 text-xs bg-blue-100 px-2 py-1 rounded"
                                            >
                                                Start Work
                                            </button>
                                        @elseif($request->status === 'in_progress')
                                            <button 
                                                wire:click.stop="openDetailsModal({{ $request->id }})"
                                                class="text-green-600 hover:text-green-900 text-xs bg-green-100 px-2 py-1 rounded"
                                            >
                                                Complete
                                            </button>
                                        @else
                                            <span class="text-gray-400 text-xs">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No maintenance requests assigned to you.</p>
            @endif
        </div>
    </div>

    <!-- Details/Completion Modal -->
    @if($showModal && $selectedRequest)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                    Maintenance Request #{{ $selectedRequest->id }}
                                </h3>
                                
                                <!-- Request Details -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Tenant</p>
                                        <p class="text-sm text-gray-900">{{ $selectedRequest->tenant->name ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Room</p>
                                        <p class="text-sm text-gray-900">{{ $selectedRequest->room->number ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Area</p>
                                        <p class="text-sm text-gray-900">{{ $selectedRequest->area }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Priority</p>
                                        <p class="text-sm text-gray-900">{{ ucfirst($selectedRequest->priority) }}</p>
                                    </div>
                                </div>
                                
                                <div class="mb-6">
                                    <p class="text-sm font-medium text-gray-500">Description</p>
                                    <p class="text-sm text-gray-900">{{ $selectedRequest->description }}</p>
                                </div>

                                @if($selectedRequest->photos)
                                    <div class="mb-6">
                                        <p class="text-sm font-medium text-gray-500 mb-2">Photos</p>
                                        <div class="grid grid-cols-2 gap-2">
                                            @foreach($selectedRequest->photos as $photo)
                                                <img src="{{ Storage::url($photo) }}" alt="Request photo" class="w-full h-32 object-cover rounded">
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if($selectedRequest->status === 'in_progress')
                                    <!-- Completion Form -->
                                    <div class="border-t pt-6">
                                        <h4 class="text-md font-medium text-gray-900 mb-4">Mark as Complete</h4>
                                        
                                        <form wire:submit.prevent="completeWithProof">
                                            {{ $this->form }}
                                            
                                            <div class="mt-4 flex justify-end space-x-3">
                                                <button type="button" wire:click="closeModal" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                                    Cancel
                                                </button>
                                                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                                    Complete Work
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @endif

                                @if($selectedRequest->status === 'completed' && $selectedRequest->completion_proof)
                                    <!-- Show completion proof -->
                                    <div class="border-t pt-6">
                                        <h4 class="text-md font-medium text-gray-900 mb-4">Completion Proof</h4>
                                        
                                        @if($selectedRequest->completion_notes)
                                            <div class="mb-4">
                                                <p class="text-sm font-medium text-gray-500">Notes</p>
                                                <p class="text-sm text-gray-900">{{ $selectedRequest->completion_notes }}</p>
                                            </div>
                                        @endif
                                        
                                        <div class="grid grid-cols-2 gap-2">
                                            @foreach($selectedRequest->completion_proof as $photo)
                                                <img src="{{ Storage::url($photo) }}" alt="Completion proof" class="w-full h-32 object-cover rounded">
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    @if($selectedRequest->status !== 'in_progress')
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" wire:click="closeModal" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-gray-600 text-base font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Close
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</x-filament::page>