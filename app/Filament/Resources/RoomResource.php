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
                
                Forms\Components\Toggle::make('is_hidden')
                    ->label('Hidden')
                    ->helperText('Hidden rooms will not be visible in listings')
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
                
                Tables\Columns\BadgeColumn::make('visibility_status')
                    ->label('Visibility')
                    ->getStateUsing(fn (Room $record): string => $record->is_hidden ? 'hidden' : 'visible')
                    ->colors([
                        'success' => 'visible',
                        'danger' => 'hidden',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'available',
                        'warning' => 'occupied',
                        'danger' => 'unavailable',
                    ]),
                
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
                
                Tables\Filters\Filter::make('is_hidden')
                    ->label('Visibility')
                    ->query(fn (Builder $query): Builder => $query->where('is_hidden', true))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('toggleHide')
                    ->label(fn (Room $record): string => $record->is_hidden ? 'Unhide' : 'Hide')
                    ->icon(fn (Room $record): string => $record->is_hidden ? 'heroicon-o-eye' : 'heroicon-o-eye-off')
                    ->color(fn (Room $record): string => $record->is_hidden ? 'success' : 'warning')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Room $record): string => $record->is_hidden ? 'Unhide Room' : 'Hide Room')
                    ->modalSubheading(fn (Room $record): string => 
                        $record->is_hidden 
                            ? 'Are you sure you want to unhide room ' . $record->room_number . '? It will be visible in listings again.'
                            : 'Are you sure you want to hide room ' . $record->room_number . '? It will not appear in listings but all data will be preserved.'
                    )
                    ->action(function (Room $record) {
                        if ($record->is_hidden) {
                            $record->unhide();
                            \Filament\Notifications\Notification::make()
                                ->title('Room Unhidden')
                                ->success()
                                ->body("Room {$record->room_number} is now visible.")
                                ->send();
                        } else {
                            $record->hide();
                            \Filament\Notifications\Notification::make()
                                ->title('Room Hidden')
                                ->warning()
                                ->body("Room {$record->room_number} has been hidden successfully.")
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('hideSelected')
                    ->label('Hide Selected')
                    ->icon('heroicon-o-eye-off')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Hide Selected Rooms')
                    ->modalSubheading('Are you sure you want to hide the selected rooms? They will not appear in listings but all data will be preserved.')
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                        $hidden = 0;
                        foreach ($records as $record) {
                            if (!$record->is_hidden) {
                                $record->hide();
                                $hidden++;
                            }
                        }
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Rooms Hidden')
                            ->success()
                            ->body("{$hidden} room(s) have been hidden successfully.")
                            ->send();
                    }),
                
                Tables\Actions\BulkAction::make('unhideSelected')
                    ->label('Unhide Selected')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Unhide Selected Rooms')
                    ->modalSubheading('Are you sure you want to unhide the selected rooms? They will be visible in listings again.')
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                        $unhidden = 0;
                        foreach ($records as $record) {
                            if ($record->is_hidden) {
                                $record->unhide();
                                $unhidden++;
                            }
                        }
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Rooms Unhidden')
                            ->success()
                            ->body("{$unhidden} room(s) are now visible.")
                            ->send();
                    }),
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
