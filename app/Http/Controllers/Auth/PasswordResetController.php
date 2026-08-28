<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function __construct(private OtpService $otpService) {}

    // ── STEP 1: Input NIK & Kirim OTP ──────────────────────────────────────

    public function request(): View
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'nik' => ['required', 'string', 'size:11'],
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'nik.size'     => 'NIK harus terdiri dari 11 karakter.',
        ]);

        $nik  = strtoupper(trim($request->nik));
        $user = User::where('nik', $nik)->where('status', 'approved')->first();

        // Anti-enumeration: selalu tampil pesan sukses meski NIK tidak ada
        if ($user) {
            $this->otpService->send($user, 'reset_password');
        }

        Session::put('reset_nik', $nik);

        return redirect()->route('password.otp')
            ->with('status', 'Jika NIK terdaftar dan aktif, kode OTP akan dikirim ke email yang terkait dalam beberapa menit.');
    }

    // ── STEP 2: Input & Verifikasi OTP ─────────────────────────────────────

    public function otpForm(): View|RedirectResponse
    {
        if (! Session::has('reset_nik')) {
            return redirect()->route('password.request');
        }

        $nik      = Session::get('reset_nik');
        $user     = User::where('nik', $nik)->where('status', 'approved')->first();
        $cooldown = $user ? $this->otpService->resendCooldownSeconds($user, 'reset_password') : 0;

        return view('auth.forgot-password-otp', compact('nik', 'cooldown'));
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
        ], [
            'otp.size'  => 'Kode OTP harus terdiri dari 6 digit.',
            'otp.regex' => 'Kode OTP hanya boleh berisi angka.',
        ]);

        if (! Session::has('reset_nik')) {
            return redirect()->route('password.request');
        }

        $user = User::where('nik', Session::get('reset_nik'))
            ->where('status', 'approved')
            ->first();

        if (! $user) {
            return redirect()->route('password.request');
        }

        $result = $this->otpService->verify($user, 'reset_password', $request->otp);

        if ($result !== 'valid') {
            return back()->withErrors(['otp' => match ($result) {
                'invalid'     => 'Kode OTP tidak valid.',
                'expired'     => 'Kode OTP sudah kedaluwarsa. Silakan minta kode baru.',
                'max_attempt' => 'Terlalu banyak percobaan salah. Silakan minta kode baru.',
                default       => 'Verifikasi gagal. Silakan coba lagi.',
            }]);
        }

        Session::put('reset_verified', true);
        Session::put('reset_user_id', $user->id);

        return redirect()->route('password.new');
    }

    public function resendOtp(): RedirectResponse
    {
        if (! Session::has('reset_nik')) {
            return redirect()->route('password.request');
        }

        $user = User::where('nik', Session::get('reset_nik'))
            ->where('status', 'approved')
            ->first();

        if (! $user) {
            return redirect()->route('password.request');
        }

        $sent = $this->otpService->send($user, 'reset_password');

        if (! $sent) {
            $cooldown = $this->otpService->resendCooldownSeconds($user, 'reset_password');
            return back()->withErrors(['otp' => "Harap tunggu {$cooldown} detik sebelum meminta kode baru."]);
        }

        return back()->with('status', 'Kode OTP baru telah dikirim ke email Anda.');
    }

    // ── STEP 3: Form & Simpan Password Baru ────────────────────────────────

    public function newPasswordForm(): View|RedirectResponse
    {
        if (! Session::get('reset_verified')) {
            return redirect()->route('password.request');
        }

        return view('auth.new-password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        if (! Session::get('reset_verified') || ! Session::get('reset_user_id')) {
            return redirect()->route('password.request');
        }

        $request->validate([
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/^[A-Z].*/',
                'regex:/\d/',
                'regex:/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/',
            ],
        ], [
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.regex'     => 'Password harus dimulai huruf besar, mengandung angka dan karakter khusus.',
        ]);

        $user = User::findOrFail(Session::get('reset_user_id'));
        $user->update(['password' => Hash::make($request->password)]);

        Session::forget(['reset_nik', 'reset_verified', 'reset_user_id']);

        return redirect()->route('login')
            ->with('status', 'Password berhasil diubah. Silakan masuk dengan password baru Anda.');
    }
}
