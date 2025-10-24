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
                    ->color('warning'),
                
                Tables\Actions\Action::make('waive_penalty')
                    ->label('Waive Penalty')
                    ->icon('heroicon-o-x')
                    ->color('success'),
                
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