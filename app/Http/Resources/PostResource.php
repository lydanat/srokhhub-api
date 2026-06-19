<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return 
            [
                'id'           => $this->id,
                'title_en'     => $this->title_en,
                'title_km'     => $this->title_km,
                'content_en'   => $this->content_en,
                'content_km'   => $this->content_km,
                'main_image'   => $this->main_image,
                'gallery'      => $this->images->pluck('image_url'),
                'status'       => $this->status,
                'source'       => $this->source,
                'category'     => [
                    'id'   => $this->category?->id,
                    'name' => $this->category?->name,
                ],
                'author'       => [
                    'id'   => $this->user?->id,
                    'name' => $this->user?->name,
                ],
                'published_at' => $this->published_at,
                'created_at'   => $this->created_at,
            ];
    }
}
