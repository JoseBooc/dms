<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

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
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->required()
                            ->maxLength(255)
                            ->rules(['regex:/^[a-zA-Z\s\-\']+$/']),
                        
                        Forms\Components\TextInput::make('middle_name')
                            ->maxLength(255)
                            ->rules(['regex:/^[a-zA-Z\s\-\']+$/']),
                        
                        Forms\Components\TextInput::make('last_name')
                            ->required()
                            ->maxLength(255)
                            ->rules(['regex:/^[a-zA-Z\s\-\']+$/']),
                    ])
                    ->columns(3),
                
                Forms\Components\Section::make('Account Information')
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->minLength(8)
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state)),
                        
                        Forms\Components\Select::make('role')
                            ->options(function (string $context, $record = null) {
                                if ($context === 'create') {
                                    return [
                                        'admin' => 'Admin',
                                        'staff' => 'Staff',
                                    ];
                                }
                                
                                // For edit context
                                if ($record && $record->role === 'tenant') {
                                    return [
                                        'tenant' => 'Tenant',
                                    ];
                                }
                                
                                return [
                                    'admin' => 'Admin',
                                    'staff' => 'Staff',
                                ];
                            })
                            ->required()
                            ->default(fn (string $context): string => $context === 'create' ? 'staff' : '')
                            ->disabled(fn (string $context, $record = null): bool => 
                                $context === 'edit' && $record && $record->role === 'tenant'
                            )
                            ->reactive(),
                        
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'suspended' => 'Suspended',
                            ])
                            ->required()
                            ->default('active')
                            ->hidden(fn (string $context): bool => $context === 'create'),
                        
                        Forms\Components\Select::make('gender')
                            ->options(function (callable $get, string $context, $record = null) {
                                $role = $get('role') ?? ($record->role ?? null);
                                
                                if ($role === 'tenant') {
                                    return [
                                        'female' => 'Female',
                                    ];
                                }
                                
                                return [
                                    'male' => 'Male',
                                    'female' => 'Female',
                                    'other' => 'Other',
                                ];
                            })
                            ->default(function (callable $get, string $context, $record = null) {
                                $role = $get('role') ?? ($record->role ?? null);
                                return $role === 'tenant' ? 'female' : null;
                            })
                            ->reactive(),
                        
                        Forms\Components\Placeholder::make('tenant_creation_note')
                            ->label('')
                            ->content('ℹ️ **Note:** Tenant users should be created via the Tenant Management page, which will automatically create the corresponding user account.')
                            ->columnSpanFull()
                            ->visible(fn (string $context): bool => $context === 'create'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Full Name')
                    ->searchable(['first_name', 'middle_name', 'last_name'])
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        return trim(
                            ($record->first_name ?? '') . ' ' . 
                            ($record->middle_name ? $record->middle_name . ' ' : '') . 
                            ($record->last_name ?? '')
                        ) ?: $record->name;
                    }),
                
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('role')
                    ->colors([
                        'danger' => 'admin',
                        'warning' => 'staff',
                        'success' => 'tenant',
                    ]),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'inactive',
                        'danger' => 'suspended',
                    ]),
                
                Tables\Columns\TextColumn::make('gender'),
                
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'staff' => 'Staff',
                        'tenant' => 'Tenant',
                    ]),
                
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
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
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }    
}
