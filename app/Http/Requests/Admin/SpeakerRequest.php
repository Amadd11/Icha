<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SpeakerRequest extends FormRequest
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
            'title'         => ['nullable', 'string', 'max:100'],
            'institution'   => ['nullable', 'string', 'max:255'],
            'country'       => ['nullable', 'string', 'max:100'],
            'bio'           => ['nullable', 'string'],
            'email'         => ['nullable', 'email', 'max:255'],
            'type'          => ['required', 'in:keynote,invited,plenary'],
            'order'         => ['integer', 'min:0'],
            'photo'         => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }
}
