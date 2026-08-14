<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDivisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_divisi' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s]+$/',
                'unique:divisions,nama_divisi',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_divisi.required' => 'Nama divisi wajib diisi.',
            'nama_divisi.regex'    => 'Nama divisi hanya boleh huruf dan spasi.',
            'nama_divisi.unique'   => 'Nama divisi sudah terdaftar.',
        ];
    }
}
