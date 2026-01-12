<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UploadfilesTreePath extends Model
{
    use HasAudit, HasUuids;

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
        'ancestor_id',
        'descendant_id',
        'depth',
    ];

    public function parentUploadfile(): BelongsTo
    {
        return $this->belongsTo(Uploadfile::class, 'ancestor_id');
    }

    public function childUploadfile(): BelongsTo
    {
        return $this->belongsTo(Uploadfile::class, 'descendant_id');
    }
}
