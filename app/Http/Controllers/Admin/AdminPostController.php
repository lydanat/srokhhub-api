<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPostController extends Controller
{
    public function __construct(protected PostService $postService) {}

    // GET /api/admin/posts — list all submissions
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status', 'pending');

        $posts = Post::with(['category', 'user', 'images'])
            ->when(
                in_array($status, ['pending', 'approved', 'rejected']),
                fn($q) => $q->where('status', $status)
            )
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'data' => PostResource::collection($posts),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page'    => $posts->lastPage(),
                'total'        => $posts->total(),
                'per_page'     => $posts->perPage(),
            ],
        ]);
    }

    // GET /api/admin/posts/{id} — view single submission
    public function show(Post $post): JsonResponse
    {
        return response()->json(
            new PostResource($post->load(['category', 'user', 'images']))
        );
    }

    // PATCH /api/admin/posts/{id}/approve — approve post
    public function approve(Post $post): JsonResponse
    {
        if ($post->status === 'approved') {
            return response()->json(['message' => 'Post is already approved.'], 422);
        }

        $post = $this->postService->approve($post);

        return response()->json([
            'message' => 'Post approved and published.',
            'post'    => new PostResource($post),
        ]);
    }

    // PATCH /api/admin/posts/{id}/reject — reject post
    public function reject(Post $post): JsonResponse
    {
        if ($post->status === 'rejected') {
            return response()->json(['message' => 'Post is already rejected.'], 422);
        }

        $post = $this->postService->reject($post);

        return response()->json([
            'message' => 'Post rejected.',
            'post'    => new PostResource($post),
        ]);
    }
}