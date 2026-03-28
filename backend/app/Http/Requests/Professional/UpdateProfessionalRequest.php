<?php

namespace App\Http\Requests\Professional;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfessionalRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['sometimes','required','string','max:255'],
            'role' => ['sometimes','required','string','max:255'],
            'bio' => ['nullable','string'],
            'photo_path' => ['nullable','string','max:255'],
            'credentials' => ['nullable','array'],
            'contact' => ['nullable','array'],
            'display_order' => ['nullable','integer'],
            'is_active' => ['nullable','boolean'],
        ];
    }
}
