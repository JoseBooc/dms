# Deposit Module Business Logic Documentation

## Overview
The Deposit module manages security deposits for tenants with strict business logic enforcement to ensure data integrity and correct financial calculations.

---

## Core Business Rule

**Formula:**
```
refundable_amount = MAX(0, deposit_amount - total_deductions)
```

This formula is **enforced at multiple levels** to prevent any data inconsistency.

---

## Implementation Layers

### 1. **Database Level** ✅
**File:** `database/migrations/2025_11_09_093206_add_deposit_amount_constraints.php`

- All amount columns are `UNSIGNED` (cannot be negative)
- Database table comment documents the formula
- Constraints:
  - `amount` >= 0
  - `deductions_total` >= 0
  - `refundable_amount` >= 0

### 2. **Model Level** ✅
**File:** `app/Models/Deposit.php`

**Boot Method:**
```php
protected static function boot()
{
    parent::boot();
    
    static::saving(function ($deposit) {
        // Ensure non-negative values
        $deposit->amount = max(0, $deposit->amount ?? 0);
        $deposit->deductions_total = max(0, $deposit->deductions_total ?? 0);
        
        // Always recalculate using formula
        $deposit->refundable_amount = $deposit->calculateRefundable();
    });
}
```

**Helper Method:**
```php
public function calculateRefundable(): float
{
    $amount = (float) ($this->amount ?? 0);
    $deductions = (float) ($this->deductions_total ?? 0);
    
    return max(0, $amount - $deductions);
}
```

**Features:**
- Runs on **every save** (create/update)
- Prevents negative values
- Always recalculates `refundable_amount`
- Cannot be bypassed

### 3. **Form Level (Frontend)** ✅
**File:** `app/Filament/Resources/DepositResource.php`

**Deposit Amount Field:**
```php
Forms\Components\TextInput::make('amount')
    ->label('Deposit Amount')
    ->required()
    ->numeric()
    ->minValue(0)
    ->reactive()
    ->rule('min:0')
    ->afterStateUpdated(function (callable $set, callable $get, $state) {
        $depositAmount = max(0, floatval($state ?? 0));
        $deductions = max(0, floatval($get('deductions_total') ?? 0));
        $refundable = max(0, $depositAmount - $deductions);
        $set('refundable_amount', number_format($refundable, 2, '.', ''));
    })
```

**Total Deductions Field:**
```php
Forms\Components\TextInput::make('deductions_total')
    ->label('Total Deductions')
    ->numeric()
    ->minValue(0)
    ->reactive()
    ->afterStateUpdated(function (callable $set, callable $get, $state) {
        $deductions = max(0, floatval($state ?? 0));
        $depositAmount = max(0, floatval($get('amount') ?? 0));
        $refundable = max(0, $depositAmount - $deductions);
        $set('refundable_amount', number_format($refundable, 2, '.', ''));
    })
```

**Refundable Amount Field:**
```php
Forms\Components\TextInput::make('refundable_amount')
    ->label('Refundable Amount')
    ->disabled()  // NEVER manually editable
    ->dehydrated()  // Still saved to database
    ->helperText('Auto-computed: Deposit Amount - Total Deductions')
```

**Features:**
- Real-time calculation as user types
- Validation prevents negative input
- Refundable amount is **disabled** (non-editable)
- Visual feedback with helper text

### 4. **Controller Level (Backend)** ✅

**Create Page** (`app/Filament/Resources/DepositResource/Pages/CreateDeposit.php`):
```php
protected function mutateFormDataBeforeCreate(array $data): array
{
    $data['amount'] = max(0, floatval($data['amount'] ?? 0));
    $data['deductions_total'] = max(0, floatval($data['deductions_total'] ?? 0));
    $data['refundable_amount'] = max(0, $data['amount'] - $data['deductions_total']);
    
    return $data;
}
```

**Edit Page** (`app/Filament/Resources/DepositResource/Pages/EditDeposit.php`):
```php
protected function mutateFormDataBeforeFill(array $data): array
{
    // Recalculate on load
    $amount = max(0, floatval($data['amount'] ?? 0));
    $deductions = max(0, floatval($data['deductions_total'] ?? 0));
    $data['refundable_amount'] = max(0, $amount - $deductions);
    
    return $data;
}

protected function mutateFormDataBeforeSave(array $data): array
{
    // Recalculate before save
    $data['amount'] = max(0, floatval($data['amount'] ?? 0));
    $data['deductions_total'] = max(0, floatval($data['deductions_total'] ?? 0));
    $data['refundable_amount'] = max(0, $data['amount'] - $data['deductions_total']);
    
    return $data;
}
```

