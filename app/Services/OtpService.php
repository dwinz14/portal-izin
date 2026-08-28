<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    const EXPIRE_MINUTES      = 10;
    const MAX_ATTEMPTS        = 3;
    const RESEND_COOLDOWN     = 60;  // detik
    const MAX_RESEND_PER_HOUR = 3;

    /**
     * Generate OTP baru, invalidate kode lama, kirim via email.
     * Return false jika terkena rate limit.
     */
    public function send(User $user, string $purpose): bool
    {
        // 1. Cek cooldown (minimal 60 detik antar pengiriman)
        $last = OtpCode::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->latest()
            ->first();

        if ($last && $last->created_at->diffInSeconds(now()) < self::RESEND_COOLDOWN) {
            return false;
        }

        // 2. Cek batas pengiriman per jam (maks 3x)
        $countThisHour = OtpCode::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($countThisHour >= self::MAX_RESEND_PER_HOUR) {
            return false;
        }

        // 3. Invalidate semua OTP lama yang belum dipakai
        OtpCode::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->delete();

        // 4. Generate 6-digit angka acak (cryptographically secure)
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // 5. Simpan hash-nya ke database (plaintext tidak pernah disimpan)
        OtpCode::create([
            'user_id'       => $user->id,
            'purpose'       => $purpose,
            'code_hash'     => Hash::make($code),
            'attempt_count' => 0,
            'expires_at'    => now()->addMinutes(self::EXPIRE_MINUTES),
        ]);

        // 6. Kirim email via queue
        Mail::to($user->email)->queue(new OtpMail($user, $code, $purpose));

        return true;
    }

    /**
     * Verifikasi OTP.
     * Return: 'valid' | 'not_found' | 'expired' | 'max_attempt' | 'invalid'
     */
    public function verify(User $user, string $purpose, string $code): string
    {
        $otp = OtpCode::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->latest()
            ->first();

        if (! $otp) {
            return 'not_found';
        }

        if ($otp->isExpired()) {
            return 'expired';
        }

        if ($otp->isMaxAttempt()) {
            return 'max_attempt';
        }

        if (! Hash::check($code, $otp->code_hash)) {
            $otp->increment('attempt_count');
            return 'invalid';
        }

        // Tandai OTP telah berhasil digunakan
        $otp->update(['used_at' => now()]);

        return 'valid';
    }

    /**
     * Hitung sisa detik cooldown sebelum bisa resend.
     */
    public function resendCooldownSeconds(User $user, string $purpose): int
    {
        $last = OtpCode::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->latest()
            ->first();

        if (! $last) {
            return 0;
        }

        $elapsed = $last->created_at->diffInSeconds(now());
        return max(0, self::RESEND_COOLDOWN - $elapsed);
    }
}
