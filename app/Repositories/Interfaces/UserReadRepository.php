<?php

namespace App\Repositories\Interfaces;

use App\Models\User;

interface UserReadRepository
{
    /**
     * Find user by email
     */
    public function findOneByEmail(string $email): ?User;

    /**
     * Find user by id
     */
    public function findOneById(string $id): ?User;
}
