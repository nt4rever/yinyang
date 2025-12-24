<?php

namespace App\Factory;

use App\Models\User;
use Illuminate\Support\Str;

class UserFactory
{
    public static function create($name, $email, $password): User
    {
        $user = new User;
        $user->id = (string) Str::uuid7();
        $user->name = $name;
        $user->email = $email;
        $user->lang = 'en';
        $user->timezone = 'UTC';
        $user->lock_version = 0;

        return $user;
    }
}
