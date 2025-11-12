<?php

namespace App\Http\Middleware;

use App\Services\AuthenticationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Only check authenticated users
        if (Auth::check()) {
            $user = Auth::user();

            // Check if user status allows access
            if (!AuthenticationService::checkUserStatusBeforeLogin($user)) {
                // Log the blocked access attempt
                AuthenticationService::logBlockedLoginAttempt(
                    $user,
                    $request->ip(),
                    $request->userAgent()
                );

                // Logout the user
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Redirect with error message
                return redirect()->route('login')->withErrors([
                    'email' => AuthenticationService::getBlockedLoginMessage($user),
                ]);
            }
        }

        return $next($request);
    }
}
