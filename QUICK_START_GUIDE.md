# 🚀 QUICK START GUIDE
## Master Refactor Implementation - Step-by-Step

**Total Time**: ~2-4 hours for core integration
**Difficulty**: Intermediate
**Prerequisites**: Laravel 9.52, Filament v2 installed

---

## ✅ PHASE 1: DATABASE & SERVICES (COMPLETED)

### What's Already Done:
- ✅ Migrations created and run
- ✅ Service classes created (Billing, Deposit, Utility, Audit, Financial)
- ✅ Policy classes scaffolded
- ✅ Models created (AuditLog, FinancialTransaction)

### Verify Installation:
```bash
# Check migrations ran successfully
php artisan migrate:status

# Should see these new migrations:
# ✅ 2025_11_11_054024_add_tenant_id_references_to_financial_tables
# ✅ 2025_11_11_055543_create_audit_logs_table
# ✅ 2025_11_11_055741_create_financial_transactions_table
```

---

## 🔧 PHASE 2: REGISTER POLICIES (15 minutes)

### Step 1: Update AuthServiceProvider

**File**: `app/Providers/AuthServiceProvider.php`

```php
<?php

namespace App\Providers;

use App\Models\Bill;
use App\Models\Deposit;
use App\Models\UtilityReading;
use App\Models\MaintenanceRequest;
use App\Models\Complaint;
use App\Models\RoomAssignment;
use App\Policies\BillPolicy;
use App\Policies\DepositPolicy;
use App\Policies\UtilityReadingPolicy;
use App\Policies\MaintenanceRequestPolicy;
use App\Policies\ComplaintPolicy;
use App\Policies\RoomAssignmentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Bill::class => BillPolicy::class,
        Deposit::class => DepositPolicy::class,
        UtilityReading::class => UtilityReadingPolicy::class,
        MaintenanceRequest::class => MaintenanceRequestPolicy::class,
        Complaint::class => ComplaintPolicy::class,
        RoomAssignment::class => RoomAssignmentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
```

### Step 2: Implement Remaining Policies

Copy the pattern from `BillPolicy.php` to implement the other 5 policies.

**Example for DepositPolicy**:
```php
public function viewAny(User $user)
{
    return in_array($user->role, ['admin', 'staff']);
}

public function view(User $user, Deposit $deposit)
{
    if (in_array($user->role, ['admin', 'staff'])) {
        return true;
    }
    
    // Tenant can only view own deposits
    return $user->id === $deposit->tenant_id;
}

public function create(User $user)
{
    return $user->role === 'admin';
}

public function update(User $user, Deposit $deposit)
{
    return $user->role === 'admin';
}
```

---

## 🔗 PHASE 3: INTEGRATE SERVICES INTO FILAMENT (30-60 minutes)

### Example 1: BillResource - Record Payment Action

**File**: `app/Filament/Resources/BillResource.php`

Add to the `table()` method:

```php
use App\Services\BillingService;
use App\Services\FinancialTransactionService;
use App\Services\AuditLogService;

public static function table(Table $table): Table
{
    return $table
        ->columns([
            // ... existing columns
        ])
        ->actions([
            Tables\Actions\Action::make('record_payment')
                ->icon('heroicon-o-currency-dollar')
                ->form([
                    Forms\Components\TextInput::make('payment_amount')
                        ->label('Payment Amount')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->maxValue(fn (Bill $record) => app(BillingService::class)->calculateBalance($record))
                        ->prefix('₱'),
                ])
                ->action(function (Bill $record, array $data) {
                    $billingService = app(BillingService::class);
                    $financialService = app(FinancialTransactionService::class);
                    $auditService = app(AuditLogService::class);

                    // Record payment with transaction safety
                    $bill = $billingService->recordPayment($record, $data['payment_amount']);
                    
                    // Log to financial ledger
                    $financialService->logBillPayment($bill, $data['payment_amount']);
                    
                    // Log to audit trail
                    $auditService->log($bill, 'payment_recorded', null, [
                        'payment_amount' => $data['payment_amount'],
                        'new_status' => $bill->status,
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Payment Recorded')
                        ->body("₱{$data['payment_amount']} payment recorded successfully")
                        ->send();
                })
                ->visible(fn (Bill $record) => $record->status !== 'paid'),
        ]);
}
```

### Example 2: DepositResource - Add Deduction Action

