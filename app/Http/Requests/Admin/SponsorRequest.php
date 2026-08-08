<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SponsorRequest extends FormRequest
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
            'website'       => ['nullable', 'url', 'max:255'],
            'tier'          => ['required', 'in:title,platinum,gold,silver,bronze,exhibitor'],
            'description'   => ['nullable', 'string'],
            'is_active'     => ['boolean'],
            'order'         => ['integer', 'min:0'],
            'logo'          => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:2048'],
        ];
    }
}
