<x-filament::page>
    <div class="space-y-6">
        <!-- Report Configuration Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">Report Configuration</h3>
            
            <!-- Form Filters -->
            {{ $this->form }}
            
            <!-- Action Buttons - Properly Aligned -->
            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Generate Report Button - Warning Orange -->
                    <x-filament::button 
                        wire:click="generateReport" 
                        color="warning"
                        icon="heroicon-o-refresh"
                        size="md">
                        Generate Report
                    </x-filament::button>
                    
                    <!-- Export CSV Button - Success Green -->
                    <x-filament::button 
                        wire:click="exportReport('csv')" 
                        color="success"
                        icon="heroicon-o-download"
                        size="md">
                        Export CSV
                    </x-filament::button>
                    
                    <!-- Export PDF Button - Danger Red -->
                    <x-filament::button 
                        wire:click="exportReport('pdf')" 
                        color="danger"
                        icon="heroicon-o-document-download"
                        size="md">
                        Export PDF
                    </x-filament::button>
                </div>
            </div>
        </div>

        <!-- Report Content -->
        @switch($report_type)
            @case('occupancy')
                @php $reportData = $this->getOccupancyReportData(); @endphp
                <div class="space-y-6">
                    <!-- Occupancy Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total Rooms</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $reportData['summary']['total_rooms'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Occupied Rooms</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $reportData['summary']['current_occupancy'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Occupancy Rate</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $reportData['summary']['occupancy_rate'] }}%</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Avg Duration</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $reportData['summary']['avg_duration_days'] }} days</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Room Type Breakdown -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Room Type Breakdown</h3>
                        </div>
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Occupied</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Occupancy Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($reportData['room_type_breakdown'] as $type)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ ucfirst($type['type']) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $type['total'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $type['occupied'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $type['available'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $type['occupancy_rate'] }}%"></div>
                                                        </div>
                                                        <span class="text-sm text-gray-500">{{ $type['occupancy_rate'] }}%</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @break

            @case('financial')
                @php $reportData = $this->getFinancialReportData(); @endphp
                <div class="space-y-6">
                    <!-- Financial Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total Revenue</p>
                                    <p class="text-2xl font-semibold text-gray-900">₱{{ number_format($reportData['summary']['total_revenue'], 2) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Pending Revenue</p>
                                    <p class="text-2xl font-semibold text-gray-900">₱{{ number_format($reportData['summary']['pending_revenue'], 2) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Penalty Revenue</p>
                                    <p class="text-2xl font-semibold text-gray-900">₱{{ number_format($reportData['summary']['penalty_revenue'], 2) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Collection Rate</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $reportData['summary']['collection_rate'] }}%</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue by Type -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Revenue by Bill Type</h3>
                        </div>
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bill Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paid Amount</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pending Amount</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bill Count</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($reportData['revenue_by_type'] as $type)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ ucfirst($type['type']) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">₱{{ number_format($type['paid_amount'], 2) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600">₱{{ number_format($type['pending_amount'], 2) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">₱{{ number_format($type['total_amount'], 2) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $type['bill_count'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @break

            @case('maintenance')
                @php $reportData = $this->getMaintenanceReportData(); @endphp
                <div class="space-y-6">
                    <!-- Maintenance Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total Requests</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $reportData['summary']['total_requests'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Completed</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $reportData['summary']['completed_requests'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Pending</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $reportData['summary']['pending_requests'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Completion Rate</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $reportData['summary']['completion_rate'] }}%</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Avg Resolution</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $reportData['summary']['avg_resolution_hours'] }}h</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Common Issues -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Most Common Issues</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                @foreach($reportData['common_issues'] as $issue)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <span class="text-sm font-medium text-gray-900">{{ $issue['area'] }}</span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $issue['count'] }} requests
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @break

            @case('summary')
                @php $reportData = $this->getDashboardSummaryData(); @endphp
                <div class="space-y-6">
                    <!-- All-in-One Summary -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Occupancy Summary -->
                        <div class="bg-white rounded-lg shadow">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">Occupancy Overview</h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Total Rooms</span>
                                        <span class="text-sm font-medium">{{ $reportData['occupancy']['total_rooms'] }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Occupied</span>
                                        <span class="text-sm font-medium">{{ $reportData['occupancy']['current_occupancy'] }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Occupancy Rate</span>
                                        <span class="text-sm font-medium text-blue-600">{{ $reportData['occupancy']['occupancy_rate'] }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Summary -->
                        <div class="bg-white rounded-lg shadow">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">Financial Overview</h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Revenue</span>
                                        <span class="text-sm font-medium text-green-600">₱{{ number_format($reportData['financial']['total_revenue'], 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Pending</span>
                                        <span class="text-sm font-medium text-yellow-600">₱{{ number_format($reportData['financial']['pending_revenue'], 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Collection Rate</span>
                                        <span class="text-sm font-medium text-blue-600">{{ $reportData['financial']['collection_rate'] }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Maintenance Summary -->
                        <div class="bg-white rounded-lg shadow">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">Maintenance Overview</h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Total Requests</span>
                                        <span class="text-sm font-medium">{{ $reportData['maintenance']['total_requests'] }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Completed</span>
                                        <span class="text-sm font-medium text-green-600">{{ $reportData['maintenance']['completed_requests'] }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Completion Rate</span>
                                        <span class="text-sm font-medium text-blue-600">{{ $reportData['maintenance']['completion_rate'] }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @break

            @default
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Select a report type</h3>
                        <p class="mt-1 text-sm text-gray-500">Choose a report type and generate to view analytics.</p>
                    </div>
                </div>
        @endswitch
    </div>
</x-filament::page>
