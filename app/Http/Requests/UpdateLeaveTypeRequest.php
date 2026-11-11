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
            'name' => ['required', 'string', 'max:255', "unique:leave_types,name,{$leaveTypeId}"],
            'quota' => ['required', 'integer', 'min:0'],
            'gender' => ['nullable', 'in:L,P'],
            'min_years' => ['required', 'integer', 'min:0'],
            'advance_notice_days' => ['required', 'integer', 'min:0', 'max:365'],
            'is_active' => ['boolean'],
            'is_annual_leave' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama jenis cuti wajib diisi.',
            'name.unique' => 'Nama jenis cuti sudah terdaftar.',
            'quota.min' => 'Kuota tidak boleh negatif.',
            'advance_notice_days.required' => 'Batas pengajuan wajib diisi (isi 0 jika tanpa aturan mendadak).',
            'advance_notice_days.integer' => 'Batas pengajuan harus berupa angka bulat.',
            'advance_notice_days.min' => 'Batas pengajuan tidak boleh negatif.',
            'advance_notice_days.max' => 'Batas pengajuan maksimal 365 hari.',
        ];
    }
}
