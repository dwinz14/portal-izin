<?php

namespace App\Mail;

use App\Models\User;
use App\Services\OtpService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User   $user,
        public readonly string $code,
        public readonly string $purpose,
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->purpose) {
            'verify_email'   => 'Kode Verifikasi Email — ' . config('app.name'),
            'reset_password' => 'Kode Reset Password — ' . config('app.name'),
            default          => 'Kode OTP — ' . config('app.name'),
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
            with: [
                'user'          => $this->user,
                'code'          => $this->code,
                'purpose'       => $this->purpose,
                'expireMinutes' => OtpService::EXPIRE_MINUTES,
            ]
        );
    }
}
