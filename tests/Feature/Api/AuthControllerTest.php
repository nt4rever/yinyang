<?php

namespace Tests\Feature\Api;

use App\Enums\AccountProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/v1/login');

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->withLocalAccount()->create();

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_with_unverified_email(): void
    {
        $user = User::factory()->unverified()->withLocalAccount()->create();

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_succeeds_with_valid_credentials(): void
    {
        $user = User::factory()->withLocalAccount()->create();

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['access_token', 'token_type']);
    }

    public function test_profile_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/profile');

        $response->assertUnauthorized();
    }

    public function test_profile_returns_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/profile');

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ],
            ]);
    }

    public function test_logout_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/logout');

        $response->assertUnauthorized();
    }

    public function test_logout_deletes_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/logout');

        $response->assertNoContent();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_keycloak_redirect_returns_redirect_url(): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('stateless')->once()->andReturnSelf();
        $provider->shouldReceive('redirect')->once()->andReturn(new RedirectResponse('https://keycloak.test/authorize'));

        Socialite::shouldReceive('driver')
            ->once()
            ->with('keycloak')
            ->andReturn($provider);

        $response = $this->getJson('/api/v1/auth/keycloak/redirect');

        $response->assertOk()
            ->assertJson([
                'redirect_url' => 'https://keycloak.test/authorize',
            ]);
    }

    public function test_keycloak_callback_creates_user_account_and_token(): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('stateless')->once()->andReturnSelf();
        $provider->shouldReceive('user')->once()->andReturn($this->keycloakUser());

        Socialite::shouldReceive('driver')
            ->once()
            ->with('keycloak')
            ->andReturn($provider);

        $response = $this->getJson('/api/v1/auth/keycloak/callback?code=valid-code');

        $response->assertOk()
            ->assertJsonStructure(['access_token', 'token_type', 'user'])
            ->assertJson(['token_type' => 'Bearer']);

        $user = User::where('email', 'keycloak@example.com')->firstOrFail();

        $this->assertSame('Keycloak User', $user->name);
        $this->assertNotNull($user->email_verified_at);
        $this->assertDatabaseHas('accounts', [
            'user_id' => $user->id,
            'provider' => AccountProvider::KEYCLOAK->value,
            'provider_id' => 'keycloak-subject-1',
        ]);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_keycloak_callback_links_existing_user_by_email(): void
    {
        $user = User::factory()->create([
            'email' => 'keycloak@example.com',
            'name' => 'Existing User',
        ]);

        $provider = Mockery::mock();
        $provider->shouldReceive('stateless')->once()->andReturnSelf();
        $provider->shouldReceive('user')->once()->andReturn($this->keycloakUser());

        Socialite::shouldReceive('driver')
            ->once()
            ->with('keycloak')
            ->andReturn($provider);

        $response = $this->getJson('/api/v1/auth/keycloak/callback?code=valid-code');

        $response->assertOk()
            ->assertJsonStructure(['access_token', 'token_type', 'user']);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('accounts', [
            'user_id' => $user->id,
            'provider' => AccountProvider::KEYCLOAK->value,
            'provider_id' => 'keycloak-subject-1',
        ]);
    }

    private function keycloakUser(?string $id = 'keycloak-subject-1', ?string $email = 'keycloak@example.com'): SocialiteUser
    {
        return (new SocialiteUser)->setRaw([
            'sub' => $id,
            'email' => $email,
            'email_verified' => true,
            'name' => 'Keycloak User',
            'preferred_username' => 'keycloak-user',
        ])->map([
            'id' => $id,
            'nickname' => 'keycloak-user',
            'name' => 'Keycloak User',
            'email' => $email,
        ]);
    }
}
