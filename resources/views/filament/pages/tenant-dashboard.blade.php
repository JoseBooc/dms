<x-filament::page>
    <div class="space-y-6">
        <!-- Welcome Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Welcome, {{ auth()->user()->name ?? 'Tenant' }}!</h2>
            <p class="text-gray-600 dark:text-gray-400">Here's an overview of your tenancy information.</p>
        </div>

        <!-- Tenant Setup Warning (if no tenant record exists) -->
        @if(($stats['total_bills'] ?? 0) === 0 && ($maintenanceStats['total_requests'] ?? 0) === 0 && !$currentAssignment)
            <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700 rounded-lg p-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-orange-800 dark:text-orange-200">Account Setup Incomplete</h3>
                        <p class="mt-2 text-sm text-orange-700 dark:text-orange-300">Your tenant profile hasn't been fully set up yet. Please contact the administrator to complete your account setup and room assignment.</p>
                    </div>
                </div>
            </div>
        @endif

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
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Current Room Assignment</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Room Number</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $currentAssignment->room->room_number ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Monthly Rent</p>
                        <p class="text-lg font-semibold text-green-600 dark:text-green-400">₱{{ number_format($currentAssignment->monthly_rent ?? 0, 2) }}</p>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">No Room Assignment</h3>
                        <p class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">You don't have an active room assignment yet. Please contact the administrator for assistance.</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Recent Bills -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Bills</h3>
                </div>
                @if($recentBills->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentBills->take(3) as $bill)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">₱{{ number_format($bill->total_amount, 2) }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $bill->bill_date ? $bill->bill_date->format('M j, Y') : 'N/A' }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full 
                                    {{ $bill->status === 'paid' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' : 
                                       ($bill->status === 'partially_paid' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' : 
                                       'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300') }}">
                                    {{ ucfirst(str_replace('_', ' ', $bill->status)) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No Bills Yet</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Your bills will appear here when they are generated.</p>
                    </div>
                @endif
            </div>

            <!-- Pending Maintenance -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Pending Maintenance</h3>
                @if($maintenanceRequests->count() > 0)
                    <div class="space-y-3">
                        @foreach($maintenanceRequests->take(3) as $request)
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded">
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $request->title ?? 'Maintenance Request' }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $request->created_at->format('M j, Y') }}</p>
                                <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 011-1h1a2 2 0 100-4H7a1 1 0 01-1-1V8a1 1 0 011-1h3a1 1 0 001-1V4z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No Maintenance Requests</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">You haven't submitted any maintenance requests yet.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Utilities Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Utilities Billing</h3>
            </div>
            
            @if($currentAssignment && $utilityReadings->count() > 0)
                <!-- Status Warning Messages -->
                @if($currentAssignment->status === 'inactive')
                    <div class="mb-4 p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg border-l-4 border-orange-400">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-orange-800 dark:text-orange-200">Assignment Status: Inactive</h3>
                                <div class="mt-2 text-sm text-orange-700 dark:text-orange-300">
                                    <p>Your room assignment is currently inactive. Please ensure to return to the dorm within the specified timeframe, otherwise your tenancy will be terminated. Any personal belongings left in the room after termination may be disposed of and the management will not be liable for it.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($currentAssignment->status === 'pending')
                    <div class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border-l-4 border-yellow-400">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Assignment Status: Pending</h3>
                                <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                    <p>Please move in to the dorm within 30 days from your start date ({{ $currentAssignment->start_date ? $currentAssignment->start_date->format('M j, Y') : 'N/A' }}) otherwise your tenancy will be terminated to make way for new tenants.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <p class="text-sm text-blue-900 dark:text-blue-200">
                        <strong>Room:</strong> {{ $currentAssignment->room->room_number ?? 'N/A' }}
                        @if($currentAssignment->status !== 'active')
                            <span class="ml-2 px-2 py-1 text-xs rounded-full 
                                @if($currentAssignment->status === 'inactive') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                @elseif($currentAssignment->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                @endif">
                                {{ ucfirst($currentAssignment->status) }}
                            </span>
                        @endif
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full table-fixed divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="w-1/6 px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bill Type</th>
                                <th class="w-1/6 px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Billing Period</th>
                                <th class="w-1/6 px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Reading</th>
                                <th class="w-1/6 px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Consumption</th>
                                <th class="w-1/6 px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                                <th class="w-1/6 px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($utilityReadings->take(5) as $reading)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if($reading->utilityType && $reading->utilityType->name === 'Water')
                                                <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                </svg>
                                            @endif
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $reading->utilityType->name ?? 'Unknown Utility' }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $reading->utilityType->unit ?? '' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $reading->reading_date->format('M Y') }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $reading->reading_date->format('M j, Y') }}</p>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ number_format($reading->current_reading, 2) }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $reading->utilityType->unit }}</p>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ number_format($reading->consumption, 2) }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $reading->utilityType->unit }}</p>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                        @php
                                            $charge = 0;
                                            if($reading->utilityType && $reading->utilityType->name === 'Water') {
                                                $charge = $reading->water_charge ?? 0;
                                            } elseif($reading->utilityType && $reading->utilityType->name === 'Electricity') {
                                                $charge = $reading->electric_charge ?? 0;
                                            } else {
                                                $charge = $reading->total_utility_charge ?? 0;
                                            }
                                        @endphp
                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format($charge, 2) }}</p>
                                        @if($reading->consumption > 0 && $charge > 0)
                                            <p class="text-xs text-gray-500 dark:text-gray-400">₱{{ number_format($charge / $reading->consumption, 2) }}/{{ $reading->utilityType->unit }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        @if($reading->bill_id)
                                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                                                Billed
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300">
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Summary Card -->
                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    @php
                        $allReadings = $utilityReadings ?? collect();
                        $totalAmount = 0;
                        $waterReadings = collect();
                        $electricityReadings = collect();
                        
                        if ($allReadings && $allReadings->count() > 0) {
                            $totalAmount = $allReadings->sum(function($reading) {
                                return $reading->total_utility_charge ?? 0;
                            });
                            
                            $waterReadings = $allReadings->filter(function($reading) { 
                                return $reading->utilityType && $reading->utilityType->name === 'Water'; 
                            });
                            
                            $electricityReadings = $allReadings->filter(function($reading) { 
                                return $reading->utilityType && $reading->utilityType->name === 'Electricity'; 
                            });
                        }
                    @endphp
                    
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                        <p class="text-xs text-blue-700 dark:text-blue-300 font-medium">Total Water</p>
                        <p class="text-lg font-bold text-blue-900 dark:text-blue-200">₱{{ number_format($waterReadings->sum(function($r) { return $r->water_charge ?? 0; }), 2) }}</p>
                        <p class="text-xs text-blue-600 dark:text-blue-400">{{ number_format($waterReadings->sum(function($r) { return $r->consumption ?? 0; }), 2) }} cu. m.</p>
                    </div>
                    
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
                        <p class="text-xs text-yellow-700 dark:text-yellow-300 font-medium">Total Electricity</p>
                        <p class="text-lg font-bold text-yellow-900 dark:text-yellow-200">₱{{ number_format($electricityReadings->sum(function($r) { return $r->electric_charge ?? 0; }), 2) }}</p>
                        <p class="text-xs text-yellow-600 dark:text-yellow-400">{{ number_format($electricityReadings->sum(function($r) { return $r->consumption ?? 0; }), 2) }} kWh</p>
                    </div>
                    
                    <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                        <p class="text-xs text-green-700 dark:text-green-300 font-medium">Total Utilities</p>
                        <p class="text-lg font-bold text-green-900 dark:text-green-200">₱{{ number_format($totalAmount, 2) }}</p>
                        <p class="text-xs text-green-600 dark:text-green-400">Last {{ $utilityReadings ? $utilityReadings->count() : 0 }} billing periods</p>
                    </div>
                </div>
            @else
                @if($currentAssignment)
                    <!-- Status Warning Messages for assignments without utility readings -->
                    @if($currentAssignment->status === 'inactive')
                        <div class="mb-4 p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg border-l-4 border-orange-400">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-orange-800 dark:text-orange-200">Assignment Status: Inactive</h3>
                                    <div class="mt-2 text-sm text-orange-700 dark:text-orange-300">
                                        <p>Your room assignment for <strong>{{ $currentAssignment->room->room_number ?? 'N/A' }}</strong> is currently inactive. Please ensure to return to the dorm within the specified timeframe, otherwise your tenancy will be terminated. Any personal belongings left in the room after termination may be disposed of and the management will not be liable for it.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($currentAssignment->status === 'pending')
                        <div class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border-l-4 border-yellow-400">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Assignment Status: Pending</h3>
                                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                        <p>You have been assigned to <strong>{{ $currentAssignment->room->room_number ?? 'N/A' }}</strong>. Please move in to the dorm within 30 days from your start date ({{ $currentAssignment->start_date ? $currentAssignment->start_date->format('M j, Y') : 'N/A' }}) otherwise your tenancy will be terminated to make way for new tenants.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No Utility Readings</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            No utility readings have been recorded for your room yet.
                        </p>
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No Utility Readings</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            You need to be assigned to a room before utility readings can be recorded.
                        </p>
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-filament::page>
