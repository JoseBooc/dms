<x-filament::page>
    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-500">Overdue Bills</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $this->overdueBillsCount }}</p>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-500">Total Penalties</p>
                    <p class="text-2xl font-semibold text-gray-900">₱{{ number_format($this->totalPenalties, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Current Settings -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Current Penalty Settings</h3>
            
            @if($this->penaltySetting)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Type</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $this->penaltySetting->type }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Rate</label>
                        <p class="mt-1 text-sm text-gray-900">
                            @if($this->penaltySetting->type === 'percentage')
                                {{ ($this->penaltySetting->value * 100) }}%
                            @else
                                ₱{{ number_format($this->penaltySetting->value, 2) }}
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Grace Period</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $this->penaltySetting->grace_period_days }} days</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Max Penalty</label>
                        <p class="mt-1 text-sm text-gray-900">₱{{ number_format($this->penaltySetting->max_penalty_amount, 2) }}</p>
                    </div>
                </div>
            @else
                <p class="text-gray-500">No penalty settings configured.</p>
            @endif
        </div>

        <!-- Overdue Bills -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Overdue Bills ({{ $this->bills->count() }})</h3>
            
            @if($this->bills->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bill ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penalty</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($this->bills as $bill)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $bill->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $bill->tenant_name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $bill->room_number ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($bill->total_amount, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $bill->due_date }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            {{ $bill->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($bill->penalty_amount > 0)
                                            ₱{{ number_format($bill->penalty_amount, 2) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500">No overdue bills found.</p>
            @endif
        </div>
    </div>
</x-filament::page>
