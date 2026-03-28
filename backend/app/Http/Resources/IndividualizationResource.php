<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IndividualizationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'library_id' => $this->library_id,
            'patient_notes' => $this->patient_notes,
            'baseline_risk_rc' => $this->baseline_risk_rc,
            'relative_risk_rr' => $this->relative_risk_rr,
            'risk_on_treatment_rt' => $this->risk_on_treatment_rt,
            'arr' => $this->arr,
            'nnt_nnh' => $this->nnt_nnh,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
