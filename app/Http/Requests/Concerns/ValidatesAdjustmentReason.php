<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Rule;

trait ValidatesAdjustmentReason
{
    protected function adjustmentReasonRules(): array
    {
        return [
            'required',
            'string',
            'min:10',
            'max:500',
            'regex:/^[a-zA-Z0-9\s.,()\/-]+$/',
        ];
    }

    protected function adjustmentConfirmationRules(string $keyword): array
    {
        return ['required', 'string', Rule::in([$keyword])];
    }

    protected function adjustmentReasonMessages(string $keyword): array
    {
        return [
            'alasan.required'       => 'Anda harus mengisi alasan.',
            'alasan.min'            => 'Alasan minimal 10 karakter.',
            'alasan.max'            => 'Alasan maksimal 500 karakter.',
            'alasan.regex'          => 'Alasan hanya boleh berisi huruf, angka, spasi, dan tanda . , ( ) / -',
            'confirmation.required' => 'Anda harus mengetik kata konfirmasi.',
            'confirmation.in'       => "Konfirmasi tidak valid. Ketik {$keyword} (huruf kapital semua) untuk melanjutkan.",
        ];
    }
}
