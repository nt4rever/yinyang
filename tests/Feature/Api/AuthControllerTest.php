<?php

namespace Tests\Feature\Api;

use App\Enums\AccountProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use OpenSSLAsymmetricKey;
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
        $this->assertDatabaseHas('personal_access_tokens', [
            'keycloak_session_id' => 'keycloak-session-1',
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

    public function test_keycloak_backchannel_logout_deletes_tokens_for_matching_session(): void
    {
        config([
            'services.keycloak.client_id' => 'yinyang-api',
            'services.keycloak.issuer' => 'https://keycloak.test/realms/yinyang',
            'services.keycloak.jwks_url' => 'https://keycloak.test/realms/yinyang/protocol/openid-connect/certs',
        ]);

        [$privateKey, $jwk] = $this->keyPair();
        Http::fake([
            'https://keycloak.test/realms/yinyang/protocol/openid-connect/certs' => Http::response([
                'keys' => [$jwk],
            ]),
        ]);

        $user = User::factory()->create();
        $matchingToken = $user->tokens()->create([
            'name' => 'auth_token',
            'token' => hash('sha256', 'matching-token'),
            'abilities' => ['*'],
            'keycloak_session_id' => 'keycloak-session-1',
        ]);
        $otherToken = $user->tokens()->create([
            'name' => 'auth_token',
            'token' => hash('sha256', 'other-token'),
            'abilities' => ['*'],
            'keycloak_session_id' => 'keycloak-session-2',
        ]);
        $localToken = $user->tokens()->create([
            'name' => 'auth_token',
            'token' => hash('sha256', 'local-token'),
            'abilities' => ['*'],
        ]);

        $response = $this->postJson('/api/v1/auth/keycloak/backchannel-logout', [
            'logout_token' => $this->logoutToken($privateKey),
        ]);

        $response->assertNoContent();
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $matchingToken->id]);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $otherToken->id]);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $localToken->id]);
    }

    public function test_keycloak_backchannel_logout_rejects_invalid_logout_token(): void
    {
        $user = User::factory()->create();
        $token = $user->tokens()->create([
            'name' => 'auth_token',
            'token' => hash('sha256', 'matching-token'),
            'abilities' => ['*'],
            'keycloak_session_id' => 'keycloak-session-1',
        ]);

        $response = $this->postJson('/api/v1/auth/keycloak/backchannel-logout', [
            'logout_token' => 'invalid-token',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['logout_token']);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $token->id]);
    }

    private function keycloakUser(?string $id = 'keycloak-subject-1', ?string $email = 'keycloak@example.com'): SocialiteUser
    {
        return (new SocialiteUser)->setRaw([
            'sub' => $id,
            'email' => $email,
            'email_verified' => true,
            'name' => 'Keycloak User',
            'preferred_username' => 'keycloak-user',
            'sid' => 'keycloak-session-1',
        ])->map([
            'id' => $id,
            'nickname' => 'keycloak-user',
            'name' => 'Keycloak User',
            'email' => $email,
        ]);
    }

    /**
     * @return array{0: OpenSSLAsymmetricKey, 1: array<string, string>}
     */
    private function keyPair(): array
    {
        $privateKey = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        $details = openssl_pkey_get_details($privateKey);

        return [$privateKey, [
            'kty' => 'RSA',
            'kid' => 'test-key',
            'use' => 'sig',
            'alg' => 'RS256',
            'n' => $this->base64UrlEncode($details['rsa']['n']),
            'e' => $this->base64UrlEncode($details['rsa']['e']),
        ]];
    }

    private function logoutToken(OpenSSLAsymmetricKey $privateKey): string
    {
        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
            'kid' => 'test-key',
        ], JSON_THROW_ON_ERROR));
        $payload = $this->base64UrlEncode(json_encode([
            'iss' => 'https://keycloak.test/realms/yinyang',
            'aud' => 'yinyang-api',
            'azp' => 'yinyang-api',
            'iat' => now()->timestamp,
            'exp' => now()->addMinute()->timestamp,
            'sid' => 'keycloak-session-1',
            'events' => [
                'http://schemas.openid.net/event/backchannel-logout' => [],
            ],
        ], JSON_THROW_ON_ERROR));
        $signedContent = $header.'.'.$payload;

        openssl_sign($signedContent, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        return $signedContent.'.'.$this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
