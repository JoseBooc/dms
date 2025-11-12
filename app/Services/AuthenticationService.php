<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthenticationService
{
    /**
     * Check if user status allows login
     * 
     * @param User $user
     * @return bool
     */
    public static function checkUserStatusBeforeLogin(User $user): bool
    {
        // Check if user is blocked or inactive
        if ($user->status === 'blocked' || $user->status === 'inactive') {
            return false;
        }

        return true;
    }

    /**
     * Get appropriate error message based on user status
     * 
     * @param User $user
     * @return string
     */
    public static function getBlockedLoginMessage(User $user): string
    {
        if ($user->status === 'blocked') {
            return 'Your account has been blocked. Please contact the administrator.';
        }

        if ($user->status === 'inactive') {
            return 'Your account is inactive. Please contact the administrator to reactivate your account.';
        }

        return 'Your account cannot be accessed at this time. Please contact the administrator.';
    }

    /**
     * Log blocked login attempt for auditing
     * 
     * @param User $user
     * @param string $ipAddress
     * @param string $userAgent
     * @return void
     */
    public static function logBlockedLoginAttempt(User $user, string $ipAddress, string $userAgent = null): void
    {
        Log::channel('daily')->warning('Blocked login attempt', [
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'status' => $user->status,
            'role' => $user->role,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'timestamp' => now()->toDateTimeString(),
        ]);

        // Also log to authentication audit log if it exists
        if (class_exists(\App\Services\AuditLogService::class)) {
            try {
                $auditService = app(\App\Services\AuditLogService::class);
                // Create a temporary audit log entry for authentication events
                \App\Models\AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'blocked_login_attempt',
                    'auditable_type' => 'User',
                    'auditable_id' => $user->id,
                    'description' => "Blocked login attempt for {$user->status} account: {$user->email}",
                    'old_values' => null,
                    'new_values' => json_encode([
                        'ip_address' => $ipAddress,
                        'user_agent' => $userAgent,
                        'status' => $user->status,
                        'timestamp' => now()->toDateTimeString(),
                    ]),
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                ]);
            } catch (\Exception $e) {
                // Silently fail if audit logging fails
                Log::debug('Failed to create audit log for blocked login attempt: ' . $e->getMessage());
            }
        }
    }

    /**
     * Validate user can login and throw exception if not
     * 
     * @param User $user
     * @param string $ipAddress
     * @param string $userAgent
     * @throws ValidationException
     * @return void
     */
    public static function validateUserCanLogin(User $user, string $ipAddress, string $userAgent = null): void
    {
        if (!self::checkUserStatusBeforeLogin($user)) {
            // Log the blocked attempt
            self::logBlockedLoginAttempt($user, $ipAddress, $userAgent);

            // Throw validation exception with appropriate message
            throw ValidationException::withMessages([
                'email' => self::getBlockedLoginMessage($user),
            ]);
        }
    }
}
