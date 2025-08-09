<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserReadRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class CacheableUserRepository implements UserReadRepository
{
    public function __construct(private EloquentUserRepository $userRepository) {}

    /**
     * Get the cache key prefix
     */
    protected function getCacheKeyPrefix(): string
    {
        return config('repository.users.prefix');
    }

    /**
     * Get the cache TTL
     */
    protected function getCacheTTL(): int
    {
        return config('repository.users.ttl');
    }

    /**
     * Find user by email
     */
    public function findOneByEmail(string $email): ?User
    {
        $cache = Cache::remember(
            "{$this->getCacheKeyPrefix()}.email.{$email}",
            $this->getCacheTTL(),
            fn () => [
                'value' => $this->userRepository->findOneByEmail($email),
            ]
        );

        return $cache['value'];
    }

    /**
     * Find all users
     */
    public function findAll(int $perPage = 15): LengthAwarePaginator
    {
        throw new \Exception('Not implemented');
    }

    public function flush(User $user): void
    {
        Cache::forget("{$this->getCacheKeyPrefix()}.email.{$user->email}");
        Cache::forget("{$this->getCacheKeyPrefix()}.id.{$user->id}");
    }
}
