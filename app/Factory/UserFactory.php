<?php

namespace App\Factory;

use App\Models\User;
use Illuminate\Support\Str;

class UserFactory
{
    public static function create($name, $email, $password): User
    {
        $user = new User;
        $user->id = Str::orderedUuid();
        $user->name = $name;
        $user->email = $email;
        $user->password = $password;

        return $user;
    }
}
