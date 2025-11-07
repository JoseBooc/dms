<x-filament::page>
    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-filament::card>
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-500">Pending</p>
                    <p class="text-2xl font-semibold text-yellow-600">{{ $this->pendingCount }}</p>
                </div>
            </x-filament::card>
            
            <x-filament::card>
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-500">In Progress</p>
                    <p class="text-2xl font-semibold text-blue-600">{{ $this->inProgressCount }}</p>
                </div>
            </x-filament::card>
            
            <x-filament::card>
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-500">Completed</p>
                    <p class="text-2xl font-semibold text-green-600">{{ $this->completedCount }}</p>
                </div>
            </x-filament::card>
        </div>

        <!-- Maintenance Requests Table -->
        <x-filament::card>
            <div class="space-y-4">
                <h3 class="text-lg font-medium">My Assigned Maintenance Requests ({{ $this->maintenanceRequests->count() }})</h3>
                
                @if($this->maintenanceRequests->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tenant</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Room</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Area</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Priority</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($this->maintenanceRequests as $request)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer" 
                                        wire:click="openDetailsModal({{ $request->id }})">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">#{{ $request->id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $request->tenant->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $request->room->number ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $request->area ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100 max-w-xs truncate">{{ $request->description }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @php
                                                $priorityColors = [
                                                    'low' => 'bg-success-100 text-success-800 dark:bg-success-800 dark:text-success-100',
                                                    'medium' => 'bg-warning-100 text-warning-800 dark:bg-warning-800 dark:text-warning-100',
                                                    'high' => 'bg-danger-100 text-danger-800 dark:bg-danger-800 dark:text-danger-100',
                                                    'urgent' => 'bg-primary-100 text-primary-800 dark:bg-primary-800 dark:text-primary-100'
                                                ];
                                            @endphp
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $priorityColors[$request->priority] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100' }}">
                                                {{ ucfirst($request->priority) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @php
                                                $statusColors = [
                                                    'pending' => 'bg-warning-100 text-warning-800 dark:bg-warning-800 dark:text-warning-100',
                                                    'in_progress' => 'bg-primary-100 text-primary-800 dark:bg-primary-800 dark:text-primary-100',
                                                    'completed' => 'bg-success-100 text-success-800 dark:bg-success-800 dark:text-success-100',
                                                    'cancelled' => 'bg-danger-100 text-danger-800 dark:bg-danger-800 dark:text-danger-100'
                                                ];
                                            @endphp
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$request->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100' }}">
                                                {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $request->created_at->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <x-filament::button 
                                                size="sm" 
                                                color="secondary"
                                                wire:click.stop="openDetailsModal({{ $request->id }})"
                                            >
                                                View
                                            </x-filament::button>
                                            
                                            @if($request->status === 'pending')
                                                <x-filament::button 
                                                    size="sm" 
                                                    color="primary"
                                                    wire:click.stop="updateStatus({{ $request->id }}, 'in_progress')"
                                                >
                                                    Start Work
                                                </x-filament::button>
                                            @elseif($request->status === 'in_progress')
                                                <x-filament::button 
                                                    size="sm" 
                                                    color="warning"
                                                    class="btn-mark-action"
                                                    wire:click.stop="openCompletionModal({{ $request->id }})"
                                                >
                                                    Mark as Completed
                                                </x-filament::button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-500 dark:text-gray-400">No maintenance requests assigned to you.</p>
                    </div>
                @endif
            </div>
        </x-filament::card>
    </div>

    <!-- Request Details Modal -->
    @if($showModal && $selectedRequest)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                Maintenance Request #{{ $selectedRequest->id }}
                            </h3>
                            <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 dark:text-gray-300 dark:hover:text-gray-100">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 px-6 py-4 space-y-6">
                        <!-- Request Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tenant</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $selectedRequest->tenant->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Room</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $selectedRequest->room->number ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Area</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $selectedRequest->area ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Priority</label>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    @if($selectedRequest->priority === 'high') bg-danger-100 text-danger-800
                                    @elseif($selectedRequest->priority === 'medium') bg-warning-100 text-warning-800
                                    @else bg-success-100 text-success-800
                                    @endif">
                                    {{ ucfirst($selectedRequest->priority) }}
                                </span>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $selectedRequest->description }}</p>
                        </div>

                        @if($selectedRequest->completion_notes)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Completion Notes</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $selectedRequest->completion_notes }}</p>
                            </div>
                        @endif



                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            Created: {{ $selectedRequest->created_at->format('M d, Y g:i A') }}
                            @if($selectedRequest->updated_at->ne($selectedRequest->created_at))
                                • Updated: {{ $selectedRequest->updated_at->format('M d, Y g:i A') }}
                            @endif
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3 flex justify-end space-x-3">
                        <x-filament::button color="secondary" wire:click="closeModal">
                            Close
                        </x-filament::button>
                        
                        @if($selectedRequest->status === 'pending')
                            <x-filament::button color="primary" wire:click="updateStatus({{ $selectedRequest->id }}, 'in_progress')">
                                Start Work
                            </x-filament::button>
                        @elseif($selectedRequest->status === 'in_progress')
                            <x-filament::button 
                                color="warning"
                                class="btn-mark-action"
                                wire:click="openCompletionModal({{ $selectedRequest->id }})"
                            >
                                Mark as Completed
                            </x-filament::button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Completion Modal -->
    @if($showCompletionModal && $selectedRequest)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeCompletionModal"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                Mark Maintenance Request #{{ $selectedRequest->id }} as Completed
                            </h3>
                            <button wire:click="closeCompletionModal" class="text-gray-400 hover:text-gray-600 dark:text-gray-300 dark:hover:text-gray-100">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 px-6 py-4 space-y-6">
                        <!-- Request Summary -->
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Request Summary</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $selectedRequest->description }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                Room {{ $selectedRequest->room->number ?? 'N/A' }} - {{ $selectedRequest->area ?? 'N/A' }}
                            </p>
                        </div>
                        
                        <!-- Completion Form -->
                        <div class="mb-4">
                            {{ $this->form }}
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3 flex justify-end space-x-3">
                        <x-filament::button color="secondary" wire:click="closeCompletionModal">
                            Cancel
                        </x-filament::button>
                        <x-filament::button 
                            color="warning"
                            class="btn-mark-action"
                            wire:click="completeWork({{ $selectedRequest->id }})"
                        >
                            Mark as Completed
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament::page>