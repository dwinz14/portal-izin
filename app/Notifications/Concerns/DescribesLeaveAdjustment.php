<?php

namespace App\Notifications\Concerns;

use Carbon\Carbon;

/**
 * Bahan teks bersama untuk notifikasi pembatalan / perubahan tanggal cuti.
 * Semua data diambil dari LeaveAdjustment (before/after/quota/snapshot), BUKAN dari model Leave,
 * karena pada pembatalan baris cutinya sudah terhapus.
 *
 * Kelas pemakai wajib punya properti: LeaveAdjustment $adjustment, string $recipientRole.
 */
trait DescribesLeaveAdjustment
{
    /** Penerima adalah pemilik cuti (bukan pengganti). */
    protected function isRequester(): bool
    {
        return $this->recipientRole === 'pemohon';
    }

    protected function adjustmentActor(): string
    {
        return ucwords($this->adjustment->snapshot['actor']['name'] ?? 'Atasan');
    }

    protected function adjustmentEmployee(): string
    {
        return ucwords($this->adjustment->snapshot['employee']['name'] ?? '-');
    }

    protected function formatDate(string $date): string
    {
        return Carbon::parse($date)->isoFormat('D MMMM Y');
    }

    /** "2 November 2026 s/d 4 November 2026" dari array before/after. */
    protected function periodText(array $state): string
    {
        return $this->formatDate($state['start_date']) . ' s/d ' . $this->formatDate($state['end_date']);
    }

    /** Kalimat perubahan kuota untuk pemilik cuti; null bila kuota tidak berubah. */
    protected function quotaSentence(): ?string
    {
        $quota = $this->adjustment->quota;

        if (! is_array($quota) || ! ($quota['applied'] ?? false)) {
            return null;
        }

        $delta = (int) ($quota['delta_used'] ?? 0); // positif = terpakai, negatif = kembali

        if ($delta === 0) {
            return null;
        }

        $remaining = isset($quota['remaining_after'])
            ? " Sisa kuota Anda sekarang {$quota['remaining_after']} hari."
            : '';

        return $delta < 0
            ? abs($delta) . ' hari kuota dikembalikan.' . $remaining
            : "{$delta} hari tambahan dipotong dari kuota." . $remaining;
    }
}
