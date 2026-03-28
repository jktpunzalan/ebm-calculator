<?php

namespace App\Http\Requests\Publication;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePublicationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('id');
        return [
            'slug' => ['sometimes','required','string','max:255', Rule::unique('publications','slug')->ignore($id)],
            'title' => ['sometimes','required','string','max:255'],
            'authors' => ['sometimes','required','string','max:255'],
            'study_type' => ['sometimes','required','string','max:255'],
            'audience_tags' => ['nullable','array'],
            'html_file_path' => ['nullable','string','max:255'],
            'thumbnail_path' => ['nullable','string','max:255'],
            'published_at' => ['nullable','date'],
            'is_active' => ['sometimes','boolean'],
        ];
    }
}
