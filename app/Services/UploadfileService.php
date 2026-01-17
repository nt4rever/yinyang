<?php

namespace App\Services;

use App\Enums\UploadfileType;
use App\Factory\UploadfileFactory;
use App\Models\Uploadfile;
use App\Models\UploadfilesTreePath;
use Illuminate\Support\Facades\DB;

class UploadfileService
{
    /**
     * Create a new uploadfile.
     *
     * @param  array{name: string, content_type: ?string, path: ?string, type: UploadfileType|int}  $data
     *
     * @throws \InvalidArgumentException if parent is not a folder.
     */
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

                $paths = $parent->uploadfilesTreePath->map(fn (UploadfilesTreePath $path) => [
                    'ancestor_id' => $path->ancestor_id,
                    'descendant_id' => $uploadfile->id,
                    'depth' => $path->depth + 1,
                ]);

                $uploadfile->uploadfilesTreePath()->createMany($paths);
            }

            return $uploadfile;
        });
    }

    /**
     * Delete an uploadfile and all its descendants.
     */
    public function delete(Uploadfile $uploadfile): bool
    {
        return DB::transaction(function () use ($uploadfile) {
            $uploadfile->descendantUploadfiles()->delete();
            $uploadfile->delete();

            return true;
        });
    }

    public function forceDelete(Uploadfile $uploadfile): bool
    {
        return DB::transaction(function () use ($uploadfile) {
            $uploadfile->descendantUploadfiles()->withTrashed()->forceDelete();
            $uploadfile->forceDelete();

            return true;
        });
    }

    public function restore(Uploadfile $uploadfile): bool
    {
        return DB::transaction(function () use ($uploadfile) {
            $uploadfile->restore();

            // Restore all children folders and files
            $uploadfile->descendantUploadfiles()
                ->withTrashed()
                ->whereNotNull('deleted_at')
                ->get()
                ->each(fn (Uploadfile $uploadfile) => $uploadfile->restore());

            // Restore parent folder path
            $uploadfile->ancestorUploadfiles()
                ->withTrashed()
                ->whereNotNull('deleted_at')
                ->get()
                ->each(fn (Uploadfile $uploadfile) => $uploadfile->restore());

            return true;
        });
    }

    /**
     * Move an uploadfile to a target folder.
     */
    public function move(Uploadfile $uploadfile, Uploadfile $targetFolder): bool
    {
        return DB::transaction(function () use ($uploadfile, $targetFolder) {
            if ($targetFolder->type !== UploadfileType::FOLDER) {
                throw new \InvalidArgumentException('Target folder must be a folder.');
            }

            if (UploadfilesTreePath::query()
                ->where('ancestor_id', $uploadfile->id)
                ->where('descendant_id', $targetFolder->id)
                ->exists()) {
                throw new \InvalidArgumentException('Uploadfile already in target folder.');
            }

            $this->moveTreePaths($uploadfile->id, $targetFolder->id);

            return true;
        });
    }

    private function moveTreePaths(string $uploadfileId, string $targetFolderId): void
    {
        UploadfilesTreePath::query()
            ->where('descendant_id', $uploadfileId)
            ->where('depth', '!=', 0)
            ->delete();

        UploadfilesTreePath::query()
            ->where('descendant_id', $targetFolderId)
            ->orderBy('depth')
            ->get()
            ->map(fn ($path) => UploadfilesTreePath::create([
                'ancestor_id' => $path->ancestor_id,
                'descendant_id' => $uploadfileId,
                'depth' => $path->depth + 1,
            ]));

        $children = UploadfilesTreePath::query()
            ->where('ancestor_id', $uploadfileId)
            ->where('depth', 1)
            ->get();

        foreach ($children as $child) {
            $this->moveTreePaths($child->descendant_id, $uploadfileId);
        }
    }
}
