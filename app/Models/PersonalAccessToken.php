<?php

namespace App\Models;

use App\Jobs\UpdatePersonalAccessToken;
use App\Repositories\CacheableUserRepository;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    use HasUuids;

    /**
     * Boot the model.
     */
    public static function boot()
    {
        parent::boot();

        // When updating, cancel normal update and manually update
        // the table asynchronously every 60 seconds.
        static::updating(function (PersonalAccessToken $personalAccessToken) {
            Cache::remember(
                config('repository.personal_access_tokens.prefix').".last_usage_updated_at.{$personalAccessToken->id}",
                60,
                function () use ($personalAccessToken) {
                    dispatch(new UpdatePersonalAccessToken(
                        $personalAccessToken->getTable(),
                        $personalAccessToken->id,
                        $personalAccessToken->getDirty()
                    ));

                    return now();
                }
            );

            return false;
        });

        static::deleted(function (PersonalAccessToken $personalAccessToken) {
            Cache::forget(config('repository.personal_access_tokens.prefix').".{$personalAccessToken->id}");
            Cache::forget(config('repository.personal_access_tokens.prefix').".{$personalAccessToken->token}");
        });
    }

    /**
     * Find the token instance matching the given token.
     *
     * @param  string  $token
     * @return static|null
     */
    public static function findToken($token)
    {
        if (strpos($token, '|') === false) {
            $hash = hash('sha256', $token);

            return static::retrieveFromCache($hash, fn ($hash) => static::where('token', $hash)->first());
        }

        [$id, $token] = explode('|', $token, 2);

        if (! \Str::isUuid($id)) {
            return null;
        }

        if ($instance = static::retrieveFromCache($id, fn ($id) => static::find($id))) {
            return hash_equals($instance->token, hash('sha256', $token)) ? $instance : null;
        }
    }

    /**
     * Get the tokenable model instance.
     */
    public function getTokenableAttribute()
    {
        switch ($this->tokenable_type) {
            case User::class:
                return resolve(CacheableUserRepository::class)->findOneById($this->tokenable_id);
            default:
                return null;
        }
    }

    /**
     * Retrieve value from cache with callback
     */
    public static function retrieveFromCache(string $identifier, callable $callback)
    {
        $key = config('repository.personal_access_tokens.prefix').".{$identifier}";

        $ttl = config('repository.personal_access_tokens.ttl');

        $cache = Cache::remember($key, $ttl, fn () => [
            'value' => $callback($identifier),
        ]);

        return $cache['value'];
    }
}
