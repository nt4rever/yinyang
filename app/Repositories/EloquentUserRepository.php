<?php

namespace App\Repositories;

use App\Enums\AccountProvider;
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
        return User::filter(['search_phrase' => $searchPhrase])
            ->latest('id')
            ->cursorPaginate($limit);
    }

    /**
     * Find users by criteria with pagination
     */
    public function findByFilters(array $filters): LengthAwarePaginator
    {
        $query = User::filter($filters);

        return $query->latest('id')
            ->paginate(data_get($filters, 'per_page', config('eloquentfilter.paginate_limit')));
    }

    /**
     * Find user by provider and provider id
     */
    public function findByProviderAndProviderId(AccountProvider $provider, string $providerId): ?User
    {
        return User::whereHas(
            'accounts',
            fn ($q) => $q->where('provider', $provider)
                ->where('provider_id', $providerId)
        )->first();
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
