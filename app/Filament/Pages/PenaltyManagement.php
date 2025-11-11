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
                        Select::make('penalty_type')
                            ->label('Penalty Type')
                            ->options([
                                'daily_fixed' => 'Daily Fixed Amount (₱/day)',
                                'percentage' => 'Percentage of Bill',
                                'flat_fee' => 'One-Time Flat Fee',
                            ])
                            ->required()
                            ->default('daily_fixed')
                            ->reactive()
                            ->helperText('Choose how penalties are calculated'),
                        
                        TextInput::make('penalty_rate')
                            ->label(fn ($get) => match($get('penalty_type')) {
                                'daily_fixed' => 'Penalty per Day (₱)',
                                'percentage' => 'Penalty Percentage (%)',
                                'flat_fee' => 'Flat Fee Amount (₱)',
                                default => 'Penalty Rate'
                            })
                            ->numeric()
                            ->step(0.01)
                            ->required()
                            ->default(50)
                            ->minValue(0)
                            ->maxValue(fn ($get) => $get('penalty_type') === 'percentage' ? 100 : 999999)
                            ->helperText(fn ($get) => match($get('penalty_type')) {
                                'daily_fixed' => 'Amount charged per day after grace period (Recommended: ₱50)',
                                'percentage' => 'Percentage of total bill (Recommended: 3%, Max: 100%)',
                                'flat_fee' => 'One-time fee when overdue (Recommended: ₱200)',
                                default => ''
                            }),
                        
                        TextInput::make('grace_period_days')
                            ->label('Grace Period (Days)')
                            ->numeric()
                            ->required()
                            ->default(3)
                            ->minValue(0)
                            ->maxValue(30)
                            ->helperText('Days after due date before penalty applies (Recommended: 3 days)'),
                        
                        TextInput::make('max_penalty')
                            ->label('Maximum Penalty Amount (₱)')
                            ->numeric()
                            ->step(0.01)
                            ->required()
                            ->default(500)
                            ->minValue(0)
                            ->helperText('Cap on total penalty amount (Recommended: ₱500)'),
                        
                        Toggle::make('active')
                            ->label('Active')
                            ->required()
                            ->default(true)
                            ->helperText('Enable or disable penalty calculation'),
                    ]),
                ])
                ->mountUsing(function ($form) {
                    $setting = $this->penaltySetting;
                    
                    // Always fill form with defaults (either from existing setting or default values)
                    $form->fill([
                        'penalty_type' => $setting->penalty_type ?? 'daily_fixed',
                        'penalty_rate' => $setting->penalty_rate ?? 50,
                        'grace_period_days' => $setting->grace_period_days ?? 3,
                        'max_penalty' => $setting->max_penalty ?? 500,
                        'active' => $setting->active ?? true, // Always set, defaults to true
                    ]);
                })
                ->action(function (array $data) {
                    // Deactivate all other penalty settings first
                    PenaltySetting::where('name', '!=', 'late_payment_penalty')->update(['active' => false]);
                    
                    // Update existing or create new penalty setting
                    PenaltySetting::updateOrCreate(
                        ['name' => 'late_payment_penalty'], // Find by name
                        [
                            'description' => 'Late payment penalty for overdue bills',
                            'penalty_type' => $data['penalty_type'],
                            'penalty_rate' => $data['penalty_rate'],
                            'grace_period_days' => $data['grace_period_days'],
                            'max_penalty' => $data['max_penalty'],
                            'active' => $data['active'] ?? true,
                        ]
                    );
                    
                    Notification::make()
                        ->title('Penalty Settings Updated')
                        ->body('New penalty rules have been applied successfully.')
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
