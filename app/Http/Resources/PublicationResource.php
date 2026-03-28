<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'authors' => $this->authors,
            'type' => $this->type,
            'audience_tags' => $this->audience_tags,
            'html_file_path' => $this->html_file_path,
            'thumbnail_path' => $this->thumbnail_path,
            'is_active' => $this->is_active,
            'published_at' => $this->published_at,
            'stats' => $this->when($this->relationLoaded('stats') && $this->stats, [
                'views' => $this->stats?->views,
                'shares' => $this->stats?->shares,
                'pdf_downloads' => $this->stats?->pdf_downloads,
                'saves' => $this->stats?->saves,
            ]),
        ];
    }
}
