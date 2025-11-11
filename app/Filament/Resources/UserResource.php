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
                                'blocked' => 'Blocked',
                                'inactive' => 'Inactive',
                                'suspended' => 'Suspended',
                            ])
                            ->required()
                            ->default('active')
                            ->hidden(fn (string $context): bool => $context === 'create')
                            ->helperText('Set to "Blocked" to prevent user login'),
                        
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
                            ->required()
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
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'blocked',
                        'warning' => 'inactive',
                        'secondary' => 'suspended',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'active' => 'Active',
                        'blocked' => 'Blocked',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                        default => ucfirst($state),
                    }),
                
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
                        'blocked' => 'Blocked',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('toggleBlock')
                    ->label(fn (User $record): string => $record->status === 'blocked' ? 'Unblock' : 'Block')
                    ->icon(fn (User $record): string => $record->status === 'blocked' ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
                    ->color(fn (User $record): string => $record->status === 'blocked' ? 'success' : 'danger')
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record): string => $record->status === 'blocked' ? 'Unblock User' : 'Block User')
                    ->modalSubheading(fn (User $record): string => 
                        $record->status === 'blocked' 
                            ? 'Are you sure you want to unblock this user? They will be able to log in again.'
                            : 'Are you sure you want to block this user? They will not be able to log in.'
                    )
                    ->action(function (User $record) {
                        if ($record->status === 'blocked') {
                            $record->update(['status' => 'active']);
                            \Filament\Notifications\Notification::make()
                                ->title('User Unblocked')
                                ->success()
                                ->body("User {$record->name} has been unblocked successfully.")
                                ->send();
                        } else {
                            $record->update(['status' => 'blocked']);
                            \Filament\Notifications\Notification::make()
                                ->title('User Blocked')
                                ->warning()
                                ->body("User {$record->name} has been blocked successfully.")
                                ->send();
                        }
                    })
                    ->visible(fn (User $record): bool => 
                        auth()->user()->isAdmin() && 
                        auth()->id() !== $record->id && // Can't block yourself
                        $record->role !== 'admin' // Can't block other admins
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('blockSelected')
                    ->label('Block Selected')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Block Selected Users')
                    ->modalSubheading('Are you sure you want to block the selected users? They will not be able to log in.')
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                        $blocked = 0;
                        foreach ($records as $record) {
                            // Don't block yourself or other admins
                            if (auth()->id() !== $record->id && $record->role !== 'admin') {
                                $record->update(['status' => 'blocked']);
                                $blocked++;
                            }
                        }
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Users Blocked')
                            ->success()
                            ->body("{$blocked} user(s) have been blocked successfully.")
                            ->send();
                    }),
                
                Tables\Actions\BulkAction::make('unblockSelected')
                    ->label('Unblock Selected')
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Unblock Selected Users')
                    ->modalSubheading('Are you sure you want to unblock the selected users? They will be able to log in again.')
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                        $unblocked = 0;
                        foreach ($records as $record) {
                            if ($record->status === 'blocked') {
                                $record->update(['status' => 'active']);
                                $unblocked++;
                            }
                        }
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Users Unblocked')
                            ->success()
                            ->body("{$unblocked} user(s) have been unblocked successfully.")
                            ->send();
                    }),
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
