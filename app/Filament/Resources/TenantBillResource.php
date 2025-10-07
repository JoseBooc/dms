<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantBillResource\Pages;
use App\Filament\Resources\TenantBillResource\RelationManagers;
use App\Models\Bill;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class TenantBillResource extends Resource
{
    protected static ?string $model = Bill::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationLabel = 'My Bills';
    
    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'tenant';
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        $tenant = $user->tenant;
        
        if (!$tenant) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }
        
        return parent::getEloquentQuery()->where('tenant_id', $tenant->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\DatePicker::make('bill_date')
                            ->label('Bill Date')
                            ->disabled(),
                        Forms\Components\DatePicker::make('due_date')
                            ->label('Due Date')
                            ->disabled(),
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->prefix('₱')
                            ->disabled(),
                        Forms\Components\TextInput::make('amount_paid')
                            ->label('Amount Paid')
                            ->prefix('₱')
                            ->disabled(),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'unpaid' => 'Unpaid',
                                'partially_paid' => 'Partially Paid',
                                'paid' => 'Paid',
                            ])
                            ->disabled(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->disabled(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bill_date')
                    ->label('Bill Date')
                    ->date('M j, Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->prefix('₱')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_paid')
                    ->label('Amount Paid')
                    ->prefix('₱')
                    ->default('0.00'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'paid',
                        'warning' => 'partially_paid',
                        'danger' => 'unpaid',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date('M j, Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'partially_paid' => 'Partially Paid',
                        'paid' => 'Paid',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Remove bulk actions for tenant view
            ])
            ->defaultSort('bill_date', 'desc');
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
            'index' => Pages\ListTenantBills::route('/'),
            'view' => Pages\ViewTenantBill::route('/{record}'),
        ];
    }    
}
