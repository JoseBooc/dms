<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantComplaintResource\Pages;
use App\Models\Complaint;
use App\Models\Room;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TenantComplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat';
    
    protected static ?string $navigationLabel = 'Complaints';
    
    protected static ?string $navigationGroup = 'My Requests';
    
    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'title';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->role === 'tenant';
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'tenant';
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'tenant') {
            return false;
        }

        $tenant = $user->tenant;
        if (!$tenant) {
            return false;
        }

        // Check if tenant has a room assignment
        $hasAssignment = \App\Models\RoomAssignment::where('tenant_id', $tenant->id)
            ->whereIn('status', ['active', 'pending', 'inactive'])
            ->exists();

        return $hasAssignment;
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'tenant') {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()->where('tenant_id', $user->id);
    }

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $tenant = $user?->tenant;
        $activeAssignment = null;
        
        if ($tenant) {
            $activeAssignment = \App\Models\RoomAssignment::where('tenant_id', $tenant->id)
                ->whereIn('status', ['active', 'pending', 'inactive'])
                ->first();
        }

        // If no active assignment, show error message
        if (!$activeAssignment) {
            return $form
                ->schema([
                    Forms\Components\Card::make()
                        ->schema([
                            Forms\Components\Placeholder::make('error_message')
                                ->content('You cannot submit a complaint because you do not have a room assignment. Please contact the administration if you believe this is an error.')
                                ->columnSpanFull(),
                        ])
                        ->extraAttributes([
                            'class' => 'bg-red-50 border border-red-200 text-red-800'
                        ]),
                ]);
        }

        return $form
            ->schema([
                Forms\Components\Section::make('Complaint Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Brief title of your complaint'),
                        
                        Forms\Components\Select::make('category')
                            ->options([
                                'noise' => 'Noise Complaint',
                                'cleanliness' => 'Cleanliness Issues',
                                'behavior' => 'Resident Behavior',
                                'facilities' => 'Facility Issues',
                                'safety' => 'Safety Concerns',
                                'billing' => 'Billing Issues',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->placeholder('Select complaint category'),
                        
                        Forms\Components\Select::make('priority')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'urgent' => 'Urgent',
                            ])
                            ->required()
                            ->default('medium')
                            ->placeholder('Select priority level'),
                        
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(4)
                            ->placeholder('Please provide detailed information about your complaint'),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Status Information')
                    ->schema([
                        Forms\Components\Placeholder::make('status')
                            ->label('Current Status')
                            ->content(fn ($record) => $record ? ucwords(str_replace('_', ' ', $record->status)) : 'Pending'),
                            
                        Forms\Components\Placeholder::make('assignedTo.name')
                            ->label('Assigned To')
                            ->content(fn ($record) => $record?->assignedTo?->name ?: 'Not assigned yet'),
                            
                        Forms\Components\Textarea::make('staff_notes')
                            ->label('Investigation Notes')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(3)
                            ->placeholder('No investigation notes yet')
                            ->visible(fn ($record) => $record?->staff_notes)
                            ->helperText('Notes from staff during investigation'),
                            
                        Forms\Components\Textarea::make('actions_taken')
                            ->label('Actions Taken')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(3)
                            ->placeholder('No actions taken yet')
                            ->visible(fn ($record) => $record?->actions_taken)
                            ->helperText('Actions taken by staff to resolve this complaint'),
                            
                        Forms\Components\Textarea::make('resolution')
                            ->label('Resolution')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(3)
                            ->placeholder('No resolution yet')
                            ->visible(fn ($record) => $record?->resolution)
                            ->helperText('Final resolution details'),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record !== null), // Only show in view/edit, not create
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Date Submitted'),
                
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                
                Tables\Columns\TextColumn::make('category')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'noise' => 'Noise Complaint',
                        'cleanliness' => 'Cleanliness Issues',
                        'behavior' => 'Resident Behavior',
                        'facilities' => 'Facility Issues',
                        'safety' => 'Safety Concerns',
                        'billing' => 'Billing Issues',
                        'other' => 'Other',
                        default => ucfirst($state),
                    }),
                
                Tables\Columns\TextColumn::make('priority')
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),
                
                Tables\Columns\TextColumn::make('resolution')
                    ->limit(50)
                    ->formatStateUsing(fn ($state) => $state ?: 'Pending'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'investigating' => 'Investigating',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                    ]),
                
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'noise' => 'Noise Complaint',
                        'cleanliness' => 'Cleanliness Issues',
                        'behavior' => 'Resident Behavior',
                        'facilities' => 'Facility Issues',
                        'safety' => 'Safety Concerns',
                        'billing' => 'Billing Issues',
                        'other' => 'Other',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => in_array($record->status, ['open', 'investigating'])),
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
            'index' => Pages\ListTenantComplaints::route('/'),
            'create' => Pages\CreateTenantComplaint::route('/create'),
            'view' => Pages\ViewTenantComplaint::route('/{record}'),
            'edit' => Pages\EditTenantComplaint::route('/{record}/edit'),
        ];
    }
}