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

    public static function personalAccessTokenById($id)
    {
        return "personal_access_tokens:id:{$id}";
    }

    public static function personalAccessTokenByToken($token)
    {
        return "personal_access_tokens:token:{$token}";
    }

    public static function personalAccessTokenByIdAndLastUpdated($id)
    {
        return "personal_access_tokens:id:{$id}:last_updated";
    }
}
