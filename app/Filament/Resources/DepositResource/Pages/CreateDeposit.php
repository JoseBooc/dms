<?php

namespace App\Filament\Resources\DepositResource\Pages;

use App\Filament\Resources\DepositResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDeposit extends CreateRecord
{
    protected static string $resource = DepositResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    /**
     * Mutate form data before creating the record
     * Enforce business logic: refundable_amount = max(0, amount - deductions_total)
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure non-negative values
        $data['amount'] = max(0, floatval($data['amount'] ?? 0));
        $data['deductions_total'] = max(0, floatval($data['deductions_total'] ?? 0));
        
        // Always recalculate refundable amount using business logic
        $data['refundable_amount'] = max(0, $data['amount'] - $data['deductions_total']);
        
        return $data;
    }

    /**
     * Custom validation rules
     */
    protected function getFormSchema(): array
    {
        return parent::getFormSchema();
    }
}
