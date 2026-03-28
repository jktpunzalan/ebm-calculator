<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudyLibraryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'appraisal_id' => $this->appraisal_id,
            'title' => $this->title,
            'study_type' => $this->study_type,
            'validity_label' => $this->validity_label,
            'key_result_label' => $this->key_result_label,
            'key_result_value' => $this->key_result_value,
            'is_starred' => $this->is_starred,
            'appraisal' => new AppraisalResource($this->whenLoaded('appraisal')),
            'saved_at' => $this->created_at,
        ];
    }
}
