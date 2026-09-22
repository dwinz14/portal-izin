<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LeaveQuotaService;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use App\Services\ActivityLogger;

class OtpVerificationController extends Controller
{
    public function __construct(private OtpService $otpService) {}

    public function showVerify(): View|RedirectResponse
    {
        if (! Session::has('verification_user_id')) {
            return redirect()->route('register');
        }

        $user = User::find(Session::get('verification_user_id'));

        if (! $user || $user->status !== 'pending') {
            Session::forget(['verification_user_id', 'verification_email', 'verification_delivery']);
            return redirect()->route('register');
        }

        // Ambil delivery info — fallback ke email jika session belum ada
        $delivery = Session::get('verification_delivery', [
            'channels' => ['email'],
            'masked'   => $this->otpService->maskEmail(
                Session::get('verification_email', $user->email)
            ),
        ]);

        $cooldown = $this->otpService->resendCooldownSeconds($user, 'verify_email');

        return view('auth.verify-otp', compact('delivery', 'cooldown'));
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
        ], [
            'otp.size'  => 'Kode OTP harus terdiri dari 6 digit.',
            'otp.regex' => 'Kode OTP hanya boleh berisi angka.',
        ]);

        if (! Session::has('verification_user_id')) {
            return redirect()->route('register');
        }

        $user = User::find(Session::get('verification_user_id'));

        if (! $user || $user->status !== 'pending') {
            Session::forget(['verification_user_id', 'verification_email', 'verification_delivery']);
            return redirect()->route('register');
        }

        $result = $this->otpService->verify($user, 'verify_email', $request->otp);

        if ($result !== 'valid') {
            return back()->withErrors(['otp' => match ($result) {
                'invalid'     => 'Kode OTP tidak valid. Periksa kembali.',
                'expired'     => 'Kode OTP sudah kedaluwarsa. Silakan minta kode baru.',
                'max_attempt' => 'Terlalu banyak percobaan salah. Silakan minta kode baru.',
                default       => 'Verifikasi gagal. Silakan coba lagi.',
            }]);
        }

        DB::transaction(function () use ($user) {
            $user->update([
                'status'            => 'approved',
                'email_verified_at' => now(),
            ]);

            app(LeaveQuotaService::class)->generateForUser($user, now()->year);

            DB::table('user_registration_approvals')->insert([
                'user_name'     => $user->name,
                'user_nik'      => $user->nik,
                'user_email'    => $user->email,
                'user_role'     => $user->role,
                'division_name' => $user->division?->nama_divisi,
                'approved_by'   => null,
                'status'        => 'approved',
                'verified_via'  => 'otp',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        });

        ActivityLogger::logAs($user, 'auth.otp_verified', 'Verifikasi OTP berhasil — akun aktif');
        Session::forget(['verification_user_id', 'verification_email', 'verification_delivery']);

        return redirect()->route('login')
            ->with('status', 'Akun berhasil diverifikasi! Silakan masuk dengan NIK dan password Anda.');
    }

    public function resend(): RedirectResponse
    {
        if (! Session::has('verification_user_id')) {
            return redirect()->route('register');
        }

        $user = User::find(Session::get('verification_user_id'));

        if (! $user || $user->status !== 'pending') {
            Session::forget(['verification_user_id', 'verification_email', 'verification_delivery']);
            return redirect()->route('register');
        }

        $delivery = $this->otpService->send($user, 'verify_email');

        if (! $delivery) {
            $cooldown = $this->otpService->resendCooldownSeconds($user, 'verify_email');
            return back()->withErrors(['otp' => "Harap tunggu {$cooldown} detik sebelum meminta kode baru."]);
        }

        // Perbarui delivery info di session (channel bisa berubah jika nomor baru ditambahkan)
        Session::put('verification_delivery', $delivery);

        $channelText = $this->deliveryChannelText($delivery['channels']);
        return back()->with('status', "Kode OTP baru telah dikirim ke {$channelText} Anda.");
    }

    private function deliveryChannelText(array $channels): string
    {
        if (in_array('email', $channels) && in_array('whatsapp', $channels)) {
            return 'email dan WhatsApp';
        }
        return in_array('whatsapp', $channels) ? 'WhatsApp' : 'email';
    }
}
