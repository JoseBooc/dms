<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantMaintenanceRequestResource\Pages;
use App\Models\MaintenanceRequest;
use App\Models\RoomAssignment;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TenantMaintenanceRequestResource extends Resource
{
    protected static ?string $model = MaintenanceRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-document';
    
    protected static ?string $navigationLabel = 'Maintenance Requests';
    
    protected static ?string $navigationGroup = 'My Requests';
    
    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'description';

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
        $tenant = $user?->tenant;
        
        if (!$tenant) {
            return parent::getEloquentQuery()->whereRaw('1 = 0'); // Return empty query
        }
        
        return parent::getEloquentQuery()->where('tenant_id', $tenant->id);
    }

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $tenant = $user?->tenant;
        $currentAssignment = null;
        
        if ($tenant) {
            $currentAssignment = RoomAssignment::where('tenant_id', $tenant->id)
                ->where('status', 'active')
                ->with('room')
                ->first();
        }

        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('area')
                            ->label('Area/Location*')
                            ->placeholder('e.g., Bathroom, Kitchen, Bedroom')
                            ->required()
                            ->maxLength(100),
                            
                        Forms\Components\Select::make('priority')
                            ->label('Priority Level*')
                            ->options([
                                'low' => 'Low - Minor issue, not urgent',
                                'medium' => 'Medium - Needs attention soon',
                                'high' => 'High - Urgent repair needed',
                                'emergency' => 'Emergency - Safety concern'
                            ])
                            ->required()
                            ->default('medium'),
                    ]),
                    
                Forms\Components\Textarea::make('description')
                    ->label('Problem Description*')
                    ->placeholder('Please describe the maintenance issue in detail...')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
                    
                Forms\Components\FileUpload::make('photos')
                    ->label('Photos (Optional)')
                    ->image()
                    ->multiple()
                    ->maxFiles(5)
                    ->disk('public')
                    ->directory('maintenance-photos')
                    ->helperText('Upload up to 5 photos to help describe the issue')
                    ->columnSpanFull(),
                    
                // Hidden fields that will be auto-filled
                Forms\Components\Hidden::make('tenant_id')
                    ->default($tenant?->id),
                    
                Forms\Components\Hidden::make('room_id')
                    ->default($currentAssignment?->room_id),
                    
                Forms\Components\Hidden::make('status')
                    ->default('pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date Submitted')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('area')
                    ->label('Area')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('description')
                    ->label('Issue Description')
                    ->limit(50)
                    ->searchable(),
                    
                Tables\Columns\BadgeColumn::make('priority')
                    ->label('Priority')
                    ->colors([
                        'secondary' => 'low',
                        'warning' => 'medium', 
                        'danger' => 'high',
                        'danger' => 'emergency',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state))),
                    
                Tables\Columns\ImageColumn::make('photos')
                    ->label('Photos')
                    ->disk('public')
                    ->size(40)
                    ->getStateUsing(function ($record) {
                        return $record->photos ? $record->photos[0] ?? null : null;
                    }),
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
                        'emergency' => 'Emergency',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->status === 'pending'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenantMaintenanceRequests::route('/'),
            'create' => Pages\CreateTenantMaintenanceRequest::route('/create'),
            'view' => Pages\ViewTenantMaintenanceRequest::route('/{record}'),
            'edit' => Pages\EditTenantMaintenanceRequest::route('/{record}/edit'),
        ];
    }
}
