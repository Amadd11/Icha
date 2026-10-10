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
            'conference_id' => ['required', 'exists:conferences,id'],
            'name'          => ['required', 'string', 'max:255'],
            'type'          => ['required', 'in:presenter,non_presenter'],
            'mode'          => ['required', 'in:offline,online'],
            'category'      => ['nullable', 'string', 'max:50'],
            'price'         => ['required', 'numeric', 'min:0'],
            'is_active'     => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'conference_id.required' => 'Konferensi wajib dipilih.',
            'name.required'          => 'Nama paket biaya wajib diisi.',
            'type.required'          => 'Tipe peserta (Presenter / Non-Presenter) wajib dipilih.',
            'mode.required'          => 'Mode kehadiran (Offline / Online) wajib dipilih.',
            'price.required'         => 'Nominal harga registrasi wajib diisi.',
            'price.min'              => 'Nominal harga tidak boleh kurang dari 0.',
        ];
    }
}
