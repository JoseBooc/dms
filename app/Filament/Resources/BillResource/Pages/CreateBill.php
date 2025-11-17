<?php

namespace App\Filament\Resources\BillResource\Pages;

use App\Filament\Resources\BillResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;
use App\Notifications\NewBillNotification;

class CreateBill extends CreateRecord
{
    protected static string $resource = BillResource::class;

    protected ?string $heading = 'Create Bill';

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

    protected function afterCreate(): void
    {
        // Get the created bill record
        $bill = $this->record;
        
        // Link utility readings to this bill and update their status
        $this->linkUtilityReadings($bill);
        
        // Find the tenant associated with this bill
        $tenant = User::find($bill->tenant_id);
        
        if ($tenant && $tenant->role === 'tenant') {
            // Send notification to the tenant
            $tenant->notify(new NewBillNotification($bill));
        }
        
        // Also notify all admins about the new bill creation
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\PaymentConfirmationNotification($bill, $bill->total_amount));
        }
    }

    /**
     * Link utility readings to the bill and update their status
     */
    protected function linkUtilityReadings($bill): void
    {
        // Find recent utility readings for this tenant and room that aren't billed yet
        $utilityReadings = \App\Models\UtilityReading::where('tenant_id', $bill->tenant_id)
            ->where('room_id', $bill->room_id)
            ->whereNull('bill_id')
            ->where('status', 'pending')
            ->where('reading_date', '<=', $bill->bill_date)
            ->get();

        foreach ($utilityReadings as $reading) {
            $reading->update([
                'bill_id' => $bill->id,
                'status' => 'billed',
            ]);
        }
    }
}