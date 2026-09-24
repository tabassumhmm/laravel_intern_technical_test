<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_post_can_be_created(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/posts', $this->postPayload($user));

        $response->assertCreated()
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.title', 'A test post')
            ->assertJsonPath('data.body', 'Test body')
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.name', $user->name);

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'title' => 'A test post',
            'body' => 'Test body',
        ]);
    }

    public function test_posts_can_be_listed(): void
    {
        $user = User::factory()->create();
        $firstPost = $this->createPost($user, ['title' => 'First post']);
        $secondPost = $this->createPost($user, ['title' => 'Second post']);

        $response = $this->getJson('/api/posts');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $secondPost->id)
            ->assertJsonPath('data.1.id', $firstPost->id)
            ->assertJsonPath('data.0.user.id', $user->id);
    }

    public function test_a_post_can_be_shown(): void
    {
        $user = User::factory()->create();
        $post = $this->createPost($user);

        $response = $this->getJson("/api/posts/{$post->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $post->id)
            ->assertJsonPath('data.title', $post->title)
            ->assertJsonPath('data.user.id', $user->id);
    }

    public function test_a_post_can_be_updated_with_put(): void
    {
        $user = User::factory()->create();
        $post = $this->createPost($user);
        $updatedUser = User::factory()->create();

        $response = $this->putJson("/api/posts/{$post->id}", $this->postPayload($updatedUser, [
            'title' => 'Updated with PUT',
            'body' => 'Updated body',
        ]));

        $response->assertOk()
            ->assertJsonPath('data.user_id', $updatedUser->id)
            ->assertJsonPath('data.title', 'Updated with PUT')
            ->assertJsonPath('data.body', 'Updated body')
            ->assertJsonPath('data.user.id', $updatedUser->id);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'user_id' => $updatedUser->id,
            'title' => 'Updated with PUT',
            'body' => 'Updated body',
        ]);
    }

    public function test_a_post_can_be_updated_with_patch(): void
    {
        $user = User::factory()->create();
        $post = $this->createPost($user);

        $response = $this->patchJson("/api/posts/{$post->id}", [
            'title' => 'Updated with PATCH',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Updated with PATCH')
            ->assertJsonPath('data.body', $post->body)
            ->assertJsonPath('data.user.id', $user->id);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated with PATCH',
            'body' => $post->body,
        ]);
    }

    public function test_a_post_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $post = $this->createPost($user);

        $this->deleteJson("/api/posts/{$post->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_showing_a_missing_post_returns_not_found(): void
    {
        $this->getJson('/api/posts/0')
            ->assertNotFound();
    }

    public function test_creating_a_post_requires_all_fields(): void
    {
        $this->postJson('/api/posts', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['user_id', 'title', 'body']);
    }

    public function test_creating_a_post_rejects_invalid_fields(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/posts', [
            'user_id' => 'not-an-integer',
            'title' => str_repeat('x', 256),
            'body' => ['not', 'a', 'string'],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['user_id', 'title', 'body']);
    }

    public function test_creating_a_post_requires_an_existing_user(): void
    {
        $user = User::factory()->create();
        $missingUserId = (int) (User::query()->max('id') ?? 0) + 1;

        $this->postJson('/api/posts', $this->postPayload($user, [
            'user_id' => $missingUserId,
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors('user_id');
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function postPayload(User $user, array $overrides = []): array
    {
        return array_merge([
            'user_id' => $user->id,
            'title' => 'A test post',
            'body' => 'Test body',
        ], $overrides);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function createPost(User $user, array $overrides = []): Post
    {
        return Post::query()->create($this->postPayload($user, $overrides));
    }
}
