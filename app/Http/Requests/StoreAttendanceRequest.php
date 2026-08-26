<?php

namespace App\Http\Requests;

use App\Models\AttendanceRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $type = $this->input('type');
        $endTimeRequired = in_array($type, [
            AttendanceRequest::TYPE_LEAVE_DURING_WORK,
            AttendanceRequest::TYPE_UPDATE_ATTENDANCE,
        ], true);

        return [
            'type' => ['required', Rule::in(AttendanceRequest::TYPES)],
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => [
                $endTimeRequired ? 'required' : 'nullable',
                'date_format:H:i',
                Rule::when($endTimeRequired, ['after:start_time']),
            ],
            'reason' => [
                'required',
                'string',
                'max:500',
                'regex:/^[a-zA-Z0-9\s.,()\/:\-]+$/',
            ],
            'proof_image' => [
                // $type === AttendanceRequest::TYPE_UPDATE_ATTENDANCE ? 'required' : 'nullable',
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048',
            ],
            'approver_id' => ['required', 'exists:users,id', Rule::notIn([$this->user()->id])],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Anda harus memilih jenis pengajuan kehadiran.',
            'type.in' => 'Jenis pengajuan kehadiran tidak valid.',
            'date.required' => 'Tanggal pengajuan wajib diisi.',
            'date.date' => 'Format tanggal tidak valid.',
            'start_time.required' => 'Waktu mulai wajib diisi.',
            'start_time.date_format' => 'Format waktu mulai harus HH:MM.',
            'end_time.required' => 'Waktu selesai wajib diisi untuk jenis pengajuan ini.',
            'end_time.date_format' => 'Format waktu selesai harus HH:MM.',
            'end_time.after' => 'Waktu selesai harus setelah waktu mulai.',
            'reason.required' => 'Alasan pengajuan wajib diisi.',
            'reason.max' => 'Alasan maksimal 500 karakter.',
            'reason.regex' => 'Alasan hanya boleh berisi huruf, angka, spasi, dan tanda baca umum.',
            // 'proof_image.required' => 'Bukti gambar wajib dilampirkan untuk update absensi.',
            'proof_image.image' => 'File bukti harus berupa gambar.',
            'proof_image.mimes' => 'Format bukti harus JPG, PNG, JPEG, atau GIF.',
            'proof_image.max' => 'Ukuran gambar maksimal 2MB.',
            'approver_id.required' => 'Anda harus memilih atasan langsung.',
            'approver_id.exists' => 'Atasan yang dipilih tidak valid.',
            'approver_id.not_in' => 'Atasan tidak boleh sama dengan pemohon.',
        ];
    }
}
