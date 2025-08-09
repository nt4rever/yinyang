<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Find user by ID
     */
    public function find(int $id): ?User
    {
        return User::find($id);
    }
}
