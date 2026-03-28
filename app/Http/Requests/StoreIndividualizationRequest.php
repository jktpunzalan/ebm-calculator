<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIndividualizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'library_id' => 'required|exists:study_library,id',
            'patient_notes' => 'nullable|string',
            'baseline_risk_rc' => 'required|numeric|min:0|max:100',
            'relative_risk_rr' => 'required|numeric|min:0',
            'risk_on_treatment_rt' => 'required|numeric|min:0|max:100',
            'arr' => 'required|numeric',
            'nnt_nnh' => 'required|numeric',
        ];
    }
}
