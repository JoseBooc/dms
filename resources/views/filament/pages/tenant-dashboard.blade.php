<x-filament::page>
    <div class="space-y-6">
        <!-- Welcome Section -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Welcome, {{ auth()->user()->name }}!</h2>
            <p class="text-gray-600">Here's an overview of your tenancy information.</p>
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
                        <p class="text-2xl font-bold text-blue-900">{{ $this->getViewData()['stats']['total_bills'] ?? 0 }}</p>
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
                        <p class="text-2xl font-bold text-red-900">{{ $this->getViewData()['stats']['unpaid_bills'] ?? 0 }}</p>
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
                        <p class="text-2xl font-bold text-yellow-900">{{ $this->getViewData()['stats']['pending_maintenance'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Room Assignment -->
        @if($this->getViewData()['currentAssignment'])
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Room Assignment</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Room Number</p>
                        <p class="text-lg font-semibold">{{ $this->getViewData()['currentAssignment']->room->room_number ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Monthly Rent</p>
                        <p class="text-lg font-semibold">₱{{ number_format($this->getViewData()['currentAssignment']->monthly_rent ?? 0, 2) }}</p>
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
                @if($this->getViewData()['recentBills']->count() > 0)
                    <div class="space-y-3">
                        @foreach($this->getViewData()['recentBills']->take(3) as $bill)
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
                @if($this->getViewData()['maintenanceRequests']->count() > 0)
                    <div class="space-y-3">
                        @foreach($this->getViewData()['maintenanceRequests']->take(3) as $request)
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
    </div>
</x-filament::page>
