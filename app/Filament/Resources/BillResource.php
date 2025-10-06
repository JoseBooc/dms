<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillResource\Pages;
use App\Filament\Resources\BillResource\RelationManagers;
use App\Models\Bill;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BillResource extends Resource
{
    protected static ?string $model = Bill::class;

    protected static ?string $navigationIcon = 'heroicon-o-cash';

    protected static ?string $navigationGroup = 'Financial Management';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->isAdmin() || auth()->user()->isStaff();
    }

    public static function canViewAny(): bool
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
                            ->relationship('tenant', 'name')
                            ->required()
                            ->searchable(),
                        
                        Forms\Components\Select::make('room_id')
                            ->relationship('room', 'room_number')
                            ->required()
                            ->searchable(),
                        
                        Forms\Components\Select::make('bill_type')
                            ->options([
                                'monthly' => 'Monthly Rent',
                                'utility' => 'Utility Bill',
                                'maintenance' => 'Maintenance Fee',
                                'deposit' => 'Security Deposit',
                                'other' => 'Other',
                            ])
                            ->required(),
                        
                        Forms\Components\DatePicker::make('bill_date')
                            ->required()
                            ->default(now()),
                        
                        Forms\Components\DatePicker::make('due_date')
                            ->required()
                            ->default(now()->addDays(30)),
                        
                        Forms\Components\Textarea::make('description')
                            ->maxLength(500),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Charges Breakdown')
                    ->schema([
                        Forms\Components\TextInput::make('room_rate')
                            ->numeric()
                            ->prefix('₱')
                            ->step(0.01)
                            ->default(0),
                        
                        Forms\Components\TextInput::make('electricity')
                            ->numeric()
                            ->prefix('₱')
                            ->step(0.01)
                            ->default(0),
                        
                        Forms\Components\TextInput::make('water')
                            ->numeric()
                            ->prefix('₱')
                            ->step(0.01)
                            ->default(0),
                        
                        Forms\Components\TextInput::make('other_charges')
                            ->numeric()
                            ->prefix('₱')
                            ->step(0.01)
                            ->default(0),
                        
                        Forms\Components\TextInput::make('other_charges_description')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('total_amount')
                            ->numeric()
                            ->prefix('₱')
                            ->step(0.01)
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Payment Information')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'overdue' => 'Overdue',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('pending'),
                        
                        Forms\Components\TextInput::make('amount_paid')
                            ->numeric()
                            ->prefix('₱')
                            ->step(0.01)
                            ->default(0),
                        
                        Forms\Components\Select::make('created_by')
                            ->relationship('createdBy', 'name')
                            ->default(auth()->id())
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('room.room_number')
                    ->label('Room')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('bill_type')
                    ->colors([
                        'primary' => 'monthly',
                        'warning' => 'utility',
                        'info' => 'maintenance',
                        'success' => 'deposit',
                        'secondary' => 'other',
                    ]),
                
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('php')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('amount_paid')
                    ->money('php')
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'overdue',
                        'secondary' => 'cancelled',
                    ]),
                
                Tables\Columns\TextColumn::make('bill_date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                        'cancelled' => 'Cancelled',
                    ]),
                
                Tables\Filters\SelectFilter::make('bill_type')
                    ->options([
                        'monthly' => 'Monthly Rent',
                        'utility' => 'Utility Bill',
                        'maintenance' => 'Maintenance Fee',
                        'deposit' => 'Security Deposit',
                        'other' => 'Other',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
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
