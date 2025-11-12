<?php

namespace App\Filament\Pages\Auth;

use App\Services\AuthenticationService;
use Filament\Facades\Filament;
use Filament\Http\Livewire\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;

class Login extends BaseLogin
{
    /**
     * Attempt to authenticate the user.
     *
     * @throws TooManyRequestsException
     */
    public function authenticate(): ?\Filament\Http\Responses\Auth\Contracts\LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            throw ValidationException::withMessages([
                'email' => __('filament::login.messages.throttled', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]),
            ]);
        }

        $data = $this->form->getState();

        // Attempt authentication
        if (!Filament::auth()->attempt([
            'email' => $data['email'],
            'password' => $data['password'],
        ], $data['remember'] ?? false)) {
            throw ValidationException::withMessages([
                'email' => __('filament::login.messages.failed'),
            ]);
        }

        // Check if user status allows login
        $user = Filament::auth()->user();
        
        if (!AuthenticationService::checkUserStatusBeforeLogin($user)) {
            // Log the blocked attempt
            AuthenticationService::logBlockedLoginAttempt(
                $user,
                request()->ip(),
                request()->userAgent()
            );

            // Logout immediately
            Filament::auth()->logout();

            throw ValidationException::withMessages([
                'email' => AuthenticationService::getBlockedLoginMessage($user),
            ]);
        }

        return app(\Filament\Http\Responses\Auth\Contracts\LoginResponse::class);
    }
}
