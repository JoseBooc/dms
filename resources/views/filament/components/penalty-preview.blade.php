<div class="space-y-4">
    <div class="bg-gray-50 rounded-lg p-4">
        <h4 class="font-semibold text-gray-900 mb-3">Bill Information</h4>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-500">Bill ID:</span>
                <span class="font-medium">{{ $bill->id }}</span>
            </div>
            <div>
                <span class="text-gray-500">Tenant:</span>
                <span class="font-medium">{{ $bill->tenant->name ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="text-gray-500">Room:</span>
                <span class="font-medium">{{ $bill->room->room_number ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="text-gray-500">Bill Type:</span>
                <span class="font-medium capitalize">{{ $bill->bill_type }}</span>
            </div>
            <div>
                <span class="text-gray-500">Original Amount:</span>
                <span class="font-medium">₱{{ number_format($bill->total_amount, 2) }}</span>
            </div>
            <div>
                <span class="text-gray-500">Due Date:</span>
                <span class="font-medium">{{ $bill->due_date->format('Y-m-d') }}</span>
            </div>
            <div>
                <span class="text-gray-500">Days Overdue:</span>
                <span class="font-medium text-red-600">{{ $bill->getDaysOverdue() }} days</span>
            </div>
            <div>
                <span class="text-gray-500">Amount Paid:</span>
                <span class="font-medium">₱{{ number_format($bill->amount_paid, 2) }}</span>
            </div>
        </div>
    </div>

    @if($preview['eligible'])
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <h4 class="font-semibold text-yellow-800 mb-3">Penalty Calculation</h4>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-yellow-700">Penalty Type:</span>
                    <span class="font-medium capitalize">{{ $preview['penalty_type'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-yellow-700">Penalty Rate:</span>
                    <span class="font-medium">
                        @if($preview['penalty_type'] === 'percentage')
                            {{ ($preview['penalty_rate'] * 100) }}%
                        @else
                            ₱{{ number_format($preview['penalty_rate'], 2) }}
                        @endif
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-yellow-700">Grace Period:</span>
                    <span class="font-medium">{{ $preview['grace_period'] }} days</span>
                </div>
                @if($preview['max_penalty'])
                    <div class="flex justify-between">
                        <span class="text-yellow-700">Maximum Penalty:</span>
                        <span class="font-medium">₱{{ number_format($preview['max_penalty'], 2) }}</span>
                    </div>
                @endif
                <hr class="border-yellow-200">
                <div class="flex justify-between">
                    <span class="text-yellow-700">Current Penalty:</span>
                    <span class="font-medium">₱{{ number_format($preview['current_penalty'], 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-yellow-700">New Penalty:</span>
                    <span class="font-bold text-yellow-900">₱{{ number_format($preview['new_penalty'], 2) }}</span>
                </div>
                @if($preview['increase'] > 0)
                    <div class="flex justify-between">
                        <span class="text-yellow-700">Increase:</span>
                        <span class="font-bold text-red-600">+₱{{ number_format($preview['increase'], 2) }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="font-semibold text-blue-800 mb-3">Total Summary</h4>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-blue-700">Original Amount:</span>
                    <span class="font-medium">₱{{ number_format($bill->total_amount, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-blue-700">New Penalty:</span>
                    <span class="font-medium">₱{{ number_format($preview['new_penalty'], 2) }}</span>
                </div>
                <hr class="border-blue-200">
                <div class="flex justify-between text-lg">
                    <span class="text-blue-800 font-semibold">Total Amount Due:</span>
                    <span class="font-bold text-blue-900">₱{{ number_format($bill->total_amount + $preview['new_penalty'], 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-blue-700">Amount Paid:</span>
                    <span class="font-medium">₱{{ number_format($bill->amount_paid, 2) }}</span>
                </div>
                <div class="flex justify-between text-lg">
                    <span class="text-blue-800 font-semibold">Remaining Balance:</span>
                    <span class="font-bold text-red-600">₱{{ number_format($bill->total_amount + $preview['new_penalty'] - $bill->amount_paid, 2) }}</span>
                </div>
            </div>
        </div>
    @else
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <h4 class="font-semibold text-red-800 mb-2">Not Eligible for Penalty</h4>
            <p class="text-red-700 text-sm">{{ $preview['reason'] }}</p>
        </div>
    @endif

    @if($bill->penalty_waived)
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <h4 class="font-semibold text-green-800 mb-2">Penalty Waived</h4>
            <div class="text-sm text-green-700 space-y-1">
                <p><strong>Reason:</strong> {{ $bill->penalty_waiver_reason }}</p>
                <p><strong>Waived by:</strong> {{ $bill->penaltyWaivedBy->name ?? 'N/A' }}</p>
                <p><strong>Waived on:</strong> {{ $bill->penalty_waived_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</p>
            </div>
        </div>
    @endif
</div>