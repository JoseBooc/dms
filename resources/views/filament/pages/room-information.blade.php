<x-filament::page>
    <div class="space-y-6">
        @if($this->getViewData()['room'])
            <!-- Room Details Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Room {{ $this->getViewData()['room']->room_number }} Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Room Type</p>
                        <p class="text-lg font-semibold text-gray-900">{{ ucfirst($this->getViewData()['room']->room_type ?? 'N/A') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Floor</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $this->getViewData()['room']->floor ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Capacity</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $this->getViewData()['room']->capacity ?? 'N/A' }} persons</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Current Occupants</p>
                        <p class="text-lg font-semibold text-blue-600">{{ $this->getViewData()['roommates']->count() + 1 }} / {{ $this->getViewData()['room']->capacity ?? 0 }}</p>
                    </div>
                </div>
                
                @if($this->getViewData()['room']->description)
                    <div class="mt-6">
                        <p class="text-sm text-gray-600 mb-2">Room Description</p>
                        <p class="text-gray-900 bg-gray-50 p-3 rounded">{{ $this->getViewData()['room']->description }}</p>
                    </div>
                @endif
            </div>

            <!-- Current Assignment Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-blue-900 mb-4">My Assignment Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-blue-700">Start Date</p>
                        <p class="text-lg font-semibold text-blue-900">{{ $this->getViewData()['currentAssignment']->start_date ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-blue-700">Monthly Rent</p>
                        <p class="text-lg font-semibold text-blue-900">₱{{ number_format($this->getViewData()['currentAssignment']->monthly_rent ?? 0, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-blue-700">Status</p>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            {{ ucfirst($this->getViewData()['currentAssignment']->status ?? 'N/A') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Roommates -->
            @if($this->getViewData()['roommates']->count() > 0)
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Roommates</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($this->getViewData()['roommates'] as $roommate)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <span class="text-blue-600 font-semibold text-lg">
                                                {{ substr($roommate->tenant->first_name ?? 'U', 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $roommate->tenant->first_name ?? 'Unknown' }} {{ $roommate->tenant->last_name ?? '' }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Since {{ $roommate->start_date ? \Carbon\Carbon::parse($roommate->start_date)->format('M Y') : 'Unknown' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-gray-50 rounded-lg p-6">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No Roommates</h3>
                        <p class="mt-1 text-sm text-gray-500">You currently have no roommates in this room.</p>
                    </div>
                </div>
            @endif

            <!-- Room Guidelines -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Room Guidelines & Rules</h3>
                <div class="prose prose-sm max-w-none">
                    <ul class="space-y-2 text-gray-700">
                        <li class="flex items-start">
                            <span class="flex-shrink-0 w-1.5 h-1.5 bg-blue-600 rounded-full mt-2 mr-3"></span>
                            <span>Keep the room clean and tidy at all times</span>
                        </li>
                        <li class="flex items-start">
                            <span class="flex-shrink-0 w-1.5 h-1.5 bg-blue-600 rounded-full mt-2 mr-3"></span>
                            <span>Respect quiet hours from 10:00 PM to 6:00 AM</span>
                        </li>
                        <li class="flex items-start">
                            <span class="flex-shrink-0 w-1.5 h-1.5 bg-blue-600 rounded-full mt-2 mr-3"></span>
                            <span>No smoking or drinking alcohol in the room</span>
                        </li>
                        <li class="flex items-start">
                            <span class="flex-shrink-0 w-1.5 h-1.5 bg-blue-600 rounded-full mt-2 mr-3"></span>
                            <span>Visitors must be registered and accompanied by tenant</span>
                        </li>
                        <li class="flex items-start">
                            <span class="flex-shrink-0 w-1.5 h-1.5 bg-blue-600 rounded-full mt-2 mr-3"></span>
                            <span>Report any maintenance issues immediately</span>
                        </li>
                        <li class="flex items-start">
                            <span class="flex-shrink-0 w-1.5 h-1.5 bg-blue-600 rounded-full mt-2 mr-3"></span>
                            <span>Respect roommates' personal space and belongings</span>
                        </li>
                        <li class="flex items-start">
                            <span class="flex-shrink-0 w-1.5 h-1.5 bg-blue-600 rounded-full mt-2 mr-3"></span>
                            <span>Pay monthly rent and utilities on time</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-yellow-900 mb-2">Need Help?</h3>
                <p class="text-yellow-800 text-sm mb-3">For any room-related concerns or maintenance requests:</p>
                <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 space-y-2 sm:space-y-0">
                    <p class="text-yellow-800 text-sm">
                        <span class="font-medium">Contact Administration:</span> admin@dormitory.com
                    </p>
                    <p class="text-yellow-800 text-sm">
                        <span class="font-medium">Emergency:</span> (02) 123-4567
                    </p>
                </div>
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
