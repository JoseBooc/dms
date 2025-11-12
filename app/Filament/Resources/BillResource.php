<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillResource\Pages;
use App\Models\Bill;
use App\Models\User;
use App\Models\Room;
use App\Services\PenaltyService;
use App\Services\BillingService;
use App\Services\FinancialTransactionService;
use App\Services\AuditLogService;
use App\Helpers\CurrencyHelper;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Closure;

class BillResource extends Resource
{
    protected static ?string $model = Bill::class;

    protected static ?string $navigationIcon = 'heroicon-o-cash';

    protected static ?string $navigationGroup = 'Financial Management';

    protected static ?string $navigationLabel = 'Billing';

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'admin';
    }

    protected static ?string $slug = 'billing';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->role === 'admin';
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->isAdmin() || auth()->user()->isStaff();
    }

    public static function canCreate(): bool
    {
        return auth()->user()->isAdmin() || auth()->user()->isStaff();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Bill Information')
                    ->schema([
                        Forms\Components\Select::make('tenant_id')
                            ->label('Tenant')
                            ->options(function () {
                                // Show ALL users with tenant role and their room assignment status
                                // Must properly join through Tenant model to RoomAssignment
                                return User::where('role', 'tenant')
                                    ->with(['tenant.assignments' => function ($query) {
                                        $query->where('status', 'active')->with('room');
                                    }])
                                    ->get()
                                    ->mapWithKeys(function ($user) {
                                        // Get tenant profile and active assignment
                                        $tenantProfile = $user->tenant;
                                        $activeAssignment = $tenantProfile ? $tenantProfile->assignments->first() : null;
                                        
                                        $roomInfo = $activeAssignment && $activeAssignment->room 
                                            ? ' (' . $activeAssignment->room->room_number . ')' 
                                            : ' (Unassigned)';
                                        return [$user->id => $user->name . $roomInfo];
                                    });
                            })
                            ->required()
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (!$state) {
                                    // Clear all fields when tenant is deselected
                                    $set('room_id', null);
                                    $set('room_display', '');
                                    $set('room_rate', 0);
                                    $set('electricity', 0);
                                    $set('water', 0);
                                    $set('total_amount', 0);
                                    return;
                                }
                                
                                // Get the User and their Tenant profile
                                $user = \App\Models\User::with('tenant')->find($state);
                                
                                if (!$user || !$user->tenant) {
                                    // User has no tenant profile, clear fields
                                    $set('room_id', null);
                                    $set('room_display', 'Unassigned - No tenant profile');
                                    $set('room_rate', 0);
                                    $set('electricity', 0);
                                    $set('water', 0);
                                    $set('total_amount', 0);
                                    return;
                                }
                                
                                // Get the tenant's active room assignment through Tenant model
                                $assignment = \App\Models\RoomAssignment::where('tenant_id', $user->tenant->id)
                                    ->where('status', 'active')
                                    ->with('room')
                                    ->first();
                                
                                if ($assignment && $assignment->room) {
                                    // Auto-populate room (both hidden ID and display field)
                                    $set('room_id', $assignment->room_id);
                                    $set('room_display', $assignment->room->room_number);
                                    
                                    // Auto-populate room rate from room's standard rate
                                    // Priority: 1) Assignment monthly_rent, 2) Room price
                                    $roomRate = $assignment->monthly_rent ?? $assignment->room->price ?? 0;
                                    $set('room_rate', $roomRate);
                                    
                                    // Get latest unbilled utility reading for THIS SPECIFIC TENANT and room
                                    // Using: water_charge = water_consumption × water_rate
                                    //        electric_charge = electric_consumption × electric_rate
                                    $latestReading = \App\Models\UtilityReading::where('room_id', $assignment->room_id)
                                        ->where('tenant_id', $state) // Filter by tenant_id for tenant-specific readings
                                        ->where('status', 'pending') // Only pending (unbilled) readings
                                        ->latest('reading_date')
                                        ->first();
                                    
                                    if ($latestReading) {
                                        // Auto-populate utility charges (already calculated as consumption × rate)
                                        $waterCharge = $latestReading->water_charge ?? 
                                                      (($latestReading->water_consumption ?? 0) * ($latestReading->water_rate ?? 0));
                                        $electricCharge = $latestReading->electric_charge ?? 
                                                         (($latestReading->electric_consumption ?? 0) * ($latestReading->electric_rate ?? 0));
                                        
                                        $set('electricity', round($electricCharge, 2));
                                        $set('water', round($waterCharge, 2));
                                        
                                        // Calculate total
                                        $total = round($roomRate + $electricCharge + $waterCharge, 2);
                                        $set('total_amount', $total);
                                    } else {
                                        // No unbilled readings for this tenant, check for shared room readings
                                        // If room has utility reading but not tenant-specific, split the charges
                                        $roomReading = \App\Models\UtilityReading::where('room_id', $assignment->room_id)
                                            ->whereNull('tenant_id') // Room-level reading (backward compatibility)
                                            ->where('status', 'pending') // Only pending (unbilled) readings
                                            ->latest('reading_date')
                                            ->first();
                                        
                                        if ($roomReading) {
                                            // Get number of active tenants to split charges
                                            $activeTenantCount = \App\Models\RoomAssignment::where('room_id', $assignment->room_id)
                                                ->where('status', 'active')
                                                ->count();
                                            
                                            $divisor = max(1, $activeTenantCount);
                                            
                                            $waterCharge = ($roomReading->water_charge ?? 
                                                          (($roomReading->water_consumption ?? 0) * ($roomReading->water_rate ?? 0))) / $divisor;
                                            $electricCharge = ($roomReading->electric_charge ?? 
                                                             (($roomReading->electric_consumption ?? 0) * ($roomReading->electric_rate ?? 0))) / $divisor;
                                            
                                            $set('electricity', round($electricCharge, 2));
                                            $set('water', round($waterCharge, 2));
                                            
                                            // Calculate total
                                            $total = round($roomRate + $electricCharge + $waterCharge, 2);
                                            $set('total_amount', $total);
                                        } else {
                                            // No readings at all, set to 0
                                            $set('electricity', 0);
                                            $set('water', 0);
                                            
                                            // Calculate total (just room rate)
                                            $set('total_amount', round($roomRate, 2));
                                        }
                                    }
                                } else {
                                    // No active assignment - tenant is unassigned
                                    $set('room_id', null);
                                    $set('room_display', 'Unassigned - Manual entry required');
                                    $set('room_rate', 0);
                                    $set('electricity', 0);
                                    $set('water', 0);
                                    $set('total_amount', 0);
                                }
                            }),
                        
                        Forms\Components\Hidden::make('room_id')
                            ->required(),
                        
                        Forms\Components\TextInput::make('room_display')
                            ->label('Room')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($component, $state, callable $get) {
                                $roomId = $get('room_id');
                                if ($roomId) {
                                    $room = Room::find($roomId);
                                    $component->state($room ? $room->room_number : '');
                                }
                            })
                            ->helperText('Auto-populated from tenant\'s assigned room. Shows "Unassigned" if no active room assignment.')
                            ->placeholder('Select a tenant first'),
                        
                        Forms\Components\DatePicker::make('bill_date')
                            ->required()
                            ->default(now()),
                        
                        Forms\Components\DatePicker::make('due_date')
                            ->required()
                            ->default(now()->addDays(30)),
                        
                        Forms\Components\Hidden::make('created_by')
                            ->default(auth()->id()),
                        
                        Forms\Components\Select::make('status')
                            ->label('Bill Status')
                            ->options([
                                'unpaid' => 'Unpaid',
                                'partially_paid' => 'Partially Paid',
                                'paid' => 'Paid',
                            ])
                            ->default('unpaid')
                            ->required()
                            ->hiddenOn('create'),
                        
                        Forms\Components\Hidden::make('status')
                            ->default('unpaid')
                            ->visibleOn('create'),
                        
                        Forms\Components\TextInput::make('amount_paid')
                            ->label('Amount Paid')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0)
                            ->hiddenOn('create'),
                        
                        Forms\Components\Hidden::make('amount_paid')
                            ->default(0)
                            ->visibleOn('create'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Charges')
                    ->schema([
                        Forms\Components\TextInput::make('room_rate')
                            ->label('Room Rate')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Recalculate total when room rate changes
                                $roomRate = (float)($state ?? 0);
                                $electricity = (float)($get('electricity') ?? 0);
                                $water = (float)($get('water') ?? 0);
                                $otherCharges = (float)($get('other_charges') ?? 0);
                                $penaltyCharge = (float)($get('penalty_charge') ?? 0);
                                
                                $total = round($roomRate + $electricity + $water + $otherCharges + $penaltyCharge, 2);
                                $set('total_amount', $total);
                            })
                            ->helperText('Auto-populated from room\'s standard rental rate. Can be edited manually.'),
                        
                        Forms\Components\TextInput::make('electricity')
                            ->label('Electricity')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Recalculate total when electricity changes
                                $roomRate = (float)($get('room_rate') ?? 0);
                                $electricity = (float)($state ?? 0);
                                $water = (float)($get('water') ?? 0);
                                $otherCharges = (float)($get('other_charges') ?? 0);
                                $penaltyCharge = (float)($get('penalty_charge') ?? 0);
                                
                                $total = round($roomRate + $electricity + $water + $otherCharges + $penaltyCharge, 2);
                                $set('total_amount', $total);
                            })
                            ->helperText('Auto-populated: consumption × electric rate from latest utility reading. Editable.'),
                        
                        Forms\Components\TextInput::make('water')
                            ->label('Water')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Recalculate total when water changes
                                $roomRate = (float)($get('room_rate') ?? 0);
                                $electricity = (float)($get('electricity') ?? 0);
                                $water = (float)($state ?? 0);
                                $otherCharges = (float)($get('other_charges') ?? 0);
                                $penaltyCharge = (float)($get('penalty_charge') ?? 0);
                                
                                $total = round($roomRate + $electricity + $water + $otherCharges + $penaltyCharge, 2);
                                $set('total_amount', $total);
                            })
                            ->helperText('Auto-populated: consumption × water rate from latest utility reading. Editable.'),
                        
                        Forms\Components\TextInput::make('other_charges')
                            ->label('Other Charges')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Recalculate total when other charges change
                                $roomRate = (float)($get('room_rate') ?? 0);
                                $electricity = (float)($get('electricity') ?? 0);
                                $water = (float)($get('water') ?? 0);
                                $otherCharges = (float)($state ?? 0);
                                $penaltyCharge = (float)($get('penalty_charge') ?? 0);
                                
                                $total = round($roomRate + $electricity + $water + $otherCharges + $penaltyCharge, 2);
                                $set('total_amount', $total);
                            }),
                        
                        Forms\Components\TextInput::make('penalty_charge')
                            ->label('Penalty Charge')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Recalculate total when penalty charge changes
                                $roomRate = (float)($get('room_rate') ?? 0);
                                $electricity = (float)($get('electricity') ?? 0);
                                $water = (float)($get('water') ?? 0);
                                $otherCharges = (float)($get('other_charges') ?? 0);
                                $penaltyCharge = (float)($state ?? 0);
                                
                                $total = round($roomRate + $electricity + $water + $otherCharges + $penaltyCharge, 2);
                                $set('total_amount', $total);
                            })
                            ->helperText('Add any late payment or violation penalties here.'),
                        
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->prefix('₱')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->default(0)
                            ->helperText('Auto-calculated from all charges above.'),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(500)
                            ->helperText('Add any additional notes or adjustments made to the bill.')
                            ->placeholder('E.g., Includes late payment penalty, Prorated for partial month, etc.'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Bill #')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('room.room_number')
                    ->label('Room')
                    ->sortable()
                    ->toggleable(),
                // Type column removed - bill_type field still exists in database
                Tables\Columns\TextColumn::make('originalPenaltySource.id')
                    ->label('Linked Bill')
                    ->formatStateUsing(fn ($state) => $state ? "Bill #{$state}" : '-')
                    ->visible(fn ($record) => $record && $record->bill_type === 'penalty' && $record->penalty_bill_for_id)
                    ->url(fn ($record) => $record->penalty_bill_for_id ? route('filament.resources.billing.edit', $record->penalty_bill_for_id) : null)
                    ->color('primary')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->formatStateUsing(fn ($state) => CurrencyHelper::format($state))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('penalty_amount')
                    ->label('Penalty')
                    ->formatStateUsing(fn ($state) => CurrencyHelper::format($state))
                    ->visible(fn ($record) => $record && $record->penalty_amount > 0)
                    ->color('danger')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('amount_paid')
                    ->label('Paid')
                    ->formatStateUsing(fn ($state) => CurrencyHelper::format($state))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Balance')
                    ->formatStateUsing(fn ($state) => CurrencyHelper::format($state))
                    ->weight('bold')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'danger' => 'unpaid',
                        'warning' => 'partially_paid',
                        'success' => 'paid',
                        'secondary' => 'cancelled',
                    ])
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('overdue_days')
                    ->label('Days Overdue')
                    ->getStateUsing(fn ($record) => $record ? app(BillingService::class)->getDaysOverdue($record) : 0)
                    ->visible(fn ($record) => $record && app(BillingService::class)->isOverdue($record))
                    ->color('danger')
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('penalty_status')
                    ->label('Penalty Status')
                    ->colors([
                        'success' => 'waived',
                        'warning' => 'applied',
                        'secondary' => 'none',
                    ])
                    ->toggleable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'partially_paid' => 'Partially Paid',
                        'paid' => 'Paid',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('record_payment')
                    ->label('Record Payment')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    ->authorize('update')
                    ->visible(fn ($record) => $record && in_array($record->status, ['unpaid', 'partially_paid']))
                    ->form([
                        Forms\Components\TextInput::make('payment_amount')
                            ->label('Payment Amount')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->maxValue(fn ($record) => app(BillingService::class)->calculateBalance($record))
                            ->prefix('₱')
                            ->helperText(fn ($record) => 'Outstanding Balance: ' . CurrencyHelper::format(app(BillingService::class)->calculateBalance($record))),
                        Forms\Components\DateTimePicker::make('payment_date')
                            ->label('Payment Date')
                            ->default(now()->timezone('Asia/Manila'))
                            ->timezone('Asia/Manila')
                            ->displayFormat('M d, Y h:i A')
                            ->required(),
                        Forms\Components\Placeholder::make('payment_method_display')
                            ->label('Payment Method')
                            ->content('Cash')
                            ->extraAttributes(['class' => 'text-lg font-semibold text-success-600']),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->maxLength(500)
                            ->placeholder('Optional payment notes')
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        DB::transaction(function () use ($record, $data) {
                            $billingService = app(BillingService::class);
                            $financialService = app(FinancialTransactionService::class);
                            $auditService = app(AuditLogService::class);

                            $oldStatus = $record->status;
                            $oldAmountPaid = $record->amount_paid;

                            // Record payment through service (cash only)
                            $bill = $billingService->recordPayment(
                                $record,
                                $data['payment_amount']
                            );

                            // Log financial transaction
                            $financialService->logBillPayment($bill, $data['payment_amount']);

                            // Log audit entry
                            $auditService->log($bill, 'payment_recorded', [
                                'old_status' => $oldStatus,
                                'old_amount_paid' => $oldAmountPaid,
                            ], [
                                'new_status' => $bill->status,
                                'new_amount_paid' => $bill->amount_paid,
                                'payment_method' => 'cash',
                                'reference_number' => null,
                                'notes' => $data['notes'] ?? null,
                            ], 'Payment of ' . CurrencyHelper::format($data['payment_amount']) . ' recorded (Cash)');
                        });

                        Notification::make()
                            ->success()
                            ->title('Payment Recorded')
                            ->body(CurrencyHelper::format($data['payment_amount']) . ' payment recorded successfully for Bill #' . $record->id)
                            ->send();
                    }),
                
                Tables\Actions\Action::make('calculate_penalty')
                    ->label('Calculate Penalty')
                    ->icon('heroicon-o-calculator')
                    ->color('warning')
                    ->visible(fn ($record) => $record && $record->isOverdue() && !$record->penalty_waived)
                    ->requiresConfirmation()
                    ->modalHeading('Calculate Penalty')
                    ->modalSubheading(fn ($record) => "Calculate penalty for Bill #{$record->id}")
                    ->action(function ($record) {
                        $record->calculatePenalty();
                        Notification::make()
                            ->title('Penalty Calculated')
                            ->body("Penalty calculated for Bill #{$record->id}")
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('waive_penalty')
                    ->label('Waive Penalty')
                    ->icon('heroicon-o-x')
                    ->color('success')
                    ->visible(fn ($record) => $record && $record->penalty_amount > 0 && !$record->penalty_waived)
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Reason for waiving penalty')
                            ->required()
                            ->placeholder('Enter reason for waiving the penalty...')
                    ])
                    ->action(function ($record, array $data) {
                        $record->waivePenalty($data['reason'], auth()->id());
                        Notification::make()
                            ->title('Penalty Waived')
                            ->body("Penalty waived for Bill #{$record->id}")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('create_penalty_bill')
                    ->label('Create Penalty Bill')
                    ->icon('heroicon-o-document-text')
                    ->color('danger')
                    ->visible(function ($record) {
                        // Only visible if:
                        // 1. Bill is overdue OR has penalty applied
                        // 2. Bill type is NOT penalty (no penalty bills for penalty bills)
                        return $record && 
                               $record->bill_type !== 'penalty' && 
                               ($record->isOverdue() || $record->penalty_amount > 0);
                    })
                    ->authorize('createPenaltyBill')
                    ->form([
                        Forms\Components\Placeholder::make('bill_info')
                            ->label('Original Bill')
                            ->content(fn ($record) => 
                                "Bill #{$record->id} - " . 
                                ucfirst(str_replace('_', ' ', $record->bill_type)) . 
                                " - ₱" . number_format($record->total_amount, 2)
                            ),
                        
                        Forms\Components\Placeholder::make('overdue_info')
                            ->label('Overdue Information')
                            ->content(fn ($record) => 
                                $record->isOverdue() 
                                    ? "{$record->getDaysOverdue()} days overdue"
                                    : "Not currently overdue"
                            )
                            ->visible(fn ($record) => $record->isOverdue()),
                        
                        Forms\Components\TextInput::make('penalty_amount')
                            ->label('Penalty Amount')
                            ->numeric()
                            ->required()
                            ->default(fn ($record) => $record->penalty_amount)
                            ->minValue(0.01)
                            ->step(0.01)
                            ->prefix('₱')
                            ->helperText('The amount to charge as a penalty bill'),
                        
                        Forms\Components\Textarea::make('reason')
                            ->label('Reason for Penalty Bill')
                            ->required()
                            ->placeholder('e.g., Late payment penalty, repeated violations, etc.')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Explain why this penalty bill is being created'),
                    ])
                    ->modalHeading('Create Standalone Penalty Bill')
                    ->modalSubheading(fn ($record) => "Convert penalty charges into a formal bill for Bill #{$record->id}")
                    ->modalButton('Create Penalty Bill')
                    ->action(function ($record, array $data) {
                        try {
                            $billingService = app(BillingService::class);
                            
                            // Create the penalty bill
                            $penaltyBill = $billingService->createPenaltyBill(
                                $record,
                                $data['penalty_amount'],
                                $data['reason']
                            );
                            
                            Notification::make()
                                ->title('Penalty Bill Created')
                                ->body(
                                    "Penalty Bill #{$penaltyBill->id} created for ₱" . 
                                    number_format($data['penalty_amount'], 2) . 
                                    " linked to Bill #{$record->id}"
                                )
                                ->success()
                                ->send();
                                
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error Creating Penalty Bill')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('deduct_from_deposit')
                    ->label('Deduct from Deposit')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('warning')
                    ->visible(function ($record) {
                        // Show if bill is unpaid and tenant has an active deposit
                        if ($record->status === 'paid') return false;
                        
                        $tenant = $record->tenant;
                        $activeDeposit = $tenant->deposits()
                            ->whereIn('status', ['active', 'partially_refunded'])
                            ->where('refundable_amount', '>', 0)
                            ->first();
                        
                        return $activeDeposit !== null;
                    })
                    ->form([
                        Forms\Components\Placeholder::make('bill_info')
                            ->content(fn ($record) => 
                                "Bill #{$record->id} - ₱" . number_format($record->getBalance(), 2) . " outstanding"
                            ),
                        
                        Forms\Components\Placeholder::make('deposit_info')
                            ->content(function ($record) {
                                $deposit = $record->tenant->deposits()
                                    ->whereIn('status', ['active', 'partially_refunded'])
                                    ->where('refundable_amount', '>', 0)
                                    ->first();
                                return $deposit ? 
                                    "Available deposit: ₱" . number_format($deposit->refundable_amount, 2) : 
                                    "No available deposit";
                            }),
                        
                        Forms\Components\TextInput::make('deduction_amount')
                            ->label('Amount to Deduct')
                            ->required()
                            ->numeric()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->prefix('₱')
                            ->rules([
                                fn ($record) => function (string $attribute, $value, \Closure $fail) use ($record) {
                                    $balance = $record->getBalance();
                                    if ($value > $balance) {
                                        $fail("Amount cannot exceed outstanding balance of ₱" . number_format($balance, 2));
                                    }
                                    
                                    $deposit = $record->tenant->deposits()
                                        ->whereIn('status', ['active', 'partially_refunded'])
                                        ->where('refundable_amount', '>', 0)
                                        ->first();
                                    
                                    if (!$deposit || $value > $deposit->refundable_amount) {
                                        $available = $deposit ? $deposit->refundable_amount : 0;
                                        $fail("Amount cannot exceed available deposit of ₱" . number_format($available, 2));
                                    }
                                },
                            ]),
                        
                        Forms\Components\TextInput::make('description')
                            ->label('Description')
                            ->required()
                            ->default(fn ($record) => "Deduction for Bill #{$record->id}")
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->placeholder('Optional notes about this deduction...')
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        try {
                            $deposit = $record->tenant->deposits()
                                ->whereIn('status', ['active', 'partially_refunded'])
                                ->where('refundable_amount', '>', 0)
                                ->first();
                            
                            if (!$deposit) {
                                throw new \Exception('No active deposit found for this tenant');
                            }
                            
                            // Add deduction to deposit
                            $deposit->addDeduction(
                                $data['deduction_amount'],
                                'unpaid_rent',
                                $data['description'],
                                $record->id,
                                $data['notes'] ?? null
                            );
                            
                            // Update bill amount paid
                            $record->amount_paid += $data['deduction_amount'];
                            
                            // Update bill status if fully paid
                            if ($record->amount_paid >= $record->total_amount + $record->penalty_amount) {
                                $record->status = 'paid';
                            } elseif ($record->amount_paid > 0) {
                                $record->status = 'partially_paid';
                            }
                            
                            $record->save();
                            
                            Notification::make()
                                ->title('Deposit Deduction Successful')
                                ->body("₱" . number_format($data['deduction_amount'], 2) . " deducted from deposit for Bill #{$record->id}")
                                ->success()
                                ->send();
                                
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Deduction Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Delete function removed - bills should be archived or marked as void instead
            ])
            ->defaultSort('created_at', 'desc');
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['tenant', 'room', 'createdBy']);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBills::route('/'),
            'create' => Pages\CreateBill::route('/create'),
            'edit' => Pages\EditBill::route('/{record}/edit'),
        ];
    }
}