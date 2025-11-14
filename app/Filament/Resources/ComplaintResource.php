<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComplaintResource\Pages;
use App\Filament\Resources\ComplaintResource\RelationManagers;
use App\Models\Complaint;
use App\Models\User;
use App\Models\Room;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ComplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-circle';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'All Complaints';

    protected static ?string $slug = 'admin-complaints';

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
        $query = Complaint::query()->with(['tenant', 'room', 'assignedTo']);
        \Log::info('Admin Complaint query count: ' . $query->count());
        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Complaint Details')
                    ->schema([
                        Forms\Components\Select::make('tenant_id')
                            ->label('Tenant')
                            ->options(User::where('role', 'tenant')->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if (!$state) {
                                    $set('room_id', null);
                                    return;
                                }
                                
                                // Get the tenant's active room assignment
                                $user = User::with('tenant.assignments')->find($state);
                                if ($user && $user->tenant) {
                                    $activeAssignment = $user->tenant->assignments()
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
                        
                        Forms\Components\Select::make('category')
                            ->options([
                                'noise' => 'Noise',
                                'maintenance' => 'Maintenance',
                                'facilities' => 'Facilities',
                                'staff' => 'Staff',
                                'security' => 'Security',
                                'cleanliness' => 'Cleanliness',
                                'other' => 'Other'
                            ])
                            ->required()
                            ->default('other')
                            ->reactive(),
                            
                        Forms\Components\Select::make('room_id')
                            ->label('Room / Area')
                            ->options(function (callable $get) {
                                $category = $get('category');
                                $editableCategories = ['facilities', 'cleanliness', 'security', 'other'];
                                
                                // If category allows common areas, include them
                                if (in_array($category, $editableCategories)) {
                                    return array_merge(
                                        Room::pluck('room_number', 'id')->toArray(),
                                        [
                                            'common_bathroom' => 'Common Bathroom',
                                            'common_kitchen' => 'Common Kitchen',
                                            'hallway' => 'Hallway',
                                            'lobby' => 'Lobby',
                                            'study_area' => 'Study Area',
                                            'laundry_area' => 'Laundry Area',
                                            'outdoor_area' => 'Outdoor Area',
                                        ]
                                    );
                                }
                                
                                return Room::pluck('room_number', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->disabled(function (callable $get) {
                                $category = $get('category');
                                $editableCategories = ['facilities', 'cleanliness', 'security', 'other'];
                                return !in_array($category, $editableCategories);
                            })
                            ->dehydrated()
                            ->helperText(function (callable $get) {
                                $category = $get('category');
                                $editableCategories = ['facilities', 'cleanliness', 'security', 'other'];
                                
                                if (in_array($category, $editableCategories)) {
                                    return 'Room is editable for this category. Select tenant\'s room or a common area.';
                                }
                                return 'Room auto-populated from tenant assignment (locked for this category).';
                            })
                            ->placeholder('Select tenant first'),
                            
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Brief summary of the complaint'),
                            
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(4)
                            ->placeholder('Detailed description of the issue'),
                            
                        Forms\Components\Select::make('priority')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'urgent' => 'Urgent'
                            ])
                            ->required()
                            ->default('medium'),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Status & Assignment')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'investigating' => 'Investigating',
                                'resolved' => 'Resolved',
                                'closed' => 'Closed'
                            ])
                            ->required()
                            ->default('pending')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get, $record) {
                                // Auto-set resolved_at when status becomes resolved or closed
                                if (in_array($state, ['resolved', 'closed'])) {
                                    $set('resolved_at', now());
                                }
                                
                                // Clear actions_taken when status is set back to investigating from resolved/completed
                                if ($state === 'investigating' && $record) {
                                    $oldStatus = $record->getOriginal('status');
                                    if (in_array($oldStatus, ['resolved', 'closed'])) {
                                        $set('actions_taken', null);
                                    }
                                }
                            }),
                            
                        Forms\Components\Select::make('assigned_to')
                            ->label('Assigned To')
                            ->options(User::whereIn('role', ['admin', 'staff'])->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(fn (callable $get) => $get('status') === 'investigating')
                            ->placeholder('Select staff member')
                            ->helperText(fn (callable $get) => 
                                $get('status') === 'investigating' 
                                    ? 'Required when status is "Investigating"' 
                                    : 'Optional for other statuses'
                            ),

                        Forms\Components\Textarea::make('staff_notes')
                            ->label('Investigation Notes')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Add investigation notes...')
                            ->visible(fn (callable $get) => in_array($get('status'), ['investigating', 'resolved', 'closed']))
                            ->helperText('Visible to tenants and administrators'),

                        Forms\Components\Textarea::make('actions_taken')
                            ->label('Actions Taken')
                            ->rows(3)
                            ->columnSpanFull()
                            ->required(fn (callable $get) => in_array($get('status'), ['resolved', 'closed']))
                            ->placeholder('Describe the actions taken to resolve this complaint...')
                            ->visible(fn (callable $get) => in_array($get('status'), ['investigating', 'resolved', 'closed']))
                            ->helperText(fn (callable $get) => 
                                in_array($get('status'), ['resolved', 'closed'])
                                    ? 'Required when status is "Resolved" or "Closed". Visible to tenants and administrators.' 
                                    : 'Optional for investigating status'
                            ),
                            
                        Forms\Components\Textarea::make('resolution')
                            ->rows(3)
                            ->columnSpanFull()
                            ->required(fn (callable $get) => in_array($get('status'), ['resolved', 'closed']))
                            ->placeholder('Enter resolution details...')
                            ->helperText(fn (callable $get) => 
                                in_array($get('status'), ['resolved', 'closed'])
                                    ? 'Required when status is "Resolved" or "Closed"' 
                                    : 'Optional for other statuses'
                            ),
                            
                        Forms\Components\DateTimePicker::make('resolved_at')
                            ->label('Resolved At')
                            ->disabled()
                            ->dehydrated()
                            ->visible(fn (callable $get) => in_array($get('status'), ['resolved', 'closed']))
                            ->helperText('Auto-set when status changes to Resolved or Closed'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        \Log::info('Admin ComplaintResource table method called');
        
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('room.room_number')
                    ->label('Room')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->limit(40)
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\BadgeColumn::make('category')
                    ->label('Category')
                    ->colors([
                        'danger' => 'noise',
                        'warning' => 'maintenance',
                        'primary' => 'facilities',
                        'secondary' => 'staff',
                        'success' => 'security',
                        'info' => 'cleanliness',
                        'secondary' => 'other',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->sortable(),
                    
                Tables\Columns\BadgeColumn::make('priority')
                    ->label('Priority')
                    ->colors([
                        'secondary' => 'low',
                        'primary' => 'medium',
                        'warning' => 'high',
                        'danger' => 'urgent',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->sortable(),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'investigating',
                        'success' => 'resolved',
                        'secondary' => 'closed',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->default('Unassigned')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Submitted')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'investigating' => 'Investigating',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed'
                    ]),
                    
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'noise' => 'Noise',
                        'maintenance' => 'Maintenance',
                        'facilities' => 'Facilities',
                        'staff' => 'Staff',
                        'security' => 'Security',
                        'cleanliness' => 'Cleanliness',
                        'other' => 'Other'
                    ]),
                    
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent'
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListComplaints::route('/'),
            'create' => Pages\CreateComplaint::route('/create'),
            'view' => Pages\ViewComplaint::route('/{record}'),
            'edit' => Pages\EditComplaint::route('/{record}/edit'),
        ];
    }    
}
