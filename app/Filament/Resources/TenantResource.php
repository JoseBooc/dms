<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Filament\Resources\TenantResource\RelationManagers;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Dormitory Management';

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
                Forms\Components\Select::make('user_id')
                    ->label('Select a tenant to edit')
                    ->options(function () {
                        return \App\Models\User::whereHas('tenant')->pluck('name', 'id');
                    })
                    ->required(fn ($livewire) => !($livewire instanceof \Filament\Resources\Pages\CreateRecord))
                    ->hidden(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                    ->reactive()
                    ->afterStateUpdated(function ($state, $livewire) {
                        if ($state && $livewire instanceof \Filament\Resources\Pages\EditRecord) {
                            // Find the tenant associated with this user
                            $tenant = \App\Models\Tenant::where('user_id', $state)->first();
                            if ($tenant && $tenant->id !== $livewire->record->id) {
                                // Redirect to the edit page of the selected tenant
                                $livewire->redirect(route('filament.resources.tenants.edit', $tenant));
                            }
                        }
                    })
                    ->searchable(),
                
                Forms\Components\Section::make('User Login')
                    ->schema([
                        Forms\Components\Placeholder::make('user_creation_note')
                            ->label('')
                            ->content('ℹ️ **Note:** A new user account will be automatically created with these login details. The user will have "Tenant" role and can be managed later in the User Management page.')
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->unique(table: 'users', column: 'email', ignoreRecord: true)
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                            ->minLength(8)
                            ->label('Login Password'),
                    ])
                    ->columns(2)
                    ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord),
                
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
                        
                        Forms\Components\DatePicker::make('birth_date')
                            ->required(),
                        
                        Forms\Components\Select::make('gender')
                            ->options([
                                'female' => 'Female',
                            ])
                            ->default('female')
                            ->required(),
                        
                        Forms\Components\TextInput::make('nationality')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('occupation')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('school')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('course')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Select::make('civil_status')
                            ->options([
                                'single' => 'Single',
                                'married' => 'Married',
                                'divorced' => 'Divorced',
                                'widowed' => 'Widowed',
                            ])
                            ->required(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\TextInput::make('phone_number')
                            ->tel()
                            ->required()
                            ->maxLength(20),
                        
                        Forms\Components\TextInput::make('alternative_phone')
                            ->tel()
                            ->maxLength(20),
                        
                        Forms\Components\Textarea::make('permanent_address')
                            ->label('Permanent Address')
                            ->required()
                            ->maxLength(500),
                        
                        Forms\Components\Textarea::make('current_address')
                            ->label('Current Address (if different from the Permanent address)')
                            ->maxLength(500),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Emergency Contact Person')
                    ->schema([
                        Forms\Components\TextInput::make('emergency_contact_first_name')
                            ->label('First Name')
                            ->required()
                            ->maxLength(255)
                            ->rules(['regex:/^[a-zA-Z\s\-\']+$/']),
                        
                        Forms\Components\TextInput::make('emergency_contact_middle_name')
                            ->label('Middle Name')
                            ->maxLength(255)
                            ->rules(['regex:/^[a-zA-Z\s\-\']+$/']),
                        
                        Forms\Components\TextInput::make('emergency_contact_last_name')
                            ->label('Last Name')
                            ->required()
                            ->maxLength(255)
                            ->rules(['regex:/^[a-zA-Z\s\-\']+$/']),
                        
                        Forms\Components\TextInput::make('emergency_contact_relationship')
                            ->label('Relationship with Tenant')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('e.g., Mother, Father, Sister, Guardian'),
                        
                        Forms\Components\TextInput::make('emergency_contact_phone')
                            ->label('Phone Number')
                            ->tel()
                            ->required()
                            ->maxLength(20),
                        
                        Forms\Components\TextInput::make('emergency_contact_alternative_phone')
                            ->label('Alternative Phone')
                            ->tel()
                            ->maxLength(20),
                        
                        Forms\Components\Textarea::make('emergency_contact_address')
                            ->label('Address')
                            ->required()
                            ->maxLength(500),
                        
                        Forms\Components\TextInput::make('emergency_contact_email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Identification')
                    ->schema([
                        Forms\Components\Select::make('id_type')
                            ->label('ID Type')
                            ->options([
                                'drivers_license' => 'Driver\'s License',
                                'passport' => 'Passport',
                                'national_id' => 'National ID',
                                'student_id' => 'Student ID',
                                'sss_id' => 'SSS ID',
                                'philhealth_id' => 'PhilHealth ID',
                            ])
                            ->required(),
                        
                        Forms\Components\TextInput::make('id_number')
                            ->label('ID Number')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\FileUpload::make('id_image_path')
                            ->label('ID Image')
                            ->image()
                            ->directory('tenant-ids'),
                        
                        Forms\Components\Textarea::make('remarks')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('personal_email')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('gender'),
                
                Tables\Columns\TextColumn::make('birth_date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                        'other' => 'Other',
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
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }    
}
