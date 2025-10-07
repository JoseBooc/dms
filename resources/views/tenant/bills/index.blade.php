@extends('layouts.tenant')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h1 class="text-2xl font-bold text-gray-900">My Bills</h1>
            <p class="mt-1 text-sm text-gray-600">View and track your billing history and payment status.</p>
        </div>
    </div>

    <!-- Bills List -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            @if($bills->count() > 0)
                <div class="space-y-4">
                    @foreach($bills as $bill)
                        <div class="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Bill Date</p>
                                            <p class="text-md font-semibold text-gray-900">
                                                {{ $bill->bill_date ? $bill->bill_date->format('M j, Y') : 'N/A' }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Total Amount</p>
                                            <p class="text-md font-semibold text-gray-900">₱{{ number_format($bill->total_amount, 2) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Due Date</p>
                                            <p class="text-md text-gray-900">
                                                {{ $bill->due_date ? $bill->due_date->format('M j, Y') : 'N/A' }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Status</p>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                                @if($bill->status === 'paid') bg-green-100 text-green-800
                                                @elseif($bill->status === 'partially_paid') bg-yellow-100 text-yellow-800
                                                @else bg-red-100 text-red-800
                                                @endif">
                                                {{ ucfirst(str_replace('_', ' ', $bill->status)) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <a href="{{ route('tenant.bills.show', $bill) }}" class="text-blue-600 hover:text-blue-500 text-sm font-medium">
                                        View Details →
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $bills->links() }}
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
</div>
@endsection