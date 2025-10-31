<x-filament::page>
    <div class="space-y-6">
        <!-- Occupancy Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-500">Total Rooms</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $this->occupancyStats['total_rooms'] }}</p>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-500">Occupied</p>
                    <p class="text-2xl font-semibold text-green-600">{{ $this->occupancyStats['occupied'] }}</p>
                    <p class="text-xs text-green-600">{{ $this->occupancyStats['occupancy_rate'] }}% occupancy</p>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-500">Vacant</p>
                    <p class="text-2xl font-semibold text-blue-600">{{ $this->occupancyStats['vacant'] }}</p>
                    <p class="text-xs text-blue-600">Available for assignment</p>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-500">Unavailable</p>
                    <p class="text-2xl font-semibold text-red-600">{{ $this->occupancyStats['unavailable'] }}</p>
                    <p class="text-xs text-red-600">Under maintenance</p>
                </div>
            </div>
        </div>

        <!-- Capacity Utilization -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Capacity Utilization</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-500">Total Capacity</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $this->capacityUtilization['total_capacity'] }}</p>
                </div>
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-500">Current Tenants</p>
                    <p class="text-xl font-semibold text-green-600">{{ $this->capacityUtilization['current_occupants'] }}</p>
                </div>
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-500">Utilization Rate</p>
                    <p class="text-xl font-semibold text-blue-600">{{ $this->capacityUtilization['utilization_percentage'] }}%</p>
                </div>
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-500">Available Spaces</p>
                    <p class="text-xl font-semibold text-yellow-600">{{ $this->capacityUtilization['available_spaces'] }}</p>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="mt-4">
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Capacity Utilization</span>
                    <span>{{ $this->capacityUtilization['utilization_percentage'] }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(100, $this->capacityUtilization['utilization_percentage']) }}%"></div>
                </div>
            </div>
        </div>

        <!-- Room Status Details -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Occupied Rooms -->
            <div class="bg-white rounded-lg shadow p-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Occupied Rooms ({{ $this->occupiedRooms->count() }})</h4>
                <div class="space-y-2 max-h-64 overflow-y-auto">
                    @foreach($this->occupiedRooms as $room)
                        <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                            <div>
                                <span class="font-medium">{{ $room->room_number }}</span>
                                <span class="text-sm text-gray-500">({{ $room->currentAssignments->count() }}/{{ $room->capacity }})</span>
                            </div>
                            <div class="text-right">
                                @if($room->currentAssignments->count() > $room->capacity)
                                    <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Overcrowded</span>
                                @else
                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Normal</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                    @if($this->occupiedRooms->count() === 0)
                        <p class="text-gray-500 text-center py-4">No occupied rooms</p>
                    @endif
                </div>
            </div>

            <!-- Vacant Rooms -->
            <div class="bg-white rounded-lg shadow p-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Vacant Rooms ({{ $this->vacantRooms->count() }})</h4>
                <div class="space-y-2 max-h-64 overflow-y-auto">
                    @foreach($this->vacantRooms as $room)
                        <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                            <div>
                                <span class="font-medium">{{ $room->room_number }}</span>
                                <span class="text-sm text-gray-500">(Capacity: {{ $room->capacity }})</span>
                            </div>
                            <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">Available</span>
                        </div>
                    @endforeach
                    @if($this->vacantRooms->count() === 0)
                        <p class="text-gray-500 text-center py-4">No vacant rooms</p>
                    @endif
                </div>
            </div>

            <!-- Unavailable Rooms -->
            <div class="bg-white rounded-lg shadow p-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Unavailable Rooms ({{ $this->unavailableRooms->count() }})</h4>
                <div class="space-y-2 max-h-64 overflow-y-auto">
                    @foreach($this->unavailableRooms as $room)
                        <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                            <div>
                                <span class="font-medium">{{ $room->room_number }}</span>
                                <span class="text-sm text-gray-500">({{ ucfirst($room->status) }})</span>
                            </div>
                            @php
                                $statusColors = [
                                    'maintenance' => 'bg-yellow-100 text-yellow-800',
                                    'cleaning' => 'bg-blue-100 text-blue-800',
                                    'renovation' => 'bg-purple-100 text-purple-800',
                                    'damaged' => 'bg-red-100 text-red-800'
                                ];
                            @endphp
                            <span class="px-2 py-1 text-xs rounded-full {{ $statusColors[$room->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($room->status) }}
                            </span>
                        </div>
                    @endforeach
                    @if($this->unavailableRooms->count() === 0)
                        <p class="text-gray-500 text-center py-4">No unavailable rooms</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Overcrowded Rooms Alert -->
        @if($this->overcrowdedRooms->count() > 0)
            <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                <h4 class="text-lg font-medium text-red-900 mb-4">⚠️ Overcrowded Rooms Alert</h4>
                <p class="text-sm text-red-700 mb-3">The following rooms exceed their capacity and require attention:</p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($this->overcrowdedRooms as $room)
                        <div class="bg-white rounded p-3 border border-red-200">
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-red-900">{{ $room->room_number }}</span>
                                <span class="text-sm text-red-700">{{ $room->currentAssignments->count() }}/{{ $room->capacity }}</span>
                            </div>
                            <p class="text-xs text-red-600 mt-1">{{ $room->currentAssignments->count() - $room->capacity }} over capacity</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-filament::page>
