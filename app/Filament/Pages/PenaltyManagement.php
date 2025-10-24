<?php

namespace App\Filament\Pages;

use App\Models\Bill;
use App\Models\PenaltySetting;
use App\Services\PenaltyService;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Pages\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class PenaltyManagement extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation';
    protected static string $view = 'filament.pages.penalty-management';
    protected static ?string $title = 'Penalties';
    protected static ?string $navigationLabel = 'Penalties';
    protected static ?string $navigationGroup = 'Financial Management';
    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'admin';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->role === 'admin';
    }

    protected PenaltyService $penaltyService;

    public function boot(): void
    {
        $this->penaltyService = app(PenaltyService::class);
    }

    protected function getActions(): array
    {
        return [
            Action::make('edit_settings')
                ->label('Edit Penalty Settings')
                ->icon('heroicon-o-cog')
                ->color('primary')
                ->form([
                    Grid::make(2)->schema([
                        Select::make('type')
                            ->label('Penalty Type')
                            ->options([
                                'percentage' => 'Percentage',
                                'fixed' => 'Fixed Amount',
                            ])
                            ->required()
                            ->default('percentage'),
                        
                        TextInput::make('value')
                            ->label('Penalty Rate')
                            ->numeric()
                            ->step(0.01)
                            ->required()
                            ->default(0.05),
                        
                        TextInput::make('grace_period_days')
                            ->label('Grace Period (Days)')
                            ->numeric()
                            ->default(3),
                        
                        TextInput::make('max_penalty_amount')
                            ->label('Max Penalty Amount')
                            ->numeric()
                            ->step(0.01)
                            ->default(1000),
                        
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),
                ])
                ->action(function (array $data) {
                    // Deactivate existing settings
                    PenaltySetting::where('name', 'late_payment_penalty')->update(['is_active' => false]);
                    
                    // Create new setting
                    PenaltySetting::create([
                        'name' => 'late_payment_penalty',
                        'description' => 'Late payment penalty for overdue bills',
                        'type' => $data['type'],
                        'value' => $data['value'],
                        'grace_period_days' => $data['grace_period_days'],
                        'max_penalty_amount' => $data['max_penalty_amount'],
                        'is_active' => $data['is_active'],
                    ]);
                    
                    Notification::make()
                        ->title('Settings Updated')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getBillsProperty()
    {
        return Bill::where('bills.due_date', '<', now())
            ->whereIn('bills.status', ['unpaid', 'partially_paid'])
            ->leftJoin('users', 'bills.tenant_id', '=', 'users.id')
            ->leftJoin('rooms', 'bills.room_id', '=', 'rooms.id')
            ->select('bills.*', 'users.name as tenant_name', 'rooms.room_number')
            ->orderBy('bills.due_date', 'asc')
            ->get();
    }

    public function getPenaltySettingProperty()
    {
        return PenaltySetting::getActiveSetting('late_payment_penalty');
    }

    public function getOverdueBillsCountProperty()
    {
        return $this->bills->count();
    }

    public function getTotalPenaltiesProperty()
    {
        return $this->bills->sum('penalty_amount');
    }
}
