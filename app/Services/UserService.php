<?php

namespace App\Services;

use App\Factory\UserFactory;
use App\Models\User;
use App\Repositories\CacheableUserRepository;
use App\Repositories\EloquentUserRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        return DB::transaction(function () use ($data) {
            $user = UserFactory::create(
                $data['name'],
                $data['email'],
                $data['password']
            );

            $this->userRepository->save($user);
            $this->cacheableUserRepository->flush($user);

            $user->sendEmailVerificationNotification();

            return $user;
        });
    }

    /**
     * Get all users
     */
    public function getByFilters(array $filters): LengthAwarePaginator
    {
        return $this->userRepository->findByFilters($filters);
    }

    /**
     * Get users by search phrase
     */
    public function getBySearchPhrase(?string $searchPhrase, int $limit = 15): CursorPaginator
    {
        return $this->userRepository->findBySearchPhrase($searchPhrase, $limit);
    }

    /**
     * Update a user
     */
    public function update(User $user, array $data): User
    {
        $user->validateOptimisticLock($data);

        return DB::transaction(function () use ($user, $data) {
            $user->fill($data);
            $user->increaseLockVersion();

            $this->userRepository->save($user);
            $this->cacheableUserRepository->flush($user);

            return $user;
        });
    }

    /**
     * Delete a user
     */
    public function delete(User $user): bool
    {
        return DB::transaction(function () use ($user) {
            $this->deleteOldAvatar($user);
            $this->userRepository->delete($user);
            $this->cacheableUserRepository->flush($user);

            return true;
        });
    }

    /**
     * Upload an avatar for a user
     */
    public function uploadAvatar(User $user, UploadedFile $avatar): User
    {
        return DB::transaction(function () use ($user, $avatar) {
            $path = $avatar->store("avatars/{$user->id}");

            $this->deleteOldAvatar($user);

            $user->avatar_path = $path;
            $user->increaseLockVersion();

            $this->userRepository->save($user);
            $this->cacheableUserRepository->flush($user);

            return $user;
        });
    }

    /**
     * Delete an avatar for a user
     */
    public function deleteAvatar(User $user): User
    {
        return DB::transaction(function () use ($user) {
            $this->deleteOldAvatar($user);

            $user->avatar_path = null;
            $user->increaseLockVersion();

            $this->userRepository->save($user);
            $this->cacheableUserRepository->flush($user);

            return $user;
        });
    }

    /**
     * Delete the old avatar for a user
     */
    private function deleteOldAvatar(User $user): void
    {
        if ($oldAvatarPath = $user->avatar_path) {
            dispatch(fn () => Storage::delete($oldAvatarPath))->afterCommit();
        }
    }
}
