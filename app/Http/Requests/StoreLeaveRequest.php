<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\LeaveType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreLeaveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user      = $this->user();
        $leaveType = LeaveType::find($this->leave_type_id);

        // Cek apakah ini jenis cuti sakit
        $isSickLeave    = $leaveType && $this->isSickLeave($leaveType->name);
        $requiresProof  = $leaveType && strtolower($leaveType->name) === 'izin sakit dengan surat dokter';

        // Tentukan apakah user wajib pilih pengganti
        $requiresReplacement = $user->role instanceof UserRole
            ? $user->role->requiresReplacement()
            : in_array($user->role, ['staff', 'kasie', 'kabag-pincab'], true);

        // Tentukan apakah user wajib pilih atasan
        $requiresSupervisor = $user->role instanceof UserRole
            ? $user->role->requiresSupervisor()
            : !in_array($user->role, ['direksi'], true);

        return [
            'leave_type_id' => ['required', 'exists:leave_types,id'],

            'start_date' => array_filter([
                'required',
                'date',
                $isSickLeave ? 'before_or_equal:today' : null,
            ]),

            'end_date' => array_filter([
                'required',
                'date',
                'after_or_equal:start_date',
                $isSickLeave ? 'before_or_equal:today' : null,
            ]),

            'alasan' => [
                'required',
                'string',
                'max:500',
                'regex:/^[a-zA-Z0-9\s.,()\/-]+$/',
            ],

            'proof_image' => [
                $requiresProof ? 'required' : 'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048',
            ],

            'pengganti_id' => [
                $requiresReplacement ? 'required' : 'nullable',
                'exists:users,id',
            ],

            'atasan_id' => [
                $requiresSupervisor ? 'required' : 'nullable',
                'exists:users,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'leave_type_id.required' => 'Anda harus memilih jenis cuti.',
            'leave_type_id.exists'   => 'Jenis cuti tidak valid.',
            'start_date.required'    => 'Anda harus memilih tanggal mulai cuti.',
            'start_date.date'        => 'Format tanggal mulai tidak valid.',
            'end_date.required'      => 'Anda harus memilih tanggal selesai cuti.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'alasan.required'        => 'Anda harus mengisi alasan cuti.',
            'alasan.max'             => 'Alasan cuti maksimal 500 karakter.',
            'proof_image.required'   => 'Anda harus menyertakan bukti surat dokter.',
            'proof_image.image'      => 'File bukti harus berupa gambar.',
            'proof_image.max'        => 'Ukuran gambar maksimal 2MB.',
            'pengganti_id.required'  => 'Anda harus memilih pengganti.',
            'pengganti_id.exists'    => 'Pengganti yang dipilih tidak valid.',
            'atasan_id.required'     => 'Anda harus memilih atasan.',
            'atasan_id.exists'       => 'Atasan yang dipilih tidak valid.',
        ];
    }

    /**
     * Cek apakah jenis cuti termasuk kategori izin sakit
     */
    private function isSickLeave(string $name): bool
    {
        $name = strtolower($name);
        return str_contains($name, 'izin sakit dengan surat dokter')
            || str_contains($name, 'izin sakit tanpa surat dokter');
    }
}
