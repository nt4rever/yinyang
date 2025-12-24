<?php

namespace App\Services;

use App\Repositories\CacheableUserRepository;
use App\Repositories\EloquentUserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
