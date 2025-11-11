# Utility Readings & Billing Integration

## Overview
This document describes the integration between utility readings and the billing system, including automatic real-time status synchronization.

## Status Flow

### Utility Reading Statuses
- **pending**: Reading recorded but not yet included in a bill
- **billed**: Reading has been included in a bill (bill created but not paid)
- **partially_paid**: The associated bill has been partially paid
- **paid**: The associated bill has been fully paid

## Automatic Status Updates

### 1. Bill Creation
When a bill is created:
- System finds all `pending` utility readings for the tenant and room
- Links these readings to the bill by setting `bill_id`
- Updates reading status from `pending` → `billed`

**Location**: `app/Filament/Resources/BillResource/Pages/CreateBill.php::linkUtilityReadings()`

```php
// Automatically links utility readings when bill is created
protected function linkUtilityReadings($bill): void
{
    $utilityReadings = UtilityReading::where('tenant_id', $bill->tenant_id)
        ->where('room_id', $bill->room_id)
        ->whereNull('bill_id')
        ->where('status', 'pending')
        ->where('reading_date', '<=', $bill->bill_date)
        ->get();

    foreach ($utilityReadings as $reading) {
        $reading->update([
            'bill_id' => $bill->id,
            'status' => 'billed',
        ]);
    }
}
```

### 2. Bill Payment (Partial)
When a bill status changes to `partially_paid`:
- System finds all linked utility readings
- Updates reading status to `partially_paid`
- Logs amount paid and total for audit

**Location**: `app/Observers/BillObserver.php::markUtilityReadingsAsPartiallyPaid()`

### 3. Bill Payment (Full)
When a bill status changes to `paid`:
- System finds all linked utility readings (from 'billed' or 'partially_paid')
- Updates reading status to `paid`

**Location**: `app/Observers/BillObserver.php::markUtilityReadingsAsPaid()`

### 4. Payment Reversal
If a bill status changes from `paid` or `partially_paid` back to `unpaid`:
- System finds all linked utility readings
- Reverts reading status to `billed` (if bill_id exists) or `pending` (if no link)

**Location**: `app/Observers/BillObserver.php::markUtilityReadingsAsUnpaid()`

## Model Relationships

### Bill Model
```php
// In app/Models/Bill.php
public function utilityReadings()
{
    return $this->hasMany(UtilityReading::class);
}
```

### UtilityReading Model
```php
// In app/Models/UtilityReading.php
public function bill()
{
    return $this->belongsTo(Bill::class);
}
```

## Observer Pattern

**BillObserver** (`app/Observers/BillObserver.php`)
- Listens to Bill model events (created, updated)
- Automatically synchronizes utility reading statuses
- Handles four-state transitions: pending → billed → partially_paid → paid
- Automatically synchronizes utility reading statuses
- Logs all status changes for audit trail

**Registered in**: `app/Providers/AppServiceProvider.php`

```php
public function boot()
{
    Bill::observe(BillObserver::class);
    // ...
}
```

## Database Indexes

For optimal query performance, the following indexes are added:

```php
// Composite index for bill-reading linking queries
$table->index(['tenant_id', 'room_id', 'status', 'bill_id']);

// Index on bill_id for relationship queries
$table->index('bill_id');

// Index on status for filtering
$table->index('status');
```

**Migration**: `2025_11_11_101352_add_indexes_to_utility_readings_for_billing.php`

## Usage Examples

### Example 1: Create Bill with Utility Readings
```php
// Create a bill (via Filament form or programmatically)
$bill = Bill::create([
    'tenant_id' => 1,
    'room_id' => 2,
    'bill_date' => now(),
    'total_amount' => 5000,
    // ...
]);

// Utility readings are automatically linked and status updated to 'billed'
```

### Example 2: Record Partial Payment
```php
// Record partial payment (via BillingService)
$billingService = app(BillingService::class);
$bill = $billingService->recordPayment($bill, 2500); // Half payment

// Bill status → 'partially_paid'
// Associated utility readings status → 'partially_paid' (automatic via observer)
```

