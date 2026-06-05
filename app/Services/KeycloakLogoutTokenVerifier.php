<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class KeycloakLogoutTokenVerifier
{
    /**
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    public function verify(string $logoutToken): array
    {
        [$header, $payload, $signature, $signedContent] = $this->decode($logoutToken);

        if (($header['alg'] ?? null) !== 'RS256') {
            throw $this->invalidToken();
        }

        if (openssl_verify($signedContent, $signature, $this->publicKey((string) ($header['kid'] ?? '')), OPENSSL_ALGO_SHA256) !== 1) {
            throw $this->invalidToken();
        }

        $this->validateClaims($payload);

        return $payload;
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, mixed>, 2: string, 3: string}
     *
     * @throws ValidationException
     */
    private function decode(string $logoutToken): array
    {
        $parts = explode('.', $logoutToken);

        if (count($parts) !== 3) {
            throw $this->invalidToken();
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;
        $header = json_decode($this->base64UrlDecode($encodedHeader), true);
        $payload = json_decode($this->base64UrlDecode($encodedPayload), true);
        $signature = $this->base64UrlDecode($encodedSignature);

        if (! is_array($header) || ! is_array($payload) || $signature === '') {
            throw $this->invalidToken();
        }

        return [$header, $payload, $signature, $encodedHeader.'.'.$encodedPayload];
    }

    /**
     * @param  array<string, mixed>  $payload
     *
     * @throws ValidationException
     */
    private function validateClaims(array $payload): void
    {
        $issuer = config('services.keycloak.issuer');
        $clientId = config('services.keycloak.client_id');
        $audiences = Arr::wrap($payload['aud'] ?? []);
        $events = $payload['events'] ?? [];

        if (($payload['iss'] ?? null) !== $issuer) {
            throw $this->invalidToken();
        }

        if (! in_array($clientId, $audiences, true)) {
            throw $this->invalidToken();
        }

        if (($payload['azp'] ?? $clientId) !== $clientId) {
            throw $this->invalidToken();
        }

        if (! is_array($events) || ! isset($events['http://schemas.openid.net/event/backchannel-logout'])) {
            throw $this->invalidToken();
        }

        if (empty($payload['sid'])) {
            throw $this->invalidToken();
        }

        if (isset($payload['nonce'])) {
            throw $this->invalidToken();
        }

        if (isset($payload['exp']) && now()->timestamp > (int) $payload['exp']) {
            throw $this->invalidToken();
        }

        if (isset($payload['iat']) && now()->addMinutes(5)->timestamp < (int) $payload['iat']) {
            throw $this->invalidToken();
        }
    }

    /**
     * @throws ValidationException
     */
    private function publicKey(string $keyId): string
    {
        if ($keyId === '') {
            throw $this->invalidToken();
        }

        $key = collect($this->jwkSet()['keys'] ?? [])->firstWhere('kid', $keyId);

        if (! is_array($key) || ($key['kty'] ?? null) !== 'RSA' || empty($key['n']) || empty($key['e'])) {
            throw $this->invalidToken();
        }

        return $this->rsaPublicKeyPem($key['n'], $key['e']);
    }

    private function rsaPublicKeyPem(string $modulus, string $exponent): string
    {
        $modulus = $this->base64UrlDecode($modulus);
        $exponent = $this->base64UrlDecode($exponent);
        $rsaPublicKey = $this->asn1Sequence(
            $this->asn1Integer($modulus).$this->asn1Integer($exponent)
        );
        $algorithmIdentifier = $this->asn1Sequence(
            chr(6).chr(9).chr(42).chr(134).chr(72).chr(134).chr(247).chr(13).chr(1).chr(1).chr(1).chr(5).chr(0)
        );
        $subjectPublicKeyInfo = $this->asn1Sequence(
            $algorithmIdentifier.$this->asn1BitString($rsaPublicKey)
        );

        return "-----BEGIN PUBLIC KEY-----\n"
            .chunk_split(base64_encode($subjectPublicKeyInfo), 64, "\n")
            ."-----END PUBLIC KEY-----\n";
    }

    private function asn1Sequence(string $value): string
    {
        return chr(48).$this->asn1Length(strlen($value)).$value;
    }

    private function asn1Integer(string $value): string
    {
        $value = ltrim($value, chr(0));

        if ($value === '') {
            $value = chr(0);
        }

        if ((ord($value[0]) & 0x80) !== 0) {
            $value = chr(0).$value;
        }

        return chr(2).$this->asn1Length(strlen($value)).$value;
    }

    private function asn1BitString(string $value): string
    {
        return chr(3).$this->asn1Length(strlen($value) + 1).chr(0).$value;
    }

    private function asn1Length(int $length): string
    {
        if ($length < 128) {
            return chr($length);
        }

        $encodedLength = '';

        while ($length > 0) {
            $encodedLength = chr($length & 0xFF).$encodedLength;
            $length >>= 8;
        }

        return chr(0x80 | strlen($encodedLength)).$encodedLength;
    }

    /**
     * @return array<string, mixed>
     */
    private function jwkSet(): array
    {
        return Cache::remember('keycloak:jwks', now()->addHour(), function () {
            return Http::timeout(5)
                ->acceptJson()
                ->get(config('services.keycloak.jwks_url'))
                ->throw()
                ->json();
        });
    }

    private function base64UrlDecode(string $value): string
    {
        $paddedValue = str_pad($value, strlen($value) + (4 - strlen($value) % 4) % 4, '=');

        return (string) base64_decode(strtr($paddedValue, '-_', '+/'), true);
    }

    private function invalidToken(): ValidationException
    {
        return ValidationException::withMessages([
            'logout_token' => [trans('auth.failed')],
        ]);
    }
}
