<?php

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Factory\UserFactory;
use App\Models\User;
use App\Repositories\CacheableUserRepository;
use App\Repositories\EloquentUserRepository;

class UserService
{
    public function __construct(
        private EloquentUserRepository $userRepository,
        private CacheableUserRepository $cacheableUserRepository
    ) {}

    /**
     * Create a new user
     */
    public function create(array $data): User
    {
        $user = UserFactory::create(
            $data['name'],
            $data['email'],
            $data['password']
        );

        $this->userRepository->save($user);
        $this->cacheableUserRepository->flush($user);

        return $user;
    }

    /**
     * Get all users
     */
    public function getAll(int $perPage = 15)
    {
        return $this->userRepository->findAll($perPage);
    }

    /**
     * Get users by search phrase
     */
    public function getBySearchPhrase(?string $searchPhrase, int $perPage = 15)
    {
        return $this->userRepository->findBySearchPhrase($searchPhrase, $perPage);
    }

    /**
     * Update a user
     */
    public function update(User $user, array $data): User
    {
        // Optimistic locking
        if ($user->updated_at?->notEqualTo($data['updated_at'])) {
            throw new ConflictException;
        }

        $user->fill($data);
        $this->userRepository->save($user);
        $this->cacheableUserRepository->flush($user);

        return $user;
    }

    /**
     * Delete a user
     */
    public function delete(User $user): bool
    {
        $this->cacheableUserRepository->flush($user);

        return $this->userRepository->delete($user);
    }
}
