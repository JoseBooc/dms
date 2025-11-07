<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillResource\Pages;
use App\Models\Bill;
use App\Models\User;
use App\Models\Room;
use App\Services\PenaltyService;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
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
                            ->options(User::where('role', 'tenant')->pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                        
                        Forms\Components\Select::make('room_id')
                            ->label('Room')
                            ->options(Room::all()->pluck('room_number', 'id'))
                            ->required()
                            ->searchable(),
                        
                        Forms\Components\Select::make('bill_type')
                            ->options([
                                'room' => 'Room Rent',
                                'utility' => 'Utility Bill', 
                                'maintenance' => 'Maintenance',
                                'other' => 'Other',
                            ])
                            ->required(),
                        
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
                            ->required(),
                        
                        Forms\Components\TextInput::make('electricity')
                            ->label('Electricity')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0)
                            ->required(),
                        
                        Forms\Components\TextInput::make('water')
                            ->label('Water')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0)
                            ->required(),
                        
                        Forms\Components\TextInput::make('other_charges')
                            ->label('Other Charges')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0)
                            ->required(),
                        
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->prefix('₱')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->default(0),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(500),
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
                    ->sortable(),
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('room.room_number')
                    ->label('Room')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bill_type')
                    ->label('Type')
                    ->formatStateUsing(function ($state) {
                        switch ($state) {
                            case 'room': return 'Room Rent';
                            case 'utility': return 'Utility Bill';
                            case 'maintenance': return 'Maintenance';
                            case 'other': return 'Other';
                            default: return ucfirst($state);
                        }
                    }),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->sortable(),
                Tables\Columns\TextColumn::make('penalty_amount')
                    ->label('Penalty')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_paid')
                    ->label('Paid')
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Balance')
                    ->weight('bold'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'danger' => 'unpaid',
                        'warning' => 'partially_paid',
                        'success' => 'paid',
                    ]),
                Tables\Columns\TextColumn::make('overdue_days')
                    ->label('Overdue Days')
                    ->visible(fn () => true),
                Tables\Columns\BadgeColumn::make('penalty_status')
                    ->label('Penalty')
                    ->colors([
                        'success' => 'waived',
                        'warning' => 'applied',
                        'secondary' => 'none',
                    ]),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'partially_paid' => 'Partially Paid',
                        'paid' => 'Paid',
                    ]),
                Tables\Filters\SelectFilter::make('bill_type')
                    ->options([
                        'room' => 'Room Rent',
                        'utility' => 'Utility Bill',
                        'maintenance' => 'Maintenance',
                        'other' => 'Other',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('calculate_penalty')
                    ->label('Calculate Penalty')
                    ->icon('heroicon-o-calculator')
                    ->color('warning')
                    ->visible(fn ($record) => $record->isOverdue() && !$record->penalty_waived)
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
                    ->visible(fn ($record) => $record->penalty_amount > 0 && !$record->penalty_waived)
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
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
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