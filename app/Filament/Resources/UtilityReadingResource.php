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

    protected static ?string $navigationLabel = 'Utility Readings';

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
                Forms\Components\Section::make('Reading Information')
                    ->schema([
                        Forms\Components\Select::make('room_id')
                            ->relationship('room', 'room_number')
                            ->required()
                            ->searchable(),
                        
                        Forms\Components\Select::make('utility_type_id')
                            ->relationship('utilityType', 'name')
                            ->required()
                            ->searchable(),
                        
                        Forms\Components\DatePicker::make('reading_date')
                            ->required()
                            ->default(now()),
                    ])
                    ->columns(3),
                
                Forms\Components\Section::make('Meter Readings')
                    ->schema([
                        Forms\Components\TextInput::make('previous_reading')
                            ->numeric()
                            ->step(0.01)
                            ->default(0),
                        
                        Forms\Components\TextInput::make('current_reading')
                            ->numeric()
                            ->step(0.01)
                            ->required(),
                        
                        Forms\Components\TextInput::make('consumption')
                            ->numeric()
                            ->step(0.01)
                            ->disabled()
                            ->dehydrated(false)
                            ->reactive()
                            ->afterStateHydrated(function (Forms\Components\TextInput $component, $state, $record) {
                                if ($record) {
                                    $component->state($record->current_reading - $record->previous_reading);
                                }
                            })
                            ->formatStateUsing(function ($state, $get) {
                                $current = $get('current_reading') ?? 0;
                                $previous = $get('previous_reading') ?? 0;
                                return $current - $previous;
                            }),
                    ])
                    ->columns(3),
                
                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Select::make('recorded_by')
                            ->relationship('recordedBy', 'name')
                            ->default(auth()->id())
                            ->disabled(),
                        
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(500)
                            ->rows(3),
                        
                        Forms\Components\Select::make('bill_id')
                            ->relationship('bill', 'id')
                            ->searchable()
                            ->placeholder('Link to bill (optional)'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('room.room_number')
                    ->label('Room')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('utilityType.name')
                    ->label('Utility Type')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('previous_reading')
                    ->label('Previous')
                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('current_reading')
                    ->label('Current')
                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('consumption')
                    ->label('Consumption')
                    ->formatStateUsing(function ($record) {
                        return number_format($record->current_reading - $record->previous_reading, 2);
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('utilityType.unit_of_measurement')
                    ->label('Unit'),
                
                Tables\Columns\TextColumn::make('reading_date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('recordedBy.name')
                    ->label('Recorded By'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('utility_type_id')
                    ->relationship('utilityType', 'name')
                    ->label('Utility Type'),
                
                Tables\Filters\SelectFilter::make('room_id')
                    ->relationship('room', 'room_number')
                    ->label('Room'),
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
