<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AuthenticationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BlockedUserAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that active users can login
     */
    public function test_active_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'active@example.com',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'email' => 'active@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test that blocked users cannot login
     */
    public function test_blocked_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'blocked@example.com',
            'password' => Hash::make('password'),
            'status' => 'blocked',
        ]);

        $response = $this->post('/login', [
            'email' => 'blocked@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * Test that inactive users cannot login
     */
    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => Hash::make('password'),
            'status' => 'inactive',
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * Test AuthenticationService checkUserStatusBeforeLogin method
     */
    public function test_authentication_service_checks_user_status(): void
    {
        $activeUser = User::factory()->create(['status' => 'active']);
        $blockedUser = User::factory()->create(['status' => 'blocked']);
        $inactiveUser = User::factory()->create(['status' => 'inactive']);

        $this->assertTrue(AuthenticationService::checkUserStatusBeforeLogin($activeUser));
        $this->assertFalse(AuthenticationService::checkUserStatusBeforeLogin($blockedUser));
        $this->assertFalse(AuthenticationService::checkUserStatusBeforeLogin($inactiveUser));
    }

    /**
     * Test User model canLogin method
     */
    public function test_user_model_can_login_method(): void
    {
        $activeUser = User::factory()->create(['status' => 'active']);
        $blockedUser = User::factory()->create(['status' => 'blocked']);
        $inactiveUser = User::factory()->create(['status' => 'inactive']);

        $this->assertTrue($activeUser->canLogin());
        $this->assertFalse($blockedUser->canLogin());
        $this->assertFalse($inactiveUser->canLogin());
    }

    /**
     * Test User model status check methods
     */
    public function test_user_model_status_check_methods(): void
    {
        $activeUser = User::factory()->create(['status' => 'active']);
        $blockedUser = User::factory()->create(['status' => 'blocked']);
        $inactiveUser = User::factory()->create(['status' => 'inactive']);

        // Active user
        $this->assertTrue($activeUser->isActive());
        $this->assertFalse($activeUser->isBlocked());
        $this->assertFalse($activeUser->isInactive());

        // Blocked user
        $this->assertFalse($blockedUser->isActive());
        $this->assertTrue($blockedUser->isBlocked());
        $this->assertFalse($blockedUser->isInactive());

        // Inactive user
        $this->assertFalse($inactiveUser->isActive());
        $this->assertFalse($inactiveUser->isBlocked());
        $this->assertTrue($inactiveUser->isInactive());
    }

    /**
     * Test that blocked user gets appropriate error message
     */
    public function test_blocked_user_error_message(): void
    {
        $user = User::factory()->create([
            'email' => 'blocked@example.com',
            'password' => Hash::make('password'),
            'status' => 'blocked',
        ]);

        $response = $this->post('/login', [
            'email' => 'blocked@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'Your account has been blocked. Please contact the administrator.'
        ]);
    }

    /**
     * Test that inactive user gets appropriate error message
     */
    public function test_inactive_user_error_message(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => Hash::make('password'),
            'status' => 'inactive',
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'Your account is inactive. Please contact the administrator to reactivate your account.'
        ]);
    }

    /**
     * Test API endpoint blocks inactive/blocked users
     */
    public function test_api_endpoint_blocks_inactive_users(): void
    {
        $blockedUser = User::factory()->create([
            'status' => 'blocked',
        ]);

        $response = $this->actingAs($blockedUser, 'sanctum')
            ->getJson('/api/user');

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'account_blocked_or_inactive',
        ]);
    }
}
