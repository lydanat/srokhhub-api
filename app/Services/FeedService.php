<?php

namespace App\Services;

use App\Models\ExternalNews;
use App\Models\Post;
use Illuminate\Pagination\LengthAwarePaginator;

class FeedService
{
    public function getUnifiedFeed(int $perPage = 20, ?string $source = null, int $page = 1): LengthAwarePaginator
    {
        // Fetch approved internal posts
        $internalPosts = Post::with(['category', 'user'])
            ->where('status', 'approved')
            ->when($source && $source === 'internal', fn($q) => $q)
            ->get()
            ->map(fn($post) => $this->normalizePost($post));

        // Fetch external news
        $externalNews = ExternalNews::when($source && $source !== 'internal', fn($q) => $q->where('source', $source))
            ->get()
            ->map(fn($news) => $this->normalizeExternalNews($news));

        // Skip internal posts if filtering by external source
        if ($source && $source !== 'internal') {
            $combined = $externalNews;
        } elseif ($source === 'internal') {
            $combined = $internalPosts;
        } else {
            $combined = $internalPosts->concat($externalNews);
        }

        // Sort by published_at descending (newest first)
        $sorted = $combined->sortByDesc('published_at')->values();

        // Pagination
        $offset = ($page - 1) * $perPage;
        $items  = $sorted->slice($offset, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $sorted->count(),
            $perPage,
            $page,
            [
                'path'  => LengthAwarePaginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]
        );
    }

    // Normalize internal blog post to unified format
    private function normalizePost(Post $post): array
    {
        return [
            'id' => $post->id,
            'title' => $post->title_en,
            'content' => $post->content_en,
            'image' => $post->main_image,
            'source' => 'internal',
            'category' => $post->category?->name,
            'author' => $post->user?->name,
            'published_at' => $post->published_at,
            'url' => null,
        ];
    }

    // Normalize external RSS news to unified format
    private function normalizeExternalNews(ExternalNews $news): array
    {
        return [
            'id' => $news->id,
            'title' => $news->title,
            'content' => $news->content,
            'image' => $news->image,
            'source' => $news->source,
            'category' => $news->category,
            'author' => null,
            'published_at' => $news->published_at,
            'url' => $news->url,
        ];
    }
}