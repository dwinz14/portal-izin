<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $leaveTypeId = $this->route('leave_type')->id;

        return [
            'name'      => ['required', 'string', 'max:255', "unique:leave_types,name,{$leaveTypeId}"],
            'quota'     => ['required', 'integer', 'min:0'],
            'gender'    => ['nullable', 'in:L,P'],
            'min_years' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama jenis cuti wajib diisi.',
            'name.unique'   => 'Nama jenis cuti sudah terdaftar.',
            'quota.min'     => 'Kuota tidak boleh negatif.',
        ];
    }
}
