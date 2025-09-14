<?php

namespace App\Helpers;

class CacheKeys
{
    public static function users()
    {
        return 'users';
    }

    public static function userById($id)
    {
        return "users:id:{$id}";
    }

    public static function userByEmail($email)
    {
        return "users:email:{$email}";
    }

    public static function personalAccessTokens()
    {
        return 'personal_access_tokens';
    }

    public static function personalAccessTokenByIdentifier($id)
    {
        return "personal_access_tokens:{$id}";
    }

    public static function personalAccessTokenLastUpdatedByIdentifier($id)
    {
        return "personal_access_tokens:{$id}:last_updated";
    }
}
