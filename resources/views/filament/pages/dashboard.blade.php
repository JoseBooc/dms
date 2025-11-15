<x-filament::page>
    <div class="space-y-6">
        <!-- Welcome Section -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold text-gray-900">Welcome, {{ Auth::user()->name }}!</h2>
            <p class="text-gray-600 mt-1">Here's an overview of the dormitory management system.</p>
        </div>

        <!-- Main Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Rooms -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Rooms</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $this->totalRooms }}</p>
                    </div>
                </div>
            </div>

            <!-- Occupied Rooms -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Occupied Rooms</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $this->occupiedRooms }}</p>
                    </div>
                </div>
            </div>

            <!-- Available Rooms -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Available Rooms</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $this->availableRooms }}</p>
                    </div>
                </div>
            </div>

            <!-- Total Tenants -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Tenants</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $this->totalTenants }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Occupancy Rate -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Occupancy Rate</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $this->occupancyRate }}%</p>
                    </div>
                </div>
            </div>

            <!-- Unpaid Bills -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Unpaid Bills</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $this->unpaidBills }}</p>
                    </div>
                </div>
            </div>

            <!-- Monthly Revenue -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Monthly Revenue (Excluding Utilities)</p>
                        <p class="text-2xl font-semibold text-gray-900">₱{{ number_format($this->monthlyRevenue, 2) }}</p>
                        <p class="text-xs text-gray-400 mt-1">Rent + Penalties only</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions and System Status -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ url('/dashboard/rooms') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5 text-blue-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                        </svg>
                        <span class="text-sm font-medium">Manage Rooms</span>
                    </a>
                    
                    <a href="{{ url('/dashboard/tenants') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                        </svg>
                        <span class="text-sm font-medium">Manage Tenants</span>
                    </a>
                    
                    <a href="{{ url('/dashboard/billing') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5 text-yellow-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm font-medium">Manage Bills</span>
                    </a>
                </div>
            </div>

            <!-- System Status -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">System Status</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Pending Maintenance</span>
                        <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">{{ $this->pendingMaintenance }} items</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">System Status</span>
                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Online</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Last Updated</span>
                        <span class="text-sm text-gray-900">{{ now()->format('M d, Y g:i A') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Penalty Settings & Overdue Bills -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Current Penalty Settings -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Penalty Settings</h3>
                    @if($this->activePenaltySetting)
                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Active</span>
                    @else
                        <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Not Configured</span>
                    @endif
                </div>
                
                @if($this->activePenaltySetting)
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">Penalty Type</span>
                            <span class="text-sm font-medium text-gray-900">{{ $this->penaltyTypeDisplay }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">Penalty Rate</span>
                            <span class="text-sm font-medium text-gray-900">{{ $this->penaltyRateDisplay }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">Grace Period</span>
                            <span class="text-sm font-medium text-gray-900">{{ $this->gracePeriodDisplay }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-sm text-gray-600">Maximum Penalty</span>
                            <span class="text-sm font-medium text-gray-900">{{ $this->maxPenaltyDisplay }}</span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="{{ url('/dashboard/penalty-management') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                            Edit Settings →
                        </a>
                    </div>
                @else
                    <div class="text-center py-6">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <p class="text-sm text-gray-600 mb-3">No penalty settings configured</p>
                        <a href="{{ url('/dashboard/penalty-management') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                            Configure Now →
                        </a>
                    </div>
                @endif
            </div>

            <!-- Overdue Bills & Penalties -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Bills & Penalties</h3>
                <div class="space-y-4">
                    <div class="bg-red-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Overdue Bills (With Penalties)</p>
                                <p class="text-2xl font-semibold text-red-600">{{ $this->overdueBills }}</p>
                            </div>
                            <svg class="w-10 h-10 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            @if($this->activePenaltySetting && $this->overdueBills > 0)
                                Beyond {{ $this->gracePeriodDisplay }} grace period - penalties apply
                            @else
                                No penalties configured
                            @endif
                        </p>
                    </div>
                    
                    @if($this->activePenaltySetting && $this->overdueBillsWithinGracePeriod > 0)
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Within Grace Period</p>
                                <p class="text-2xl font-semibold text-yellow-600">{{ $this->overdueBillsWithinGracePeriod }}</p>
                            </div>
                            <svg class="w-10 h-10 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Past due but no penalties yet</p>
                    </div>
                    @endif
                    
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Penalties Collected (This Month)</p>
                                <p class="text-2xl font-semibold text-green-600">₱{{ number_format($this->totalPenaltiesCollected, 2) }}</p>
                            </div>
                            <svg class="w-10 h-10 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">From paid bills with penalties</p>
                    </div>
                    
                    <div class="pt-2">
                        <a href="{{ url('/dashboard/billing') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                            View All Bills →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament::page>