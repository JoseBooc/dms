<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UtilityReadingResource\Pages;
use App\Filament\Resources\UtilityReadingResource\RelationManagers;
use App\Models\UtilityReading;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UtilityReadingResource extends Resource
{
    protected static ?string $model = UtilityReading::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationGroup = 'Utilities Management';

    protected static ?string $navigationLabel = 'Tenant Utilities';

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
        // Always use create form for now to debug
        return static::createForm($form);
    }

    public static function createForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tenant_id')
                    ->relationship('tenant', 'first_name')
                    ->required()
                    ->searchable()
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        $roomInfo = '';
                        $roomAssignment = $record->roomAssignments()->where('status', 'active')->first();
                        if ($roomAssignment && $roomAssignment->room) {
                            $roomInfo = ' (Room ' . $roomAssignment->room->room_number . ')';
                        }
                        return $record->first_name . ' ' . $record->last_name . $roomInfo;
                    })
                    ->label('Tenant*')
                    ->placeholder('Select a tenant')
                    ->validationAttribute('tenant')
                    ->columnSpanFull(),
                
                Forms\Components\Section::make('Water Reading')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('water_price')
                                    ->label('Price*')
                                    ->numeric()
                                    ->step(0.01)
                                    ->required()
                                    ->prefix('₱')
                                    ->placeholder('0.00')
                                    ->inputMode('decimal'),
                                
                                Forms\Components\TextInput::make('water_current_reading')
                                    ->label('Current Meter Reading*')
                                    ->numeric()
                                    ->step(0.01)
                                    ->required()
                                    ->suffix('m³')
                                    ->placeholder('0.00')
                                    ->inputMode('decimal'),
                                
                                Forms\Components\DatePicker::make('water_reading_date')
                                    ->label('Reading Date*')
                                    ->required()
                                    ->default(now()),
                            ]),
                        
                        Forms\Components\Textarea::make('water_notes')
                            ->label('Notes (Optional)')
                            ->maxLength(500)
                            ->rows(2)
                            ->placeholder('Additional notes for water reading')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->compact(),
                
                Forms\Components\Section::make('Electricity Reading')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('electricity_price')
                                    ->label('Price*')
                                    ->numeric()
                                    ->step(0.01)
                                    ->required()
                                    ->prefix('₱')
                                    ->placeholder('0.00')
                                    ->inputMode('decimal'),
                                
                                Forms\Components\TextInput::make('electricity_current_reading')
                                    ->label('Current Meter Reading*')
                                    ->numeric()
                                    ->step(0.01)
                                    ->required()
                                    ->suffix('kWh')
                                    ->placeholder('0.00')
                                    ->inputMode('decimal'),
                                
                                Forms\Components\DatePicker::make('electricity_reading_date')
                                    ->label('Reading Date*')
                                    ->required()
                                    ->default(now()),
                            ]),
                        
                        Forms\Components\Textarea::make('electricity_notes')
                            ->label('Notes (Optional)')
                            ->maxLength(500)
                            ->rows(2)
                            ->placeholder('Additional notes for electricity reading')
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
                Tables\Columns\TextColumn::make('tenant.first_name')
                    ->label('Tenant')
                    ->formatStateUsing(fn ($record) => $record->tenant ? $record->tenant->first_name . ' ' . $record->tenant->last_name : 'N/A')
                    ->searchable(['tenant.first_name', 'tenant.last_name'])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('room.room_number')
                    ->label('Room')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('reading_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('water_price')
                    ->label('Water Price')
                    ->formatStateUsing(function ($record) {
                        $waterReading = UtilityReading::where('tenant_id', $record->tenant_id)
                            ->where('reading_date', $record->reading_date)
                            ->whereHas('utilityType', fn($q) => $q->where('name', 'Water'))
                            ->first();
                        return $waterReading ? '₱' . number_format($waterReading->price, 2) : 'N/A';
                    })
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('water_reading')
                    ->label('Water Reading')
                    ->formatStateUsing(function ($record) {
                        $waterReading = UtilityReading::where('tenant_id', $record->tenant_id)
                            ->where('reading_date', $record->reading_date)
                            ->whereHas('utilityType', fn($q) => $q->where('name', 'Water'))
                            ->first();
                        if ($waterReading) {
                            $consumption = $waterReading->current_reading - $waterReading->previous_reading;
                            return number_format($waterReading->current_reading, 2) . ' m³ (' . number_format($consumption, 2) . ')';
                        }
                        return 'N/A';
                    }),
                
                Tables\Columns\TextColumn::make('electricity_price')
                    ->label('Electricity Price')
                    ->formatStateUsing(function ($record) {
                        $electricityReading = UtilityReading::where('tenant_id', $record->tenant_id)
                            ->where('reading_date', $record->reading_date)
                            ->whereHas('utilityType', fn($q) => $q->where('name', 'Electricity'))
                            ->first();
                        return $electricityReading ? '₱' . number_format($electricityReading->price, 2) : 'N/A';
                    })
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('electricity_reading')
                    ->label('Electricity Reading')
                    ->formatStateUsing(function ($record) {
                        $electricityReading = UtilityReading::where('tenant_id', $record->tenant_id)
                            ->where('reading_date', $record->reading_date)
                            ->whereHas('utilityType', fn($q) => $q->where('name', 'Electricity'))
                            ->first();
                        if ($electricityReading) {
                            $consumption = $electricityReading->current_reading - $electricityReading->previous_reading;
                            return number_format($electricityReading->current_reading, 2) . ' kWh (' . number_format($consumption, 2) . ')';
                        }
                        return 'N/A';
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tenant_id')
                    ->relationship('tenant', 'first_name')
                    ->label('Filter by Tenant'),
                
                Tables\Filters\SelectFilter::make('utility_type_id')
                    ->relationship('utilityType', 'name')
                    ->label('Filter by Utility'),
            ])
            ->defaultSort('reading_date', 'desc')
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
            'index' => Pages\ListUtilityReadings::route('/'),
            'create' => Pages\CreateUtilityReading::route('/create'),
            'edit' => Pages\EditUtilityReading::route('/{record}/edit'),
        ];
    }    
}
