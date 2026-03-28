<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLibraryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'appraisal_id' => 'required|exists:appraisals,id',
            'key_result_label' => 'nullable|string|max:100',
            'key_result_value' => 'nullable|string|max:100',
            'validity_label' => 'nullable|in:high,moderate,low',
        ];
    }
}
