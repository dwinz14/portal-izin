<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesAdjustmentReason;
use Illuminate\Foundation\Http\FormRequest;

class DeleteApprovedAttendanceRequest extends FormRequest
{
    use ValidatesAdjustmentReason;

    public const CONFIRMATION = 'HAPUS';

    /**
     * Otorisasi memakai AttendanceRequestPolicy::deleteApproved() —
     * hanya atasan yang menyetujui pengajuan tersebut.
     */
    public function authorize(): bool
    {
        $attendanceRequest = $this->route('attendanceRequest');

        return $attendanceRequest && $this->user()?->can('deleteApproved', $attendanceRequest);
    }

    public function rules(): array
    {
        return [
            'alasan'       => $this->adjustmentReasonRules(),
            'confirmation' => $this->adjustmentConfirmationRules(self::CONFIRMATION),
        ];
    }

    public function messages(): array
    {
        return $this->adjustmentReasonMessages(self::CONFIRMATION);
    }
}
