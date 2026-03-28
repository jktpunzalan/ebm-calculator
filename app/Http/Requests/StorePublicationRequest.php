<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePublicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:500',
            'authors' => 'required|string|max:500',
            'slug' => 'required|string|max:200|unique:publications,slug|regex:/^[a-z0-9-]+$/',
            'type' => 'required|in:therapy,diagnosis,harm,prognosis,systematic_review,economic_evaluation,cpg,screening,other',
            'audience_tags' => 'nullable|array',
            'html_file' => 'required|file|mimes:html|max:5120',
            'thumbnail' => 'nullable|image|max:2048',
            'published_at' => 'nullable|date',
        ];
    }
}
