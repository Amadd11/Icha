<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CommitteeRequest extends FormRequest
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
            'role'          => ['required', 'string', 'max:255'],
            'institution'   => ['nullable', 'string', 'max:255'],
            'group'         => ['required', 'in:steering,organizing,scientific'],
            'order'         => ['integer', 'min:0'],
        ];
    }
}
