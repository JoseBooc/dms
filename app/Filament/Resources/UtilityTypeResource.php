<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UtilityTypeResource\Pages;
use App\Filament\Resources\UtilityTypeResource\RelationManagers;
use App\Models\UtilityType;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UtilityTypeResource extends Resource
{
    protected static ?string $model = UtilityType::class;

    protected static ?string $navigationIcon = 'heroicon-o-lightning-bolt';

    protected static ?string $navigationGroup = 'Utilities Management';

    protected static ?string $navigationLabel = 'Utility Types';

    public static function shouldRegisterNavigation(): bool
    {
        return false; // Hidden since we only use Electricity and Water
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->isAdmin() || auth()->user()->isStaff();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('name')
                    ->label('Utility Type')
                    ->options([
                        'Electricity' => 'Electricity',
                        'Water' => 'Water',
                    ])
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->reactive()
                    ->afterStateUpdated(function (callable $set, $state) {
                        // Auto-set appropriate unit based on utility type
                        if ($state === 'Electricity') {
                            $set('unit', 'kWh');
                        } elseif ($state === 'Water') {
                            $set('unit', 'Cubic meter (m³)');
                        }
                    }),
                
                Forms\Components\TextInput::make('unit')
                    ->label('Unit of Measurement')
                    ->required()
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Automatically set based on utility type'),
                
                Forms\Components\Textarea::make('description')
                    ->maxLength(500)
                    ->rows(3),
                
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->required()
                    ->default('active'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('unit')
                    ->label('Unit')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'secondary' => 'inactive',
                    ]),
                
                Tables\Columns\TextColumn::make('readings_count')
                    ->counts('readings')
                    ->label('Readings'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
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
            'index' => Pages\ListUtilityTypes::route('/'),
            'create' => Pages\CreateUtilityType::route('/create'),
            'edit' => Pages\EditUtilityType::route('/{record}/edit'),
        ];
    }    
}
