<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_number',
        'type',
        'capacity',
        'rate',
        'status',
        'description',
        'current_occupants',
        'hidden',
        'is_hidden',
        'status_before_hidden',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_hidden' => 'boolean',
        'hidden' => 'boolean',
    ];

    /**
     * Boot method to handle status changes when hiding/unhiding
     */
    protected static function boot()
    {
        parent::boot();

        // Before updating, handle status changes for hide/unhide
        static::updating(function ($room) {
            // If is_hidden is being changed to true (hiding the room)
            if ($room->isDirty('is_hidden') && $room->is_hidden === true) {
                // Save current status before hiding
                $room->status_before_hidden = $room->getOriginal('status');
                // Set status to unavailable
                $room->status = 'unavailable';
            }
            
            // If is_hidden is being changed to false (unhiding the room)
            if ($room->isDirty('is_hidden') && $room->is_hidden === false) {
                // Restore previous status if it was saved
                if ($room->status_before_hidden) {
                    $room->status = $room->status_before_hidden;
                    $room->status_before_hidden = null;
                } else {
                    // Default to available if no previous status
                    $room->status = 'available';
                }
            }
        });
    }

    // Relationships
    public function currentTenant()
    {
        // For now, let's use a simple approach - we'll enhance this later when we have proper room assignments
        return $this->belongsTo(Tenant::class, 'current_tenant_id');
    }

    public function tenant()
    {
        // Get the currently assigned tenant through active room assignment
        return $this->hasOneThrough(
            Tenant::class,
            RoomAssignment::class,
            'room_id', // Foreign key on room_assignments table
            'id', // Foreign key on tenants table
            'id', // Local key on rooms table
            'tenant_id' // Local key on room_assignments table
        )->where('room_assignments.status', 'active');
    }

    public function assignments()
    {
        return $this->hasMany(RoomAssignment::class);
    }

    public function activeAssignment()
    {
        return $this->hasOne(RoomAssignment::class)->where('status', 'active');
    }

    public function currentAssignments()
    {
        return $this->hasMany(RoomAssignment::class)->where('status', 'active');
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    public function utilityReadings()
    {
        return $this->hasMany(UtilityReading::class);
    }

    /**
     * Update room occupancy based on current assignments
     */
    public function updateOccupancy()
    {
        $activeAssignments = $this->assignments()->where('status', 'active')->count();
        
        $this->current_occupants = $activeAssignments;
        
        // Update status based on capacity
        if ($activeAssignments >= $this->capacity) {
            $this->status = 'occupied';
        } elseif ($activeAssignments > 0) {
            $this->status = 'available'; // Partially occupied but still available
        } else {
            $this->status = 'available'; // Empty room
        }
        
        $this->saveQuietly();
    }

    /**
     * Get formatted occupancy display (e.g., "1/2", "2/2")
     */
    public function getOccupancyDisplayAttribute(): string
    {
        return $this->current_occupants . '/' . $this->capacity;
    }

    /**
     * Check if room is at full capacity
     */
    public function isFullyOccupied(): bool
    {
        return $this->current_occupants >= $this->capacity;
    }

    /**
     * Check if room has available space
     */
    public function hasAvailableSpace(): bool
    {
        return $this->current_occupants < $this->capacity;
    }

    /**
     * Check if room is hidden
     */
    public function isHidden(): bool
    {
        return $this->is_hidden === true;
    }

    /**
     * Hide this room
     */
    public function hide(): void
    {
        $this->update(['is_hidden' => true]);
    }

    /**
     * Unhide this room
     */
    public function unhide(): void
    {
        $this->update(['is_hidden' => false]);
    }
}
