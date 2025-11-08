<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'room_id',
        'bill_type',
        'description',
        'details',
        'created_by',
        'bill_date',
        'room_rate',
        'electricity',
        'water',
        'other_charges',
        'other_charges_description',
        'total_amount',
        'status',
        'amount_paid',
        'due_date',
        'penalty_amount',
        'penalty_applied_date',
        'overdue_days',
        'penalty_waived',
        'penalty_waiver_reason',
        'penalty_waived_by',
        'penalty_waived_at',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
        'penalty_applied_date' => 'date',
        'penalty_waived_at' => 'datetime',
        'room_rate' => 'decimal:2',
        'electricity' => 'decimal:2',
        'water' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'penalty_waived' => 'boolean',
        'overdue_days' => 'integer',
        'details' => 'array',
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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function penaltyWaivedBy()
    {
        return $this->belongsTo(User::class, 'penalty_waived_by');
    }

    // Penalty-related methods

    /**
     * Check if the bill is overdue
     */
    public function isOverdue(): bool
    {
        return $this->due_date && 
               $this->due_date->isPast() && 
               $this->status !== 'paid' &&
               $this->getBalance() > 0;
    }

    /**
     * Get days overdue
     */
    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        return now()->diffInDays($this->due_date);
    }

    /**
     * Get remaining balance including penalties
     */
    public function getBalance(): float
    {
        return $this->total_amount + $this->penalty_amount - $this->amount_paid;
    }

    /**
     * Get total amount including penalties
     */
    public function getTotalWithPenalty(): float
    {
        return $this->total_amount + ($this->penalty_waived ? 0 : $this->penalty_amount);
    }

    /**
     * Calculate and apply penalty if applicable
     */
    public function calculatePenalty(): void
    {
        if (!$this->isOverdue() || $this->penalty_waived) {
            return;
        }

        $penaltySetting = PenaltySetting::getActiveSetting('late_payment_penalty');
        if (!$penaltySetting) {
            return;
        }

        $overdueDays = $this->getDaysOverdue();
        $penaltyAmount = $penaltySetting->calculatePenalty($this->total_amount, $overdueDays);

        $this->update([
            'penalty_amount' => $penaltyAmount,
            'penalty_applied_date' => now()->toDateString(),
            'overdue_days' => $overdueDays,
        ]);
    }

    /**
     * Waive penalty for this bill
     */
    public function waivePenalty(string $reason, int $waivedBy): bool
    {
        return $this->update([
            'penalty_waived' => true,
            'penalty_waiver_reason' => $reason,
            'penalty_waived_by' => $waivedBy,
            'penalty_waived_at' => now(),
        ]);
    }

    /**
     * Scope for overdue bills
     */
    public function scopeOverdue($query)
    {
        return $query->where('bills.due_date', '<', now())
                    ->whereIn('bills.status', ['unpaid', 'partially_paid'])
                    ->where(function ($q) {
                        $q->whereColumn('bills.amount_paid', '<', 'bills.total_amount')
                          ->orWhere('bills.amount_paid', 0);
                    });
    }

    /**
     * Get deposit deductions related to this bill
     */
    public function depositDeductions()
    {
        return $this->hasMany(DepositDeduction::class);
    }
}
