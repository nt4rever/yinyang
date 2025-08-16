<?php

namespace App\Repositories;

use App\Criteria\Criteria;
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
     * Find user by id
     */
    public function findOneById(string $id): ?User
    {
        return User::find($id);
    }

    /**
     * Find users by search phrase with pagination
     */
    public function findBySearchPhrase(?string $searchPhrase, int $limit = 15): CursorPaginator
    {
        return User::filter(['search_phrase' => $searchPhrase])->latest('id')->cursorPaginate($limit);
    }

    /**
     * Find users by criteria with pagination
     */
    public function findByCriteria(Criteria $criteria): LengthAwarePaginator
    {
        $query = User::filter($criteria->filters);

        $query->when(
            $criteria->sortAttribute,
            fn ($query) => $query->orderBy($criteria->sortAttribute, $criteria->sortDirection)
        );

        return $query->latest('id')->paginate($criteria->limit);
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
