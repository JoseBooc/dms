<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deposit extends Model
{
    use HasFactory;

    protected $with = ['tenant', 'roomAssignment.room'];

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
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'deductions_total' => 'decimal:2',
        'refundable_amount' => 'decimal:2',
        'collected_date' => 'date',
        'refund_date' => 'date',
    ];

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

    // Helper methods
    public function updateRefundableAmount(): void
    {
        $this->refundable_amount = $this->amount - $this->deductions_total;
        $this->save();
    }

    public function addDeduction(float $amount, string $type, string $description, ?int $billId = null, ?string $details = null): DepositDeduction
    {
        $deduction = $this->deductions()->create([
            'bill_id' => $billId,
            'deduction_type' => $type,
            'amount' => $amount,
            'description' => $description,
            'details' => $details,
            'deduction_date' => now()->toDateString(),
            'processed_by' => auth()->id() ?? 1, // Fallback to admin user ID
        ]);

        $this->deductions_total += $amount;
        $this->updateRefundableAmount();
        $this->updateStatus();

        return $deduction;
    }

    public function updateStatus(): void
    {
        if ($this->refundable_amount <= 0) {
            $this->status = 'forfeited';
        } elseif ($this->deductions_total > 0) {
            $this->status = 'partially_refunded';
        } else {
            $this->status = 'active';
        }

        $this->save();
    }

    public function canBeRefunded(): bool
    {
        return in_array($this->status, ['active', 'partially_refunded']) && $this->refundable_amount > 0;
    }

    public function processRefund(?string $notes = null): void
    {
        if (!$this->canBeRefunded()) {
            throw new \Exception('Deposit cannot be refunded');
        }

        $this->status = 'fully_refunded';
        $this->refund_date = now()->toDateString();
        $this->refunded_by = auth()->id() ?? 1; // Fallback to admin user ID
        
        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . "\n\n" : '') . "Refund: " . $notes;
        }

        $this->save();
    }
}
