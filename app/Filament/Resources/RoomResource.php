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
                Forms\Components\TextInput::make('room_number')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(10),
                
                Forms\Components\Select::make('type')
                    ->options([
                        'single' => 'Single',
                        'double' => 'Double',
                        'triple' => 'Triple',
                        'quad' => 'Quad',
                    ])
                    ->required(),
                
                Forms\Components\TextInput::make('capacity')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->maxValue(10),
                
                Forms\Components\TextInput::make('rate')
                    ->numeric()
                    ->required()
                    ->prefix('₱')
                    ->step(0.01),
                
                Forms\Components\Select::make('status')
                    ->options([
                        'available' => 'Available',
                        'occupied' => 'Occupied',
                        'maintenance' => 'Under Maintenance',
                        'reserved' => 'Reserved',
                    ])
                    ->required()
                    ->default('available'),
                
                Forms\Components\Textarea::make('description')
                    ->maxLength(500)
                    ->columnSpanFull(),
                
                Forms\Components\TextInput::make('current_occupants')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                
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
                        'warning' => 'triple',
                        'danger' => 'quad',
                    ]),
                
                Tables\Columns\TextColumn::make('capacity')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('current_occupants')
                    ->sortable()
                    ->label('Occupants'),
                
                Tables\Columns\TextColumn::make('rate')
                    ->money('php')
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'available',
                        'warning' => 'occupied',
                        'danger' => 'maintenance',
                        'primary' => 'reserved',
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
                        'maintenance' => 'Under Maintenance',
                        'reserved' => 'Reserved',
                    ]),
                
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'single' => 'Single',
                        'double' => 'Double',
                        'triple' => 'Triple',
                        'quad' => 'Quad',
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
