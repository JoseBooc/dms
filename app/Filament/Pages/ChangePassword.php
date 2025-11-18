<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class ChangePassword extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static string $view = 'filament.pages.change-password';
    protected static ?string $title = 'Change Password';
    protected static ?string $navigationLabel = 'Change Password';
    protected static bool $shouldRegisterNavigation = false; // Hide from navigation
    
    public $current_password = '';
    public $password = '';
    public $password_confirmation = '';

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('current_password')
                ->label('Current Password')
                ->password()
                ->required()
                ->rules(['required', 'string'])
                ->validationAttribute('current password')
                ->helperText('Enter your current password to confirm your identity.')
                ->columnSpanFull(),

            TextInput::make('password')
                ->label('New Password')
                ->password()
                ->required()
                ->rules([Password::default()])
                ->validationAttribute('new password')
                ->helperText('Must be at least 8 characters long.')
                ->columnSpanFull(),

            TextInput::make('password_confirmation')
                ->label('Confirm New Password')
                ->password()
                ->required()
                ->same('password')
                ->validationAttribute('password confirmation')
                ->helperText('Re-enter your new password to confirm.')
                ->columnSpanFull(),
        ];
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        
        // Additional validation
        if (empty($data['current_password']) || empty($data['password']) || empty($data['password_confirmation'])) {
            Notification::make()
                ->title('Error')
                ->body('All fields are required.')
                ->danger()
                ->send();
            return;
        }
        
        if ($data['password'] !== $data['password_confirmation']) {
            Notification::make()
                ->title('Error')
                ->body('New password and confirmation do not match.')
                ->danger()
                ->send();
            return;
        }

        // Verify current password
        if (!Hash::check($data['current_password'], Auth::user()->password)) {
            Notification::make()
                ->title('Error')
                ->body('Current password is incorrect.')
                ->danger()
                ->send();
            return;
        }
        
        // Check if new password is different from current password
        if (Hash::check($data['password'], Auth::user()->password)) {
            Notification::make()
                ->title('Error')
                ->body('New password must be different from your current password.')
                ->warning()
                ->send();
            return;
        }

        // Update password
        Auth::user()->update([
            'password' => Hash::make($data['password'])
        ]);

        // Reset form
        $this->reset(['current_password', 'password', 'password_confirmation']);
        $this->form->fill();

        Notification::make()
            ->title('Success')
            ->body('Your password has been updated successfully.')
            ->success()
            ->send();
    }

    public function getCancelButtonUrlProperty(): string
    {
        return url()->previous() ?: '/';
    }
}
