<?php

namespace Database\Seeders\Test;

use App\Enums\UploadfileType;
use App\Models\Uploadfile;
use App\Models\UploadfilesTreePath;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UploadfileSeeder extends Seeder
{
    private const FOLDER_TREE = [
        'A' => [
            'B' => [
                'C' => [
                    'C1',
                    'C2',
                ],
            ],
            'D' => [
                'E' => [
                    'E1',
                ],
            ],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            foreach (self::FOLDER_TREE as $folderName => $subFolders) {
                $folder = Uploadfile::create([
                    'name' => $folderName,
                    'type' => UploadfileType::FOLDER,
                    'path' => $folderName,
                ]);

                $this->addTreePath($folder->id, $folder->id, 0);
                $this->buildTree($folder, $subFolders);
            }
        });
    }

    private function buildTree(Uploadfile $parent, array $items): void
    {
        foreach ($items as $itemName => $children) {
            $name = is_array($children) ? $itemName : $children;
            $item = Uploadfile::create([
                'name' => $name,
                'type' => UploadfileType::FOLDER,
                'path' => $parent->path.'/'.$name,
            ]);

            // Self-reference
            $this->addTreePath($item->id, $item->id, 0);

            // Direct parent-child
            $this->addTreePath($parent->id, $item->id, 1);

            // Paths from ancestors
            $this->addTreePathsForAncestors($parent, $item);

            if (is_array($children)) {
                $this->buildTree($item, $children);
            }
        }
    }

    private function addTreePath(string $ancestorId, string $descendantId, int $depth): void
    {
        UploadfilesTreePath::create([
            'ancestor_id' => $ancestorId,
            'descendant_id' => $descendantId,
            'depth' => $depth,
        ]);
    }

    private function addTreePathsForAncestors(Uploadfile $parent, Uploadfile $child): void
    {
        // Find ancestors of parent (where parent is DESCENDANT)
        $ancestors = UploadfilesTreePath::where('descendant_id', $parent->id)
            ->where('depth', '>', 0)
            ->get();

        foreach ($ancestors as $ancestor) {
            $this->addTreePath(
                $ancestor->ancestor_id,
                $child->id,
                $ancestor->depth + 1
            );
        }
    }
}
