<?php

namespace App\Filament\Resources\DepositResource\RelationManagers;

use App\Models\Bill;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class DeductionsRelationManager extends RelationManager
{
    protected static string $relationship = 'deductions';

    protected static ?string $recordTitleAttribute = 'description';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('deduction_type')
                            ->label('Deduction Type')
                            ->options([
                                'unpaid_rent' => 'Unpaid Rent',
                                'damage_charge' => 'Damage Charge',
                                'cleaning_fee' => 'Cleaning Fee',
                                'utility_arrears' => 'Utility Arrears',
                                'other' => 'Other',
                            ])
                            ->required(),

                        Forms\Components\Select::make('bill_id')
                            ->label('Related Bill (Optional)')
                            ->options(function (RelationManager $livewire) {
                                $deposit = $livewire->ownerRecord;
                                return Bill::where('tenant_id', $deposit->tenant_id)
                                    ->get()
                                    ->mapWithKeys(fn($bill) => [
                                        $bill->id => "Bill #{$bill->id} - ₱" . number_format($bill->total_amount, 2) . " ({$bill->status})"
                                    ]);
                            })
                            ->searchable(),
                    ]),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->label('Deduction Amount')
                            ->required()
                            ->numeric()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->prefix('₱'),

                        Forms\Components\DatePicker::make('deduction_date')
                            ->label('Deduction Date')
                            ->required()
                            ->default(now()),
                    ]),

                Forms\Components\TextInput::make('description')
                    ->label('Description')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('details')
                    ->label('Details')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Hidden::make('processed_by')
                    ->default(auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('deduction_type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'unpaid_rent' => 'Unpaid Rent',
                        'damage_charge' => 'Damage Charge',
                        'cleaning_fee' => 'Cleaning Fee',
                        'utility_arrears' => 'Utility Arrears',
                        'other' => 'Other',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->colors([
                        'danger' => 'unpaid_rent',
                        'warning' => 'damage_charge',
                        'secondary' => 'cleaning_fee',
                        'primary' => 'utility_arrears',
                        'success' => 'other',
                    ]),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state) => '₱' . number_format($state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('bill.id')
                    ->label('Related Bill')
                    ->formatStateUsing(fn ($state) => $state ? "Bill #{$state}" : '—'),

                Tables\Columns\TextColumn::make('deduction_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('processedBy.first_name')
                    ->label('Processed By')
                    ->formatStateUsing(fn ($record) => $record->processedBy->first_name . ' ' . $record->processedBy->last_name),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('deduction_type')
                    ->options([
                        'unpaid_rent' => 'Unpaid Rent',
                        'damage_charge' => 'Damage Charge',
                        'cleaning_fee' => 'Cleaning Fee',
                        'utility_arrears' => 'Utility Arrears',
                        'other' => 'Other',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (RelationManager $livewire, array $data) {
                        $deposit = $livewire->ownerRecord;
                        
                        // Create the deduction
                        $deduction = $deposit->deductions()->create($data);
                        
                        // Update deposit totals
                        $deposit->deductions_total += $data['amount'];
                        $deposit->updateRefundableAmount();
                        $deposit->updateStatus();
                        
                        return $deduction;
                    })
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Deduction added successfully')
                            ->body('The deposit amounts have been updated.')
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->using(function (RelationManager $livewire, $record) {
                        $deposit = $livewire->ownerRecord;
                        
                        // Update deposit totals before deleting
                        $deposit->deductions_total -= $record->amount;
                        $deposit->updateRefundableAmount();
                        $deposit->updateStatus();
                        
                        $record->delete();
                    })
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Deduction removed successfully')
                            ->body('The deposit amounts have been updated.')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => auth()->user()->isAdmin()),
            ])
            ->defaultSort('deduction_date', 'desc');
    }    
}
