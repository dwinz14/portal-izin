<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],

            'nik' => [
                'required',
                'string',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],

            // Nomor WA — opsional, format Indonesia
            'phone' => [
                'nullable',
                'string',
                'regex:/^(\+62|62|0)[0-9]{8,13}$/',
            ],

            'position_id' => ['nullable', 'exists:positions,id'],
            'office_id'   => ['nullable', 'exists:offices,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Format nomor tidak valid. Gunakan format 08xxx, 628xxx, atau +628xxx.',
        ];
    }
}
