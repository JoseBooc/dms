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

    protected static ?string $slug = 'admin-maintenance-requests';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->isAdmin() || auth()->user()->isStaff();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->isAdmin() || auth()->user()->isStaff();
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = MaintenanceRequest::query();
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
                            ->searchable(),
                        
                        Forms\Components\Select::make('room_id')
                            ->relationship('room', 'room_number')
                            ->required()
                            ->searchable(),
                        
                        Forms\Components\TextInput::make('area')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Bathroom, Kitchen, Bedroom'),
                        
                        Forms\Components\Select::make('priority')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'urgent' => 'Urgent',
                            ])
                            ->required()
                            ->default('medium'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Request Details')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->maxLength(1000)
                            ->rows(4)
                            ->columnSpanFull(),
                        
                        Forms\Components\FileUpload::make('photos')
                            ->image()
                            ->multiple()
                            ->directory('maintenance-photos')
                            ->maxFiles(5)
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Assignment & Status')
                    ->schema([
                        Forms\Components\Select::make('assigned_to')
                            ->relationship('assignee', 'name')
                            ->searchable()
                            ->placeholder('Assign to staff member'),
                        
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('pending'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        \Log::info('ADMIN MaintenanceRequestResource table method called');
        
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Request #')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tenant.first_name')
                    ->label('Tenant')
                    ->formatStateUsing(fn ($record) => $record->tenant ? 
                        $record->tenant->first_name . ' ' . $record->tenant->last_name : 'Unknown'),
                Tables\Columns\TextColumn::make('room.room_number')
                    ->label('Room')
                    ->sortable(),
                Tables\Columns\TextColumn::make('area')
                    ->label('Area'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50),
                Tables\Columns\TextColumn::make('priority')
                    ->label('Priority')
                    ->formatStateUsing(fn ($state) => $state ? ucfirst($state) : ''),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state ? ucwords(str_replace('_', ' ', $state)) : ''),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Submitted'),
            ])
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
            'edit' => Pages\EditMaintenanceRequest::route('/{record}/edit'),
        ];
    }    
}
