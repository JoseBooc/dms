<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceRequestResource\Pages;
use App\Filament\Resources\MaintenanceRequestResource\RelationManagers;
use App\Models\MaintenanceRequest;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MaintenanceRequestResource extends Resource
{
    protected static ?string $model = MaintenanceRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'All Maintenance Requests';

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    protected static ?string $slug = 'admin-maintenance-requests';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->isAdmin();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->isAdmin();
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = MaintenanceRequest::query()->with(['tenant', 'room', 'assignee']);
        \Log::info('Admin MaintenanceRequest query count: ' . $query->count());
        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Request Information')
                    ->schema([
                        Forms\Components\Select::make('tenant_id')
                            ->relationship('tenant', 'first_name')
                            ->required()
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if (!$state) {
                                    $set('room_id', null);
                                    return;
                                }
                                
                                // Don't auto-fill if common area is checked
                                if ($get('is_common_area')) {
                                    return;
                                }
                                
                                // Get tenant's active room assignment
                                $tenant = \App\Models\Tenant::with('assignments')->find($state);
                                if ($tenant) {
                                    $activeAssignment = $tenant->assignments()
                                        ->where('status', 'active')
                                        ->with('room')
                                        ->first();
                                    
                                    if ($activeAssignment && $activeAssignment->room) {
                                        $set('room_id', $activeAssignment->room->id);
                                    } else {
                                        $set('room_id', null);
                                    }
                                }
                            }),
                        
                        Forms\Components\Toggle::make('is_common_area')
                            ->label('This issue is in a common area')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($state) {
                                    // Clear room when toggled on
                                    $set('room_id', null);
                                } else {
                                    // Re-populate room from tenant when toggled off
                                    $tenantId = $get('tenant_id');
                                    if ($tenantId) {
                                        $tenant = \App\Models\Tenant::with('assignments')->find($tenantId);
                                        if ($tenant) {
                                            $activeAssignment = $tenant->assignments()
                                                ->where('status', 'active')
                                                ->with('room')
                                                ->first();
                                            
                                            if ($activeAssignment && $activeAssignment->room) {
                                                $set('room_id', $activeAssignment->room->id);
                                            }
                                        }
                                    }
                                }
                            })
                            ->helperText('Check this if the maintenance is needed for a shared/common area'),
                        
                        Forms\Components\Select::make('room_id')
                            ->label(fn (callable $get) => $get('is_common_area') ? 'Common Area' : 'Room')
                            ->options(function (callable $get) {
                                if ($get('is_common_area')) {
                                    return [
                                        'common_bathroom' => 'Common Bathroom',
                                        'common_kitchen' => 'Common Kitchen',
                                        'hallway' => 'Hallway',
                                        'lobby' => 'Lobby',
                                        'study_area' => 'Study Area',
                                        'laundry_area' => 'Laundry Area',
                                        'water_pump_room' => 'Water Pump Room',
                                        'electrical_room' => 'Electrical Room',
                                        'outdoor_area' => 'Outdoor Area',
                                    ];
                                }
                                return \App\Models\Room::pluck('room_number', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->disabled(fn (callable $get) => !$get('is_common_area'))
                            ->dehydrated()
                            ->helperText(function (callable $get) {
                                if ($get('is_common_area')) {
                                    return 'Select the common area requiring maintenance';
                                }
                                return 'Auto-locked to tenant\'s assigned room';
                            }),
                        
                        Forms\Components\TextInput::make('area')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., AC Unit, Bathroom, Door Lock, Window')
                            ->helperText('Specific area/item needing maintenance'),
                        
                        Forms\Components\Select::make('priority')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'urgent' => 'Urgent',
                            ])
                            ->required()
                            ->default('medium')
                            ->helperText('Urgent = safety hazard or major functionality issue'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Request Details')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->maxLength(1000)
                            ->rows(4)
                            ->placeholder('Describe the issue in detail...')
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Assignment & Status')
                    ->schema([
                        Forms\Components\Select::make('assigned_to')
                            ->relationship('assignee', 'name', fn ($query) => $query->where('role', 'staff'))
                            ->searchable()
                            ->placeholder('Assign to maintenance staff')
                            ->required(fn (callable $get) => $get('status') === 'in_progress')
                            ->helperText(fn (callable $get) => 
                                $get('status') === 'in_progress' 
                                    ? 'Required when status is "In Progress"' 
                                    : 'Optional for other statuses'
                            ),
                        
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('pending')
                            ->reactive(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Completion Details')
                    ->schema([
                        Forms\Components\Textarea::make('completion_notes')
                            ->label('Completion Notes')
                            ->rows(3)
                            ->required(fn (callable $get) => $get('status') === 'completed')
                            ->placeholder('Describe what was done to fix the issue...')
                            ->helperText(fn (callable $get) => 
                                $get('status') === 'completed' 
                                    ? 'Required when status is "Completed"' 
                                    : 'Optional for other statuses'
                            )
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('cancel_reason')
                            ->label('Cancellation Reason')
                            ->rows(2)
                            ->required(fn (callable $get) => $get('status') === 'cancelled')
                            ->visible(fn (callable $get) => $get('status') === 'cancelled')
                            ->placeholder('Why is this request being cancelled?')
                            ->helperText('Required when cancelling a request'),
                    ])
                    ->visible(fn (callable $get) => in_array($get('status'), ['completed', 'cancelled'])),
            ]);
    }

    public static function table(Table $table): Table
    {
        \Log::info('ADMIN MaintenanceRequestResource table method called');
        
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Request #')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('tenant.first_name')
                    ->label('Tenant')
                    ->formatStateUsing(fn ($record) => $record->tenant ? 
                        $record->tenant->first_name . ' ' . $record->tenant->last_name : 'Unknown')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('room.room_number')
                    ->label('Room/Area')
                    ->formatStateUsing(function ($record) {
                        if ($record->room_id && is_numeric($record->room_id)) {
                            return $record->room ? $record->room->room_number : 'N/A';
                        }
                        // Display common area name
                        $commonAreas = [
                            'common_bathroom' => 'Common Bathroom',
                            'common_kitchen' => 'Common Kitchen',
                            'hallway' => 'Hallway',
                            'lobby' => 'Lobby',
                            'study_area' => 'Study Area',
                            'laundry_area' => 'Laundry Area',
                            'water_pump_room' => 'Water Pump Room',
                            'electrical_room' => 'Electrical Room',
                            'outdoor_area' => 'Outdoor Area',
                        ];
                        return $commonAreas[$record->room_id] ?? 'Unknown';
                    })
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('area')
                    ->label('Specific Area')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(40)
                    ->searchable(),
                
                Tables\Columns\BadgeColumn::make('priority')
                    ->label('Priority')
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->colors([
                        'secondary' => 'low',
                        'primary' => 'medium',
                        'warning' => 'high',
                        'danger' => 'urgent',
                    ])
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state)))
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('assignee.name')
                    ->label('Assigned To')
                    ->formatStateUsing(fn ($state) => $state ?? 'Unassigned')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime('M d, Y g:i A')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime('M d, Y g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListMaintenanceRequests::route('/'),
            'create' => Pages\CreateMaintenanceRequest::route('/create'),
            'view' => Pages\ViewMaintenanceRequest::route('/{record}'),
            'edit' => Pages\EditMaintenanceRequest::route('/{record}/edit'),
        ];
    }    
}
