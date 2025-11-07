<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepositDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'deposit_id',
        'bill_id',
        'deduction_type',
        'amount',
        'description',
        'details',
        'deduction_date',
        'processed_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'deduction_date' => 'date',
    ];

    // Relationships
    public function deposit(): BelongsTo
    {
        return $this->belongsTo(Deposit::class);
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Helper methods
    public function getDeductionTypeLabel(): string
    {
        return match($this->deduction_type) {
            'unpaid_rent' => 'Unpaid Rent',
            'damage_charge' => 'Damage Charge',
            'cleaning_fee' => 'Cleaning Fee',
            'utility_arrears' => 'Utility Arrears',
            'other' => 'Other',
            default => ucfirst(str_replace('_', ' ', $this->deduction_type)),
        };
    }
}
