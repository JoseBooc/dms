<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'room_id',
        'start_date',
        'end_date',
        'monthly_rent',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'monthly_rent' => 'decimal:2',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // When a room assignment is created, update occupancy for all non-terminated statuses
        static::created(function (RoomAssignment $assignment) {
            if (in_array($assignment->status, ['active', 'pending', 'inactive'])) {
                static::updateRoomOccupancy($assignment->room_id);
            }
        });

        // When a room assignment is updated
        static::updated(function (RoomAssignment $assignment) {
            // If status changed to/from active, update occupancy
            if ($assignment->wasChanged('status')) {
                static::updateRoomOccupancy($assignment->room_id);
            }
            
            // If room changed, update both old and new rooms
            if ($assignment->wasChanged('room_id')) {
                $originalRoomId = $assignment->getOriginal('room_id');
                if ($originalRoomId) {
                    static::updateRoomOccupancy($originalRoomId);
                }
                static::updateRoomOccupancy($assignment->room_id);
            }
        });

        // When a room assignment is deleted
        static::deleted(function (RoomAssignment $assignment) {
            static::updateRoomOccupancy($assignment->room_id);
        });
    }

    /**
     * Update room occupancy count and status
     */
    protected static function updateRoomOccupancy(int $roomId): void
    {
        $room = Room::find($roomId);
        if (!$room) return;

        // Count all non-terminated assignments for this room (active, pending, inactive)
        $occupiedCount = static::where('room_id', $roomId)
            ->whereIn('status', ['active', 'pending', 'inactive'])
            ->count();

        // Update current occupants
        $room->current_occupants = $occupiedCount;

        // Update room status based on occupancy
        if ($occupiedCount >= $room->capacity) {
            $room->status = 'occupied';
        } elseif ($occupiedCount > 0) {
            $room->status = 'available'; // Partially occupied but still available
        } else {
            $room->status = 'available'; // Empty room
        }

        $room->saveQuietly(); // Use saveQuietly to avoid triggering other events
    }

    /**
     * Update occupancy for all rooms (useful for data sync)
     */
    public static function syncAllRoomOccupancies(): void
    {
        $rooms = Room::all();
        foreach ($rooms as $room) {
            static::updateRoomOccupancy($room->id);
        }
    }

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function deposit()
    {
        return $this->hasOne(Deposit::class);
    }
}
