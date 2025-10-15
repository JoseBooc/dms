<x-filament::page>
    <div class="space-y-6">
        @if($this->getViewData()['currentAssignment'])
            <!-- Current Room Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-blue-900 mb-2">Room {{ $this->getViewData()['currentAssignment']->room->room_number }} - Utility Information</h3>
                <p class="text-blue-700">Latest utility readings and consumption data for your room.</p>
            </div>

            <!-- Utility Types Overview -->
            @if($this->getViewData()['utilityTypes']->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($this->getViewData()['utilityTypes'] as $utilityType)
                        @php
                            $readings = $this->getViewData()['utilityReadings']->get($utilityType->id, collect());
                            $latestReading = $readings->first();
                            $previousReading = $readings->skip(1)->first();
                            $consumption = $latestReading && $previousReading ? 
                                $latestReading->reading_value - $previousReading->reading_value : 0;
                        @endphp
                        
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center mb-4">
                                <div class="p-2 rounded-full bg-green-100">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-lg font-semibold text-gray-900">{{ $utilityType->name }}</h4>
                                    <p class="text-sm text-gray-600">{{ $utilityType->unit }}</p>
                                </div>
                            </div>
                            
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm text-gray-600">Latest Reading</p>
                                    <p class="text-xl font-bold text-gray-900">
                                        {{ $latestReading ? number_format($latestReading->reading_value, 2) : 'N/A' }} {{ $utilityType->unit }}
                                    </p>
                                    @if($latestReading)
                                        <p class="text-xs text-gray-500">{{ $latestReading->reading_date->format('M j, Y') }}</p>
                                    @endif
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-600">Monthly Consumption</p>
                                    <p class="text-lg font-semibold {{ $consumption > 0 ? 'text-red-600' : 'text-gray-900' }}">
                                        {{ $consumption > 0 ? number_format($consumption, 2) : '0.00' }} {{ $utilityType->unit }}
                                    </p>
                                </div>
                                
                                @if($utilityType->rate_per_unit)
                                    <div>
                                        <p class="text-sm text-gray-600">Estimated Cost</p>
                                        <p class="text-lg font-semibold text-green-600">
                                            ₱{{ number_format($consumption * $utilityType->rate_per_unit, 2) }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Detailed Readings Table -->
            @if($this->getViewData()['utilityReadings']->count() > 0)
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Utility Readings</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utility Type</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Reading</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Consumption</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($this->getViewData()['utilityReadings']->flatten()->sortByDesc('reading_date')->take(10) as $reading)
                                    @php
                                        $previousReading = \App\Models\UtilityReading::where('room_id', $reading->room_id)
                                            ->where('utility_type_id', $reading->utility_type_id)
                                            ->where('reading_date', '<', $reading->reading_date)
                                            ->orderBy('reading_date', 'desc')
                                            ->first();
                                        $consumption = $previousReading ? 
                                            $reading->reading_value - $previousReading->reading_value : 0;
                                        $cost = $consumption * ($reading->utilityType->rate_per_unit ?? 0);
                                    @endphp
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $reading->reading_date->format('M j, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $reading->utilityType->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $reading->utilityType->unit }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                            {{ number_format($reading->reading_value, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                            <span class="{{ $consumption > 0 ? 'text-red-600' : 'text-gray-900' }}">
                                                {{ number_format($consumption, 2) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                            ₱{{ number_format($cost, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-gray-50 rounded-lg p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No Utility Readings</h3>
                    <p class="mt-1 text-sm text-gray-500">No utility readings have been recorded for your room yet.</p>
                </div>
            @endif
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
