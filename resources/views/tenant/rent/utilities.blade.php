@extends('layouts.tenant')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h1 class="text-2xl font-bold text-gray-900">Utility Details</h1>
            <p class="mt-1 text-sm text-gray-600">View the latest utility readings for your room.</p>
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
        <!-- Room Information -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Room Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                    <span class="text-white font-bold">📊</span>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Active Utilities</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $utilityReadings->count() }} of {{ $utilityTypes->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Utility Readings -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Latest Utility Readings</h3>
                
                @if($utilityReadings->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($utilityTypes as $utilityType)
                            @php
                                $reading = $utilityReadings->get($utilityType->id);
                            @endphp
                            <div class="border rounded-lg p-4 {{ $reading ? 'bg-white' : 'bg-gray-50' }}">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-md font-semibold text-gray-900">{{ $utilityType->name }}</h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $reading ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $reading ? 'Active' : 'No Data' }}
                                    </span>
                                </div>

                                @if($reading)
                                    <div class="space-y-2">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">Current Reading:</span>
                                            <span class="text-lg font-bold text-gray-900">
                                                {{ number_format($reading->reading_value, 2) }} {!! $utilityType->unit !!}
                                            </span>
                                        </div>

                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">Previous Reading:</span>
                                            <span class="text-sm font-medium text-gray-700">
                                                {{ $reading->previous_reading ? number_format($reading->previous_reading, 2) : 'N/A' }} {!! $utilityType->unit !!}
                                            </span>
                                        </div>

                                        @if($reading->previous_reading)
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm text-gray-600">Consumption:</span>
                                                <span class="text-sm font-medium {{ ($reading->reading_value - $reading->previous_reading) > 0 ? 'text-red-600' : 'text-green-600' }}">
                                                    {{ number_format($reading->reading_value - $reading->previous_reading, 2) }} {!! $utilityType->unit !!}
                                                </span>
                                            </div>
                                        @endif

                                        <div class="pt-2 border-t border-gray-200">
                                            <div class="flex justify-between items-center text-xs text-gray-500">
                                                <span>Reading Date:</span>
                                                <span>{{ $reading->reading_date ? $reading->reading_date->format('M j, Y') : 'N/A' }}</span>
                                            </div>
                                            <div class="flex justify-between items-center text-xs text-gray-500 mt-1">
                                                <span>Recorded By:</span>
                                                <span>{{ $reading->recordedBy->first_name ?? $reading->recordedBy->name ?? 'System' }}</span>
                                            </div>
                                        </div>

                                        @if($reading->notes)
                                            <div class="pt-2 border-t border-gray-200">
                                                <p class="text-xs text-gray-600">
                                                    <strong>Notes:</strong> {{ $reading->notes }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                        <p class="mt-1 text-xs text-gray-500">No readings available</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No utility readings found</h3>
                        <p class="mt-1 text-sm text-gray-500">No utility readings have been recorded for your room yet.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Utility Summary -->
        @if($utilityReadings->count() > 0)
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Consumption Summary</h3>
                    <div class="overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utility Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Reading</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Previous Reading</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Consumption</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reading Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($utilityReadings as $reading)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $reading->utilityType ? $reading->utilityType->name : 'Unknown' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($reading->reading_value, 2) }} {!! $reading->utilityType ? $reading->utilityType->unit : '' !!}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $reading->previous_reading ? number_format($reading->previous_reading, 2) : 'N/A' }} {!! $reading->utilityType ? $reading->utilityType->unit : '' !!}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($reading->previous_reading)
                                                <span class="{{ ($reading->reading_value - $reading->previous_reading) > 0 ? 'text-red-600' : 'text-green-600' }}">
                                                    {{ number_format($reading->reading_value - $reading->previous_reading, 2) }} {!! $reading->utilityType->unit !!}
                                                </span>
                                            @else
                                                <span class="text-gray-400">N/A</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $reading->reading_date ? $reading->reading_date->format('M j, Y') : 'N/A' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Quick Actions -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Quick Actions</h3>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('tenant.rent.details') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        View Rent Details
                    </a>
                    <a href="{{ route('tenant.bills.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        View Bills
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