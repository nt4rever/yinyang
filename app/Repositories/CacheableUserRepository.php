<?php

namespace App\Repositories;

use App\Helpers\CacheKeys;
use App\Models\User;
use App\Repositories\Interfaces\UserReadRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CacheableUserRepository implements UserReadRepository
{
    public function __construct(private EloquentUserRepository $userRepository) {}

    /**
     * Retrieve value from cache with callback
     */
    private function retrieveFromCache(string $key, callable $callback): mixed
    {
        $cache = Cache::tags(CacheKeys::users())->remember(
            $key,
            calculate_cache_ttl(),
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
            CacheKeys::userByEmail($email),
            fn () => $this->userRepository->findOneByEmail($email)
        );
    }

    /**
     * Find user by id
     */
    public function findOneById(string $id): ?User
    {
        return $this->retrieveFromCache(
            CacheKeys::userById($id),
            fn () => $this->userRepository->findOneById($id)
        );
    }

    /**
     * Flush specific user from cache
     */
    public function flush(User $user): void
    {
        DB::afterCommit(function () use ($user) {
            Cache::tags(CacheKeys::users())->forget(CacheKeys::userByEmail($user->email));
            Cache::tags(CacheKeys::users())->forget(CacheKeys::userById($user->id));
        });
    }
}
