<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\AccountProvider;
use App\Helpers\CacheKeys;
use App\Traits\HasAudit;
use App\Traits\HasOptimisticLocking;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Filterable,
        HasApiTokens,
        HasAudit,
        HasOptimisticLocking,
        HasUuids,
        Notifiable,
        SoftDeletes;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'avatar_path',
        'lang',
        'timezone',
        'lock_version',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'lock_version' => 'integer',
        ];
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $field ??= $this->getRouteKeyName();

        $cache = Cache::tags(CacheKeys::users())
            ->remember(
                key: CacheKeys::userById($value), // TODO: replace with right key
                ttl: calculate_cache_ttl(),
                callback: fn () => [
                    'value' => parent::resolveRouteBinding($value, $field),
                ]
            );

        return $cache['value'];
    }

    /**
     * Get the avatar URL for the user.
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->avatar_path ? Storage::url($this->avatar_path) : null,
        );
    }

    /**
     * Get the accounts for the user.
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Get the local account for the user.
     */
    public function localAccount(): HasOne
    {
        return $this->accounts()->where('provider', AccountProvider::LOCAL)->one();
    }

    /**
     * Get the tenants that the user belongs to.
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user', 'user_id', 'tenant_id')
            ->withPivot('type', 'status')
            ->withTimestamps()
            ->using(TenantUser::class);
    }

    /**
     * Get the current tenant for the user.
     */
    public function currentTenant(): ?Tenant
    {
        return $this->tenants()->find(session('tenant_id'));
    }
}
