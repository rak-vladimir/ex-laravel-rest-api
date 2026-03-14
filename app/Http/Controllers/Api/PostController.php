<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\ModelCrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    protected ModelCrudService $crud;

    public function __construct()
    {
        $this->crud = new ModelCrudService(Post::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $pagination = $this->crud->paginate(
            perPage: $request->integer('per_page', 15),
            sortBy: $request->input('sort', 'created_at'),
            sortDirection: $request->input('order', 'desc'),
            searchFields: ['title', 'content'],
        );

        return PostResource::collection($pagination);
    }

    /**
     * Store a newly created resource in storage.
     * @throws \Throwable
     */
    public function store(PostRequest $request): JsonResponse
    {
        /** @var Post $post */
        $post = $this->crud
            ->create([
                ...$request->validated(),
                'user_id' => $request->user()->id,
            ]);

        return $post
            ->toResource()
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post): JsonResource
    {
        return new PostResource(
            $post->load('user')
        );
    }

    /**
     * Update the specified resource in storage.
     * @throws \Throwable
     */
    public function update(PostRequest $request, Post $post): JsonResource
    {
        /** @var Post $post */
        $post = $this->crud
            ->update(
                $post,
                $request->validated()
            );

        return $post->toResource();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post): Response
    {
        Gate::authorize('delete', $post);

        $this->crud->delete($post);

        return response()->noContent();
    }
}
