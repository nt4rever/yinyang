<?php

namespace Tests\Unit\Models;

use App\Enums\UploadfileType;
use App\Models\Uploadfile;
use App\Services\UploadfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UploadfileServiceTest extends TestCase
{
    use RefreshDatabase;

    private UploadfileService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UploadfileService;
    }

    private function createFolderStructure(): array
    {
        $folderA = $this->service->create([
            'name' => 'Folder A',
            'type' => UploadfileType::FOLDER,
        ]);

        $folderB = $this->service->create([
            'name' => 'Folder B',
            'type' => UploadfileType::FOLDER,
        ], $folderA);

        $fileC = $this->service->create([
            'name' => 'doc.txt',
            'content_type' => 'text/plain',
            'path' => 'doc.txt',
            'type' => UploadfileType::FILE,
        ], $folderB);

        return compact('folderA', 'folderB', 'fileC');
    }

    public function test_create_root_uploadfile(): void
    {
        $uploadfile = $this->service->create([
            'name' => 'Folder A',
            'content_type' => 'text/plain',
            'path' => 'Folder A',
            'type' => UploadfileType::FOLDER,
        ]);

        $this->assertInstanceOf(Uploadfile::class, $uploadfile);

        $this->assertDatabaseHas('uploadfiles', [
            'id' => $uploadfile->id,
        ]);

        $this->assertDatabaseHas('uploadfiles_tree_paths', [
            'ancestor_id' => $uploadfile->id,
            'descendant_id' => $uploadfile->id,
            'depth' => 0,
        ]);
    }

    public function test_create_child_uploadfile(): void
    {
        ['folderA' => $folderA, 'folderB' => $folderB, 'fileC' => $fileC] = $this->createFolderStructure();

        $folderPath = $fileC->ancestorUploadfiles
            ->pluck('name')
            ->implode('/');

        $parentFolder = $fileC->parentFolder();

        $this->assertInstanceOf(Uploadfile::class, $fileC);

        $this->assertDatabaseHas('uploadfiles', [
            'id' => $fileC->id,
        ]);

        $this->assertDatabaseHas('uploadfiles_tree_paths', [
            'ancestor_id' => $fileC->id,
            'descendant_id' => $fileC->id,
            'depth' => 0,
        ]);

        $this->assertDatabaseHas('uploadfiles_tree_paths', [
            'ancestor_id' => $folderB->id,
            'descendant_id' => $fileC->id,
            'depth' => 1,
        ]);

        $this->assertDatabaseHas('uploadfiles_tree_paths', [
            'ancestor_id' => $folderA->id,
            'descendant_id' => $fileC->id,
            'depth' => 2,
        ]);

        $this->assertEquals('Folder A/Folder B', $folderPath);

        $this->assertEquals($folderB->id, $parentFolder->id);
    }

    public function test_delete_uploadfile(): void
    {
        ['folderA' => $folderA, 'folderB' => $folderB, 'fileC' => $fileC] = $this->createFolderStructure();

        $this->service->delete($folderB);

        $this->assertSoftDeleted('uploadfiles', [
            'id' => $folderB->id,
        ]);

        $this->assertSoftDeleted('uploadfiles', [
            'id' => $fileC->id,
        ]);

        $this->assertDatabaseHas('uploadfiles', [
            'id' => $folderA->id,
        ]);
    }
}
