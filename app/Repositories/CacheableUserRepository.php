<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserReadRepository;
use Illuminate\Support\Facades\Cache;

class CacheableUserRepository implements UserReadRepository
{
    public function __construct(private EloquentUserRepository $userRepository) {}

    /**
     * Get the cache key prefix
     */
    private function getCacheKeyPrefix(): string
    {
        return config('repository.users.prefix');
    }

    /**
     * Get the cache TTL
     */
    private function getCacheTTL(): int
    {
        return config('repository.users.ttl');
    }

    /**
     * Retrieve value from cache with callback
     */
    private function retrieveFromCache(string $key, callable $callback): mixed
    {
        $cache = Cache::remember(
            $key,
            $this->getCacheTTL(),
            fn () => ['value' => $callback()]
        );

        return $cache['value'];
    }

    /**
     * Find user by email
     */
    public function findOneByEmail(string $email): ?User
    {
        return $this->retrieveFromCache(
            "{$this->getCacheKeyPrefix()}.email.{$email}",
            fn () => $this->userRepository->findOneByEmail($email)
        );
    }

    /**
     * Find user by id
     */
    public function findOneById(string $id): ?User
    {
        return $this->retrieveFromCache(
            "{$this->getCacheKeyPrefix()}.id.{$id}",
            fn () => $this->userRepository->findOneById($id)
        );
    }

    /**
     * Flush specific user from cache
     */
    public function flush(User $user): void
    {
        Cache::forget("{$this->getCacheKeyPrefix()}.email.{$user->email}");
        Cache::forget("{$this->getCacheKeyPrefix()}.id.{$user->id}");
    }
}
