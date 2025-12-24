<?php

namespace Tests\Unit\Helpers;

use App\Helpers\CacheKeys;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;

class CacheKeysTest extends TestCase
{
    public function test_users_cache_key()
    {
        $this->assertEquals('users', CacheKeys::users());
    }

    public function test_user_by_id_cache_key()
    {
        $id = Str::uuid();
        $expected = "users:id:{$id}";
        $this->assertEquals($expected, CacheKeys::userById($id));
    }

    public function test_user_by_email_cache_key()
    {
        $email = 'test@example.com';
        $expected = "users:email:{$email}";
        $this->assertEquals($expected, CacheKeys::userByEmail($email));
    }

    public function test_personal_access_tokens_cache_key()
    {
        $this->assertEquals('personal_access_tokens', CacheKeys::personalAccessTokens());
    }

    public function test_personal_access_token_by_identifier_cache_key()
    {
        $id = Str::uuid();
        $expected = "personal_access_tokens:{$id}";
        $this->assertEquals($expected, CacheKeys::personalAccessTokenByIdentifier($id));
    }

    public function test_personal_access_token_last_updated_by_identifier_cache_key()
    {
        $id = Str::uuid();
        $expected = "personal_access_tokens:{$id}:last_updated";
        $this->assertEquals($expected, CacheKeys::personalAccessTokenLastUpdatedByIdentifier($id));
    }
}
