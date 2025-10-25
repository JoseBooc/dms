<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenaltySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'value',
        'grace_period_days',
        'max_penalty_days',
        'max_penalty_amount',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'max_penalty_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'grace_period_days' => 'integer',
        'max_penalty_days' => 'integer',
    ];

    /**
     * Get the active penalty setting by name
     */
    public static function getActiveSetting(string $name): ?self
    {
        return self::where('name', $name)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Calculate penalty amount based on the setting
     */
    public function calculatePenalty(float $billAmount, int $overdueDays): float
    {
        if ($overdueDays <= $this->grace_period_days) {
            return 0;
        }

        $applicableDays = $overdueDays - $this->grace_period_days;
        
        // Limit to max penalty days if set
        if ($this->max_penalty_days) {
            $applicableDays = min($applicableDays, $this->max_penalty_days);
        }

        $penaltyAmount = 0;

        if ($this->type === 'fixed') {
            // Fixed amount per day
            $penaltyAmount = $this->value * $applicableDays;
        } else {
            // Percentage of bill amount
            $penaltyAmount = ($billAmount * $this->value / 100) * $applicableDays;
        }

        // Apply maximum penalty limit if set
        if ($this->max_penalty_amount) {
            $penaltyAmount = min($penaltyAmount, $this->max_penalty_amount);
        }

        return round($penaltyAmount, 2);
    }
}
