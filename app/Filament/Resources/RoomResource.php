<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomResource\Pages;
use App\Filament\Resources\RoomResource\RelationManagers;
use App\Models\Room;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $navigationIcon = 'heroicon-o-office-building';

    protected static ?string $navigationGroup = 'Dormitory Management';

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

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
        return $form
            ->schema([
                Forms\Components\TextInput::make('room_number')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(4)
                    ->rules([
                        'regex:/^[a-zA-Z0-9]+$/',
                    ])
                    ->helperText('Maximum 4 characters, letters and numbers only'),
                
                Forms\Components\Select::make('type')
                    ->options([
                        'single' => 'Single',
                        'double' => 'Double',
                    ])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state === 'single') {
                            $set('capacity', 1);
                        } elseif ($state === 'double') {
                            $set('capacity', 2);
                        }
                    }),
                
                Forms\Components\TextInput::make('capacity')
                    ->numeric()
                    ->required()
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Automatically set based on room type'),
                
                Forms\Components\TextInput::make('rate')
                    ->numeric()
                    ->required()
                    ->prefix('₱')
                    ->step(0.01),
                
                Forms\Components\Select::make('status')
                    ->options([
                        'available' => 'Available',
                        'occupied' => 'Occupied',
                        'unavailable' => 'Unavailable',
                    ])
                    ->required()
                    ->default('available'),
                
                Forms\Components\Textarea::make('description')
                    ->maxLength(500)
                    ->columnSpanFull(),
                
                Forms\Components\TextInput::make('current_occupants')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Automatically updated based on active room assignments'),
                
                Forms\Components\Toggle::make('hidden')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('room_number')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'single',
                        'success' => 'double',
                    ]),
                
                Tables\Columns\BadgeColumn::make('occupancy_display')
                    ->label('Occupancy')
                    ->sortable(['current_occupants'])
                    ->colors([
                        'success' => static function ($state, $record): bool {
                            return $record->current_occupants == 0;
                        },
                        'warning' => static function ($state, $record): bool {
                            return $record->current_occupants > 0 && !$record->isFullyOccupied();
                        },
                        'danger' => static function ($state, $record): bool {
                            return $record->isFullyOccupied();
                        },
                    ]),
                
                Tables\Columns\TextColumn::make('rate')
                    ->formatStateUsing(fn ($state) => '₱' . number_format($state, 2))
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'available',
                        'warning' => 'occupied',
                        'danger' => 'unavailable',
                    ]),
                
                Tables\Columns\BooleanColumn::make('hidden'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'available' => 'Available',
                        'occupied' => 'Occupied',
                        'unavailable' => 'Unavailable',
                    ]),
                
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'single' => 'Single',
                        'double' => 'Double',
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
            RelationManagers\AssignmentsRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRooms::route('/'),
            'create' => Pages\CreateRoom::route('/create'),
            'edit' => Pages\EditRoom::route('/{record}/edit'),
        ];
    }    
}
