<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role',
        'name',
        'first_name',
        'middle_name', 
        'last_name',
        'email',
        'password',
        'role',
        'status',
        'gender',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($user) {
            // Automatically update the name field when individual name fields change
            if ($user->first_name || $user->last_name) {
                $user->name = trim(
                    ($user->first_name ?? '') . ' ' . 
                    ($user->middle_name ? $user->middle_name . ' ' : '') . 
                    ($user->last_name ?? '')
                );
            }
        });
        
        static::deleting(function ($user) {
            // If this user is a tenant, handle room cleanup
            if ($user->role === 'tenant' && $user->tenant) {
                $tenant = $user->tenant;
                
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
            }
        });
    }

    /**
     * Get the room assignments for the user.
     */
    public function roomAssignments()
    {
        return $this->hasMany(RoomAssignment::class, 'tenant_id');
    }

    /**
     * Get the tenant profile for the user.
     */
    public function tenant()
    {
        return $this->hasOne(Tenant::class);
    }

    /**
     * Determine if the user can access Filament admin panel.
     */
    public function canAccessFilament(): bool
    {
        return in_array($this->role, ['admin', 'staff', 'tenant']);
    }
    
    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
    
    /**
     * Check if user is staff.
     */
    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }
    
    /**
     * Check if user is tenant.
     */
    public function isTenant(): bool
    {
        return $this->role === 'tenant';
    }

    /**
     * Get complaints submitted by this user (if tenant).
     */
    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'tenant_id');
    }

    /**
     * Get complaints assigned to this user (if staff/admin).
     */
    public function assignedComplaints()
    {
        return $this->hasMany(Complaint::class, 'assigned_to');
    }
}