**File**: `app/Filament/Resources/DepositResource.php`

```php
use App\Services\DepositService;
use App\Services\FinancialTransactionService;

Tables\Actions\Action::make('add_deduction')
    ->icon('heroicon-o-minus-circle')
    ->form([
        Forms\Components\Select::make('deduction_type')
            ->options([
                'unpaid_rent' => 'Unpaid Rent',
                'unpaid_electricity' => 'Unpaid Electricity',
                'unpaid_water' => 'Unpaid Water',
                'penalty' => 'Penalty',
                'damage' => 'Damage',
            ])
            ->required(),
        Forms\Components\TextInput::make('amount')
            ->numeric()
            ->required()
            ->minValue(0)
            ->prefix('₱'),
        Forms\Components\Textarea::make('description')
            ->required()
            ->maxLength(255),
    ])
    ->action(function (Deposit $record, array $data) {
        $depositService = app(DepositService::class);
        $financialService = app(FinancialTransactionService::class);

        // Add deduction with transaction safety
        $deduction = $depositService->addDeduction($record, [
            'deduction_type' => $data['deduction_type'],
            'amount' => $data['amount'],
            'description' => $data['description'],
            'processed_by' => auth()->id(),
        ]);

        // Log to financial ledger
        $financialService->logDepositDeduction($deduction);

        Notification::make()
            ->success()
            ->title('Deduction Added')
            ->body("₱{$data['amount']} deducted from deposit")
            ->send();
    })
```

### Example 3: UtilityReadingResource - Use UtilityService

**File**: `app/Filament/Resources/UtilityReadingResource/Pages/CreateUtilityReading.php`

```php
use App\Services\UtilityService;

protected function mutateFormDataBeforeCreate(array $data): array
{
    $utilityService = app(UtilityService::class);
    
    // Get previous reading automatically
    if (!isset($data['previous_reading'])) {
        $lastReading = $utilityService->getLastReading(
            $data['room_id'],
            $data['utility_type_id']
        );
        $data['previous_reading'] = $lastReading ? $lastReading->current_reading : 0;
    }

    // Validate and calculate
    try {
        $utilityType = \App\Models\UtilityType::find($data['utility_type_id']);
        $consumption = $utilityService->calculateConsumption(
            $data['current_reading'],
            $data['previous_reading']
        );

        $validation = $utilityService->validateConsumption($utilityType, $consumption);
        
        if (!$validation['valid'] && empty($data['override_reason'])) {
            Notification::make()
                ->danger()
                ->title('Consumption Exceeds Limit')
                ->body("Consumption: {$consumption} {$utilityType->unit} exceeds limit of {$validation['limit']} {$utilityType->unit}")
                ->persistent()
                ->send();
                
            $this->halt();
        }

        $data['consumption'] = $consumption;
        $data['amount'] = $utilityService->calculateAmount(
            $consumption,
            $data['rate'] ?? $utilityService->getCurrentRate($utilityType)
        );
        
    } catch (\Exception $e) {
        Notification::make()
            ->danger()
            ->title('Validation Error')
            ->body($e->getMessage())
            ->persistent()
            ->send();
            
        $this->halt();
    }

    return $data;
}
```

---

## 📊 PHASE 4: ADD EAGER LOADING (15 minutes)

### BillResource
```php
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->with(['tenant', 'room', 'createdBy']);
}
```

### DepositResource
```php
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->with(['tenant', 'roomAssignment']);
}
```

### Apply to all resources to eliminate N+1 queries.

---

## 🗄️ PHASE 5: ADD COMPOSITE INDEXES (10 minutes)

Create a new migration:
```bash
php artisan make:migration add_composite_indexes_for_performance
```

**Migration content**:
```php
public function up()
{
    Schema::table('bills', function (Blueprint $table) {
        $table->index(['tenant_id', 'status']);
        $table->index(['due_date', 'status']);
        $table->index(['room_id', 'bill_date']);
    });

    Schema::table('deposits', function (Blueprint $table) {
        $table->index(['tenant_id', 'status']);
    });

    Schema::table('room_assignments', function (Blueprint $table) {
        $table->index(['tenant_id', 'status']);
        $table->index(['room_id', 'status']);
        $table->index(['start_date', 'end_date']);
    });
}

public function down()
{
    Schema::table('bills', function (Blueprint $table) {
        $table->dropIndex(['tenant_id', 'status']);
        $table->dropIndex(['due_date', 'status']);
        $table->dropIndex(['room_id', 'bill_date']);
    });

    Schema::table('deposits', function (Blueprint $table) {
        $table->dropIndex(['tenant_id', 'status']);
    });

    Schema::table('room_assignments', function (Blueprint $table) {
        $table->dropIndex(['tenant_id', 'status']);
        $table->dropIndex(['room_id', 'status']);
        $table->dropIndex(['start_date', 'end_date']);
    });
}
```

