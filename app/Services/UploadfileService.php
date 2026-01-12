<?php

namespace App\Services;

use App\Enums\UploadfileType;
use App\Factory\UploadfileFactory;
use App\Models\Uploadfile;
use App\Models\UploadfilesTreePath;
use Illuminate\Support\Facades\DB;

class UploadfileService
{
    public function create(array $data, ?Uploadfile $parent = null): Uploadfile
    {
        return DB::transaction(function () use ($data, $parent) {
            $uploadfile = UploadfileFactory::create(
                data_get($data, 'name'),
                data_get($data, 'content_type'),
                data_get($data, 'path'),
                data_get($data, 'type'),
            );
            $uploadfile->save();

            $uploadfile->uploadfilesTreePath()->create([
                'ancestor_id' => $uploadfile->id,
                'descendant_id' => $uploadfile->id,
                'depth' => 0,
            ]);

            if ($parent) {
                if ($parent->type !== UploadfileType::FOLDER) {
                    throw new \InvalidArgumentException('Parent must be a folder.');
                }

                $uploadfile->uploadfilesTreePath()->create([
                    'ancestor_id' => $parent->id,
                    'descendant_id' => $uploadfile->id,
                    'depth' => 1,
                ]);

                $parent->uploadfilesTreePath()
                    ->where('depth', '>', 0)
                    ->each(function (UploadfilesTreePath $path) use ($uploadfile) {
                        $uploadfile->uploadfilesTreePath()->create([
                            'ancestor_id' => $path->ancestor_id,
                            'descendant_id' => $uploadfile->id,
                            'depth' => $path->depth + 1,
                        ]);
                    });
            }

            return $uploadfile;
        });
    }

    public function delete(Uploadfile $uploadfile): bool
    {
        return DB::transaction(function () use ($uploadfile) {
            $uploadfile->delete();
            $uploadfile->descendantUploadfiles()->delete();

            return true;
        });
    }

    public function move(Uploadfile $uploadfile, Uploadfile $targetFolder): bool
    {
        return DB::transaction(function () use ($uploadfile, $targetFolder) {
            $this->moveToNewParent($uploadfile, $targetFolder);

            return true;
        });
    }

    private function moveToNewParent(Uploadfile $uploadfile, Uploadfile $targetFolder): void
    {
        if ($targetFolder->type !== UploadfileType::FOLDER) {
            throw new \InvalidArgumentException('Target must be a folder.');
        }

        $this->removeOldTreePaths($uploadfile);
        $this->attachToNewParent($uploadfile, $targetFolder);
        if ($uploadfile->type === UploadfileType::FOLDER) {
            $this->moveDescendants($uploadfile);
        }
    }

    private function removeOldTreePaths(Uploadfile $uploadfile): void
    {
        $uploadfile->uploadfilesTreePath()
            ->where('depth', '>', 0)
            ->delete();
    }

    private function attachToNewParent(Uploadfile $uploadfile, Uploadfile $targetFolder): void
    {
        $targetFolder->uploadfilesTreePath()
            ->where('depth', '>', 0)
            ->each(function (UploadfilesTreePath $path) use ($uploadfile) {
                $uploadfile->uploadfilesTreePath()->create([
                    'ancestor_id' => $path->ancestor_id,
                    'descendant_id' => $uploadfile->id,
                    'depth' => $path->depth + 1,
                ]);
            });
    }

    private function moveDescendants(Uploadfile $uploadfile): void
    {
        foreach ($uploadfile->descendantUploadfiles as $descendant) {
            $this->moveToNewParent($descendant, $uploadfile);
        }
    }
}
