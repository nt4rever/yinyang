<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserReadRepository;
use App\Repositories\Interfaces\UserWriteRepository;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentUserRepository implements UserReadRepository, UserWriteRepository
{
    /**
     * Find user by email
     */
    public function findOneByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Find all users with pagination
     */
    public function findAll(int $perPage = 15): LengthAwarePaginator
    {
        return User::paginate($perPage);
    }

    /**
     * Find users by search phrase with pagination
     */
    public function findBySearchPhrase(?string $searchPhrase, int $perPage = 15): CursorPaginator
    {
        return User::filter(['search_phrase' => $searchPhrase])->cursorPaginate($perPage);
    }

    /**
     * Save a user
     */
    public function save(User $user): bool
    {
        return $user->save();
    }

    /**
     * Delete a user
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }
}
