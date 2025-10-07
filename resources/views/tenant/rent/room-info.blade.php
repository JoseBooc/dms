@extends('layouts.tenant')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h1 class="text-2xl font-bold text-gray-900">Room Information</h1>
            <p class="mt-1 text-sm text-gray-600">View your room details and roommate information.</p>
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
        <!-- Room Details -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Room Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-blue-500 rounded-md flex items-center justify-center">
                                    <span class="text-white font-bold text-lg">{{ substr($room->room_number, -2) }}</span>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Room Number</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $room->room_number }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-green-500 rounded-md flex items-center justify-center">
                                    <span class="text-white font-bold">🏠</span>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Room Type</p>
                                <p class="text-lg font-semibold text-gray-900">{{ ucfirst($room->room_type ?? 'Standard') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-purple-500 rounded-md flex items-center justify-center">
                                    <span class="text-white font-bold">{{ $roommates->count() + 1 }}</span>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total Occupants</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $roommates->count() + 1 }} / {{ $room->capacity ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-orange-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-orange-500 rounded-md flex items-center justify-center">
                                    <span class="text-white font-bold">₱</span>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Monthly Rate</p>
                                <p class="text-lg font-semibold text-gray-900">₱{{ number_format($room->rate ?? 0, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Room Features/Amenities -->
                @if($room->description || $room->amenities)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h4 class="text-md font-medium text-gray-900 mb-3">Room Features</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if($room->description)
                                <div>
                                    <p class="text-sm font-medium text-gray-500 mb-1">Description</p>
                                    <p class="text-sm text-gray-700">{{ $room->description }}</p>
                                </div>
                            @endif
                            @if($room->amenities)
                                <div>
                                    <p class="text-sm font-medium text-gray-500 mb-1">Amenities</p>
                                    <p class="text-sm text-gray-700">{{ $room->amenities }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Room Status -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h4 class="text-md font-medium text-gray-900 mb-3">Room Status</h4>
                    <div class="flex items-center space-x-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                            @if($room->status === 'available') bg-green-100 text-green-800
                            @elseif($room->status === 'occupied') bg-blue-100 text-blue-800
                            @elseif($room->status === 'maintenance') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst($room->status ?? 'Unknown') }}
                        </span>
                        
                        @if($currentAssignment->start_date)
                            <span class="text-sm text-gray-500">
                                Assigned since {{ $currentAssignment->start_date->format('F j, Y') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Your Information -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Your Information</h3>
                <div class="border rounded-lg p-4 bg-blue-50">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                                <span class="text-white font-bold text-lg">{{ substr(Auth::user()->first_name ?? Auth::user()->name ?? 'U', 0, 1) }}</span>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Name</p>
                                    <p class="text-md font-semibold text-gray-900">
                                        {{ Auth::user()->first_name ?? '' }} {{ Auth::user()->last_name ?? '' }}
                                        @if(!Auth::user()->first_name && !Auth::user()->last_name)
                                            {{ Auth::user()->name ?? Auth::user()->email }}
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Contact</p>
                                    <p class="text-md text-gray-900">{{ Auth::user()->phone_number ?? 'Not provided' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Assignment Status</p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ ucfirst($currentAssignment->status ?? 'Active') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Roommates Information -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Roommates</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $roommates->count() }} roommate{{ $roommates->count() !== 1 ? 's' : '' }}
                    </span>
                </div>

                @if($roommates->count() > 0)
                    <div class="space-y-4">
                        @foreach($roommates as $assignment)
                            @php
                                $roommate = $assignment->tenant;
                            @endphp
                            <div class="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-gray-500 rounded-full flex items-center justify-center">
                                            <span class="text-white font-bold">{{ substr($roommate->first_name ?? $roommate->name ?? 'U', 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                            <div>
                                                <p class="text-sm font-medium text-gray-500">Name</p>
                                                <p class="text-md font-semibold text-gray-900">
                                                    {{ $roommate->first_name ?? '' }} {{ $roommate->last_name ?? '' }}
                                                    @if(!$roommate->first_name && !$roommate->last_name)
                                                        {{ $roommate->name ?? 'Name not available' }}
                                                    @endif
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-500">Contact</p>
                                                <p class="text-sm text-gray-900">{{ $roommate->phone_number ?? 'Not provided' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-500">Email</p>
                                                <p class="text-sm text-gray-900">{{ $roommate->personal_email ?? $roommate->email ?? 'Not provided' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-500">Assigned Since</p>
                                                <p class="text-sm text-gray-900">
                                                    {{ $assignment->start_date ? $assignment->start_date->format('M j, Y') : 'N/A' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            @if($assignment->status === 'active') bg-green-100 text-green-800
                                            @else bg-yellow-100 text-yellow-800
                                            @endif">
                                            {{ ucfirst($assignment->status ?? 'Active') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No roommates</h3>
                        <p class="mt-1 text-sm text-gray-500">You currently have this room to yourself.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Room Guidelines -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Room Guidelines</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <ul class="space-y-2 text-sm text-gray-700">
                        <li class="flex items-start">
                            <svg class="flex-shrink-0 h-5 w-5 text-green-500 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Keep common areas clean and organized
                        </li>
                        <li class="flex items-start">
                            <svg class="flex-shrink-0 h-5 w-5 text-green-500 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Respect quiet hours (10 PM - 6 AM)
                        </li>
                        <li class="flex items-start">
                            <svg class="flex-shrink-0 h-5 w-5 text-green-500 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Communicate openly with roommates
                        </li>
                        <li class="flex items-start">
                            <svg class="flex-shrink-0 h-5 w-5 text-green-500 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Report maintenance issues promptly
                        </li>
                        <li class="flex items-start">
                            <svg class="flex-shrink-0 h-5 w-5 text-green-500 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Follow dormitory rules and regulations
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Quick Actions</h3>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('tenant.rent.details') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        View Rent Details
                    </a>
                    <a href="{{ route('tenant.rent.utilities') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Utility Details
                    </a>
                    <a href="{{ route('tenant.maintenance.create') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Report Issue
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection