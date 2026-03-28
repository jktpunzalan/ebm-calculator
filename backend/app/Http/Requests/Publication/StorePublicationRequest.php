<?php

namespace App\Http\Requests\Publication;

use Illuminate\Foundation\Http\FormRequest;

class StorePublicationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'slug' => ['required','string','max:255','unique:publications,slug'],
            'title' => ['required','string','max:255'],
            'authors' => ['required','string','max:255'],
            'study_type' => ['required','string','max:255'],
            'audience_tags' => ['nullable','array'],
            'html_file_path' => ['nullable','string','max:255'],
            'thumbnail_path' => ['nullable','string','max:255'],
            'published_at' => ['nullable','date'],
            'is_active' => ['sometimes','boolean'],
        ];
    }
}
