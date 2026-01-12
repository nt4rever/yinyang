<?php

namespace App\Models;

use App\Enums\UploadfileType;
use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        return $this->uploadfilesTreePath()->firstWhere('level', 1)?->parentUploadfile;
    }
}
