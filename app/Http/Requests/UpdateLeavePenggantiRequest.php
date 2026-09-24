<?php

namespace App\Http\Requests;

use App\Services\PenggantiEligibilityService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeavePenggantiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Otorisasi memakai LeavePolicy::changePengganti() — hanya atasan terkait
     */
    public function authorize(): bool
    {
        $leave = $this->route('leave');

        return $leave && $this->user()?->can('changePengganti', $leave);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $leave = $this->route('leave');

        return [
            'new_pengganti_id' => [
                'required',
                'integer',
                'exists:users,id',
                Rule::notIn(array_filter([$leave->pengganti_id, $leave->user_id])),
                function ($attribute, $value, $fail) use ($leave) {
                    $eligibleIds = app(PenggantiEligibilityService::class)
                        ->forRequester($leave->user)
                        ->pluck('id');

                    if (! $eligibleIds->contains((int) $value)) {
                        $fail('Pengganti yang dipilih tidak memenuhi aturan penempatan pengganti untuk pemohon ini.');
                    }
                },
            ],

            'confirmation' => ['required', 'string', 'in:GANTI'],
        ];
    }

    public function messages(): array
    {
        return [
            'new_pengganti_id.required' => 'Anda harus memilih pengganti baru.',
            'new_pengganti_id.integer'  => 'Pengganti yang dipilih tidak valid.',
            'new_pengganti_id.exists'   => 'Pengganti yang dipilih tidak ditemukan.',
            'new_pengganti_id.not_in'   => 'Pengganti baru harus berbeda dari pengganti saat ini dan bukan pemohon itu sendiri.',
            'confirmation.required'     => 'Anda harus mengetik kata konfirmasi.',
            'confirmation.in'           => 'Konfirmasi tidak valid. Ketik GANTI (huruf kapital semua) untuk melanjutkan.',
        ];
    }
}
