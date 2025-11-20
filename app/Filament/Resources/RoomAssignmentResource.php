<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomAssignmentResource\Pages;
use App\Filament\Resources\RoomAssignmentResource\RelationManagers;
use App\Models\RoomAssignment;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RoomAssignmentResource extends Resource
{
    protected static ?string $model = RoomAssignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard';

    protected static ?string $navigationGroup = 'Dormitory Management';

    protected static ?string $navigationLabel = 'Room Assignments';

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
                Forms\Components\Section::make('Assignment Details')
                    ->schema([
                        Forms\Components\Select::make('tenant_id')
                            ->label('Tenant')
                            ->searchable()
                            ->required()
                            ->getSearchResultsUsing(function (string $search) {
                                return \App\Models\Tenant::query()
                                    ->whereHas('user', function ($query) {
                                        $query->where('status', '!=', 'blocked');
                                    })
                                    ->where(function ($query) use ($search) {
                                        $query->where('last_name', 'like', "%{$search}%")
                                            ->orWhere('first_name', 'like', "%{$search}%")
                                            ->orWhere('id', 'like', "%{$search}%");
                                    })
                                    ->limit(10)
                                    ->get()
                                    ->mapWithKeys(function ($tenant) {
                                        return [$tenant->id => "{$tenant->first_name} {$tenant->last_name}"];
                                    });
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $tenant = \App\Models\Tenant::find($value);
                                return $tenant ? "{$tenant->first_name} {$tenant->last_name}" : '';
                            })
                            ->helperText('Start typing tenant\'s last name or first name to search')
                            ->preload(false)
                            ->createOptionForm([
                                Forms\Components\TextInput::make('first_name')
                                    ->required(),
                                Forms\Components\TextInput::make('last_name')
                                    ->required(),
                                Forms\Components\TextInput::make('phone_number'),
                                Forms\Components\TextInput::make('personal_email')
                                    ->email(),
                            ]),
                        
                        Forms\Components\Select::make('room_id')
                            ->label('Room')
                            ->searchable()
                            ->required()
                            ->getSearchResultsUsing(function (string $search) {
                                return \App\Models\Room::query()
                                    ->whereRaw('current_occupants < capacity')
                                    ->where('status', '!=', 'unavailable')
                                    ->where('is_hidden', false)
                                    ->where('room_number', 'like', "%{$search}%")
                                    ->select('id', 'room_number', 'capacity', 'current_occupants')
                                    ->limit(10)
                                    ->get()
                                    ->mapWithKeys(function ($room) {
                                        $available = $room->capacity - $room->current_occupants;
                                        $slots = $available === 1 ? '1 Slot' : "{$available} Slots";
                                        return [$room->id => "Room {$room->room_number} – {$slots} Available"];
                                    });
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $room = \App\Models\Room::find($value);
                                if (!$room) return '';
                                $available = $room->capacity - $room->current_occupants;
                                $slots = $available === 1 ? '1 Slot' : "{$available} Slots";
                                return "Room {$room->room_number} – {$slots} Available";
                            })
                            ->helperText('Only rooms with available space are shown. Start typing room number to search.')
                            ->preload(false),
                        
                        Forms\Components\DatePicker::make('start_date')
                            ->required()
                            ->default(now()),
                        
                        Forms\Components\DatePicker::make('end_date')
                            ->after('start_date'),
                        
                        Forms\Components\TextInput::make('monthly_rent')
                            ->numeric()
                            ->prefix('₱')
                            ->step(0.01)
                            ->required(),
                        
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'active' => 'Active', 
                                'inactive' => 'Inactive',
                                'terminated' => 'Terminated',
                            ])
                            ->required()
                            ->default('pending'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(1000)
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.first_name')
                    ->label('Tenant')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($record) => $record->tenant->first_name . ' ' . $record->tenant->last_name),
                
                Tables\Columns\TextColumn::make('room.room_number')
                    ->label('Room')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('monthly_rent')
                    ->formatStateUsing(fn ($state) => '₱' . number_format($state, 2))
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? $state->format('M j, Y') : 'Ongoing'),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'pending',
                        'secondary' => 'inactive',
                        'danger' => 'terminated',
                    ]),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'inactive' => 'Inactive', 
                        'terminated' => 'Terminated',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Delete function removed - use status changes instead
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
            'index' => Pages\ListRoomAssignments::route('/'),
            'create' => Pages\CreateRoomAssignment::route('/create'),
            'edit' => Pages\EditRoomAssignment::route('/{record}/edit'),
        ];
    }    
}
