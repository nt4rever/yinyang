<?php

namespace Tests\Unit\Models;

use App\Enums\UploadfileType;
use App\Models\Uploadfile;
use App\Services\UploadfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
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

    /**
     * Create a simple folder structure.
     *
     * @example
     * ```
     * Folder A
     * └── Folder B
     *     └── doc.txt
     * ```
     *
     * @return array<string, Uploadfile>
     */
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

    /**
     * Create a complex folder structure.
     *
     * @example
     * ```
     * Folder A
     * ├── Folder B
     * │   ├── Folder C1
     * │   │   ├── Folder E
     * │   └── Folder C2
     * └──---- doc.txt
     * ```
     *
     * @return array<string, Uploadfile>
     */
    private function createComplexFolderStructure(): array
    {
        $folderA = $this->service->create([
            'name' => 'Folder A',
            'type' => UploadfileType::FOLDER,
        ]);

        $folderB = $this->service->create([
            'name' => 'Folder B',
            'type' => UploadfileType::FOLDER,
        ], $folderA);

        $folderC1 = $this->service->create([
            'name' => 'Folder C1',
            'type' => UploadfileType::FOLDER,
        ], $folderB);

        $folderC2 = $this->service->create([
            'name' => 'Folder C2',
            'type' => UploadfileType::FOLDER,
        ], $folderB);

        $folderE = $this->service->create([
            'name' => 'Folder E',
            'type' => UploadfileType::FOLDER,
        ], $folderC1);

        $fileC = $this->service->create([
            'name' => 'doc.txt',
            'content_type' => 'text/plain',
            'path' => 'doc.txt',
            'type' => UploadfileType::FILE,
        ], $folderB);

        return compact('folderA', 'folderB', 'folderC1', 'folderC2', 'folderE', 'fileC');
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

    #[DataProvider('moveFolderProvider')]
    public function test_move_uploadfile(array $params, string $expected): void
    {
        $data = $this->createComplexFolderStructure();

        $this->service->move($data[$params[0]], $data[$params[1]]);

        $folderPath = $data[$params[0]]->ancestorUploadfiles
            ->pluck('name')
            ->implode('/');

        $this->assertEquals($expected, $folderPath);
    }

    public static function moveFolderProvider(): array
    {
        return [
            'move folder E to folder A' => [
                'params' => [
                    'folderE',
                    'folderA',
                ],
                'expected' => 'Folder A',
            ],
            'move folder E to folder B' => [
                'params' => [
                    'folderE',
                    'folderB',
                ],
                'expected' => 'Folder A/Folder B',
            ],
            'move folder C1 to folder A' => [
                'params' => [
                    'folderC1',
                    'folderA',
                ],
                'expected' => 'Folder A',
            ],
        ];
    }

    public function test_force_delete_uploadfile(): void
    {
        ['folderA' => $folderA, 'folderB' => $folderB, 'fileC' => $fileC] = $this->createFolderStructure();

        $folderBId = $folderB->id;
        $fileCId = $fileC->id;

        $this->service->forceDelete($folderB);

        $this->assertDatabaseMissing('uploadfiles', [
            'id' => $folderBId,
        ]);

        $this->assertDatabaseMissing('uploadfiles', [
            'id' => $fileCId,
        ]);

        $this->assertDatabaseHas('uploadfiles', [
            'id' => $folderA->id,
        ]);
    }

    public function test_restore_uploadfile(): void
    {
        ['folderB' => $folderB, 'fileC' => $fileC] = $this->createFolderStructure();

        $this->service->delete($folderB);

        $this->assertSoftDeleted('uploadfiles', [
            'id' => $folderB->id,
        ]);

        $this->assertSoftDeleted('uploadfiles', [
            'id' => $fileC->id,
        ]);

        $this->service->restore($folderB);

        $this->assertDatabaseHas('uploadfiles', [
            'id' => $folderB->id,
            'deleted_at' => null,
        ]);

        $this->assertDatabaseHas('uploadfiles', [
            'id' => $fileC->id,
            'deleted_at' => null,
        ]);
    }

    public function test_restore_returns_true(): void
    {
        ['folderB' => $folderB] = $this->createFolderStructure();

        $this->service->delete($folderB);

        $result = $this->service->restore($folderB);

        $this->assertTrue($result);
    }

    public function test_restore_uploads_tree_paths(): void
    {
        ['folderA' => $folderA, 'folderB' => $folderB, 'fileC' => $fileC] = $this->createFolderStructure();

        $this->service->delete($folderA);

        $this->assertSoftDeleted('uploadfiles', [
            'id' => $folderA->id,
        ]);

        $this->service->restore($folderB);

        $this->assertDatabaseHas('uploadfiles', [
            'id' => $folderA->id,
            'deleted_at' => null,
        ]);

        $this->assertDatabaseHas('uploadfiles', [
            'id' => $folderB->id,
            'deleted_at' => null,
        ]);

        $this->assertDatabaseHas('uploadfiles', [
            'id' => $fileC->id,
            'deleted_at' => null,
        ]);
    }
}
