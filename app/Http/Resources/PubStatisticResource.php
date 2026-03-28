<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PubStatisticResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'views' => $this->views,
            'shares' => $this->shares,
            'pdf_downloads' => $this->pdf_downloads,
            'saves' => $this->saves,
            'updated_at' => $this->updated_at,
        ];
    }
}
