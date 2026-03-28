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
            'study_type' => $this->study_type,
            'audience_tags' => $this->audience_tags,
            'html_file_path' => $this->html_file_path,
            'thumbnail_path' => $this->thumbnail_path,
            'is_active' => $this->is_active,
            'published_at' => $this->published_at,
            'views' => $this->whenLoaded('stats', fn() => $this->stats->views ?? 0),
            'shares' => $this->whenLoaded('stats', fn() => $this->stats->shares ?? 0),
            'pdf_downloads' => $this->whenLoaded('stats', fn() => $this->stats->pdf_downloads ?? 0),
            'saves' => $this->whenLoaded('stats', fn() => $this->stats->saves ?? 0),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
