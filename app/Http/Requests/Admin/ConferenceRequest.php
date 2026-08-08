<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ConferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'tagline'     => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date'  => ['nullable', 'date'],
            'end_date'    => ['nullable', 'date', 'after_or_equal:start_date'],
            'venue'       => ['nullable', 'string', 'max:255'],
            'city'        => ['nullable', 'string', 'max:100'],
            'country'     => ['nullable', 'string', 'max:100'],
            'theme'       => ['nullable', 'string', 'max:255'],
            'website'     => ['nullable', 'url', 'max:255'],
            'email'       => ['nullable', 'email', 'max:255'],
            'status'      => ['required', 'in:draft,active,archived'],
            'is_active'   => ['boolean'],
        ];
    }
}
