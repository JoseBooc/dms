<x-filament::page>
    <div class="space-y-6">
        <!-- Welcome Section -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg p-6" style="background: linear-gradient(to right, #3b82f6, #8b5cf6);">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold mb-2" style="color: white !important;">Welcome, {{ auth()->user()->name }}!</h2>
                    <p class="text-blue-100" style="color: #dbeafe !important;">Here's a comprehensive summary of your tenancy with us</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-blue-100" style="color: #dbeafe !important;">Member Since</p>
                    <p class="text-xl font-semibold" style="color: white !important;">{{ $tenancyStats['member_since'] ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Quick Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Current Stay -->
            <x-filament::card>
                <div class="text-center">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full bg-blue-100">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500">Current Stay</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $tenancyStats['current_stay_months'] ?? 0 }}</p>
                    <p class="text-xs text-gray-400">months</p>
                </div>
            </x-filament::card>

            <!-- Total Paid -->
            <x-filament::card>
                <div class="text-center">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full bg-green-100">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500">Total Paid</p>
                    <p class="text-2xl font-semibold text-gray-900">₱{{ number_format($financialStats['total_paid'] ?? 0, 0) }}</p>
                    <p class="text-xs text-gray-400">{{ $financialStats['paid_bills'] ?? 0 }} bills</p>
                </div>
            </x-filament::card>

            <!-- Current Room -->
            <x-filament::card>
                <div class="text-center">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full bg-purple-100">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2v2zm14 0V5a2 2 0 00-2-2H9a2 2 0 00-2 2v2h10z"></path>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500">Current Room</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $tenancyStats['current_room'] ?? 'None' }}</p>
                    <p class="text-xs text-gray-400">{{ $tenancyStats['current_status'] ?? 'No assignment' }}</p>
                </div>
            </x-filament::card>

            <!-- Payment Rate -->
            <x-filament::card>
                <div class="text-center">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full bg-yellow-100">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500">Payment Rate</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $financialStats['payment_rate'] ?? 0 }}%</p>
                    <p class="text-xs text-gray-400">on time payments</p>
                </div>
            </x-filament::card>
        </div>

        <!-- Detailed Analytics -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Tenancy Information -->
            <x-filament::card>
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2v2zm14 0V5a2 2 0 00-2-2H9a2 2 0 00-2 2v2h10z"></path>
                        </svg>
                        Tenancy Overview
                    </h3>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Total Room Assignments</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $tenancyStats['total_assignments'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Current Stay Duration</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $tenancyStats['current_stay_days'] ?? 0 }} days</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Total Time as Tenant</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $tenancyStats['total_stay_months'] ?? 0 }} months</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Move-in Date</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $tenancyStats['start_date'] ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </x-filament::card>

            <!-- Financial Summary -->
            <x-filament::card>
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                        Financial Summary
                    </h3>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Total Billed</span>
                            <span class="text-sm font-semibold text-gray-900">₱{{ number_format($financialStats['total_billed'] ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Total Paid</span>
                            <span class="text-sm font-semibold text-green-600">₱{{ number_format($financialStats['total_paid'] ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Current Month</span>
                            <span class="text-sm font-semibold text-gray-900">₱{{ number_format($financialStats['current_month_amount'] ?? 0, 2) }}</span>
                        </div>
                        @if(($financialStats['overdue_amount'] ?? 0) > 0)
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Overdue Amount</span>
                            <span class="text-sm font-semibold text-red-600">₱{{ number_format($financialStats['overdue_amount'], 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between items-center py-2">
                            <span class="text-sm font-medium text-gray-600">Total Bills</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $financialStats['total_bills'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </x-filament::card>
        </div>

        <!-- Activity Statistics -->
        <x-filament::card>
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Activity & Service History
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Maintenance Requests -->
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-medium text-blue-900">Maintenance Requests</h4>
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <div class="flex justify-between text-sm">
                                <span class="text-blue-700">Total Requests</span>
                                <span class="font-semibold text-blue-900">{{ $activityStats['total_maintenance_requests'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-blue-700">Completed</span>
                                <span class="font-semibold text-green-700">{{ $activityStats['completed_maintenance'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-blue-700">Pending</span>
                                <span class="font-semibold text-yellow-700">{{ $activityStats['pending_maintenance'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Complaints -->
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-medium text-yellow-900">Complaints</h4>
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.864-.833-2.634 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <div class="flex justify-between text-sm">
                                <span class="text-yellow-700">Total Complaints</span>
                                <span class="font-semibold text-yellow-900">{{ $activityStats['total_complaints'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-yellow-700">Resolved</span>
                                <span class="font-semibold text-green-700">{{ $activityStats['resolved_complaints'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-yellow-700">Pending</span>
                                <span class="font-semibold text-red-700">{{ $activityStats['pending_complaints'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Resolution Time -->
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-medium text-green-900">Service Quality</h4>
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-900">{{ $activityStats['avg_resolution_days'] ?? 0 }}</div>
                            <div class="text-sm text-green-700">avg days to resolve</div>
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::card>

        <!-- Room History -->
        @if(!empty($tenancyHistory))
        <x-filament::card>
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    Room Assignment History
                </h3>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($tenancyHistory as $history)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    Room {{ $history['room_number'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $history['start_date'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $history['end_date'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $history['duration_months'] }} months
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $history['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($history['status']) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </x-filament::card>
        @endif

        <!-- Recent Activity Timeline -->
        @if(!empty($recentActivity))
        <x-filament::card>
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white
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
                                            <p class="text-sm text-gray-900 font-medium">{{ $activity['title'] }}</p>
                                            <p class="text-sm text-gray-500">{{ $activity['description'] }}</p>
                                        </div>
                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                            <span>{{ $activity['date'] }}</span>
                                            <div class="mt-1">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                    {{ $activity['color'] === 'success' ? 'bg-green-100 text-green-800' : 
                                                       ($activity['color'] === 'warning' ? 'bg-yellow-100 text-yellow-800' : 
                                                       ($activity['color'] === 'danger' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
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
        </x-filament::card>
        @endif
    </div>
</x-filament::page>