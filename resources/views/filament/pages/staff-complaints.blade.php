<x-filament::page>
    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-filament::card>
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-500">Pending</p>
                    <p class="text-2xl font-semibold text-blue-600">{{ $this->openCount }}</p>
                </div>
            </x-filament::card>
            
            <x-filament::card>
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-500">Investigating</p>
                    <p class="text-2xl font-semibold text-yellow-600">{{ $this->inProgressCount }}</p>
                </div>
            </x-filament::card>
            
            <x-filament::card>
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-500">Resolved</p>
                    <p class="text-2xl font-semibold text-green-600">{{ $this->resolvedCount }}</p>
                </div>
            </x-filament::card>
        </div>

        <!-- Complaints Table -->
        <x-filament::card>
            <div class="space-y-4">
                <h3 class="text-lg font-medium">My Assigned Complaints ({{ $this->complaints->count() }})</h3>
                
                @if($this->complaints->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tenant</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Room</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Priority</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($this->complaints as $complaint)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer" 
                                        wire:click="openDetailsModal({{ $complaint->id }})">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">#{{ $complaint->id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $complaint->tenant->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $complaint->room->number ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $complaint->title }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($complaint->category) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @php
                                                $priorityColors = [
                                                    'low' => 'bg-success-100 text-success-800 dark:bg-success-800 dark:text-success-100',
                                                    'medium' => 'bg-warning-100 text-warning-800 dark:bg-warning-800 dark:text-warning-100',
                                                    'high' => 'bg-danger-100 text-danger-800 dark:bg-danger-800 dark:text-danger-100',
                                                    'urgent' => 'bg-primary-100 text-primary-800 dark:bg-primary-800 dark:text-primary-100'
                                                ];
                                            @endphp
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $priorityColors[$complaint->priority] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100' }}">
                                                {{ ucfirst($complaint->priority) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @php
                                                $statusColors = [
                                                    'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100',
                                                    'investigating' => 'bg-warning-100 text-warning-800 dark:bg-warning-800 dark:text-warning-100',
                                                    'resolved' => 'bg-success-100 text-success-800 dark:bg-success-800 dark:text-success-100',
                                                    'completed' => 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100',
                                                    'closed' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100'
                                                ];
                                            @endphp
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$complaint->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100' }}">
                                                {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $complaint->created_at->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <x-filament::button 
                                                size="sm" 
                                                color="secondary"
                                                wire:click.stop="openDetailsModal({{ $complaint->id }})"
                                            >
                                                View
                                            </x-filament::button>
                                            
                                            @if($complaint->status === 'pending')
                                                <x-filament::button 
                                                    size="sm" 
                                                    color="primary"
                                                    wire:click.stop="updateStatus({{ $complaint->id }}, 'investigating')"
                                                >
                                                    Start Investigation
                                                </x-filament::button>
                                            @elseif($complaint->status === 'investigating')
                                                <x-filament::button 
                                                    size="sm" 
                                                    color="success"
                                                    wire:click.stop="updateStatus({{ $complaint->id }}, 'resolved')"
                                                >
                                                    Resolve
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
                        <p class="text-gray-500 dark:text-gray-400">No complaints assigned to you.</p>
                    </div>
                @endif
            </div>
        </x-filament::card>
    </div>

    <!-- Complaint Details Modal -->
    @if($showModal && $selectedComplaint)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                Complaint #{{ $selectedComplaint->id }}
                            </h3>
                            <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 dark:text-gray-300 dark:hover:text-gray-100">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 px-6 py-4 space-y-6">
                        <!-- Complaint Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tenant</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $selectedComplaint->tenant->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Room</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $selectedComplaint->room->number ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($selectedComplaint->category) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Priority</label>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    @if($selectedComplaint->priority === 'high') bg-danger-100 text-danger-800
                                    @elseif($selectedComplaint->priority === 'medium') bg-warning-100 text-warning-800
                                    @else bg-success-100 text-success-800
                                    @endif">
                                    {{ ucfirst($selectedComplaint->priority) }}
                                </span>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $selectedComplaint->title }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $selectedComplaint->description }}</p>
                        </div>

                        @if($selectedComplaint->resolution)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Resolution</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $selectedComplaint->resolution }}</p>
                            </div>
                        @endif

                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            Created: {{ $selectedComplaint->created_at->format('M d, Y g:i A') }}
                            @if($selectedComplaint->updated_at->ne($selectedComplaint->created_at))
                                • Updated: {{ $selectedComplaint->updated_at->format('M d, Y g:i A') }}
                            @endif
                            @if($selectedComplaint->resolved_at)
                                • Resolved: {{ $selectedComplaint->resolved_at->format('M d, Y g:i A') }}
                            @endif
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3 flex justify-end space-x-3">
                        <x-filament::button color="secondary" wire:click="closeModal">
                            Close
                        </x-filament::button>
                        
                        @if($selectedComplaint->status === 'pending')
                            <x-filament::button color="primary" wire:click="updateStatus({{ $selectedComplaint->id }}, 'investigating')">
                                Start Investigation
                            </x-filament::button>
                        @elseif($selectedComplaint->status === 'investigating')
                            <x-filament::button color="success" wire:click="updateStatus({{ $selectedComplaint->id }}, 'resolved')">
                                Mark Resolved
                            </x-filament::button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament::page>