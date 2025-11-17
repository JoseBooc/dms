<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Room Assignment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-md">
                            <h4 class="font-medium mb-2">Please correct the following errors:</h4>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li class="text-sm">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('room-assignments.update', $roomAssignment) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Room Selection -->
                            <div class="md:col-span-2">
                                <label for="room_id" class="block text-sm font-medium text-gray-700 mb-2">Room</label>
                                <select name="room_id" id="room_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('room_id') border-red-300 @enderror" required>
                                    <option value="">Select Room</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}" 
                                                {{ (old('room_id', $roomAssignment->room_id) == $room->id) ? 'selected' : '' }}>
                                            {{ $room->room_number }} - {{ $room->type }}
                                            (₱{{ number_format($room->rate, 2) }}/month)
                                        </option>
                                    @endforeach
                                </select>
                                @error('room_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Tenant Selection -->
                            <div class="md:col-span-2">
                                <label for="tenant_id" class="block text-sm font-medium text-gray-700 mb-2">Tenant</label>
                                <select name="tenant_id" id="tenant_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('tenant_id') border-red-300 @enderror" required>
                                    <option value="">Select Tenant</option>
                                    @foreach($tenants as $tenant)
                                        <option value="{{ $tenant->id }}"
                                                {{ (old('tenant_id', $roomAssignment->tenant_id) == $tenant->id) ? 'selected' : '' }}>
                                            {{ $tenant->full_name }} ({{ $tenant->personal_email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('tenant_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Start Date -->
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                <input type="date" name="start_date" id="start_date" 
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('start_date') border-red-300 @enderror"
                                       value="{{ old('start_date', $roomAssignment->start_date->format('Y-m-d')) }}" required>
                                @error('start_date')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- End Date -->
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date <span class="text-gray-500">(Optional)</span></label>
                                <input type="date" name="end_date" id="end_date" 
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('end_date') border-red-300 @enderror"
                                       value="{{ old('end_date', $roomAssignment->end_date?->format('Y-m-d')) }}">
                                @error('end_date')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Monthly Rent -->
                            <div>
                                <label for="monthly_rent" class="block text-sm font-medium text-gray-700 mb-2">Monthly Rent</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                        ₱
                                    </span>
                                    <input type="number" name="monthly_rent" id="monthly_rent" 
                                           class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('monthly_rent') border-red-300 @enderror"
                                           value="{{ old('monthly_rent', $roomAssignment->monthly_rent) }}" step="0.01" required>
                                </div>
                                @error('monthly_rent')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select name="status" id="status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('status') border-red-300 @enderror" required>
                                    @foreach(['pending', 'active', 'inactive', 'terminated'] as $status)
                                        <option value="{{ $status }}" 
                                                {{ (old('status', $roomAssignment->status) == $status) ? 'selected' : '' }}>
                                            {{ ucfirst($status) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div class="md:col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes <span class="text-gray-500">(Optional)</span></label>
                                <textarea name="notes" id="notes" rows="4" 
                                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('notes') border-red-300 @enderror"
                                          placeholder="Additional notes about this room assignment...">{{ old('notes', $roomAssignment->notes) }}</textarea>
                                @error('notes')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                            <a href="{{ route('room-assignments.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                                Update Assignment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
