<?php

namespace App\Filament\Resources\TestBillResource\Pages;

use App\Filament\Resources\TestBillResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTestBill extends CreateRecord
{
    protected static string $resource = TestBillResource::class;

    protected ?string $heading = 'Create Bill';
}