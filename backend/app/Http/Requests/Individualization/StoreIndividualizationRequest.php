<?php

namespace App\Http\Requests\Individualization;

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
            'library_id' => ['nullable','exists:study_library,id'],
            'baseline_risk_rc' => ['required','numeric'],
            'relative_risk_rr' => ['required','numeric'],
            'patient_notes' => ['nullable','string'],
        ];
    }
}
