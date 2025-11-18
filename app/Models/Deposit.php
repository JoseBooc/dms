<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deposit extends Model
{
    use HasFactory;

    // Removed eager loading - use ->with() only when needed
    // protected $with = ['tenant', 'roomAssignment.room'];

    protected $fillable = [
        'tenant_id',
        'room_assignment_id',
        'amount',
        'deductions_total',
        'refundable_amount',
        'status',
        'collected_date',
        'refund_date',
        'notes',
        'collected_by',
        'refunded_by',
        'refunded_amount',
        'refund_method',
        'reference_number',
        'refund_notes',
        'refunded_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'deductions_total' => 'decimal:2',
        'refundable_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'collected_date' => 'date',
        'refund_date' => 'date',
        'refunded_at' => 'datetime',
    ];

    // Boot method to enforce business logic on create/update
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($deposit) {
            // Ensure non-negative values
            $deposit->amount = max(0, $deposit->amount ?? 0);
            $deposit->deductions_total = max(0, $deposit->deductions_total ?? 0);
            
            // Always recalculate refundable_amount using the formula
            $deposit->refundable_amount = $deposit->calculateRefundable();
        });
    }

    /**
     * Calculate refundable amount based on business logic
     * Formula: refundable_amount = max(0, deposit_amount - total_deductions)
     * 
     * @return float
     */
    public function calculateRefundable(): float
    {
        $amount = (float) ($this->amount ?? 0);
        $deductions = (float) ($this->deductions_total ?? 0);
        
        return max(0, $amount - $deductions);
    }

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id')->withDefault([
            'first_name' => 'Former',
            'last_name' => 'Tenant'
        ]);
    }

    public function roomAssignment(): BelongsTo
    {
        return $this->belongsTo(RoomAssignment::class);
    }

    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function refundedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(DepositDeduction::class);
    }

    /**
     * Get only active (non-archived) deductions
     */
    public function activeDeductions(): HasMany
    {
        return $this->hasMany(DepositDeduction::class)->whereNull('deleted_at');
    }

    /**
     * Get archived (soft-deleted) deductions
     */
    public function archivedDeductions(): HasMany
    {
        return $this->hasMany(DepositDeduction::class)->onlyTrashed();
    }

    /**
     * Recalculate deductions total from active deductions only
     */
    public function recalculateDeductionsTotal(): void
    {
        $this->deductions_total = $this->activeDeductions()->sum('amount');
        $this->refundable_amount = $this->calculateRefundable();
        $this->save();
    }

    // Helper methods
    public function updateRefundableAmount(): void
    {
        $this->refundable_amount = $this->calculateRefundable();
        $this->save();
    }

    public function addDeduction(float $amount, string $type, string $description, ?int $billId = null, ?string $details = null): DepositDeduction
    {
        // Validate deduction amount is positive
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Deduction amount must be greater than zero');
        }

        // Wrap in transaction for data integrity
        return \DB::transaction(function () use ($amount, $type, $description, $billId, $details) {
            $deduction = $this->deductions()->create([
                'bill_id' => $billId,
                'deduction_type' => $type,
                'amount' => $amount,
                'description' => $description,
                'details' => $details,
                'deduction_date' => now()->toDateString(),
                'processed_by' => auth()->id() ?? 1, // Fallback to admin user ID
            ]);

            // Recalculate deductions total from active deductions only
            $this->recalculateDeductionsTotal();
            
            // Update status based on new refundable amount
            $this->updateStatus();
            
            return $deduction;
        });
    }

    public function updateStatus(): void
    {
        // Recalculate refundable amount to ensure it's current
        $refundable = $this->calculateRefundable();
        
        if ($refundable <= 0) {
            $this->status = 'forfeited';
        } elseif ($this->deductions_total > 0 && $refundable < $this->amount) {
            $this->status = 'deducted';
        } else {
            $this->status = 'active';
        }

        $this->save();
    }

    public function canBeRefunded(): bool
    {
        return in_array($this->status, ['active', 'deducted']) && $this->refundable_amount > 0;
    }

    public function processRefund(?string $notes = null): void
    {
        if (!$this->canBeRefunded()) {
            throw new \Exception('Deposit cannot be refunded');
        }

        $this->status = 'refunded';
        $this->refund_date = now()->toDateString();
        $this->refunded_by = auth()->id() ?? 1; // Fallback to admin user ID
        
        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . "\n\n" : '') . "Refund: " . $notes;
        }

        $this->save();
    }
}
