<?php

namespace App\Http\Requests;

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
            'study_type' => 'nullable|in:therapy,diagnosis,harm,prognosis,systematic_review,economic_evaluation,cpg,screening',
            'title' => 'nullable|string|max:500',
            'authors' => 'nullable|string|max:500',
            'year' => 'nullable|integer|min:1900|max:2099',
            'checklist' => 'nullable|array',
            'notes' => 'nullable|string',
            'validity_score' => 'nullable|integer|min:0|max:10',
        ];
    }
}
