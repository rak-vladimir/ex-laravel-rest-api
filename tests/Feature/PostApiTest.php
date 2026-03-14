<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PostApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    #[Test]
    public function pagination_with_per_page_and_page()
    {
        Post::factory()->count(25)->create();

        $response = $this->getJson('/api/posts?per_page=10&page=1');

        // Page 1
        $response->assertOk()
            ->assertJsonStructure([
                'data'  => [
                    '*' => [
                        'id',
                        'user_id',
                        'title',
                        'content',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta'  => [
                    'current_page',
                    'from',
                    'last_page',
                    'links',
                    'path',
                    'per_page',
                    'to',
                    'total',
                ],
            ])
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 25)
            ->assertJsonPath('meta.last_page', 3);

        // Page 2
        $responsePage2 = $this->getJson('/api/posts?per_page=10&page=2');

        $responsePage2->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.from', 11)
            ->assertJsonPath('meta.to', 20);

        // Page 3
        $responseLast = $this->getJson('/api/posts?per_page=10&page=3');

        $responseLast->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.current_page', 3)
            ->assertJsonPath('meta.from', 21)
            ->assertJsonPath('meta.to', 25);
    }

    #[Test]
    public function all_can_list_posts()
    {
        Post::factory()->count(5)->create();

        $response = $this->getJson('/api/posts');

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    }

    #[Test]
    public function guest_cannot_create_post()
    {
        $response = $this->postJson('/api/posts', [
            'title'   => 'Test',
            'content' => 'Test content',
        ]);

        $response->assertUnauthorized();
    }

    #[Test]
    public function user_can_create_post_validation()
    {
        Sanctum::actingAs($this->user);

        // 1. required
        $responseEmpty = $this->postJson('/api/posts', []);

        $responseEmpty->assertUnprocessable()
            ->assertJsonValidationErrors([
                'title'   => 'The title field is required.',
                'content' => 'The content field is required.',
            ]);

        // 2. max:255
        $responseLongTitle = $this->postJson('/api/posts', [
            'title'   => str_repeat('a', 256),
            'content' => 'Valid content',
        ]);

        $responseLongTitle->assertUnprocessable()
            ->assertJsonValidationErrors('title');

        // 3. unique:title - error
        Post::factory()->create([
            'title'   => 'Duplicate Title',
            'user_id' => $this->user->id,
        ]);

        $responseDuplicate = $this->postJson('/api/posts', [
            'title'   => 'Duplicate Title',
            'content' => 'Valid content',
        ]);

        $responseDuplicate->assertUnprocessable()
            ->assertJsonValidationErrors('title')
            ->assertJsonFragment(['The title has already been taken.']);

        // 4. valid
        $responseSuccess = $this->postJson('/api/posts', [
            'title'   => 'Valid Unique Title',
            'content' => 'Valid content here',
        ]);

        $responseSuccess->assertCreated()
            ->assertJsonStructure([
                'id',
                'user_id',
                'title',
                'content',
                'created_at',
                'updated_at',
            ]);

        $this->assertDatabaseHas('posts', [
            'title'   => 'Valid Unique Title',
            'user_id' => $this->user->id,
        ]);
    }

    #[Test]
    public function owner_can_update_post_validation()
    {
        Sanctum::actingAs($this->user);

        $post = Post::factory()->create([
            'title'   => 'Original Title',
            'content' => 'Original content',
            'user_id' => $this->user->id,
        ]);

        // 1. sometimes
        $responseEmpty = $this->putJson("/api/posts/{$post->id}", []);

        $responseEmpty->assertOk();

        // 2. max:255
        $responseLongTitle = $this->putJson("/api/posts/{$post->id}", [
            'title' => str_repeat('a', 256),
        ]);

        $responseLongTitle->assertUnprocessable()
            ->assertJsonValidationErrors('title');

        // 3. unique:title - error
        Post::factory()->create([
            'title'   => 'Duplicate Title',
            'user_id' => User::factory()->create()->id,
        ]);

        $responseDuplicate = $this->putJson("/api/posts/{$post->id}", [
            'title' => 'Duplicate Title',
        ]);

        $responseDuplicate->assertUnprocessable()
            ->assertJsonValidationErrors('title')
            ->assertJsonFragment(['The title has already been taken.']);

        // 4. unique:title - same as original
        $responseSameTitle = $this->putJson("/api/posts/{$post->id}", [
            'title' => 'Original Title',
        ]);

        $responseSameTitle->assertOk();

        // 5. unique:title - valid
        $responseSuccess = $this->putJson("/api/posts/{$post->id}", [
            'title'   => 'New Title',
            'content' => 'New content here',
        ]);

        $responseSuccess->assertOk()
            ->assertJsonStructure([
                'id',
                'user_id',
                'title',
                'content',
                'created_at',
                'updated_at',
            ])
            ->assertJson([
                'title'   => 'New Title',
                'content' => 'New content here',
            ]);

        $this->assertDatabaseHas('posts', [
            'id'      => $post->id,
            'title'   => 'New Title',
            'content' => 'New content here',
        ]);
    }

    #[Test]
    public function user_cannot_update_foreign_post()
    {
        Sanctum::actingAs($this->user);

        $post = Post::factory()->create();

        $response = $this->putJson("/api/posts/{$post->id}", [
            'title' => 'Hacked Title',
        ]);

        $response->assertForbidden();
    }

    #[Test]
    public function owner_can_delete_post()
    {
        Sanctum::actingAs($this->user);

        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/posts/{$post->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    #[Test]
    public function user_cannot_delete_foreign_post()
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $post      = Post::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->deleteJson("/api/posts/{$post->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    #[Test]
    public function returns_404_for_non_existent_post()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/posts/999999');

        $response->assertNotFound();
    }
}
