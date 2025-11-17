@props([
    'action',
    'method' => 'POST',
    'roomAssignment' => null
])

<form action="{{ $action }}" method="POST">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="row">
        <!-- Room Selection -->
        <div class="col-md-6 mb-3">
            <label for="room_id" class="form-label">Room <span class="text-danger">*</span></label>
            <select name="room_id" id="room_id" class="form-select @error('room_id') is-invalid @enderror" required>
                <option value="">Select Room</option>
                @foreach($rooms as $room)
                    <option value="{{ $room->id }}" 
                            {{ (old('room_id', $roomAssignment?->room_id) == $room->id) ? 'selected' : '' }}
                            data-price="{{ $room->price }}">
                        {{ $room->room_number }} (₱{{ number_format($room->price, 2) }}/month)
                    </option>
                @endforeach
            </select>
            @error('room_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Tenant Selection -->
        <div class="col-md-6 mb-3">
            <label for="tenant_id" class="form-label">Tenant <span class="text-danger">*</span></label>
            <select name="tenant_id" id="tenant_id" class="form-select @error('tenant_id') is-invalid @enderror" required>
                <option value="">Select Tenant</option>
                @foreach($tenants as $tenant)
                    <option value="{{ $tenant->id }}" 
                            {{ (old('tenant_id', $roomAssignment?->tenant_id) == $tenant->id) ? 'selected' : '' }}>
                        {{ $tenant->full_name }}
                    </option>
                @endforeach
            </select>
            @error('tenant_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="row">
        <!-- Start Date -->
        <div class="col-md-6 mb-3">
            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
            <input type="date" name="start_date" id="start_date" 
                   class="form-control @error('start_date') is-invalid @enderror"
                   value="{{ old('start_date', $roomAssignment?->start_date?->format('Y-m-d')) }}" 
                   required>
            @error('start_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- End Date -->
        <div class="col-md-6 mb-3">
            <label for="end_date" class="form-label">End Date (Optional)</label>
            <input type="date" name="end_date" id="end_date" 
                   class="form-control @error('end_date') is-invalid @enderror"
                   value="{{ old('end_date', $roomAssignment?->end_date?->format('Y-m-d')) }}">
            @error('end_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="row">
        <!-- Monthly Rent -->
        <div class="col-md-6 mb-3">
            <label for="monthly_rent" class="form-label">Monthly Rent <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text">₱</span>
                <input type="number" name="monthly_rent" id="monthly_rent" 
                       class="form-control @error('monthly_rent') is-invalid @enderror"
                       value="{{ old('monthly_rent', $roomAssignment?->monthly_rent) }}" 
                       step="0.01" required>
            </div>
            @error('monthly_rent')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Status -->
        @if($roomAssignment)
            <div class="col-md-6 mb-3">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                    @foreach(['pending', 'active', 'inactive', 'terminated'] as $status)
                        <option value="{{ $status }}" 
                                {{ (old('status', $roomAssignment->status) == $status) ? 'selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        @else
            <input type="hidden" name="status" value="pending">
        @endif
    </div>

    <!-- Notes -->
    <div class="mb-3">
        <label for="notes" class="form-label">Notes</label>
        <textarea name="notes" id="notes" rows="3" 
                  class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $roomAssignment?->notes) }}</textarea>
        @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    @isset($slot)
        {{ $slot }}
    @endisset
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-fill monthly rent based on selected room
    const roomSelect = document.getElementById('room_id');
    const rentInput = document.getElementById('monthly_rent');
    
    roomSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            rentInput.value = selectedOption.dataset.price;
        }
    });

    // Check room availability when dates change
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const form = document.querySelector('form');

    async function checkAvailability() {
        const roomId = roomSelect.value;
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        
        if (!roomId || !startDate) return;

        try {
            const response = await fetch(`/room-assignments/check-availability?${new URLSearchParams({
                room_id: roomId,
                start_date: startDate,
                end_date: endDate || startDate,
                exclude_assignment: '{{ $roomAssignment?->id }}'
            })}`);
            
            const data = await response.json();
            
            if (!data.available) {
                alert('This room is not available for the selected dates.');
            }
        } catch (error) {
            console.error('Error checking availability:', error);
        }
    }

    [startDateInput, endDateInput].forEach(input => {
        if (input) input.addEventListener('change', checkAvailability);
    });
    roomSelect.addEventListener('change', checkAvailability);
});
</script>
@endpush
