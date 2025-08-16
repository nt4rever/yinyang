<?php

namespace App\Services;

use App\Repositories\EloquentUserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuthService
{
    public function __construct(
        private EloquentUserRepository $userRepository
    ) {}

    /**
     * Authenticate user with email and password
     *
     * @throws ValidationException
     */
    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findOneByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
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
            throw new NotFoundHttpException(trans('User not found.'));
        }

        if ($user->hasVerifiedEmail()) {
            throw new BadRequestHttpException(trans('Email already verified.'));
        }

        $user->markEmailAsVerified();
    }
}
