<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UtilityReadingResource\Pages;
use App\Filament\Resources\UtilityReadingResource\RelationManagers;
use App\Models\UtilityReading;
use App\Services\UtilityService;
use App\Services\AuditLogService;
use App\Helpers\CurrencyHelper;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class UtilityReadingResource extends Resource
{
    protected static ?string $model = UtilityReading::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationGroup = 'Utilities Management';

    protected static ?string $navigationLabel = 'Tenant Utilities';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->isAdmin();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->isAdmin();
    }

    public static function form(Form $form): Form
    {
        // Always use create form for now to debug
        return static::createForm($form);
    }

    public static function createForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('reading_number_info')
                    ->label('Reading Number')
                    ->content('Auto-generated on save (e.g., READ-001)')
                    ->columnSpanFull()
                    ->hidden(fn ($record) => $record !== null), // Hide on edit, show on create
                
                Forms\Components\Select::make('tenant_id')
                    ->label('Tenant')
                    ->required()
                    ->searchable()
                    ->options(function () {
                        // Get all tenants with active room assignments
                        return \App\Models\Tenant::whereHas('assignments', function ($query) {
                            $query->where('status', 'active');
                        })
                        ->with('user:id,first_name,last_name')
                        ->get()
                        ->mapWithKeys(function ($tenant) {
                            $name = trim($tenant->user->first_name . ' ' . $tenant->user->last_name);
                            // Get their active room assignment
                            $activeAssignment = $tenant->assignments()->where('status', 'active')->with('room')->first();
                            if ($activeAssignment && $activeAssignment->room) {
                                $name .= ' (' . $activeAssignment->room->room_number . ')';
                            }
                            return [$tenant->id => $name];
                        });
                    })
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if (!$state) {
                            $set('room_id', null);
                            return;
                        }
                        
                        // Auto-populate room based on tenant's active assignment
                        $tenant = \App\Models\Tenant::find($state);
                        if ($tenant) {
                            $activeAssignment = $tenant->assignments()
                                ->where('status', 'active')
                                ->with('room')
                                ->first();
                            
                            if ($activeAssignment) {
                                $roomId = $activeAssignment->room_id;
                                $set('room_id', $roomId);
                                
                                // Get previous water reading for this room and tenant
                                $prevWater = \App\Models\UtilityReading::where('room_id', $roomId)
                                    ->where('tenant_id', $state)
                                    ->whereNotNull('current_water_reading')
                                    ->orderBy('reading_date', 'desc')
                                    ->first();
                                $set('previous_water_reading', $prevWater ? $prevWater->current_water_reading : 0);
                                
                                // Get previous electric reading for this room and tenant
                                $prevElectric = \App\Models\UtilityReading::where('room_id', $roomId)
                                    ->where('tenant_id', $state)
                                    ->whereNotNull('current_electric_reading')
                                    ->orderBy('reading_date', 'desc')
                                    ->first();
                                $set('previous_electric_reading', $prevElectric ? $prevElectric->current_electric_reading : 0);
                            }
                        }
                    })
                    ->helperText('Select tenant - their assigned room will be auto-populated')
                    ->placeholder('Select tenant')
                    ->columnSpanFull(),
                
                Forms\Components\Select::make('room_id')
                    ->relationship('room', 'room_number')
                    ->required()
                    ->searchable()
                    ->disabled()
                    ->dehydrated()
                    ->label('Room')
                    ->placeholder('Auto-populated from tenant assignment')
                    ->helperText('✓ Room auto-populated based on tenant selection')
                    ->columnSpanFull(),
                
                Forms\Components\DatePicker::make('reading_date')
                    ->label('Reading Date')
                    ->required()
                    ->default(now())
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $date = \Carbon\Carbon::parse($state);
                            $set('billing_period', $date->format('M Y'));
                        }
                    })
                    ->columnSpanFull(),
                
                Forms\Components\TextInput::make('billing_period')
                    ->label('Billing Period')
                    ->placeholder('e.g., Nov 2025')
                    ->disabled()
                    ->dehydrated()
                    ->columnSpanFull(),
                
                Forms\Components\Section::make('Water Reading')
                    ->description('Previous reading auto-fills from last record. Enter current reading and consumption will auto-calculate.')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('previous_water_reading')
                                    ->label('Previous Reading')
                                    ->numeric()
                                    ->step(0.01)
                                    ->required()
                                    ->suffix('m³')
                                    ->placeholder('0.00')
                                    ->helperText('Auto-filled from last reading. Can be edited manually.')
                                    ->inputMode('decimal'),
                                
                                Forms\Components\TextInput::make('current_water_reading')
                                    ->label('Current Reading')
                                    ->numeric()
                                    ->step(0.01)
                                    ->required()
                                    ->suffix('m³')
                                    ->placeholder('0.00')
                                    ->helperText('Enter the current meter reading')
                                    ->inputMode('decimal')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        // AUTO-CALCULATE consumption = current - previous
                                        $previous = $get('previous_water_reading') ?? 0;
                                        $consumption = max(0, $state - $previous);
                                        $set('water_consumption', $consumption);
                                        
                                        // Also recalculate charge
                                        $rate = $get('water_rate') ?? 0;
                                        $set('water_charge', $consumption * $rate);
                                        
                                        // Check validation limits
                                        if ($consumption > 40 && !$get('override_validation')) {
                                            $set('validation_warning', 'Water consumption exceeds 40 m³ limit. Enable override to proceed.');
                                        } else {
                                            $set('validation_warning', null);
                                        }
                                    }),
                            ]),
                        
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('water_consumption')
                                    ->label('Consumption')
                                    ->numeric()
                                    ->step(0.01)
                                    ->required()
                                    ->suffix('m³')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $rate = $get('water_rate');
                                        if ($rate) {
                                            $set('water_charge', $state * $rate);
                                        }
                                        // Check if exceeds 40 m³ limit
                                        if ($state > 40 && !$get('override_validation')) {
                                            $set('validation_warning', 'Water consumption exceeds 40 m³ limit. Enable override to proceed.');
                                        } else {
                                            $set('validation_warning', null);
                                        }
                                    })
                                    ->helperText('Auto-calculated (Current - Previous). Philippine dorm average: 5-15 m³/month. Limit: 40 m³')
                                    ->placeholder('0.00')
                                    ->inputMode('decimal')
                                    ->rules([
                                        fn ($get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            if ($value > 40 && !$get('override_validation')) {
                                                $fail('Water consumption exceeds 40 m³ limit. Enable override validation to proceed.');
                                            }
                                        },
                                    ]),
                                
                                Forms\Components\TextInput::make('water_rate')
                                    ->label('Rate (₱/m³)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->required()
                                    ->prefix('₱')
                                    ->placeholder('0.00')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $consumption = $get('water_consumption') ?? 0;
                                        $set('water_charge', $consumption * $state);
                                    })
                                    ->rules(['min:0'])
                                    ->helperText('Rate per cubic meter')
                                    ->inputMode('decimal'),
                                
                                Forms\Components\TextInput::make('water_charge')
                                    ->label('Charge')
                                    ->numeric()
                                    ->prefix('₱')
                                    ->disabled()
                                    ->dehydrated()
                                    ->placeholder('Auto-calculated'),
                            ]),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('Water Notes (Optional)')
                            ->maxLength(500)
                            ->rows(2)
                            ->placeholder('Additional notes for water reading')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->compact(),
                
                Forms\Components\Section::make('Electric Reading')
                    ->description('Previous reading auto-fills from last record. Enter current reading and consumption will auto-calculate.')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('previous_electric_reading')
                                    ->label('Previous Reading')
                                    ->numeric()
                                    ->step(0.01)
                                    ->required()
                                    ->suffix('kWh')
                                    ->placeholder('0.00')
                                    ->helperText('Auto-filled from last reading. Can be edited manually.')
                                    ->inputMode('decimal'),
                                
                                Forms\Components\TextInput::make('current_electric_reading')
                                    ->label('Current Reading')
                                    ->numeric()
                                    ->step(0.01)
                                    ->required()
                                    ->suffix('kWh')
                                    ->placeholder('0.00')
                                    ->helperText('Enter the current meter reading')
                                    ->inputMode('decimal')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        // AUTO-CALCULATE consumption = current - previous
                                        $previous = $get('previous_electric_reading') ?? 0;
                                        $consumption = max(0, $state - $previous);
                                        $set('electric_consumption', $consumption);
                                        
                                        // Also recalculate charge
                                        $rate = $get('electric_rate') ?? 0;
                                        $set('electric_charge', $consumption * $rate);
                                        
                                        // Check validation limits
                                        $waterConsumption = $get('water_consumption') ?? 0;
                                        if ($consumption > 500 && !$get('override_validation')) {
                                            $set('validation_warning', 'Electric consumption exceeds 500 kWh limit. Enable override to proceed.');
                                        } else if ($waterConsumption <= 40 || $get('override_validation')) {
                                            $set('validation_warning', null);
                                        }
                                    }),
                            ]),
                        
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('electric_consumption')
                                    ->label('Consumption')
                                    ->numeric()
                                    ->step(0.01)
                                    ->required()
                                    ->suffix('kWh')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $rate = $get('electric_rate');
                                        if ($rate) {
                                            $set('electric_charge', $state * $rate);
                                        }
                                        // Check if exceeds 500 kWh limit
                                        if ($state > 500 && !$get('override_validation')) {
                                            $set('validation_warning', 'Electric consumption exceeds 500 kWh limit. Enable override to proceed.');
                                        } else if ($get('water_consumption') <= 40 || $get('override_validation')) {
                                            $set('validation_warning', null);
                                        }
                                    })
                                    ->helperText('Auto-calculated (Current - Previous). Philippine dorm average: 150-250 kWh/month. Limit: 500 kWh')
                                    ->placeholder('0.00')
                                    ->inputMode('decimal')
                                    ->rules([
                                        fn ($get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            if ($value > 500 && !$get('override_validation')) {
                                                $fail('Electric consumption exceeds 500 kWh limit. Enable override validation to proceed.');
                                            }
                                        },
                                    ]),
                                
                                Forms\Components\TextInput::make('electric_rate')
                                    ->label('Rate (₱/kWh)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->required()
                                    ->prefix('₱')
                                    ->placeholder('0.00')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $consumption = $get('electric_consumption') ?? 0;
                                        $set('electric_charge', $consumption * $state);
                                    })
                                    ->rules(['min:0'])
                                    ->helperText('Rate per kilowatt-hour')
                                    ->inputMode('decimal'),
                                
                                Forms\Components\TextInput::make('electric_charge')
                                    ->label('Charge')
                                    ->numeric()
                                    ->prefix('₱')
                                    ->disabled()
                                    ->dehydrated()
                                    ->placeholder('Auto-calculated'),
                            ]),
                        
                        Forms\Components\Checkbox::make('override_validation')
                            ->label('Override Validation Limits')
                            ->helperText('Check this if consumption exceeds normal limits (500 kWh / 40 m³)')
                            ->reactive()
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('override_reason')
                            ->label('Override Reason')
                            ->required(fn ($get) => $get('override_validation') === true)
                            ->visible(fn ($get) => $get('override_validation') === true)
                            ->maxLength(500)
                            ->rows(3)
                            ->placeholder('Explain why consumption exceeds normal limits (e.g., multiple occupants, extended usage, faulty meter)')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->compact(),
            ]);
    }

    public static function editForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('tenant_id')
                            ->relationship('tenant', 'first_name')
                            ->required()
                            ->searchable()
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->first_name . ' ' . $record->last_name)
                            ->label('Tenant*')
                            ->disabled(),
                        
                        Forms\Components\Select::make('utility_type_id')
                            ->relationship('utilityType', 'name')
                            ->required()
                            ->label('Utility Type*')
                            ->disabled(),
                    ]),
                
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('Price*')
                            ->numeric()
                            ->step(0.01)
                            ->required()
                            ->prefix('₱')
                            ->placeholder('0.00')
                            ->inputMode('decimal'),
                        
                        Forms\Components\TextInput::make('current_reading')
                            ->label('Current Meter Reading*')
                            ->numeric()
                            ->step(0.01)
                            ->required()
                            ->suffix('units')
                            ->placeholder('0.00')
                            ->inputMode('decimal'),
                        
                        Forms\Components\DatePicker::make('reading_date')
                            ->label('Reading Date*')
                            ->required(),
                    ]),
                
                Forms\Components\Textarea::make('notes')
                    ->label('Notes (Optional)')
                    ->maxLength(500)
                    ->rows(2)
                    ->placeholder('Additional notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reading_number')
                    ->label('Reading No.')
                    ->sortable()
                    ->searchable()
                    ->weight('bold')
                    ->color('primary')
                    ->copyable()
                    ->copyMessage('Reading number copied')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('room.room_number')
                    ->label('Room')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('tenant.full_name')
                    ->label('Tenant')
                    ->formatStateUsing(function ($record) {
                        if ($record->tenant) {
                            return $record->tenant->first_name . ' ' . $record->tenant->last_name;
                        }
                        return 'N/A';
                    })
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('reading_date')
                    ->label('Date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('billing_period')
                    ->label('Billing Period')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'paid',
                        'primary' => 'billed',
                        'warning' => 'partially_paid',
                        'info' => 'verified',
                        'secondary' => 'pending',
                    ])
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('water_consumption')
                    ->label('Water Usage')
                    ->formatStateUsing(function ($record) {
                        if ($record->water_consumption !== null) {
                            return number_format($record->water_consumption, 2) . ' m³';
                        }
                        return 'N/A';
                    })
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('water_charge')
                    ->label('Water Charge')
                    ->formatStateUsing(function ($record) {
                        return $record->water_charge ? CurrencyHelper::format($record->water_charge) : 'N/A';
                    })
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('electric_consumption')
                    ->label('Electric Usage')
                    ->formatStateUsing(function ($record) {
                        if ($record->electric_consumption !== null) {
                            return number_format($record->electric_consumption, 2) . ' kWh';
                        }
                        return 'N/A';
                    })
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('electric_charge')
                    ->label('Electric Charge')
                    ->formatStateUsing(function ($record) {
                        return $record->electric_charge ? CurrencyHelper::format($record->electric_charge) : 'N/A';
                    })
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('total_utility_charge')
                    ->label('Total Charge')
                    ->formatStateUsing(function ($record) {
                        return CurrencyHelper::format($record->total_utility_charge);
                    })
                    ->sortable()
                    ->weight('bold')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('room_id')
                    ->relationship('room', 'room_number')
                    ->label('Filter by Room'),
                
                Tables\Filters\SelectFilter::make('billing_period')
                    ->options(function () {
                        return \App\Models\UtilityReading::select('billing_period')
                            ->distinct()
                            ->whereNotNull('billing_period')
                            ->orderBy('billing_period', 'desc')
                            ->pluck('billing_period', 'billing_period');
                    })
                    ->label('Filter by Month'),
                
                Tables\Filters\Filter::make('unbilled')
                    ->label('Readings without Bills')
                    ->query(fn (Builder $query) => $query->whereNull('bill_id')),
                
                Tables\Filters\TrashedFilter::make()
                    ->label('Archived')
                    ->placeholder('Without archived')
                    ->trueLabel('With archived')
                    ->falseLabel('Only archived')
                    ->queries(
                        true: fn (Builder $query) => $query->withTrashed(),
                        false: fn (Builder $query) => $query->onlyTrashed(),
                        blank: fn (Builder $query) => $query->withoutTrashed(),
                    ),
            ])
            ->defaultSort('reading_date', 'desc')
            ->actions([
                Tables\Actions\Action::make('generateBill')
                    ->label('Generate Bill')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->visible(fn ($record) => $record && $record->status === 'pending' && $record->tenant_id)
                    ->action(function ($record) {
                        try {
                            // Get the tenant and room assignment
                            $tenant = $record->tenant;
                            $room = $record->room;
                            
                            if (!$tenant || !$room) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Bill Generation Failed')
                                    ->body('Missing tenant or room information.')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            
                            // Get the user_id from the tenant (bills.tenant_id references users.id)
                            $userId = $tenant->user_id;
                            
                            if (!$userId) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Bill Generation Failed')
                                    ->body('Tenant is not linked to a user account.')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            
                            // Get room rate from active assignment or room price
                            $assignment = \App\Models\RoomAssignment::where('tenant_id', $tenant->id)
                                ->where('room_id', $room->id)
                                ->where('status', 'active')
                                ->first();
                            
                            $roomRate = $assignment ? ($assignment->monthly_rent ?? $room->price ?? 0) : ($room->price ?? 0);
                            
                            // Calculate utility charges
                            $waterCharge = $record->water_charge ?? (($record->water_consumption ?? 0) * ($record->water_rate ?? 0));
                            $electricCharge = $record->electric_charge ?? (($record->electric_consumption ?? 0) * ($record->electric_rate ?? 0));
                            
                            // Calculate total
                            $totalAmount = round($roomRate + $waterCharge + $electricCharge, 2);
                            
                            // Create the bill
                            $bill = \App\Models\Bill::create([
                                'tenant_id' => $userId, // Use user_id, not tenant->id
                                'room_id' => $room->id,
                                'bill_type' => 'room', // Monthly bill including room + utilities
                                'room_rate' => $roomRate,
                                'electricity' => round($electricCharge, 2),
                                'water' => round($waterCharge, 2),
                                'other_charges' => 0,
                                'total_amount' => $totalAmount,
                                'bill_date' => now(),
                                'due_date' => now()->addDays(7),
                                'status' => 'unpaid', // ENUM: unpaid, partially_paid, paid
                                'amount_paid' => 0,
                                'created_by' => auth()->id(),
                            ]);
                            
                            // Link the utility reading to the bill and update status
                            $record->update([
                                'bill_id' => $bill->id,
                                'status' => 'billed',
                            ]);
                            
                            // Success notification with bill details
                            \Filament\Notifications\Notification::make()
                                ->title('Bill Generated Successfully')
                                ->body("Bill #{$bill->id} created for {$tenant->first_name} {$tenant->last_name} - Room {$room->room_number}\nTotal: ₱" . number_format($totalAmount, 2))
                                ->success()
                                ->duration(5000)
                                ->send();
                                
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Bill Generation Failed')
                                ->body('Error: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Generate Utility Bill')
                    ->modalSubheading(fn ($record) => 'Generate bill for ' . ($record->tenant ? $record->tenant->first_name . ' ' . $record->tenant->last_name : 'Tenant') . ' - Room ' . $record->room->room_number . '?\n\nWater: ₱' . number_format($record->water_charge ?? 0, 2) . ' | Electricity: ₱' . number_format($record->electric_charge ?? 0, 2) . ' | Total: ₱' . number_format($record->total_utility_charge, 2))
                    ->modalButton('Generate Bill'),
                
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\DeleteAction::make()
                    ->label('Archive')
                    ->modalHeading('Archive Utility Reading')
                    ->modalSubheading('This will archive the reading. You can restore it later from the archived items view.')
                    ->modalButton('Archive Reading')
                    ->successNotificationTitle('Reading archived successfully'),
                
                Tables\Actions\RestoreAction::make()
                    ->label('Restore')
                    ->successNotificationTitle('Reading restored successfully'),
                
                Tables\Actions\ForceDeleteAction::make()
                    ->label('Delete Permanently')
                    ->hidden(), // Hide force delete to prevent permanent deletion
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->label('Archive Selected')
                    ->modalHeading('Archive Selected Utility Readings')
                    ->modalSubheading('This will archive the selected readings. You can restore them later.')
                    ->successNotificationTitle('Readings archived successfully'),
                
                Tables\Actions\RestoreBulkAction::make()
                    ->label('Restore Selected')
                    ->successNotificationTitle('Readings restored successfully'),
                
                Tables\Actions\ForceDeleteBulkAction::make()
                    ->label('Delete Permanently')
                    ->hidden(), // Hide force delete to prevent permanent deletion
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['room', 'tenant', 'recordedBy']);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUtilityReadings::route('/'),
            'create' => Pages\CreateUtilityReading::route('/create'),
            'edit' => Pages\EditUtilityReading::route('/{record}/edit'),
        ];
    }    
}
