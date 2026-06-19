<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostService
{
    // Create a new post with images
    public function store(array $data, int|string $userId): Post
    {
        return DB::transaction(function () use ($data, $userId) {

            // Upload main image
            $mainImageUrl = $this->uploadImage(
                $data['main_image'],
                "posts/{$userId}"
            );

            // Create the post
            $post = Post::create([
                'user_id' => $userId,
                'category_id' => $data['category_id'],
                'title_en' => $data['title_en'],
                'title_km' => $data['title_km'] ?? null,
                'content_en' => $data['content_en'],
                'content_km' => $data['content_km'] ?? null,
                'main_image' => $mainImageUrl,
                'status' => 'pending',
                'source' => 'internal',
            ]);

            // Upload gallery images if provided
            if (!empty($data['gallery_images'])) {
                foreach ($data['gallery_images'] as $image) {
                    $url = $this->uploadImage($image, "posts/{$post->id}/gallery");
                    PostImage::create([
                        'post_id' => $post->id,
                        'image_url' => $url,
                    ]);
                }
            }

            return $post->load(['category', 'user', 'images']);
        });
    }

    // Update an existing post
    public function update(Post $post, array $data, int|string $userId): Post
    {
        $this->ensureOwnership($post, $userId);
        $this->ensureEditable($post);

        return DB::transaction(function () use ($post, $data) {

            // Replace main image if new one provided
            if (isset($data['main_image'])) {
                $this->deleteImage($post->main_image);
                $data['main_image'] = $this->uploadImage(
                    $data['main_image'],
                    "posts/{$post->id}"
                );
            }

            $post->update([
                'category_id' => $data['category_id'] ?? $post->category_id,
                'title_en' => $data['title_en'] ?? $post->title_en,
                'title_km' => $data['title_km'] ?? $post->title_km,
                'content_en' => $data['content_en'] ?? $post->content_en,
                'content_km' => $data['content_km'] ?? $post->content_km,
                'main_image' => $data['main_image'] ?? $post->main_image,
            ]);

            // Replace gallery images if new ones provided
            if (!empty($data['gallery_images'])) {
                // Delete old gallery images
                foreach ($post->images as $oldImage) {
                    $this->deleteImage($oldImage->image_url);
                    $oldImage->delete();
                }

                // Upload new gallery images
                foreach ($data['gallery_images'] as $image) {
                    $url = $this->uploadImage($image, "posts/{$post->id}/gallery");
                    PostImage::create([
                        'post_id'   => $post->id,
                        'image_url' => $url,
                    ]);
                }
            }

            return $post->load(['category', 'user', 'images']);
        });
    }

    // Delete a post and its images
    public function delete(Post $post, int|string $userId): void
    {
        $this->ensureOwnership($post, $userId);

        DB::transaction(function () use ($post) {
            // Delete all images from storage
            $this->deleteImage($post->main_image);

            foreach ($post->images as $image) {
                $this->deleteImage($image->image_url);
            }

            $post->delete();
        });
    }

    // Admin: approve a post
    public function approve(Post $post): Post
    {
        $post->update([
            'status' => 'approved',
            'published_at' => now(),
        ]);

        return $post;
    }

    // Admin: reject a post
    public function reject(Post $post): Post
    {
        $post->update([
            'status' => 'rejected',
        ]);

        return $post;
    }

    // Preview validate and return data without saving
    public function preview(array $data): array
    {
        return [
            'title_en' => $data['title_en'],
            'title_km' => $data['title_km'] ?? null,
            'content_en' => $data['content_en'],
            'content_km' => $data['content_km'] ?? null,
            'category_id' => $data['category_id'],
            'source' => 'internal',
            'status' => 'preview',
        ];
    }

    // Upload image to Supabase storage
    private function uploadImage(UploadedFile $file, string $path): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $fullPath = "{$path}/{$filename}";

        Storage::disk('supabase')->put($fullPath, file_get_contents($file), 'public');

        return Storage::disk('supabase')->url($fullPath);
    }

    // Delete image from Supabase storage
    private function deleteImage(?string $url): void
    {
        if (!$url) return;

        // Extract path from full URL
        $path = parse_url($url, PHP_URL_PATH);
        $path = preg_replace('/^\/storage\/v1\/object\/public\/srokhhub-posts\//', '', $path);

        if ($path) {
            Storage::disk('supabase')->delete($path);
        }
    }

    // Ensure the user owns the post
    private function ensureOwnership(Post $post, int|string $userId): void
    {
        if ($post->user_id !== $userId) {
            abort(403, 'You do not own this post.');
        }
    }

    // Ensure the post is still editable (pending only)
    private function ensureEditable(Post $post): void
    {
        if ($post->status !== 'pending') {
            abort(403, 'Only pending posts can be edited.');
        }
    }
}