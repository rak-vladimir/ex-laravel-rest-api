<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Services\ModelCrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    protected ModelCrudService $crud;

    public function __construct()
    {
        $this->crud = new ModelCrudService(Comment::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Post $post): AnonymousResourceCollection
    {
        $comments = $this->crud->paginate(
            perPage: request()->integer('per_page', 15),
            filters: ['post_id' => $post->id],
            with: ['user'],
            sortBy: 'created_at',
            searchFields: ['content']
        );

        return CommentResource::collection($comments);
    }

    /**
     * Store a newly created resource in storage.
     * @throws \Throwable
     */
    public function store(CommentRequest $request, Post $post): JsonResponse
    {
        /** @var Comment $comment */
        $comment = $this->crud->create([
            ...$request->validated(),
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
        ]);

        return $comment
            ->toResource()
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post, Comment $comment): JsonResource
    {
        abort_unless($comment->post_id === $post->id, 404);

        return $comment
            ->load('user')
            ->toResource();
    }

    /**
     * Update the specified resource in storage.
     * @throws \Throwable
     */
    public function update(CommentRequest $request, Post $post, Comment $comment): JsonResource
    {
        abort_unless($comment->post_id === $post->id, 404);

        $comment = $this->crud->update(
            $comment,
            $request->validated()
        );

        return $comment->toResource();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post, Comment $comment): Response
    {
        Gate::authorize('delete', $comment);

        abort_unless($comment->post_id === $post->id, 404);

        $this->crud->delete($comment);

        return response()->noContent();
    }
}
