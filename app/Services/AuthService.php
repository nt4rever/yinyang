<?php

namespace App\Services;

use App\Enums\AccountProvider;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Repositories\CacheableUserRepository;
use App\Repositories\EloquentUserRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class AuthService
{
    public function __construct(
        private EloquentUserRepository $userRepository,
        private CacheableUserRepository $cacheableUserRepository
    ) {}

    /**
     * Authenticate user with email and password
     *
     * @throws ValidationException
     */
    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findOneByEmail($email);

        if (! $user?->localAccount || ! Hash::check($password, $user->localAccount->password)) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.email_not_verified')],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    public function loginWithKeycloak(SocialiteUser $keycloakUser): array
    {
        $providerId = (string) $keycloakUser->getId();
        $email = $keycloakUser->getEmail();

        if ($providerId === '' || ! $email) {
            throw ValidationException::withMessages([
                'keycloak' => [trans('auth.failed')],
            ]);
        }

        $user = DB::transaction(function () use ($keycloakUser, $providerId, $email) {
            $user = $this->resolveKeycloakUser($providerId, $email);

            $user->name = $keycloakUser->getName() ?: $keycloakUser->getNickname() ?: $email;
            $rawKeycloakUser = method_exists($keycloakUser, 'getRaw') ? $keycloakUser->getRaw() : [];

            if (! $user->email_verified_at && data_get($rawKeycloakUser, 'email_verified')) {
                $user->email_verified_at = Carbon::now();
            }

            $this->userRepository->save($user);

            $user->accounts()->firstOrCreate([
                'provider' => AccountProvider::KEYCLOAK,
                'provider_id' => $providerId,
            ]);

            return $user;
        });

        $this->cacheableUserRepository->flush($user);

        return [
            'access_token' => $user->createToken('auth_token')->plainTextToken,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ];
    }

    private function resolveKeycloakUser(string $providerId, string $email): User
    {
        $user = $this->userRepository->findByProviderAndProviderId(AccountProvider::KEYCLOAK, $providerId)
            ?? $this->userRepository->findOneByEmail($email);
        if (! $user) {
            $user = new User;
            $user->email = $email;
        }

        return $user;
    }

    public function verifyEmail(string $id): void
    {
        $user = $this->userRepository->findOneById($id);

        if (! $user) {
            abort(404, trans('User not found.'));
        }

        if ($user->hasVerifiedEmail()) {
            abort(403, trans('Email already verified.'));
        }

        $user->markEmailAsVerified();

        $this->cacheableUserRepository->flush($user);
    }
}
