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
        'penalty_type',
        'penalty_rate',
        'grace_period_days',
        'max_penalty',
        'active',
    ];

    protected $casts = [
        'penalty_rate' => 'decimal:2',
        'max_penalty' => 'decimal:2',
        'active' => 'boolean',
        'grace_period_days' => 'integer',
    ];

    /**
     * Get the active penalty setting by name
     */
    public static function getActiveSetting(string $name): ?self
    {
        return self::where('name', $name)
            ->where('active', true)
            ->first();
    }

    /**
     * Calculate penalty amount based on the setting
     * Following realistic Philippine dormitory rules
     */
    public function calculatePenalty(float $billAmount, int $overdueDays): float
    {
        // No penalty if within grace period
        if ($overdueDays <= $this->grace_period_days) {
            return 0;
        }

        // Calculate days after grace period
        $applicableDays = $overdueDays - $this->grace_period_days;
        
        $penaltyAmount = 0;

        switch ($this->penalty_type) {
            case 'daily_fixed':
                // Fixed peso amount per day late (e.g., ₱50/day)
                $penaltyAmount = $this->penalty_rate * $applicableDays;
                break;
                
            case 'percentage':
                // Percentage of bill total (one-time, not per day)
                // e.g., 3% of total bill
                $penaltyAmount = $billAmount * ($this->penalty_rate / 100);
                break;
                
            case 'flat_fee':
                // One-time flat fee
                $penaltyAmount = $this->penalty_rate;
                break;
                
            default:
                $penaltyAmount = 0;
        }

        // Apply maximum penalty limit
        if ($this->max_penalty && $penaltyAmount > $this->max_penalty) {
            $penaltyAmount = $this->max_penalty;
        }

        return round($penaltyAmount, 2);
    }
}
