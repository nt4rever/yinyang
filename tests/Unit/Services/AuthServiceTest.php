<?php

namespace Tests\Unit\Services;

use App\Enums\AccountProvider;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_keycloak_creates_user_account_and_token(): void
    {
        $result = app(AuthService::class)->loginWithKeycloak($this->keycloakUser());

        $this->assertArrayHasKey('access_token', $result);
        $this->assertSame('Bearer', $result['token_type']);
        $this->assertDatabaseHas('users', [
            'email' => 'keycloak@example.com',
            'name' => 'Keycloak User',
        ]);

        $user = User::where('email', 'keycloak@example.com')->firstOrFail();

        $this->assertNotNull($user->email_verified_at);
        $this->assertDatabaseHas('accounts', [
            'user_id' => $user->id,
            'provider' => AccountProvider::KEYCLOAK->value,
            'provider_id' => 'keycloak-subject-1',
        ]);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_login_with_keycloak_reuses_user_by_provider_id(): void
    {
        $user = User::factory()->create([
            'email' => 'old@example.com',
            'name' => 'Old Name',
        ]);
        $user->accounts()->create([
            'provider' => AccountProvider::KEYCLOAK,
            'provider_id' => 'keycloak-subject-1',
        ]);

        $result = app(AuthService::class)->loginWithKeycloak($this->keycloakUser());

        $this->assertArrayHasKey('access_token', $result);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'old@example.com',
            'name' => 'Keycloak User',
        ]);
        $this->assertDatabaseCount('accounts', 1);
    }

    public function test_login_with_keycloak_links_existing_user_by_email(): void
    {
        $user = User::factory()->create([
            'email' => 'keycloak@example.com',
            'name' => 'Existing User',
        ]);

        $result = app(AuthService::class)->loginWithKeycloak($this->keycloakUser());

        $this->assertArrayHasKey('access_token', $result);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('accounts', [
            'user_id' => $user->id,
            'provider' => AccountProvider::KEYCLOAK->value,
            'provider_id' => 'keycloak-subject-1',
        ]);
    }

    public function test_login_with_keycloak_requires_provider_id(): void
    {
        $this->expectException(ValidationException::class);

        app(AuthService::class)->loginWithKeycloak($this->keycloakUser(id: null));
    }

    public function test_login_with_keycloak_requires_email(): void
    {
        $this->expectException(ValidationException::class);

        app(AuthService::class)->loginWithKeycloak($this->keycloakUser(email: null));
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
