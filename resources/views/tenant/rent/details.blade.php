@extends('layouts.tenant')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h1 class="text-2xl font-bold text-gray-900">Rent Details</h1>
            <p class="mt-1 text-sm text-gray-600">View your current rent amount, payment status, and due dates.</p>
        </div>
    </div>

    @if(!$currentAssignment)
        <!-- No Room Assignment -->
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        You are not currently assigned to any room. Please contact the administration for room assignment.
                    </p>
                </div>
            </div>
        </div>
    @else
        <!-- Current Room & Monthly Rate -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Current Room Assignment</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                    <span class="text-white font-bold">🏠</span>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Room Number</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $currentAssignment->room->room_number }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                    <span class="text-white font-bold">₱</span>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Monthly Rate</p>
                                <p class="text-lg font-semibold text-gray-900">₱{{ number_format($monthlyRate, 2) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                    <span class="text-white font-bold">📅</span>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Assigned Since</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $currentAssignment->start_date ? $currentAssignment->start_date->format('M j, Y') : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest Bill Information -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Latest Bill</h3>
                @if($latestBill)
                    <div class="border rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Bill Date</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $latestBill->bill_date ? $latestBill->bill_date->format('M j, Y') : 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Amount</p>
                                <p class="text-lg font-semibold text-gray-900">₱{{ number_format($latestBill->total_amount, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Amount Paid</p>
                                <p class="text-lg font-semibold text-gray-900">₱{{ number_format($latestBill->amount_paid, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Status</p>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                    @if($latestBill->status === 'paid') bg-green-100 text-green-800
                                    @elseif($latestBill->status === 'partially_paid') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $latestBill->status)) }}
                                </span>
                            </div>
                        </div>

                        @if($latestBill->total_amount > $latestBill->amount_paid)
                            <div class="mt-4 p-3 bg-red-50 rounded-md">
                                <p class="text-sm text-red-800">
                                    <strong>Balance Due:</strong> ₱{{ number_format($latestBill->total_amount - $latestBill->amount_paid, 2) }}
                                </p>
                            </div>
                        @endif

                        <!-- Bill Breakdown -->
                        <div class="mt-4 border-t pt-4">
                            <h4 class="text-md font-medium text-gray-900 mb-3">Bill Breakdown</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Room Rate:</span>
                                    <span class="font-medium">₱{{ number_format($latestBill->room_rate, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Electricity:</span>
                                    <span class="font-medium">₱{{ number_format($latestBill->electricity, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Water:</span>
                                    <span class="font-medium">₱{{ number_format($latestBill->water, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Other Charges:</span>
                                    <span class="font-medium">₱{{ number_format($latestBill->other_charges, 2) }}</span>
                                </div>
                            </div>
                            @if($latestBill->other_charges_description)
                                <div class="mt-2">
                                    <p class="text-sm text-gray-600">
                                        <strong>Other Charges Details:</strong> {{ $latestBill->other_charges_description }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No bills found</h3>
                        <p class="mt-1 text-sm text-gray-500">No bills have been generated for your account yet.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Next Due Bill -->
        @if($nextDueBill && $nextDueBill->id !== $latestBill?->id)
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Next Due Payment</h3>
                    <div class="border-l-4 border-red-400 bg-red-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <h4 class="text-sm font-medium text-red-800">Payment Due</h4>
                                <div class="mt-2 text-sm text-red-700">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <p><strong>Due Date:</strong> {{ $nextDueBill->due_date ? $nextDueBill->due_date->format('M j, Y') : 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p><strong>Amount Due:</strong> ₱{{ number_format($nextDueBill->total_amount - $nextDueBill->amount_paid, 2) }}</p>
                                        </div>
                                        <div>
                                            <p><strong>Days {{ $nextDueBill->due_date && $nextDueBill->due_date->isPast() ? 'Overdue' : 'Remaining' }}:</strong> 
                                                {{ $nextDueBill->due_date ? abs($nextDueBill->due_date->diffInDays(now())) : 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Quick Actions -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Quick Actions</h3>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('tenant.bills.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        View All Bills
                    </a>
                    <a href="{{ route('tenant.rent.utilities') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        View Utility Details
                    </a>
                    <a href="{{ route('tenant.rent.room-info') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Room Information
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection