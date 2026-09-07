<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRegistrationFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'conference_id' => [
                'required',
                Rule::exists('conferences', 'id')->where(fn($query) => $query->where('is_active', true)),
            ],
            'name'          => ['required', 'string', 'max:255'],
            'mode'          => ['required', 'in:offline,online'],
            'price'         => ['required', 'numeric', 'min:0'],
        ];
    }
}
