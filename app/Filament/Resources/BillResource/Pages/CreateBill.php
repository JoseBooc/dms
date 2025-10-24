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

    protected function afterCreate(): void
    {
        // Get the created bill record
        $bill = $this->record;
        
        // Find the tenant associated with this bill
        $tenant = User::find($bill->tenant_id);
        
        if ($tenant) {
            // Send notification to the tenant
            $tenant->notify(new NewBillNotification($bill));
        }
        
        // Also notify all admins about the new bill creation
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\PaymentConfirmationNotification($bill, $bill->total_amount));
        }
    }
}