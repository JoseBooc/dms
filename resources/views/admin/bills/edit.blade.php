<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Bill') }}
            </h2>
            <a href="{{ route('admin.bills.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                {{ __('Back to Bills') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.bills.update', $bill) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Tenant -->
                            <div class="mb-6">
                                <label for="tenant_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Tenant') }}
                                </label>
                                <select id="tenant_id" 
                                        name="tenant_id" 
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('tenant_id') border-red-300 @enderror"
                                        required>
                                    <option value="">Select Tenant</option>
                                    @foreach(\App\Models\Tenant::all() as $tenant)
                                        <option value="{{ $tenant->id }}" {{ old('tenant_id', $bill->tenant_id) == $tenant->id ? 'selected' : '' }}>
                                            {{ $tenant->first_name }} {{ $tenant->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tenant_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Room -->
                            <div class="mb-6">
                                <label for="room_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Room') }}
                                </label>
                                <select id="room_id" 
                                        name="room_id" 
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('room_id') border-red-300 @enderror"
                                        required>
                                    <option value="">Select Room</option>
                                    @foreach(\App\Models\Room::all() as $room)
                                        <option value="{{ $room->id }}" {{ old('room_id', $bill->room_id) == $room->id ? 'selected' : '' }}>
                                            {{ $room->room_number }} - {{ $room->room_type }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('room_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Bill Type -->
                            <div class="mb-6">
                                <label for="bill_type" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Bill Type') }}
                                </label>
                                <select id="bill_type" 
                                        name="bill_type" 
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('bill_type') border-red-300 @enderror"
                                        required>
                                    <option value="">Select Bill Type</option>
                                    <option value="rent" {{ old('bill_type', $bill->bill_type) == 'rent' ? 'selected' : '' }}>Rent</option>
                                    <option value="utility" {{ old('bill_type', $bill->bill_type) == 'utility' ? 'selected' : '' }}>Utility</option>
                                    <option value="maintenance" {{ old('bill_type', $bill->bill_type) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                    <option value="other" {{ old('bill_type', $bill->bill_type) == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('bill_type')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Amount -->
                            <div class="mb-6">
                                <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Amount (₱)') }}
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">₱</span>
                                    </div>
                                    <input type="number" 
                                           id="amount" 
                                           name="amount" 
                                           value="{{ old('amount', $bill->total_amount) }}" 
                                           step="0.01"
                                           min="0"
                                           class="block w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('amount') border-red-300 @enderror"
                                           required>
                                </div>
                                @error('amount')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Due Date -->
                            <div class="mb-6">
                                <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Due Date') }}
                                </label>
                                <input type="date" 
                                       id="due_date" 
                                       name="due_date" 
                                       value="{{ old('due_date', $bill->due_date?->format('Y-m-d')) }}" 
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('due_date') border-red-300 @enderror"
                                       required>
                                @error('due_date')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="mb-6">
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Status') }}
                                </label>
                                <select id="status" 
                                        name="status" 
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('status') border-red-300 @enderror">
                                    <option value="pending" {{ old('status', $bill->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="paid" {{ old('status', $bill->status) == 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="overdue" {{ old('status', $bill->status) == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                    <option value="cancelled" {{ old('status', $bill->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                @error('status')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('Description') }}
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      rows="3"
                                      class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('description') border-red-300 @enderror">{{ old('description', $bill->description) }}</textarea>
                            @error('description')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Paid Date (if status is paid) -->
                        <div class="mb-6" id="paid_date_field" style="{{ old('status', $bill->status) === 'paid' ? '' : 'display: none;' }}">
                            <label for="paid_at" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('Paid Date & Time') }}
                            </label>
                            <input type="datetime-local" 
                                   id="paid_at" 
                                   name="paid_at" 
                                   value="{{ old('paid_at', $bill->paid_at?->format('Y-m-d\TH:i')) }}" 
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('paid_at') border-red-300 @enderror">
                            @error('paid_at')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end">
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('Update Bill') }}
                            </button>
                        </div>
                    </form>

                    <script>
                        document.getElementById('status').addEventListener('change', function() {
                            const paidDateField = document.getElementById('paid_date_field');
                            if (this.value === 'paid') {
                                paidDateField.style.display = 'block';
                                // Set current date/time if not already set
                                const paidAtInput = document.getElementById('paid_at');
                                if (!paidAtInput.value) {
                                    const now = new Date();
                                    paidAtInput.value = now.toISOString().slice(0, 16);
                                }
                            } else {
                                paidDateField.style.display = 'none';
                                document.getElementById('paid_at').value = '';
                            }
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>