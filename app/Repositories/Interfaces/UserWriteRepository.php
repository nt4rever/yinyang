<?php

namespace App\Repositories\Interfaces;

use App\Models\User;

interface UserWriteRepository
{
    /**
     * Save a user
     */
    public function save(User $user): bool;

    /**
     * Delete a user
     */
    public function delete(User $user): bool;
}
