<?php

namespace App\Filament\Resources\BillResource\Pages;

use App\Filament\Resources\BillResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\User;
use App\Notifications\PaymentConfirmationNotification;

class EditBill extends EditRecord
{
    protected static string $resource = BillResource::class;

    protected ?string $heading = 'Edit Bill';

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $bill = $this->record;
        $originalData = $this->record->getOriginal();
        
        // Check if amount_paid was updated (payment was made)
        if ($bill->amount_paid > $originalData['amount_paid']) {
            $paidAmount = $bill->amount_paid - $originalData['amount_paid'];
            
            // Notify all admins about the payment confirmation
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new PaymentConfirmationNotification($bill, $paidAmount));
            }
            
            // Also notify the tenant about their payment being processed
            $tenant = User::find($bill->tenant_id);
            if ($tenant) {
                $tenant->notify(new PaymentConfirmationNotification($bill, $paidAmount));
            }
        }
    }
}