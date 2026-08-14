<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255', 'unique:leave_types,name'],
            'quota'     => ['required', 'integer', 'min:0'],
            'gender'    => ['nullable', 'in:L,P'],
            'min_years' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'   => 'Nama jenis cuti wajib diisi.',
            'name.unique'     => 'Nama jenis cuti sudah terdaftar.',
            'quota.required'  => 'Kuota wajib diisi.',
            'quota.min'       => 'Kuota tidak boleh negatif.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Normalisasi nama sebelum validasi
        if ($this->has('name')) {
            $this->merge([
                'name' => strtolower(strip_tags(trim($this->name))),
            ]);
        }
    }
}
