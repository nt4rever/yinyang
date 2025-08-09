<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserReadRepository
{
    /**
     * Find user by email
     */
    public function findOneByEmail(string $email): ?User;

    /**
     * Find all users with pagination
     */
    public function findAll(int $perPage = 15): LengthAwarePaginator;
}
