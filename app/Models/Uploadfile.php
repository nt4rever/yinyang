<?php

namespace App\Models;

use App\Enums\UploadfileType;
use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Uploadfile extends Model
{
    use HasAudit, HasUuids, SoftDeletes;

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
        'type',
        'name',
        'content_type',
        'path',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => UploadfileType::class,
        ];
    }

    public function uploadfilesTreePath(): HasMany
    {
        return $this->hasMany(UploadfilesTreePath::class, 'descendant_id')->latest('depth');
    }

    public function parentFolder(): ?Uploadfile
    {
        return $this->ancestorUploadfiles()->firstWhere('depth', 1);
    }

    public function ancestorUploadfiles(): HasManyThrough
    {
        return $this->hasManyThrough(
            Uploadfile::class,
            UploadfilesTreePath::class,
            'descendant_id',
            'id',
            'id',
            'ancestor_id'
        )
            ->where('depth', '>', 0)
            ->latest('depth');
    }

    public function descendantUploadfiles(): HasManyThrough
    {
        return $this->hasManyThrough(
            Uploadfile::class,
            UploadfilesTreePath::class,
            'ancestor_id',
            'id',
            'id',
            'descendant_id'
        )
            ->where('depth', '>', 0);
    }
}
