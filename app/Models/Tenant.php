<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'birth_date',
        'gender',
        'nationality',
        'occupation',
        'school',
        'course',
        'civil_status',
        'phone_number',
        'alternative_phone',
        'personal_email',
        'permanent_address',
        'current_address',
        'emergency_contact_first_name',
        'emergency_contact_middle_name',
        'emergency_contact_last_name',
        'emergency_contact_relationship',
        'emergency_contact_phone',
        'emergency_contact_alternative_phone',
        'emergency_contact_address',
        'emergency_contact_email',
        'id_type',
        'id_number',
        'id_image_path',
        'remarks',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($tenant) {
            // Find all active room assignments for this tenant
            $activeAssignments = $tenant->assignments()->where('status', 'active')->get();
            
            foreach ($activeAssignments as $assignment) {
                // End the assignment
                $assignment->update([
                    'status' => 'completed',
                    'end_date' => now()->toDateString()
                ]);
                
                // Update the room occupancy
                $room = $assignment->room;
                if ($room) {
                    $room->updateOccupancy();
                }
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function currentRoom()
    {
        // Simple relationship - we'll enhance this later with proper room assignments
        return $this->belongsTo(Room::class, 'current_room_id');
    }

    public function assignments()
    {
        return $this->hasMany(RoomAssignment::class);
    }

    public function roomAssignments()
    {
        return $this->hasMany(RoomAssignment::class);
    }

    // For backward compatibility
    public function currentAssignment()
    {
        return $this->currentRoom();
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    public function emergencyContacts()
    {
        return $this->hasMany(EmergencyContact::class);
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'tenant_id', 'user_id');
    }

    // Accessors
    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