Run migration:
```bash
php artisan migrate
```

---

## 🧪 PHASE 6: TESTING (30 minutes)

### Test Service Classes

```bash
php artisan tinker
```

```php
// Test BillingService
$billingService = app(\App\Services\BillingService::class);
$bill = \App\Models\Bill::first();
$summary = $billingService->getBillSummary($bill);
print_r($summary);

// Test DepositService
$depositService = app(\App\Services\DepositService::class);
$deposit = \App\Models\Deposit::first();
$summary = $depositService->getDepositSummary($deposit);
print_r($summary);

// Test FinancialTransactionService
$financialService = app(\App\Services\FinancialTransactionService::class);
$tenantId = 1;
$balance = $financialService->getCurrentBalance($tenantId);
echo "Current Balance: ₱" . number_format($balance, 2);
```

### Test Payment Recording

1. Go to Bills in Filament
2. Click "Record Payment" on an unpaid bill
3. Enter payment amount
4. Verify:
   - Bill status updated
   - Amount paid increased
   - Financial transaction logged
   - Audit log created

---

## 📝 PHASE 7: UPDATE MODELS (15 minutes)

### Add UtilityReading fields

**File**: `app/Models/UtilityReading.php`

Add to `$fillable`:
```php
protected $fillable = [
    // ... existing fields
    'override_reason',
    'override_by',
    'bill_id',
];
```

Add to `$casts`:
```php
protected $casts = [
    // ... existing casts
    'override_by' => 'integer',
];
```

---

## ✅ VERIFICATION CHECKLIST

After completing all phases:

- [ ] All migrations run successfully
- [ ] Policies registered in AuthServiceProvider
- [ ] At least one service integrated into a Filament resource
- [ ] Eager loading added to main resources
- [ ] Composite indexes migration created and run
- [ ] Services tested in Tinker
- [ ] Payment recording works in UI
- [ ] No errors in `storage/logs/laravel.log`

---

## 🐛 TROUBLESHOOTING

### Issue: "Class 'App\Services\BillingService' not found"
**Solution**: Run `composer dump-autoload`

### Issue: Policy not working
**Solution**: 
1. Clear config cache: `php artisan config:clear`
2. Verify registration in AuthServiceProvider
3. Check user has correct role

### Issue: Migration fails
**Solution**: 
1. Check database connection
2. Ensure all previous migrations ran
3. Check for existing column conflicts

### Issue: Services not logging
**Solution**:
1. Verify auth()->id() returns valid user
2. Check database permissions
3. Review `storage/logs/laravel.log` for errors

---

## 🎯 RECOMMENDED NEXT STEPS

After core integration is stable:

1. **Create Room Occupancy Sync Command** (30 min)
2. **Build Move-Out Wizard Page** (2-3 hours)
3. **Add Financial Reports Module** (2-3 hours)
4. **Write Unit Tests** (4-6 hours)
5. **Add Excel/PDF Export** (2 hours)

---

## 📚 HELPFUL COMMANDS

```bash
# Clear all caches
php artisan optimize:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache

# Check migration status
php artisan migrate:status

# Rollback last migration batch (if needed)
php artisan migrate:rollback

# See all routes
php artisan route:list

# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();
```

---

## 💡 PRO TIPS

1. **Test in Local First**: Always test changes in local environment before production
2. **Backup Database**: Before running migrations in production
3. **Use Transactions**: Wrap all financial operations in DB::transaction()
4. **Log Everything**: Use AuditLogService for critical actions
5. **Monitor Performance**: Use Laravel Debugbar to catch N+1 queries

---

**Happy Coding! 🚀**

For issues or questions, refer to:
- SYSTEM_DOCUMENTATION.md
- REFACTOR_IMPLEMENTATION.md
- Laravel docs: https://laravel.com/docs/9.x
- Filament docs: https://filamentphp.com/docs/2.x

