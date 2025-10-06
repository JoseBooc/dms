<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtilityType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unit',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // Relationships
    public function rates()
    {
        return $this->hasMany(UtilityRate::class);
    }

    public function readings()
    {
        return $this->hasMany(UtilityReading::class);
    }
}
