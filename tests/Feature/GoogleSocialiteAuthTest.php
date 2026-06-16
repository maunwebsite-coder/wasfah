<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class GoogleSocialiteAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_register_creates_non_admin_user_and_logs_them_in(): void
    {
        $googleUser = $this->fakeGoogleUser(
            id: 'google-new-user',
            name: 'New Google User',
            email: 'new-google-user@example.com',
        );

        Socialite::shouldReceive('driver')->once()->with('google')->andReturnSelf();
        Socialite::shouldReceive('redirectUrl')->once()->andReturnSelf();
        Socialite::shouldReceive('user')->once()->andReturn($googleUser);

        $response = $this->withSession([
            'auth_login_flow' => 'register_customer',
            'auth_login_intent' => User::ROLE_CUSTOMER,
        ])->get(route('google.callback'));

        $user = User::where('email', 'new-google-user@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame(User::ROLE_CUSTOMER, $user->role);
        $this->assertFalse((bool) $user->is_admin);
        $this->assertFalse($user->isAdmin());
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('policy-consent.show'));
    }

    public function test_google_login_allows_existing_non_admin_user(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'existing-google-user@example.com',
            'is_admin' => false,
            'role' => User::ROLE_CUSTOMER,
            'chef_status' => null,
            'provider' => null,
            'provider_id' => null,
            'provider_token' => null,
        ]);

        $googleUser = $this->fakeGoogleUser(
            id: 'google-existing-user',
            name: 'Existing Google User',
            email: 'existing-google-user@example.com',
        );

        Socialite::shouldReceive('driver')->once()->with('google')->andReturnSelf();
        Socialite::shouldReceive('redirectUrl')->once()->andReturnSelf();
        Socialite::shouldReceive('user')->once()->andReturn($googleUser);

        $response = $this->withSession([
            'auth_login_flow' => 'login',
            'auth_login_intent' => User::ROLE_CUSTOMER,
        ])->get(route('google.callback'));

        $this->assertAuthenticatedAs($existingUser->fresh());
        $response->assertRedirect('/');
        $this->assertFalse($existingUser->fresh()->isAdmin());
    }

    private function fakeGoogleUser(string $id, string $name, string $email): SocialiteUser
    {
        $socialiteUser = new SocialiteUser();
        $socialiteUser->id = $id;
        $socialiteUser->name = $name;
        $socialiteUser->email = $email;
        $socialiteUser->token = 'fake-access-token';
        $socialiteUser->refreshToken = 'fake-refresh-token';
        $socialiteUser->expiresIn = 3600;
        $socialiteUser->map([
            'accessTokenResponseBody' => [
                'access_token' => 'fake-access-token',
                'refresh_token' => 'fake-refresh-token',
                'expires_in' => 3600,
                'id_token' => 'fake-id-token',
                'scope' => 'openid email profile',
            ],
        ]);

        return $socialiteUser;
    }
}
