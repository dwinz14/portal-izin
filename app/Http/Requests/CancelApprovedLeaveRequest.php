<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesAdjustmentReason;
use Illuminate\Foundation\Http\FormRequest;

class CancelApprovedLeaveRequest extends FormRequest
{
    use ValidatesAdjustmentReason;

    public const CONFIRMATION = 'BATALKAN';

    /**
     * Otorisasi memakai LeavePolicy::adjustApproved() — hanya atasan yang menyetujui
     * di langkah terakhir (atau HRD untuk cuti direksi).
     */
    public function authorize(): bool
    {
        $leave = $this->route('leave');

        return $leave && $this->user()?->can('adjustApproved', $leave);
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
