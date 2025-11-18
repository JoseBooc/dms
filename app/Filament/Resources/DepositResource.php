<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepositResource\Pages;
use App\Filament\Resources\DepositResource\RelationManagers;
use App\Models\Deposit;
use App\Models\User;
use App\Models\RoomAssignment;
use App\Models\Bill;
use App\Services\DepositService;
use App\Services\FinancialTransactionService;
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
use Illuminate\Support\Facades\Auth;
use Closure;

class DepositResource extends Resource
{
    protected static ?string $model = Deposit::class;

    protected static ?string $navigationIcon = 'heroicon-o-cash';

    protected static ?string $navigationGroup = 'Financial Management';

    protected static ?string $navigationLabel = 'Deposits';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'admin';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->role === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('tenant_id')
                                    ->label('Tenant')
                                    ->options(function () {
                                        return User::where('role', 'tenant')
                                            ->where('status', '!=', 'blocked')
                                            ->get()
                                            ->mapWithKeys(fn($user) => [$user->id => $user->first_name . ' ' . $user->last_name . ' (' . $user->email . ')']);
                                    })
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                        $set('room_assignment_id', null);
                                        
                                        if ($state) {
                                            // Find the tenant record for this user
                                            $user = User::find($state);
                                            if ($user && $user->tenant) {
                                                // Find the active room assignment for this tenant
                                                $activeAssignment = RoomAssignment::where('tenant_id', $user->tenant->id)
                                                    ->where('status', 'active')
                                                    ->first();
                                                
                                                if ($activeAssignment) {
                                                    $set('room_assignment_id', $activeAssignment->id);
                                                }
                                            }
                                        }
                                    }),

                                Forms\Components\Select::make('room_assignment_id')
                                    ->label('Room Assignment')
                                    ->options(function (callable $get) {
                                        $userId = $get('tenant_id');
                                        if (!$userId) return [];
                                        
                                        // Find the tenant record for this user
                                        $user = User::find($userId);
                                        if (!$user || !$user->tenant) return [];
                                        
                                        return RoomAssignment::where('tenant_id', $user->tenant->id)
                                            ->with('room')
                                            ->get()
                                            ->mapWithKeys(fn($assignment) => [
                                                $assignment->id => 'Room ' . $assignment->room->room_number . 
                                                    ' (' . ucfirst($assignment->status) . ')' .
                                                    ' - ' . ($assignment->start_date ? $assignment->start_date->format('M d, Y') : 'No start date')
                                            ]);
                                    })
                                    ->required()
                                    ->reactive(),
                            ]),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('amount')
                                    ->label('Deposit Amount')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('₱')
                                    ->reactive()
                                    ->rule('numeric')
                                    ->rule('min:0')
                                    ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                        // Ensure non-negative
                                        $depositAmount = max(0, floatval($state ?? 0));
                                        $deductions = max(0, floatval($get('deductions_total') ?? 0));
                                        
                                        // Calculate refundable amount: max(0, deposit - deductions)
                                        $refundable = max(0, $depositAmount - $deductions);
                                        $set('refundable_amount', number_format($refundable, 2, '.', ''));
                                    })
                                    ->helperText('Must be a positive number'),

                                Forms\Components\TextInput::make('deductions_total')
                                    ->label('Total Deductions')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->prefix('₱')
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                        // Ensure non-negative
                                        $deductions = max(0, floatval($state ?? 0));
                                        $depositAmount = max(0, floatval($get('amount') ?? 0));
                                        
                                        // Calculate refundable amount: max(0, deposit - deductions)
                                        $refundable = max(0, $depositAmount - $deductions);
                                        $set('refundable_amount', number_format($refundable, 2, '.', ''));
                                    })
                                    ->helperText('Automatically calculated from deductions'),

                                Forms\Components\TextInput::make('refundable_amount')
                                    ->label('Refundable Amount')
                                    ->numeric()
                                    ->prefix('₱')
                                    ->disabled()
                                    ->dehydrated()
                                    ->helperText('Auto-computed: Deposit Amount - Total Deductions'),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('collected_date')
                                    ->label('Collection Date')
                                    ->required()
                                    ->default(now()),

                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'active' => 'Active',
                                        'partially_refunded' => 'Partially Refunded',
                                        'fully_refunded' => 'Fully Refunded',
                                        'forfeited' => 'Forfeited',
                                    ])
                                    ->default('active')
                                    ->required(),
                            ]),

                        Forms\Components\DatePicker::make('refund_date')
                            ->label('Refund Date')
                            ->hidden(fn (callable $get) => !in_array($get('status'), ['fully_refunded', 'partially_refunded'])),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Hidden::make('collected_by')
                            ->default(auth()->id()),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.first_name')
                    ->label('Tenant')
                    ->formatStateUsing(fn ($record) => 
                        $record->tenant ? 
                        $record->tenant->first_name . ' ' . $record->tenant->last_name : 
                        'Unknown Tenant'
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('roomAssignment.room.room_number')
                    ->label('Room')
                    ->formatStateUsing(fn ($record) => 
                        $record->roomAssignment && $record->roomAssignment->room ? 
                        $record->roomAssignment->room->room_number : 
                        'N/A'
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state) => CurrencyHelper::format($state))
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('deductions_total')
                    ->label('Deductions')
                    ->formatStateUsing(fn ($state) => CurrencyHelper::format($state))
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('refundable_amount')
                    ->label('Refundable')
                    ->formatStateUsing(fn ($state) => CurrencyHelper::format($state))
                    ->sortable()
                    ->toggleable()
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'partially_refunded',
                        'secondary' => 'fully_refunded',
                        'danger' => 'forfeited',
                    ])
                    ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', $state)),

                Tables\Columns\TextColumn::make('collected_date')
                    ->label('Collected')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('refund_date')
                    ->label('Refunded')
                    ->date()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? $state->format('M d, Y') : '—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'partially_refunded' => 'Partially Refunded',
                        'fully_refunded' => 'Fully Refunded',
                        'forfeited' => 'Forfeited',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('addDeduction')
                    ->label('Add Deduction')
                    ->icon('heroicon-o-x')
                    ->color('warning')
                    ->visible(fn (Deposit $record) => in_array($record->status, ['active', 'partially_refunded']))
                    ->form([
                        Forms\Components\Select::make('deduction_type')
                            ->label('Deduction Type')
                            ->options([
                                'unpaid_rent' => 'Unpaid Rent',
                                'unpaid_electricity' => 'Unpaid Electricity',
                                'unpaid_water' => 'Unpaid Water',
                                'penalty' => 'Penalty',
                                'damage' => 'Damage',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('amount')
                            ->label('Deduction Amount')
                            ->required()
                            ->numeric()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->prefix('₱'),

                        Forms\Components\TextInput::make('description')
                            ->label('Description')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('details')
                            ->label('Details')
                            ->rows(3),
                    ])
                    ->action(function (Deposit $record, array $data) {
                        try {
                            $record->addDeduction(
                                $data['amount'],
                                $data['deduction_type'],
                                $data['description'],
                                null, // bill_id - simplified for now
                                $data['details'] ?? null
                            );

                            Notification::make()
                                ->title('Deduction Added Successfully')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error Adding Deduction')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('processRefund')
                    ->label('Process Refund')
                    ->icon('heroicon-o-cash')
                    ->color('success')
                    ->visible(fn (Deposit $record) => $record->refundable_amount > 0 && in_array($record->status, ['active', 'partially_refunded']))
                    ->authorize('refund')
                    ->requiresConfirmation()
                    ->modalHeading('Process Deposit Refund')
                    ->modalSubheading(fn (Deposit $record) => 
                        'Refund ' . CurrencyHelper::format($record->refundable_amount) . ' to ' . 
                        $record->tenant->first_name . ' ' . $record->tenant->last_name
                    )
                    ->form([
                        Forms\Components\Select::make('refund_method')
                            ->label('Refund Method')
                            ->options([
                                'cash' => 'Cash',
                                'gcash' => 'GCash',
                                'bank_transfer' => 'Bank Transfer',
                                'other' => 'Other',
                            ])
                            ->default('cash')
                            ->required(),
                        Forms\Components\TextInput::make('reference_number')
                            ->label('Reference Number')
                            ->maxLength(255)
                            ->placeholder('Optional'),
                        Forms\Components\Textarea::make('refund_notes')
                            ->label('Refund Notes')
                            ->placeholder('Enter any notes about the refund...')
                            ->rows(3),
                    ])
                    ->action(function (Deposit $record, array $data) {
                        DB::transaction(function () use ($record, $data) {
                            $depositService = app(DepositService::class);
                            $financialService = app(FinancialTransactionService::class);
                            $auditService = app(AuditLogService::class);

                            $refundAmount = $record->refundable_amount;
                            $oldStatus = $record->status;

                            // Process refund through service
                            $deposit = $depositService->processRefund(
                                $record,
                                [
                                    'refund_amount' => $refundAmount,
                                    'refund_method' => $data['refund_method'] ?? 'cash',
                                    'reference_number' => $data['reference_number'] ?? null,
                                    'refund_notes' => $data['refund_notes'] ?? null,
                                    'refund_date' => now()
                                ]
                            );

                            // Log financial transaction
                            $financialService->logDepositRefund($deposit, $refundAmount);

                            // Log audit entry
                            $auditService->log($deposit, 'deposit_refunded', [
                                'old_status' => $oldStatus,
                                'refundable_amount' => $refundAmount,
                            ], [
                                'new_status' => $deposit->status,
                                'refund_method' => $data['refund_method'],
                                'reference_number' => $data['reference_number'] ?? null,
                            ], 'Deposit refund of ' . CurrencyHelper::format($refundAmount) . ' processed');
                        });

                        Notification::make()
                            ->success()
                            ->title('Deposit Refunded')
                            ->body('Refund processed successfully')
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'partially_refunded' => 'Partially Refunded',
                        'fully_refunded' => 'Fully Refunded',
                        'forfeited' => 'Forfeited',
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => auth()->user()->isAdmin()),
            ])
            ->defaultSort('created_at', 'desc');
    }
    
    public static function getRelations(): array
    {
        return [
            RelationManagers\DeductionsRelationManager::class,
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['tenant', 'roomAssignment.room']);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeposits::route('/'),
            'create' => Pages\CreateDeposit::route('/create'),
            'edit' => Pages\EditDeposit::route('/{record}/edit'),
        ];
    }    
}
