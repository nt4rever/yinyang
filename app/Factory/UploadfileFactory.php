<?php

namespace App\Factory;

use App\Enums\UploadfileType;
use App\Models\Uploadfile;
use Illuminate\Support\Str;

class UploadfileFactory
{
    public static function create(
        string $name,
        ?string $contentType,
        ?string $path,
        UploadfileType|int $type
    ): Uploadfile {
        $uploadfile = new Uploadfile;
        $uploadfile->id = (string) Str::uuid7();
        $uploadfile->name = $name;
        $uploadfile->content_type = $contentType;
        $uploadfile->path = $path;
        $uploadfile->type = $type;

        return $uploadfile;
    }
}
