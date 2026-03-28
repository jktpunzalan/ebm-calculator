<?php

namespace App\Http\Requests\Journal;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJournalRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => ['sometimes','required','string','max:255'],
            'authors' => ['sometimes','required','string','max:255'],
            'abstract' => ['nullable','string'],
            'doi' => ['nullable','string','max:255'],
            'volume' => ['nullable','string','max:255'],
            'issue' => ['nullable','string','max:255'],
            'year' => ['sometimes','required','integer','min:0','max:32767'],
            'pdf_path' => ['nullable','string','max:255'],
            'published_at' => ['nullable','date'],
        ];
    }
}
