<?php

namespace Tests\Unit\Models;

use App\Jobs\UpdatePersonalAccessToken;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PersonalAccessTokenTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    #[DataProvider('invalidTokenProvider')]
    public function test_find_token_returns_null_with_invalid_token(string $token): void
    {
        $model = PersonalAccessToken::findToken($token);

        $this->assertNull($model);
    }

    public static function invalidTokenProvider(): array
    {
        return [
            'random string only' => [
                Str::random(40),
            ],
            'random|string format but invalid' => [
                Str::random(40).'|'.Str::random(40),
            ],
            'uuid|string format but not existing' => [
                Str::uuid().'|'.Str::random(40),
            ],
        ];
    }

    public function test_find_token_returns_model_with_valid_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth')->plainTextToken;
        $model = PersonalAccessToken::findToken($token);

        $this->assertInstanceOf(PersonalAccessToken::class, $model);
        $this->assertEquals($user->id, $model->tokenable_id);
        $this->assertEquals($user->id, $model->tokenable->id);
    }

    public function test_get_tokenable_attribute_returns_null_when_invalid(): void
    {
        $model = new PersonalAccessToken;
        $model->tokenable_type = 'UnknownModel';
        $model->tokenable_id = Str::uuid();
        $model->name = 'Test Token';
        $model->token = hash('sha256', Str::random(40));
        $model->abilities = ['*'];
        $model->save();

        $this->assertNull($model->tokenable);
    }

    public function test_delete_token_dispatches_event_successfully(): void
    {
        $user = User::factory()->create();
        $newToken = $user->createToken('auth');

        $newToken->accessToken->delete();

        $this->assertNull(PersonalAccessToken::findToken($newToken->plainTextToken));
    }

    public function test_update_token_dispatches_event_successfully(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $model = $user->createToken('auth')->accessToken;

        $model->last_used_at = now();
        $model->save();

        Queue::assertPushed(UpdatePersonalAccessToken::class);
    }
}
