<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'room_id',
        'description',
        'area',
        'status',
        'priority',
        'photos',
        'assigned_to',
        'completion_proof',
        'completion_notes',
    ];

    protected $casts = [
        'photos' => 'array',
        'completion_proof' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class)->withDefault([
            'first_name' => 'Former',
            'last_name' => 'Tenant'
        ]);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
