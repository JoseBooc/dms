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
    ];

    protected $casts = [
        'rate' => 'decimal:2',
    ];

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
}
