<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'room_id',
        'title',
        'description',
        'category',
        'priority',
        'status',
        'attachments',
        'assigned_to',
        'resolution',
        'staff_notes',
        'actions_taken',
        'resolved_at'
    ];

    protected $casts = [
        'attachments' => 'array',
        'resolved_at' => 'datetime'
    ];

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id')->withDefault([
            'first_name' => 'Former',
            'last_name' => 'Tenant'
        ]);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Scopes
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeInvestigating(Builder $query): Builder
    {
        return $query->where('status', 'investigating');
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', 'resolved');
    }

    public function scopeUrgent(Builder $query): Builder
    {
        return $query->where('priority', 'urgent');
    }

    public function scopeHighPriority(Builder $query): Builder
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }

    // Mutators
    public function setStatusAttribute($value)
    {
        $old_status = $this->attributes['status'] ?? null;
        $this->attributes['status'] = $value;
        
        // Auto-set resolved_at when status becomes resolved or closed
        if (in_array($value, ['resolved', 'closed']) && !$this->resolved_at) {
            $this->attributes['resolved_at'] = now();
        }
        
        // Clear actions_taken when status is set back to investigating from resolved/completed
        if ($value === 'investigating' && in_array($old_status, ['resolved', 'completed'])) {
            $this->attributes['actions_taken'] = null;
        }
    }
}
