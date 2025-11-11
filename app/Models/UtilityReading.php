<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UtilityReading extends Model
{
    use HasFactory, SoftDeletes;

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
        'reading_number', // Sequential reading identifier
        // Water fields
        'previous_water_reading',
        'current_water_reading',
        'water_consumption',
        'water_rate',
        'water_charge',
        // Electric fields
        'previous_electric_reading',
        'current_electric_reading',
        'electric_consumption',
        'electric_rate',
        'electric_charge',
        // Billing
        'billing_period',
        // Override validation fields
        'override_reason',
        'override_by',
        'status',
    ];

    protected $casts = [
        'current_reading' => 'decimal:2',
        'previous_reading' => 'decimal:2',
        'consumption' => 'decimal:2',
        'price' => 'decimal:2',
        'reading_date' => 'date',
        // Water
        'previous_water_reading' => 'decimal:2',
        'current_water_reading' => 'decimal:2',
        'water_consumption' => 'decimal:2',
        'water_rate' => 'decimal:2',
        'water_charge' => 'decimal:2',
        // Electric
        'previous_electric_reading' => 'decimal:2',
        'current_electric_reading' => 'decimal:2',
        'electric_consumption' => 'decimal:2',
        'electric_rate' => 'decimal:2',
        'electric_charge' => 'decimal:2',
    ];

    protected $appends = [
        'billing_status',
        'total_utility_charge',
    ];

    /**
     * Boot the model
     */
    protected static function booted(): void
    {
        static::creating(function (UtilityReading $reading) {
            // Auto-generate reading number if not set
            if (!$reading->reading_number) {
                $reading->reading_number = static::generateReadingNumber();
            }
            
            // Auto-set the user who recorded this reading
            if (!$reading->recorded_by) {
                $reading->recorded_by = auth()->id();
            }
            
            // Auto-set billing period if not set
            if (!$reading->billing_period && $reading->reading_date) {
                $reading->billing_period = $reading->reading_date->format('M Y');
            }
            
            // Auto-calculate water consumption if not manually set
            if ($reading->current_water_reading !== null && $reading->previous_water_reading !== null) {
                // Allow manual consumption override, otherwise auto-calculate
                if ($reading->water_consumption === null) {
                    $reading->water_consumption = max(0, $reading->current_water_reading - $reading->previous_water_reading);
                }
                // Calculate charge
                if ($reading->water_rate !== null) {
                    $reading->water_charge = $reading->water_consumption * $reading->water_rate;
                }
            }
            
            // Auto-calculate electric consumption if not manually set
            if ($reading->current_electric_reading !== null && $reading->previous_electric_reading !== null) {
                // Allow manual consumption override, otherwise auto-calculate
                if ($reading->electric_consumption === null) {
                    $reading->electric_consumption = max(0, $reading->current_electric_reading - $reading->previous_electric_reading);
                }
                // Calculate charge
                if ($reading->electric_rate !== null) {
                    $reading->electric_charge = $reading->electric_consumption * $reading->electric_rate;
                }
            }
            
            // Legacy fields support
            if (!$reading->previous_reading) {
                $reading->previous_reading = $reading->getPreviousReading();
            }
            if ($reading->current_reading && $reading->previous_reading) {
                $reading->consumption = $reading->current_reading - $reading->previous_reading;
            }
        });

        static::updating(function (UtilityReading $reading) {
            // Auto-set billing period if not set
            if (!$reading->billing_period && $reading->reading_date) {
                $reading->billing_period = $reading->reading_date->format('M Y');
            }
            
            // Recalculate water consumption if changed, but allow manual override
            if ($reading->isDirty(['current_water_reading', 'previous_water_reading'])) {
                if ($reading->current_water_reading !== null && $reading->previous_water_reading !== null) {
                    // Only auto-calculate if consumption wasn't manually changed
                    if (!$reading->isDirty('water_consumption')) {
                        $reading->water_consumption = max(0, $reading->current_water_reading - $reading->previous_water_reading);
                    }
                }
            }
            
            // Recalculate water charge
            if ($reading->water_consumption !== null && $reading->water_rate !== null) {
                $reading->water_charge = $reading->water_consumption * $reading->water_rate;
            }
            
            // Recalculate electric consumption if changed, but allow manual override
            if ($reading->isDirty(['current_electric_reading', 'previous_electric_reading'])) {
                if ($reading->current_electric_reading !== null && $reading->previous_electric_reading !== null) {
                    // Only auto-calculate if consumption wasn't manually changed
                    if (!$reading->isDirty('electric_consumption')) {
                        $reading->electric_consumption = max(0, $reading->current_electric_reading - $reading->previous_electric_reading);
                    }
                }
            }
            
            // Recalculate electric charge
            if ($reading->electric_consumption !== null && $reading->electric_rate !== null) {
                $reading->electric_charge = $reading->electric_consumption * $reading->electric_rate;
            }
            
            // Legacy fields support
            if (!$reading->previous_reading) {
                $reading->previous_reading = $reading->getPreviousReading();
            }
            if ($reading->current_reading && $reading->previous_reading) {
                $reading->consumption = $reading->current_reading - $reading->previous_reading;
            }
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
    public function getBillingPeriodAttribute($value): string
    {
        if ($value) {
            return $value;
        }
        return $this->reading_date ? $this->reading_date->format('M Y') : '';
    }

    /**
     * Get total utility charge (water + electric)
     */
    public function getTotalUtilityChargeAttribute(): float
    {
        return ($this->water_charge ?? 0) + ($this->electric_charge ?? 0);
    }

    /**
     * Get total amount (legacy support)
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

    /**
     * Validate that current reading is >= previous reading
     */
    public function validateReadings(): array
    {
        $errors = [];
        
        if ($this->current_water_reading !== null && $this->previous_water_reading !== null) {
            if ($this->current_water_reading < $this->previous_water_reading) {
                $errors[] = 'Water: Current reading must be greater than or equal to previous reading';
            }
        }
        
        if ($this->current_electric_reading !== null && $this->previous_electric_reading !== null) {
            if ($this->current_electric_reading < $this->previous_electric_reading) {
                $errors[] = 'Electric: Current reading must be greater than or equal to previous reading';
            }
        }
        
        if ($this->water_rate !== null && $this->water_rate < 0) {
            $errors[] = 'Water rate cannot be negative';
        }
        
        if ($this->electric_rate !== null && $this->electric_rate < 0) {
            $errors[] = 'Electric rate cannot be negative';
        }
        
        return $errors;
    }

    /**
     * Get all utility readings for a shared room in the same billing period
     */
    public static function getSharedRoomReadings($roomId, $billingPeriod)
    {
        return static::where('room_id', $roomId)
            ->where('billing_period', $billingPeriod)
            ->whereNotNull('tenant_id')
            ->with('tenant')
            ->get();
    }

    /**
     * Check if room has multiple tenants and thus needs split readings
     */
    public function isSharedRoom(): bool
    {
        return $this->room && $this->room->assignments()->where('status', 'active')->count() > 1;
    }

    /**
     * Get the tenant's share of utility charges in a shared room
     * Default: split equally, but can be customized
     */
    public function calculateTenantShare(): float
    {
        if (!$this->isSharedRoom()) {
            return $this->total_utility_charge;
        }

        // For shared rooms, find all readings for this billing period
        $sharedReadings = static::getSharedRoomReadings($this->room_id, $this->billing_period);
        
        if ($sharedReadings->isEmpty()) {
            return $this->total_utility_charge;
        }

        // Split equally among tenants
        $tenantCount = $sharedReadings->count();
        return $this->total_utility_charge / $tenantCount;
    }

    /**
     * Scope: Get readings for a specific tenant
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope: Get unbilled readings (pending status with no bill_id)
     */
    public function scopeUnbilled($query)
    {
        return $query->where('status', 'pending')->whereNull('bill_id');
    }

    /**
     * Scope: Get readings for billing period
     */
    public function scopeForBillingPeriod($query, $period)
    {
        return $query->where('billing_period', $period);
    }

    /**
     * Generate sequential reading number (e.g., READ-001, READ-002)
     */
    public static function generateReadingNumber(): string
    {
        // Get the latest reading number
        $latestReading = static::withTrashed()
            ->whereNotNull('reading_number')
            ->orderBy('id', 'desc')
            ->first();

        if (!$latestReading || !$latestReading->reading_number) {
            // First reading
            return 'READ-001';
        }

        // Extract number from format READ-XXX
        $lastNumber = (int) substr($latestReading->reading_number, 5);
        $newNumber = $lastNumber + 1;

        // Format with leading zeros (3 digits)
        return 'READ-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}
