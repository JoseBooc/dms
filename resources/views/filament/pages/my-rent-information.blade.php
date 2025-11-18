<x-filament::page>
    @if($this->getViewData()['hasActiveAssignment'])
        @php $assignment = $this->getViewData()['assignment']; @endphp
        
        <!-- Status Warning Messages -->
        @if($assignment->status === 'inactive')
            <div class="mb-6 p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg border-l-4 border-orange-400">
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
        @elseif($assignment->status === 'pending')
            <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border-l-4 border-yellow-400">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Assignment Status: Pending</h3>
                        <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                            <p>Please move in to the dorm within 30 days from your start date ({{ $assignment->start_date ? $assignment->start_date->format('M j, Y') : 'N/A' }}) otherwise your tenancy will be terminated to make way for new tenants.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="space-y-6">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Room Number -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="text-center">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full bg-blue-100 dark:bg-blue-900/50">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Room Number</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $this->getViewData()['room']->room_number }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ ucfirst($this->getViewData()['room']->room_type ?? 'N/A') }}</p>
                    </div>
                </div>

                <!-- Monthly Rent -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="text-center">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full bg-green-100 dark:bg-green-900/50">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Monthly Rent</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format($this->getViewData()['monthlyRent'], 2) }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">Base rent</p>
                    </div>
                </div>

                <!-- Outstanding Balance -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="text-center">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full 
                            {{ $this->getViewData()['outstandingBalance'] > 0 ? 'bg-red-100 dark:bg-red-900/50' : 'bg-green-100 dark:bg-green-900/50' }}">
                            <svg class="w-6 h-6 {{ $this->getViewData()['outstandingBalance'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Outstanding Balance</p>
                        <p class="text-2xl font-semibold {{ $this->getViewData()['outstandingBalance'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                            ₱{{ number_format($this->getViewData()['outstandingBalance'], 2) }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">
                            {{ $this->getViewData()['outstandingBalance'] > 0 ? 'Unpaid bills' : 'All paid up!' }}
                        </p>
                    </div>
                </div>

                <!-- Due Date -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="text-center">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full bg-yellow-100 dark:bg-yellow-900/50">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Next Due Date</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            {{ $this->getViewData()['dueDate'] ? $this->getViewData()['dueDate']->format('M j') : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">
                            {{ $this->getViewData()['dueDate'] ? $this->getViewData()['dueDate']->format('Y') : '' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Room & Assignment Details -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    Room & Assignment Details
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Room Number</p>
                        <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $this->getViewData()['room']->room_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Room Type</p>
                        <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ ucfirst($this->getViewData()['room']->room_type ?? 'N/A') }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Assignment Status</p>
                        <span class="inline-flex mt-1 px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                            {{ ucfirst($this->getViewData()['assignment']->status) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Start Date</p>
                        <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $this->getViewData()['startDate'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Floor</p>
                        <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $this->getViewData()['room']->floor ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Capacity</p>
                        <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $this->getViewData()['room']->capacity ?? 'N/A' }} persons</p>
                    </div>
                </div>
            </div>

            <!-- Rent & Utility Charges -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    Current Month Charges
                </h3>

                <div class="space-y-4">
                    <!-- Monthly Rent -->
                    <div class="flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/50 mr-3">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Monthly Rent</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Base room rental fee</p>
                            </div>
                        </div>
                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format($this->getViewData()['monthlyRent'], 2) }}</p>
                    </div>

                    <!-- Electricity -->
                    <div class="flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-yellow-100 dark:bg-yellow-900/50 mr-3">
                                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Electricity</p>
                                @if(isset($this->getViewData()['electricityReading']))
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ number_format($this->getViewData()['electricityReading']->consumption, 2) }} kWh
                                    </p>
                                @else
                                    <p class="text-xs text-gray-500 dark:text-gray-400">No reading yet</p>
                                @endif
                            </div>
                        </div>
                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format($this->getViewData()['electricityCharges'], 2) }}</p>
                    </div>

                    <!-- Water -->
                    <div class="flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/50 mr-3">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Water</p>
                                @if(isset($this->getViewData()['waterReading']))
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ number_format($this->getViewData()['waterReading']->consumption, 2) }} cu. m.
                                    </p>
                                @else
                                    <p class="text-xs text-gray-500 dark:text-gray-400">No reading yet</p>
                                @endif
                            </div>
                        </div>
                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format($this->getViewData()['waterCharges'], 2) }}</p>
                    </div>

                    <!-- Total -->
                    <div class="flex justify-between items-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border-2 border-green-200 dark:border-green-700">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/50 mr-3">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-green-900 dark:text-green-200">Total Amount Due</p>
                                <p class="text-xs text-green-700 dark:text-green-300">Rent + Utilities</p>
                            </div>
                        </div>
                        <p class="text-xl font-bold text-green-900 dark:text-green-200">₱{{ number_format($this->getViewData()['totalDue'], 2) }}</p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- No Active Assignment -->
        <div class="flex items-center justify-center min-h-[400px]">
            <div class="text-center max-w-md">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800">
                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No Active Room Assignment</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    You currently have no active room assignment. Please contact the administrator.
                </p>
                <div class="flex items-center justify-center text-sm text-gray-500 dark:text-gray-400">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Need help? Contact your property manager
                </div>
            </div>
        </div>
    @endif
</x-filament::page>
