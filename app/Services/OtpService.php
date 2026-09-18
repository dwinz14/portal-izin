<?php

namespace App\Services;

use App\DTOs\WhatsAppMessage;
use App\Mail\OtpMail;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    const EXPIRE_MINUTES      = 10;
    const MAX_ATTEMPTS        = 3;
    const RESEND_COOLDOWN     = 60;
    const MAX_RESEND_PER_HOUR = 3;

    /**
     * Generate dan kirim OTP ke user.
     *
     * Return array info pengiriman, atau false jika terkena rate limit:
     * [
     *   'channels' => ['email'] | ['whatsapp'] | ['email', 'whatsapp'],
     *   'masked'   => 'j***@gmail.com' | '0812****789',
     * ]
     */
    public function send(User $user, string $purpose): false|array
    {
        // 1. Cek cooldown antar pengiriman (60 detik)
        $last = OtpCode::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->latest()
            ->first();

        if ($last && $last->created_at->diffInSeconds(now()) < self::RESEND_COOLDOWN) {
            return false;
        }

        // 2. Cek batas kirim per jam (maks 3x)
        $countThisHour = OtpCode::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($countThisHour >= self::MAX_RESEND_PER_HOUR) {
            return false;
        }

        // 3. Hapus OTP lama yang belum dipakai
        OtpCode::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->delete();

        // 4. Generate OTP baru (6 digit, cryptographically secure)
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpCode::create([
            'user_id'       => $user->id,
            'purpose'       => $purpose,
            'code_hash'     => Hash::make($code),
            'attempt_count' => 0,
            'expires_at'    => now()->addMinutes(self::EXPIRE_MINUTES),
        ]);

        // 5. Tentukan channel berdasarkan config + ketersediaan nomor WA user
        $configChannel = config('otp.delivery_channel', 'email');
        $phone         = $user->routeNotificationForWhatsApp(); // null jika tidak ada/tidak valid

        $channels = match ($configChannel) {
            'whatsapp' => $phone ? ['whatsapp'] : ['email'],         // fallback email jika no phone
            'both'     => $phone ? ['email', 'whatsapp'] : ['email'], // WA hanya jika ada phone
            default    => ['email'],
        };

        // 6. Kirim ke setiap channel yang aktif
        $sentChannels  = [];
        $maskedContact = '';

        foreach ($channels as $ch) {
            if ($ch === 'email') {
                Mail::to($user->email)->queue(new OtpMail($user, $code, $purpose));
                $sentChannels[] = 'email';
                if (! $maskedContact) {
                    $maskedContact = $this->maskEmail($user->email);
                }
            } elseif ($ch === 'whatsapp' && $phone) {
                $this->sendViaWhatsApp($phone, $code, $purpose);
                $sentChannels[] = 'whatsapp';
                if (! $maskedContact) {
                    $maskedContact = $this->maskPhone($phone);
                }
            }
        }

        return [
            'channels' => $sentChannels,
            'masked'   => $maskedContact,
        ];
    }

    /**
     * Verifikasi OTP yang diinput user.
     * Return: 'valid' | 'not_found' | 'expired' | 'max_attempt' | 'invalid'
     */
    public function verify(User $user, string $purpose, string $code): string
    {
        $otp = OtpCode::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->latest()
            ->first();

        if (! $otp) return 'not_found';
        if ($otp->isExpired()) return 'expired';
        if ($otp->isMaxAttempt()) return 'max_attempt';

        if (! Hash::check($code, $otp->code_hash)) {
            $otp->increment('attempt_count');
            return 'invalid';
        }

        $otp->update(['used_at' => now()]);
        return 'valid';
    }

    /**
     * Sisa detik cooldown sebelum boleh resend.
     */
    public function resendCooldownSeconds(User $user, string $purpose): int
    {
        $last = OtpCode::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->latest()
            ->first();

        if (! $last) return 0;

        return max(0, self::RESEND_COOLDOWN - $last->created_at->diffInSeconds(now()));
    }

    // ── Helpers publik (dipakai controller sebagai fallback) ─────────────────

    public function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);
        $visible = min(3, strlen($local));
        return substr($local, 0, $visible)
            . str_repeat('*', max(1, strlen($local) - $visible))
            . '@' . $domain;
    }

    public function maskPhone(string $phone): string
    {
        // Tampilkan dalam format lokal (628xxx → 08xxx)
        $local = str_starts_with($phone, '62') ? '0' . substr($phone, 2) : $phone;

        $len = strlen($local);
        if ($len <= 7) return $local;

        $visibleStart = 4;
        $visibleEnd   = 3;
        $maskedCount  = max(2, $len - $visibleStart - $visibleEnd);

        return substr($local, 0, $visibleStart)
            . str_repeat('*', $maskedCount)
            . substr($local, -$visibleEnd);
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function sendViaWhatsApp(string $phone, string $code, string $purpose): void
    {
        $label = $purpose === 'verify_email' ? 'Verifikasi Email' : 'Reset Password';

        $message = WhatsAppMessage::create(
            " Kode OTP {$label}\n" .
                "" . config('app.name') . "\n\n" .
                "Kode Anda:\n\n" .
                "     *{$code}*\n\n" .
                "Berlaku " . self::EXPIRE_MINUTES . " menit.\n" .
                "Jangan bagikan kode ini kepada siapapun.\n\n" .
                "_Jika Anda tidak merasa melakukan tindakan ini, abaikan pesan ini._"
        );

        app('whatsapp')->send($phone, $message);
    }
}