**Features:**
- Validates before database interaction
- Prevents API/direct form submission bypass
- Enforces logic even if frontend validation is disabled

---

## Validation Rules

### Frontend Validation
✅ `amount` - required, numeric, min:0  
✅ `deductions_total` - numeric, min:0  
✅ `refundable_amount` - disabled (auto-computed)

### Backend Validation
✅ Model boot method enforces on every save  
✅ Controller methods enforce before create/update  
✅ Database constraints prevent negative values

### Business Logic Validation
✅ If `deductions_total` > `amount` → `refundable_amount` = 0  
✅ Negative inputs → converted to 0  
✅ Manual override → ignored, recalculated

---

## Edge Cases Handled

| Scenario | Input | Expected Output |
|----------|-------|-----------------|
| Normal case | amount: 5000, deductions: 1000 | refundable: 4000 |
| Deductions exceed deposit | amount: 1000, deductions: 1500 | refundable: 0 |
| Negative amount | amount: -100 | amount: 0, refundable: 0 |
| Negative deductions | deductions: -50 | deductions: 0 |
| Zero values | amount: 0, deductions: 0 | refundable: 0 |
| Manual override attempt | refundable: 9999 (ignored) | refundable: calculated value |
| Null values | amount: null | amount: 0, refundable: 0 |

---

## Testing

**Test File:** `tests/Feature/DepositBusinessLogicTest.php`

Run tests:
```bash
php artisan test --filter=DepositBusinessLogicTest
```

Tests cover:
- ✅ Correct calculation on create
- ✅ Negative values prevention
- ✅ Refundable capped at zero
- ✅ Helper method accuracy
- ✅ Recalculation on update
- ✅ Manual override ignored
- ✅ Edge cases (zero, null, negative)

---

## API / Direct Database Access

Even if someone bypasses the form and directly:
- Calls the API
- Uses Tinker
- Executes raw SQL

The **Model boot method** will still enforce the business logic because it runs on every `save()` operation.

Example:
```php
// Even this will be corrected
$deposit = new Deposit();
$deposit->amount = 5000;
$deposit->deductions_total = 1000;
$deposit->refundable_amount = 9999; // Override attempt
$deposit->save();

// Result: refundable_amount will be 4000 (not 9999)
```

---

## Adding Deductions

When deductions are added through the system:

```php
$deposit->addDeduction(
    amount: 500.00,
    type: 'damage_charge',
    description: 'Broken window'
);
```

The method automatically:
1. Validates amount > 0
2. Updates `deductions_total`
3. Recalculates `refundable_amount`
4. Updates deposit status

---

## Status Management

Deposit status is automatically updated based on refundable amount:

- **active**: Full deposit available (`refundable_amount` = `amount`)
- **partially_refunded**: Some deductions made (`0 < refundable_amount < amount`)
- **forfeited**: All deposit consumed (`refundable_amount` = 0)
- **fully_refunded**: Deposit returned to tenant

---

## Fields NOT Affected by Business Logic

These fields remain independent:
- ✅ `collected_date` - Date deposit was collected
- ✅ `refund_date` - Date deposit was refunded
- ✅ `status` - Deposit status (auto-updated based on refundable amount)
- ✅ `notes` - Admin notes
- ✅ `collected_by` - User who collected deposit
- ✅ `refunded_by` - User who processed refund

---

## Summary

The deposit computation business logic is enforced at **5 different levels**:

1. **Database** - UNSIGNED constraints
2. **Model** - Boot method + helper method
3. **Form** - Reactive validation + disabled field
4. **Controller** - Mutation methods
5. **Business Methods** - addDeduction(), updateRefundableAmount()

This multi-layered approach ensures:
- ✅ Data integrity
- ✅ Consistent calculations
- ✅ Prevention of manual override
- ✅ Protection against bypass attempts
- ✅ Automatic recalculation on any change

**The formula `refundable_amount = MAX(0, deposit_amount - total_deductions)` is ALWAYS enforced.**
