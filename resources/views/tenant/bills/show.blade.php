@extends('layouts.tenant')

@section('content')
<div class="space-y-6">
    <!-- Back Button -->
    <div class="flex items-center space-x-2">
        <a href="{{ route('tenant.bills') }}" class="text-blue-600 hover:text-blue-500 text-sm font-medium">
            ← Back to Bills
        </a>
    </div>

    <!-- Bill Header -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Bill Details</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Bill Date: {{ $bill->bill_date ? $bill->bill_date->format('F j, Y') : 'N/A' }}
                    </p>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-lg font-medium 
                        @if($bill->status === 'paid') bg-green-100 text-green-800
                        @elseif($bill->status === 'partially_paid') bg-yellow-100 text-yellow-800
                        @else bg-red-100 text-red-800
                        @endif">
                        {{ ucfirst(str_replace('_', ' ', $bill->status)) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bill Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Amount</dt>
                            <dd class="text-lg font-medium text-gray-900">₱{{ number_format($bill->total_amount, 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Due Date</dt>
                            <dd class="text-lg font-medium text-gray-900">
                                {{ $bill->due_date ? $bill->due_date->format('M j, Y') : 'N/A' }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Amount Paid</dt>
                            <dd class="text-lg font-medium text-gray-900">₱{{ number_format($bill->amount_paid ?? 0, 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bill Breakdown -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Bill Breakdown</h3>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @if($bill->rent_amount)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Monthly Rent</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">₱{{ number_format($bill->rent_amount, 2) }}</td>
                        </tr>
                        @endif
                        
                        @if($bill->utilities_amount)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Utilities</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">₱{{ number_format($bill->utilities_amount, 2) }}</td>
                        </tr>
                        @endif
                        
                        @if($bill->other_charges)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Other Charges</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">₱{{ number_format($bill->other_charges, 2) }}</td>
                        </tr>
                        @endif
                        
                        <tr class="bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Total Amount</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">₱{{ number_format($bill->total_amount, 2) }}</td>
                        </tr>
                        
                        @if($bill->amount_paid > 0)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Amount Paid</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 text-right">-₱{{ number_format($bill->amount_paid ?? 0, 2) }}</td>
                        </tr>
                        <tr class="bg-yellow-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Balance</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600 text-right">
                                ₱{{ number_format($bill->total_amount - ($bill->amount_paid ?? 0), 2) }}
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Notes -->
    @if($bill->notes)
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Notes</h3>
            <p class="text-sm text-gray-600">{{ $bill->notes }}</p>
        </div>
    </div>
    @endif
</div>
@endsection