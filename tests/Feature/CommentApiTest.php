<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommentApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->post = Post::factory()->create(['user_id' => $this->user->id]);
    }

    #[Test]
    public function user_can_create_comment()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/posts/{$this->post->id}/comments", [
            'content' => 'This is a great post!',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'id',
                'content',
                'user_id',
                'post_id',
                'created_at',
                'updated_at',
            ]);

        $this->assertDatabaseHas('comments', [
            'content' => 'This is a great post!',
            'user_id' => $this->user->id,
            'post_id' => $this->post->id,
        ]);
    }

    #[Test]
    public function guest_cannot_create_comment()
    {
        $response = $this->postJson("/api/posts/{$this->post->id}/comments", [
            'content' => 'Test comment',
        ]);

        $response->assertUnauthorized();
    }

    #[Test]
    public function owner_can_delete_comment()
    {
        Sanctum::actingAs($this->user);

        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->deleteJson("/api/posts/{$this->post->id}/comments/{$comment->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    #[Test]
    public function user_cannot_delete_foreign_comment()
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $comment   = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->deleteJson("/api/posts/{$this->post->id}/comments/{$comment->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }
}
