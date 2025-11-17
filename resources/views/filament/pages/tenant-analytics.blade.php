<x-filament::page>
    <div class="space-y-6">
        <!-- Welcome Section -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 dark:from-blue-600 dark:to-purple-700 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold mb-2 !text-white">Welcome, {{ auth()->user()->name }}!</h2>
                    <p class="!text-blue-100 dark:!text-blue-200">Here's a comprehensive summary of your tenancy with us</p>
                </div>
                <div class="text-right">
                    <p class="text-sm !text-blue-100 dark:!text-blue-200">Member Since</p>
                    <p class="text-xl font-semibold !text-white">{{ $tenancyStats['member_since'] ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Quick Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Total Time -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-center">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full bg-blue-100 dark:bg-blue-900/50">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Time</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100 min-h-[3rem] flex items-center justify-center">{{ $tenancyStats['total_stay_formatted'] ?? '0 days' }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $tenancyStats['total_stay_days'] ?? 0 }} days</p>
                </div>
            </div>

            <!-- Total Paid -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-center">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full bg-green-100 dark:bg-green-900/50">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Paid</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format($financialStats['total_paid'] ?? 0, 0) }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $financialStats['paid_bills'] ?? 0 }} bills</p>
                </div>
            </div>

            <!-- Current Room -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-center">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full bg-purple-100 dark:bg-purple-900/50">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2v2zm14 0V5a2 2 0 00-2-2H9a2 2 0 00-2 2v2h10z"></path>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Room</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $tenancyStats['current_room'] ?? 'None' }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $tenancyStats['current_status'] ?? 'No assignment' }}</p>
                    @if(isset($tenancyStats['current_status']) && in_array($tenancyStats['current_status'], ['inactive', 'pending']))
                    <div class="mt-2">
                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                            @if($tenancyStats['current_status'] === 'pending')
                                bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300
                            @else
                                bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300
                            @endif
                        ">
                            {{ ucfirst($tenancyStats['current_status']) }} Assignment
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Payment Rate -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-center">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full bg-yellow-100 dark:bg-yellow-900/50">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Payment Rate</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $financialStats['payment_rate'] ?? 0 }}%</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">on time payments</p>
                </div>
            </div>
        </div>

        <!-- Detailed Analytics -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Tenancy Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2v2zm14 0V5a2 2 0 00-2-2H9a2 2 0 00-2 2v2h10z"></path>
                    </svg>
                    Tenancy Overview
                </h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Room Assignments</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $tenancyStats['total_assignments'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Time as Tenant</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $tenancyStats['total_stay_formatted'] ?? '0 days' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Move-in Date</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $tenancyStats['start_date'] ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                    Financial Summary
                </h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Billed</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format($financialStats['total_billed'] ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Paid</span>
                        <span class="text-sm font-semibold text-green-600 dark:text-green-400">₱{{ number_format($financialStats['total_paid'] ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Current Month</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format($financialStats['current_month_amount'] ?? 0, 2) }}</span>
                    </div>
                    @if(($financialStats['overdue_amount'] ?? 0) > 0)
                    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Overdue Amount</span>
                        <span class="text-sm font-semibold text-red-600 dark:text-red-400">₱{{ number_format($financialStats['overdue_amount'], 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Bills</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $financialStats['total_bills'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Statistics -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Activity & Service History
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Maintenance Requests -->
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-medium text-blue-900 dark:text-blue-200">Maintenance Requests</h4>
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <div class="flex justify-between text-sm">
                            <span class="text-blue-700 dark:text-blue-300">Total Requests</span>
                            <span class="font-semibold text-blue-900 dark:text-blue-200">{{ $activityStats['total_maintenance_requests'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-blue-700 dark:text-blue-300">Completed</span>
                            <span class="font-semibold text-green-700 dark:text-green-400">{{ $activityStats['completed_maintenance'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-blue-700 dark:text-blue-300">Pending</span>
                            <span class="font-semibold text-yellow-700 dark:text-yellow-400">{{ $activityStats['pending_maintenance'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <!-- Complaints -->
                <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-medium text-yellow-900 dark:text-yellow-200">Complaints</h4>
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.864-.833-2.634 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <div class="flex justify-between text-sm">
                            <span class="text-yellow-700 dark:text-yellow-300">Total Complaints</span>
                            <span class="font-semibold text-yellow-900 dark:text-yellow-200">{{ $activityStats['total_complaints'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-yellow-700 dark:text-yellow-300">Resolved</span>
                            <span class="font-semibold text-green-700 dark:text-green-400">{{ $activityStats['resolved_complaints'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-yellow-700 dark:text-yellow-300">Pending</span>
                            <span class="font-semibold text-red-700 dark:text-red-400">{{ $activityStats['pending_complaints'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <!-- Resolution Time -->
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-medium text-green-900 dark:text-green-200">Service Quality</h4>
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-900 dark:text-green-200">{{ $activityStats['avg_resolution_days'] ?? 0 }}</div>
                        <div class="text-sm text-green-700 dark:text-green-300">avg days to resolve</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Room History -->
        @if(!empty($tenancyHistory))
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 w-full">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                Room Assignment History
            </h3>
            
            <div class="overflow-x-auto w-full">
                <table class="w-full table-auto divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Room</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Start Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">End Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($tenancyHistory as $history)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                Room {{ $history['room_number'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $history['start_date'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $history['end_date'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $history['duration_months'] }} months
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    @if($history['status'] === 'active')
                                        bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300
                                    @elseif($history['status'] === 'pending')
                                        bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300
                                    @elseif($history['status'] === 'inactive')
                                        bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300
                                    @elseif($history['status'] === 'terminated')
                                        bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300
                                    @else
                                        bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                    @endif
                                ">
                                    {{ ucfirst($history['status']) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">No Room History</h3>
                    <p class="mt-2 text-sm text-blue-700 dark:text-blue-300">You don't have any room assignment history yet. Your assignments will appear here when you are assigned to a room.</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Recent Activity Timeline -->
        @if(!empty($recentActivity))
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Recent Activity
            </h3>
            
            <div class="flow-root">
                <ul class="-mb-8">
                    @foreach($recentActivity as $index => $activity)
                    <li>
                        <div class="relative pb-8">
                            @if($index < count($recentActivity) - 1)
                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                            @endif
                            <div class="relative flex space-x-3">
                                <div>
                                    <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-gray-800
                                        {{ $activity['color'] === 'success' ? 'bg-green-500' : 
                                           ($activity['color'] === 'warning' ? 'bg-yellow-500' : 
                                           ($activity['color'] === 'danger' ? 'bg-red-500' : 'bg-gray-500')) }}">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if($activity['type'] === 'bill')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                            @elseif($activity['type'] === 'maintenance')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.864-.833-2.634 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            @endif
                                        </svg>
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                    <div>
                                        <p class="text-sm text-gray-900 dark:text-gray-100 font-medium">{{ $activity['title'] }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $activity['description'] }}</p>
                                    </div>
                                    <div class="text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        <span>{{ $activity['date'] }}</span>
                                        <div class="mt-1">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                {{ $activity['color'] === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' : 
                                                   ($activity['color'] === 'warning' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' : 
                                                   ($activity['color'] === 'danger' ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300')) }}">
                                                {{ ucfirst($activity['status']) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @else
        <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No Recent Activity</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Your recent activities will appear here once you start using the system.</p>
            </div>
        </div>
        @endif
    </div>
</x-filament::page>