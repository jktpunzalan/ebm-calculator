<?php

namespace App\Http\Requests\Appraisal;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppraisalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'study_type' => ['required','string','max:255'],
            'title' => ['required','string','max:255'],
            'checklist' => ['required','array'],
            'authors' => ['nullable','string','max:255'],
            'year' => ['nullable','integer','min:0','max:32767'],
            'notes' => ['nullable','string'],
            'validity_score' => ['nullable','integer','min:0','max:127'],
        ];
    }
}
