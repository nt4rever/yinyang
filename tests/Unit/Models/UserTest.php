<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_avatar_url_returns_null_when_no_avatar(): void
    {
        $user = User::factory()->create(['avatar_path' => null]);

        $this->assertNull($user->avatar_url);
    }

    public function test_avatar_url_returns_storage_url(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['avatar_path' => 'avatars/user-1.jpg']);

        $this->assertStringContainsString('avatars/user-1.jpg', $user->avatar_url);
    }

    public function test_resolve_route_binding_returns_user(): void
    {
        $user = User::factory()->create();

        $resolved = $user->resolveRouteBinding($user->id);

        $this->assertInstanceOf(User::class, $resolved);
        $this->assertEquals($user->id, $resolved->id);
    }
}
