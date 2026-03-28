<?php

namespace App\Http\Requests\Appraisal;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppraisalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'study_type' => ['sometimes','required','string','max:255'],
            'title' => ['sometimes','required','string','max:255'],
            'checklist' => ['sometimes','required','array'],
            'authors' => ['nullable','string','max:255'],
            'year' => ['nullable','integer','min:0','max:32767'],
            'notes' => ['nullable','string'],
            'validity_score' => ['nullable','integer','min:0','max:127'],
        ];
    }
}
