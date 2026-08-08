<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'conference_id' => ['required', 'exists:conferences,id'],
            'name'          => ['required', 'string', 'max:255'],
            'badge'         => ['nullable', 'string', 'max:50'],
            'description'   => ['nullable', 'string'],
            'icon'          => ['nullable', 'string', 'max:50'],
            'order'         => ['integer', 'min:0'],
        ];
    }
}
