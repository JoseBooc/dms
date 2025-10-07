<x-filament::page>
    <div class="space-y-6">
        @if($this->getViewData()['currentAssignment'])
            <!-- Current Assignment Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Room Assignment</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Room Number</p>
                        <p class="text-xl font-bold text-gray-900">{{ $this->getViewData()['currentAssignment']->room->room_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Monthly Rent</p>
                        <p class="text-xl font-bold text-green-600">₱{{ number_format($this->getViewData()['monthlyRate'], 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Start Date</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $this->getViewData()['currentAssignment']->start_date ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Latest Bill -->
            @if($this->getViewData()['latestBill'])
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Latest Bill</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Bill Date</p>
                            <p class="text-lg font-semibold">{{ $this->getViewData()['latestBill']->bill_date ? $this->getViewData()['latestBill']->bill_date->format('M j, Y') : 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Amount</p>
                            <p class="text-lg font-semibold">₱{{ number_format($this->getViewData()['latestBill']->total_amount, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Amount Paid</p>
                            <p class="text-lg font-semibold text-green-600">₱{{ number_format($this->getViewData()['latestBill']->amount_paid ?? 0, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                @if($this->getViewData()['latestBill']->status === 'paid') bg-green-100 text-green-800
                                @elseif($this->getViewData()['latestBill']->status === 'partially_paid') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $this->getViewData()['latestBill']->status)) }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Next Due Bill -->
            @if($this->getViewData()['nextDueBill'])
                <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-red-900 mb-4">Next Due Payment</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-red-600">Due Date</p>
                            <p class="text-lg font-semibold text-red-900">{{ $this->getViewData()['nextDueBill']->due_date ? $this->getViewData()['nextDueBill']->due_date->format('M j, Y') : 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-red-600">Amount Due</p>
                            <p class="text-lg font-semibold text-red-900">₱{{ number_format($this->getViewData()['nextDueBill']->total_amount - ($this->getViewData()['nextDueBill']->amount_paid ?? 0), 2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-red-600">Days Left</p>
                            <p class="text-lg font-semibold text-red-900">
                                @if($this->getViewData()['nextDueBill']->due_date)
                                    {{ \Carbon\Carbon::now()->diffInDays($this->getViewData()['nextDueBill']->due_date, false) }} days
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Room Details -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Room Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Room Type</p>
                        <p class="text-lg font-semibold">{{ $this->getViewData()['currentAssignment']->room->room_type ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Floor</p>
                        <p class="text-lg font-semibold">{{ $this->getViewData()['currentAssignment']->room->floor ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Capacity</p>
                        <p class="text-lg font-semibold">{{ $this->getViewData()['currentAssignment']->room->capacity ?? 'N/A' }} persons</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status</p>
                        <p class="text-lg font-semibold">{{ ucfirst($this->getViewData()['currentAssignment']->room->status ?? 'N/A') }}</p>
                    </div>
                </div>
                @if($this->getViewData()['currentAssignment']->room->description)
                    <div class="mt-4">
                        <p class="text-sm text-gray-600">Description</p>
                        <p class="text-gray-900">{{ $this->getViewData()['currentAssignment']->room->description }}</p>
                    </div>
                @endif
            </div>
        @else
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">No Room Assignment</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>You don't have an active room assignment. Please contact the administrator for assistance.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament::page>
