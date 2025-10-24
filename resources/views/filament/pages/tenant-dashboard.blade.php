<x-filament::page>
    <div class="space-y-6">
        <!-- Welcome Section -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Welcome, {{ auth()->user()->name }}!</h2>
            <p class="text-gray-600">Here's an overview of your tenancy information.</p>
        </div>

        <!-- Maintenance Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m0 0h6a2 2 0 002-2V7a2 2 0 00-2-2h-2m0 0V3a2 2 0 00-2-2H9a2 2 0 00-2 2v2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-blue-900">Total Requests</p>
                        <p class="text-2xl font-bold text-blue-900">{{ $maintenanceStats['total_requests'] ?? 0 }}</p>
                        <p class="text-xs text-blue-700">All maintenance requests</p>
                    </div>
                </div>
            </div>

            <div class="bg-yellow-50 rounded-lg p-6 border border-yellow-200">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-yellow-900">Pending</p>
                        <p class="text-2xl font-bold text-yellow-900">{{ $maintenanceStats['pending_requests'] ?? 0 }}</p>
                        <p class="text-xs text-yellow-700">Awaiting review</p>
                    </div>
                </div>
            </div>

            <div class="bg-purple-50 rounded-lg p-6 border border-purple-200">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 011-1h1a2 2 0 100-4H7a1 1 0 01-1-1V8a1 1 0 011-1h3a1 1 0 001-1V4z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-purple-900">In Progress</p>
                        <p class="text-2xl font-bold text-purple-900">{{ $maintenanceStats['in_progress_requests'] ?? 0 }}</p>
                        <p class="text-xs text-purple-700">Currently being worked on</p>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 rounded-lg p-6 border border-green-200">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-green-900">Completed</p>
                        <p class="text-2xl font-bold text-green-900">{{ $maintenanceStats['completed_requests'] ?? 0 }}</p>
                        <p class="text-xs text-green-700">Finished repairs</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-blue-900">Total Bills</p>
                        <p class="text-2xl font-bold text-blue-900">{{ $stats['total_bills'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-red-50 rounded-lg p-6 border border-red-200">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-red-900">Unpaid Bills</p>
                        <p class="text-2xl font-bold text-red-900">{{ $stats['unpaid_bills'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-yellow-50 rounded-lg p-6 border border-yellow-200">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 011-1h1a2 2 0 100-4H7a1 1 0 01-1-1V8a1 1 0 011-1h3a1 1 0 001-1V4z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-yellow-900">Pending Maintenance</p>
                        <p class="text-2xl font-bold text-yellow-900">{{ $stats['pending_maintenance'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Room Assignment -->
        @if($currentAssignment)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Room Assignment</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Room Number</p>
                        <p class="text-lg font-semibold">{{ $currentAssignment->room->room_number ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Monthly Rent</p>
                        <p class="text-lg font-semibold">₱{{ number_format($currentAssignment->monthly_rent ?? 0, 2) }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Recent Bills -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Bills</h3>
                    <a href="{{ url('/dashboard/tenant-bill-resources') }}" class="text-blue-600 hover:text-blue-500 text-sm font-medium">View All</a>
                </div>
                @if($recentBills->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentBills->take(3) as $bill)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                                <div>
                                    <p class="font-medium">₱{{ number_format($bill->total_amount, 2) }}</p>
                                    <p class="text-sm text-gray-600">{{ $bill->bill_date ? $bill->bill_date->format('M j, Y') : 'N/A' }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($bill->status === 'paid') bg-green-100 text-green-800
                                    @elseif($bill->status === 'partially_paid') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $bill->status)) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-600">No bills found.</p>
                @endif
            </div>

            <!-- Pending Maintenance -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Pending Maintenance</h3>
                @if($maintenanceRequests->count() > 0)
                    <div class="space-y-3">
                        @foreach($maintenanceRequests->take(3) as $request)
                            <div class="p-3 bg-gray-50 rounded">
                                <p class="font-medium">{{ $request->title ?? 'Maintenance Request' }}</p>
                                <p class="text-sm text-gray-600">{{ $request->created_at->format('M j, Y') }}</p>
                                <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-600">No pending maintenance requests.</p>
                @endif
            </div>
        </div>

        <!-- Utilities Section -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Utilities Billing</h3>
                <a href="{{ url('/dashboard/utility-details') }}" class="text-blue-600 hover:text-blue-500 text-sm font-medium">View All</a>
            </div>
            
            @if($currentAssignment && $utilityReadings->count() > 0)
                <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                    <p class="text-sm text-blue-900">
                        <strong>Room:</strong> {{ $currentAssignment->room->room_number ?? 'N/A' }}
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bill Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Billing Period</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Reading</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Consumption</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($utilityReadings->take(5) as $date => $readings)
                                @foreach($readings as $reading)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="flex items-center">
                                                @if($reading->utilityType->name === 'Water')
                                                    <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-5 h-5 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                    </svg>
                                                @endif
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ $reading->utilityType->name }}</p>
                                                    <p class="text-xs text-gray-500">{{ $reading->utilityType->unit }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <p class="text-sm text-gray-900">{{ $reading->reading_date->format('M Y') }}</p>
                                            <p class="text-xs text-gray-500">{{ $reading->reading_date->format('M j, Y') }}</p>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <p class="text-sm text-gray-900">{{ number_format($reading->current_reading, 2) }}</p>
                                            <p class="text-xs text-gray-500">{{ $reading->utilityType->unit }}</p>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <p class="text-sm font-medium text-gray-900">{{ number_format($reading->consumption, 2) }}</p>
                                            <p class="text-xs text-gray-500">{{ $reading->utilityType->unit }}</p>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <p class="text-sm font-semibold text-gray-900">₱{{ number_format($reading->price ?? 0, 2) }}</p>
                                            @if($reading->consumption > 0 && $reading->price > 0)
                                                <p class="text-xs text-gray-500">₱{{ number_format($reading->price / $reading->consumption, 2) }}/{{ $reading->utilityType->unit }}</p>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                            @if($reading->bill_id)
                                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                                    Billed
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">
                                                    Pending
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Summary Card -->
                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    @php
                        $allReadings = $utilityReadings->flatten();
                        $totalAmount = $allReadings->sum('price');
                        $waterReadings = $allReadings->where('utilityType.name', 'Water');
                        $electricityReadings = $allReadings->where('utilityType.name', 'Electricity');
                    @endphp
                    
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-xs text-blue-700 font-medium">Total Water</p>
                        <p class="text-lg font-bold text-blue-900">₱{{ number_format($waterReadings->sum('price'), 2) }}</p>
                        <p class="text-xs text-blue-600">{{ number_format($waterReadings->sum('consumption'), 2) }} cu. m.</p>
                    </div>
                    
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <p class="text-xs text-yellow-700 font-medium">Total Electricity</p>
                        <p class="text-lg font-bold text-yellow-900">₱{{ number_format($electricityReadings->sum('price'), 2) }}</p>
                        <p class="text-xs text-yellow-600">{{ number_format($electricityReadings->sum('consumption'), 2) }} kWh</p>
                    </div>
                    
                    <div class="bg-green-50 p-4 rounded-lg">
                        <p class="text-xs text-green-700 font-medium">Total Utilities</p>
                        <p class="text-lg font-bold text-green-900">₱{{ number_format($totalAmount, 2) }}</p>
                        <p class="text-xs text-green-600">Last {{ $utilityReadings->count() }} billing periods</p>
                    </div>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No Utility Readings</h3>
                    <p class="mt-1 text-sm text-gray-500">No utility readings have been recorded for your room yet.</p>
                </div>
            @endif
        </div>
    </div>
</x-filament::page>
