<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtilityReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'tenant_id',
        'utility_type_id',
        'current_reading',
        'previous_reading',
        'consumption',
        'price',
        'reading_date',
        'recorded_by',
        'notes',
        'bill_id',
    ];

    protected $casts = [
        'current_reading' => 'decimal:2',
        'previous_reading' => 'decimal:2',
        'consumption' => 'decimal:2',
        'price' => 'decimal:2',
        'reading_date' => 'date',
    ];

    protected $appends = [
        'billing_period',
        'total_amount',
        'billing_status',
    ];

    /**
     * Boot the model
     */
    protected static function booted(): void
    {
        static::creating(function (UtilityReading $reading) {
            // Auto-set the user who recorded this reading
            if (!$reading->recorded_by) {
                $reading->recorded_by = auth()->id();
            }
            
            // Auto-get previous reading for same tenant and utility type
            if (!$reading->previous_reading) {
                $reading->previous_reading = $reading->getPreviousReading();
            }
            
            // Auto-calculate consumption
            $reading->consumption = $reading->current_reading - $reading->previous_reading;
        });

        static::updating(function (UtilityReading $reading) {
            // Update previous reading if not set
            if (!$reading->previous_reading) {
                $reading->previous_reading = $reading->getPreviousReading();
            }
            
            // Recalculate consumption when readings are updated
            $reading->consumption = $reading->current_reading - $reading->previous_reading;
        });
    }

    /**
     * Get the previous reading for the same tenant and utility type
     */
    public function getPreviousReading(): float
    {
        $previous = static::where('tenant_id', $this->tenant_id)
            ->where('utility_type_id', $this->utility_type_id)
            ->where('id', '!=', $this->id)
            ->orderBy('reading_date', 'desc')
            ->first();
        
        return $previous ? $previous->current_reading : 0;
    }

    // Relationships
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function utilityType()
    {
        return $this->belongsTo(UtilityType::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get formatted billing period
     */
    public function getBillingPeriodAttribute(): string
    {
        return $this->reading_date->format('F Y');
    }

    /**
     * Get total amount (price stored in the record)
     */
    public function getTotalAmountAttribute(): float
    {
        return $this->price ?? $this->calculateCost();
    }

    /**
     * Check if this reading has been billed
     */
    public function isBilled(): bool
    {
        return !is_null($this->bill_id);
    }

    /**
     * Get billing status
     */
    public function getBillingStatusAttribute(): string
    {
        return $this->isBilled() ? 'Billed' : 'Pending';
    }

    /**
     * Calculate the cost of this utility reading based on consumption and current rate
     */
    public function calculateCost()
    {
        // Get the current rate for this utility type
        $rate = UtilityRate::where('utility_type_id', $this->utility_type_id)
            ->where('status', 'active')
            ->where('effective_from', '<=', $this->reading_date)
            ->where(function ($query) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $this->reading_date);
            })
            ->first();

        if (!$rate) {
            return 0; // No rate found, return 0
        }

        return $this->consumption * $rate->rate_per_unit;
    }
}
