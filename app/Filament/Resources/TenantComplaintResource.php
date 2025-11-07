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