### Example 3: Record Full Payment
```php
// Record remaining payment
$billingService = app(BillingService::class);
$bill = $billingService->recordPayment($bill, 2500);

// If bill is now fully paid:
// - Bill status → 'paid'
// - Associated utility readings status → 'paid' (automatic via observer)
```

### Example 4: Query Utility Readings by Status
```php
// Get all pending readings (not yet billed)
$pendingReadings = UtilityReading::where('status', 'pending')->get();

// Get all billed readings (in a bill but not paid)
$billedReadings = UtilityReading::where('status', 'billed')->get();

// Get all partially paid readings
$partiallyPaidReadings = UtilityReading::where('status', 'partially_paid')->get();

// Get all fully paid readings
$paidReadings = UtilityReading::where('status', 'paid')->get();

// Get readings for a specific bill
$billReadings = $bill->utilityReadings;
```

## Status Flow Diagram

```
Utility Reading Created
        ↓
   [pending]  ← Not yet included in a bill
        ↓
Bill Created & Reading Linked
        ↓
    [billed]  ← Included in bill (bill_id set)
        ↓
Partial Payment Received
        ↓
[partially_paid]  ← Bill partially paid
        ↓
Full Payment Received
        ↓
     [paid]   ← Bill fully paid

Reversal Flow:
[paid] → [partially_paid] → [billed] → [pending]
(if bill status changes or link removed)
```

## Logging

All status changes are logged for audit purposes with detailed context:

```php
Log::info('Utility reading status updated to partially_paid', [
    'reading_id' => $reading->id,
    'bill_id' => $bill->id,
    'tenant_id' => $bill->tenant_id,
    'previous_status' => 'billed',
    'bill_amount_paid' => $bill->amount_paid,
    'bill_total' => $bill->total_amount,
]);
```

Check logs at: `storage/logs/laravel.log`

## Benefits

1. **Real-Time Synchronization**: Status updates happen automatically when bill payment status changes
2. **Audit Trail**: All changes logged with bill and tenant information
3. **Data Integrity**: Readings always reflect correct billing status
4. **Performance**: Indexed queries for fast lookups
5. **Transparency**: Clear relationship between readings and bills
6. **Four-State Tracking**: pending → billed → partially_paid → paid

## Future Enhancements

Potential improvements:
- [ ] Dashboard widget showing utility reading statistics by status
- [ ] Bulk utility reading operations (mark multiple as billed)
- [ ] Utility reading history timeline in bill details
- [ ] Status change notifications to admins
- [ ] Report: Unbilled utility readings older than X days
- [ ] Partial payment allocation (distribute across multiple utility readings)

## Testing Checklist

### Bill Creation & Linking
- [ ] Create bill → utility readings marked as 'billed'
- [ ] Bill creation links pending readings via bill_id
- [ ] Multiple readings per bill handled correctly
- [ ] Observer logs bill creation event

### Payment Status Synchronization
- [ ] No payment (unpaid) → utility readings status: 'billed'
- [ ] Partial payment → utility readings status: 'partially_paid'
- [ ] Full payment → utility readings status: 'paid'
- [ ] Payment amount and total logged in audit trail

### Status Reversals
- [ ] Paid → Partially Paid: utility readings revert to 'partially_paid'
- [ ] Paid → Unpaid: utility readings revert to 'billed'
- [ ] Partially Paid → Unpaid: utility readings revert to 'billed'
- [ ] Observer logs all status reversals

### Data Integrity
- [ ] bill_id foreign key links reading to bill
- [ ] Status changes only when bill status changes
- [ ] Multiple readings linked to same bill all update
- [ ] Tenant-specific readings linked correctly

### Performance
- [ ] Database indexes improve query performance
- [ ] No N+1 queries when updating multiple readings
- [ ] Batch status updates efficient

### Audit & Logging
- [ ] Observer logs all status changes
- [ ] Previous status tracked in logs
- [ ] Bill amount details logged for partial payments
- [ ] Tenant and bill IDs included in all logs
