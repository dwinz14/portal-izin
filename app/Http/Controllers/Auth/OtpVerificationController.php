<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LeaveQuotaService;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Illuminate\Http\Request;

class OtpVerificationController extends Controller
{
    public function __construct(private OtpService $otpService) {}

    /** Tampilkan halaman verifikasi OTP setelah register. */
    public function showVerify(): View|RedirectResponse
    {
        if (! Session::has('verification_user_id')) {
            return redirect()->route('register');
        }

        $user = User::find(Session::get('verification_user_id'));

        if (! $user || $user->status !== 'pending') {
            Session::forget(['verification_user_id', 'verification_email']);
            return redirect()->route('register');
        }

        $maskedEmail = $this->maskEmail(Session::get('verification_email', $user->email));
        $cooldown    = $this->otpService->resendCooldownSeconds($user, 'verify_email');

        return view('auth.verify-otp', compact('maskedEmail', 'cooldown'));
    }

    /** Proses verifikasi OTP registrasi. */
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
            Session::forget(['verification_user_id', 'verification_email']);
            return redirect()->route('register');
        }

        $result = $this->otpService->verify($user, 'verify_email', $request->otp);

        if ($result !== 'valid') {
            return back()->withErrors(['otp' => match ($result) {
                'invalid'     => 'Kode OTP tidak valid. Periksa kembali kode yang Anda masukkan.',
                'expired'     => 'Kode OTP sudah kedaluwarsa. Silakan minta kode baru.',
                'max_attempt' => 'Terlalu banyak percobaan salah. Silakan minta kode baru.',
                default       => 'Verifikasi gagal. Silakan coba lagi.',
            }]);
        }

        // OTP valid — approve user, generate kuota, catat riwayat
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

        Session::forget(['verification_user_id', 'verification_email']);

        return redirect()->route('login')
            ->with('status', 'Akun berhasil diverifikasi! Silakan masuk dengan NIK dan password Anda.');
    }

    /** Kirim ulang OTP verifikasi. */
    public function resend(): RedirectResponse
    {
        if (! Session::has('verification_user_id')) {
            return redirect()->route('register');
        }

        $user = User::find(Session::get('verification_user_id'));

        if (! $user || $user->status !== 'pending') {
            Session::forget(['verification_user_id', 'verification_email']);
            return redirect()->route('register');
        }

        $sent = $this->otpService->send($user, 'verify_email');

        if (! $sent) {
            $cooldown = $this->otpService->resendCooldownSeconds($user, 'verify_email');
            return back()->withErrors(['otp' => "Harap tunggu {$cooldown} detik sebelum meminta kode baru."]);
        }

        return back()->with('status', 'Kode OTP baru telah dikirim ke email Anda.');
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);
        $visible = min(3, strlen($local));
        return substr($local, 0, $visible) . str_repeat('*', max(0, strlen($local) - $visible)) . '@' . $domain;
    }
}
