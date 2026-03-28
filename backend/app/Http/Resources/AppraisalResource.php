<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppraisalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'study_type' => $this->study_type,
            'title' => $this->title,
            'authors' => $this->authors,
            'year' => $this->year,
            'checklist' => $this->checklist,
            'notes' => $this->notes,
            'validity_score' => $this->validity_score,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
