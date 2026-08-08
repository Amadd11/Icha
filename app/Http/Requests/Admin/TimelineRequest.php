<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TimelineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'conference_id' => ['required', 'exists:conferences,id'],
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'date'          => ['nullable', 'date'],
            'period'        => ['nullable', 'string', 'max:100'],
            'is_completed'  => ['boolean'],
            'order'         => ['integer', 'min:0'],
        ];
    }
}
