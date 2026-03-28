<?php

namespace App\Http\Requests\Library;

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
            'appraisal_id' => ['nullable','exists:appraisals,id'],
            'title' => ['required','string','max:255'],
            'study_type' => ['required','string','max:255'],
            'validity_label' => ['required','string','max:255'],
            'key_result_label' => ['required','string','max:255'],
            'key_result_value' => ['required','string','max:255'],
            'is_starred' => ['sometimes','boolean'],
            'saved_at' => ['required','date'],
        ];
    }
}
