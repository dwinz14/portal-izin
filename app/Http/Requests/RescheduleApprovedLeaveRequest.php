<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesAdjustmentReason;
use Illuminate\Foundation\Http\FormRequest;

class RescheduleApprovedLeaveRequest extends FormRequest
{
    use ValidatesAdjustmentReason;

    public const CONFIRMATION = 'UBAH TANGGAL';

    /**
     * Otorisasi memakai LeavePolicy::adjustApproved() — sama dengan pembatalan.
     */
    public function authorize(): bool
    {
        $leave = $this->route('leave');

        return $leave && $this->user()?->can('adjustApproved', $leave);
    }

    public function rules(): array
    {
        return [
            'start_date'   => ['required', 'date'],
            'end_date'     => ['required', 'date', 'after_or_equal:start_date'],
            'alasan'       => $this->adjustmentReasonRules(),
            'confirmation' => $this->adjustmentConfirmationRules(self::CONFIRMATION),
        ];
    }

    public function messages(): array
    {
        return array_merge([
            'start_date.required'     => 'Anda harus memilih tanggal mulai yang baru.',
            'start_date.date'         => 'Format tanggal mulai tidak valid.',
            'end_date.required'       => 'Anda harus memilih tanggal selesai yang baru.',
            'end_date.date'           => 'Format tanggal selesai tidak valid.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ], $this->adjustmentReasonMessages(self::CONFIRMATION));
    }
}
