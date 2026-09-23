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
        $type       = $this->input('type');
        $updateType = $this->input('update_type');

        $isUpdateAttendance  = $type === AttendanceRequest::TYPE_UPDATE_ATTENDANCE;
        $isLeaveDuringWork   = $type === AttendanceRequest::TYPE_LEAVE_DURING_WORK;

        // Tentukan kebutuhan start_time
        // Tidak wajib hanya jika update_attendance + checkout_only
        $startTimeRequired = ! ($isUpdateAttendance && $updateType === AttendanceRequest::UPDATE_TYPE_CHECKOUT_ONLY);

        // Tentukan kebutuhan end_time
        // Wajib jika: leave_during_work, ATAU update_attendance + (both / checkout_only)
        $endTimeRequired = $isLeaveDuringWork
            || ($isUpdateAttendance && in_array($updateType, [
                AttendanceRequest::UPDATE_TYPE_BOTH,
                AttendanceRequest::UPDATE_TYPE_CHECKOUT_ONLY,
            ], true));

        // end_time harus after start_time hanya jika keduanya diisi (both)
        $endTimeAfterStart = $isUpdateAttendance
            && $updateType === AttendanceRequest::UPDATE_TYPE_BOTH;

        return [
            'type' => ['required', Rule::in(AttendanceRequest::TYPES)],

            'update_type' => [
                $isUpdateAttendance ? 'required' : 'nullable',
                Rule::when(
                    $isUpdateAttendance,
                    [Rule::in(AttendanceRequest::UPDATE_TYPES)]
                ),
            ],

            'date' => ['required', 'date'],

            'start_time' => [
                $startTimeRequired ? 'required' : 'nullable',
                'date_format:H:i',
            ],

            'end_time' => [
                $endTimeRequired ? 'required' : 'nullable',
                'date_format:H:i',
                Rule::when($endTimeAfterStart, ['after:start_time']),
            ],

            'reason' => [
                'required',
                'string',
                'max:500',
                'regex:/^[a-zA-Z0-9\s.,()\/:\-]+$/',
            ],

            'proof_image' => [
                $isUpdateAttendance ? 'required' : 'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048',
            ],

            'approver_id' => [
                'required',
                'exists:users,id',
                Rule::notIn([$this->user()->id]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'        => 'Anda harus memilih jenis pengajuan kehadiran.',
            'type.in'              => 'Jenis pengajuan kehadiran tidak valid.',
            'update_type.required' => 'Pilih bagian absensi yang ingin diupdate.',
            'update_type.in'       => 'Pilihan update absensi tidak valid.',
            'date.required'        => 'Tanggal pengajuan wajib diisi.',
            'date.date'            => 'Format tanggal tidak valid.',
            'start_time.required'  => 'Jam check-in wajib diisi.',
            'start_time.date_format' => 'Format waktu mulai harus HH:MM.',
            'end_time.required'    => 'Jam check-out wajib diisi.',
            'end_time.date_format' => 'Format waktu selesai harus HH:MM.',
            'end_time.after'       => 'Jam check-out harus setelah jam check-in.',
            'reason.required'      => 'Alasan pengajuan wajib diisi.',
            'reason.max'           => 'Alasan maksimal 500 karakter.',
            'reason.regex'         => 'Alasan hanya boleh berisi huruf, angka, spasi, dan tanda baca umum.',
            'proof_image.required' => 'Foto bukti wajib dilampirkan untuk pengajuan update absensi.',
            'proof_image.image'    => 'File bukti harus berupa gambar.',
            'proof_image.mimes'    => 'Format bukti harus JPG, PNG, JPEG, atau GIF.',
            'proof_image.max'      => 'Ukuran gambar maksimal 2MB.',
            'approver_id.required' => 'Anda harus memilih atasan langsung.',
            'approver_id.exists'   => 'Atasan yang dipilih tidak valid.',
            'approver_id.not_in'   => 'Atasan tidak boleh sama dengan pemohon.',
        ];
    }
}
