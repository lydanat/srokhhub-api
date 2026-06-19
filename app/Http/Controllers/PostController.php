<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(protected PostService $postService) {}

    // GET /api/posts/{id} — view single post
    public function show(Post $post): JsonResponse
    {
        // Only show approved posts to public
        if ($post->status !== 'approved') {
            return response()->json(['message' => 'Post not found.'], 404);
        }

        return response()->json(new PostResource($post->load(['category', 'user', 'images'])));
    }

    // POST /api/posts — create post
    public function store(StorePostRequest $request): JsonResponse
    {
        $post = $this->postService->store(
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'message' => 'Post submitted for review.',
            'post'    => new PostResource($post),
        ], 201);
    }

    // POST /api/posts/preview — preview post without saving
    public function preview(StorePostRequest $request): JsonResponse
    {
        $data = $this->postService->preview($request->validated());

        return response()->json([
            'message' => 'Preview generated.',
            'preview' => $data,
        ]);
    }

    // PUT /api/posts/{id} — update own post
    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        $updated = $this->postService->update(
            $post,
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'message' => 'Post updated successfully.',
            'post'    => new PostResource($updated),
        ]);
    }

    // DELETE /api/posts/{id} — delete own post
    public function destroy(Request $request, Post $post): JsonResponse
    {
        $this->postService->delete($post, $request->user()->id);

        return response()->json([
            'message' => 'Post deleted successfully.',
        ]);
    }
}