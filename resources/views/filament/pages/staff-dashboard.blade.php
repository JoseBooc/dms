<x-filament::page>
    <div class="space-y-6">
        <!-- Welcome Section -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold text-gray-900">Welcome, {{ Auth::user()->name }}!</h2>
            <p class="text-gray-600 mt-1">Here's an overview of your assignments and the dormitory system.</p>
        </div>

        <!-- Assignment Overview Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- My Maintenance Requests -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">My Maintenance Tasks</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $this->maintenanceStats['total'] }}</p>
                        <p class="text-xs text-blue-600">{{ $this->maintenanceStats['pending'] }} pending</p>
                    </div>
                </div>
            </div>

            <!-- My Complaints -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">My Complaints</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $this->complaintStats['total'] }}</p>
                        <p class="text-xs text-yellow-600">{{ $this->complaintStats['open'] }} open</p>
                    </div>
                </div>
            </div>

            <!-- Urgent Tasks -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Urgent Tasks</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $this->urgentTasks['total'] }}</p>
                        <p class="text-xs text-red-600">High priority items</p>
                    </div>
                </div>
            </div>

            <!-- Total Rooms -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Rooms</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $this->roomStats['total_rooms'] }}</p>
                        <p class="text-xs text-green-600">{{ $this->roomStats['occupied_rooms'] }} occupied</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Stats Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Maintenance Status -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Maintenance Tasks Status</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Pending</span>
                        <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">{{ $this->maintenanceStats['pending'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">In Progress</span>
                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">{{ $this->maintenanceStats['in_progress'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Completed</span>
                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">{{ $this->maintenanceStats['completed'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Complaint Status -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Complaints Status</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Open</span>
                        <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">{{ $this->complaintStats['open'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">In Progress</span>
                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">{{ $this->complaintStats['in_progress'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Resolved</span>
                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">{{ $this->complaintStats['resolved'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Room Overview -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Room Overview</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Total Rooms</span>
                        <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">{{ $this->roomStats['total_rooms'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Occupied</span>
                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">{{ $this->roomStats['occupied_rooms'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Available</span>
                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">{{ $this->roomStats['available_rooms'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Total Tenants</span>
                        <span class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded-full">{{ $this->roomStats['total_tenants'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions and Recent Items -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ url('/dashboard/my-maintenance-requests/') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5 text-blue-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm font-medium">Manage Maintenance Requests</span>
                    </a>
                    
                    <a href="{{ url('/dashboard/my-complaints/') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5 text-yellow-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm font-medium">Manage Complaints</span>
                    </a>
                    
                    <a href="{{ url('/dashboard/room-occupancy-reports/') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                        </svg>
                        <span class="text-sm font-medium">View Room Occupancy Reports</span>
                    </a>
                </div>
            </div>

            <!-- Recent Assignments -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Assignments</h3>
                <div class="space-y-3">
                    @if($this->recentMaintenance->count() > 0 || $this->recentComplaints->count() > 0)
                        @foreach($this->recentMaintenance->take(3) as $maintenance)
                            <div class="flex items-center justify-between p-2 bg-blue-50 rounded">
                                <div>
                                    <p class="text-sm font-medium">Maintenance #{{ $maintenance->id }}</p>
                                    <p class="text-xs text-gray-600">{{ Str::limit($maintenance->description, 40) }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                    {{ ucfirst($maintenance->status) }}
                                </span>
                            </div>
                        @endforeach
                        
                        @foreach($this->recentComplaints->take(2) as $complaint)
                            <div class="flex items-center justify-between p-2 bg-yellow-50 rounded">
                                <div>
                                    <p class="text-sm font-medium">Complaint #{{ $complaint->id }}</p>
                                    <p class="text-xs text-gray-600">{{ Str::limit($complaint->title, 40) }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">
                                    {{ ucfirst($complaint->status) }}
                                </span>
                            </div>
                        @endforeach
                    @else
                        <p class="text-gray-500 text-center py-4">No recent assignments</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">System Status</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">System Status</span>
                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Online</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Last Updated</span>
                    <span class="text-sm text-gray-900">{{ now()->format('M d, Y g:i A') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Your Role</span>
                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">Staff Member</span>
                </div>
            </div>
        </div>
    </div>
</x-filament::page>
