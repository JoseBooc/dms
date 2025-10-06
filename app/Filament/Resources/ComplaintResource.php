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

    protected static ?string $navigationLabel = 'Complaints';

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
                Forms\Components\Section::make('Complaint Details')
                    ->schema([
                        Forms\Components\Select::make('tenant_id')
                            ->label('Tenant')
                            ->options(User::where('role', 'tenant')->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                            
                        Forms\Components\Select::make('room_id')
                            ->label('Room')
                            ->options(Room::all()->pluck('room_number', 'id'))
                            ->searchable()
                            ->preload(),
                            
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                            
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(4),
                            
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
                            ->default('other'),
                            
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
                            ->default('pending'),
                            
                        Forms\Components\Select::make('assigned_to')
                            ->label('Assigned To')
                            ->options(User::whereIn('role', ['admin', 'staff'])->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                            
                        Forms\Components\Textarea::make('resolution')
                            ->rows(3)
                            ->columnSpanFull(),
                            
                        Forms\Components\DateTimePicker::make('resolved_at')
                            ->label('Resolved At'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('room.room_number')
                    ->label('Room')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(30),
                    
                Tables\Columns\BadgeColumn::make('category')
                    ->colors([
                        'secondary' => 'other',
                        'warning' => 'noise',
                        'danger' => 'maintenance',
                        'primary' => 'facilities',
                        'success' => 'staff',
                        'info' => 'security',
                        'gray' => 'cleanliness',
                    ]),
                    
                Tables\Columns\BadgeColumn::make('priority')
                    ->colors([
                        'secondary' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'primary' => 'urgent',
                    ]),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'investigating',
                        'success' => 'resolved',
                        'secondary' => 'closed',
                    ]),
                    
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->default('Unassigned'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),
            ])
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
