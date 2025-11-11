<?php

namespace App\Filament\Resources\DepositResource\Pages;

use App\Filament\Resources\DepositResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeposit extends EditRecord
{
    protected static string $resource = DepositResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Mutate form data before filling the form
     * Ensure refundable amount is correctly calculated
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Ensure refundable amount is correctly calculated on load
        $amount = max(0, floatval($data['amount'] ?? 0));
        $deductions = max(0, floatval($data['deductions_total'] ?? 0));
        $data['refundable_amount'] = max(0, $amount - $deductions);
        
        return $data;
    }

    /**
     * Mutate form data before saving the record
     * Enforce business logic: refundable_amount = max(0, amount - deductions_total)
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure non-negative values
        $data['amount'] = max(0, floatval($data['amount'] ?? 0));
        $data['deductions_total'] = max(0, floatval($data['deductions_total'] ?? 0));
        
        // Always recalculate refundable amount using business logic
        $data['refundable_amount'] = max(0, $data['amount'] - $data['deductions_total']);
        
        return $data;
    }
}
