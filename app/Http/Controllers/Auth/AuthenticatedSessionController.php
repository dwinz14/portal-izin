<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Models\QuotaSetting;
use App\Models\UserLeaveBalance;
use App\Services\LeaveQuotaService;

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request): View
    {
        // Tampilkan pesan jika dialihkan karena idle
        if ($request->has('idle')) {
            session()->flash('status', 'Sesi Anda telah berakhir karena tidak ada aktivitas. Silakan login kembali.');
        }
        return view('auth.auth', ['mode' => 'login']);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        // ── 1. Verifikasi kredensial ────────────────────────────────────────
        $request->authenticate();
        $user = Auth::user();

        // ── 2. Cek status akun ──────────────────────────────────────────────
        if ($user->status !== 'approved') {
            Auth::logout();

            $message = is_null($user->email_verified_at)
                ? 'Akun belum diverifikasi. Cek dan masukkan kode OTP yang dikirim saat pendaftaran.'
                : 'Akun Anda tidak memiliki akses. Hubungi administrator.';

            return redirect()->route('login')->withErrors(['nik' => $message]);
        }

        // ── 3. Cek sesi aktif (single session per user) ─────────────────────
        // super_admin dikecualikan agar tidak terganggu saat monitoring
        if ($user->role !== 'super_admin' && ! $request->boolean('force_login')) {
            $sessionLifetime = config('session.lifetime') * 60; // detik
            $hasActiveSession = DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('last_activity', '>', now()->timestamp - $sessionLifetime)
                ->exists();

            if ($hasActiveSession) {
                Auth::logout();
                return redirect()->route('login')
                    ->withInput($request->only('nik'))
                    ->withErrors([
                        'session_conflict' =>
                        'Akun Anda masih aktif login di perangkat atau browser lain. ' .
                            'Masukkan password kembali dan klik "Paksa Login" — ' .
                            'perangkat lain akan ter-logout secara otomatis.',
                    ]);
            }
        }

        // ── 4. Paksa login: hapus semua sesi lama ───────────────────────────
        if ($request->boolean('force_login')) {
            // Hapus sesi-sesi lama; sesi saat ini belum tercatat dengan
            // user_id di DB (ditulis di akhir request), jadi aman
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->delete();
        }

        // ── 5. Cek harus ganti password ─────────────────────────────────────
        if ($user->must_change_password) {
            return redirect()->route('password.force-change');
        }

        // ── 6. Catat last login & regenerasi sesi ───────────────────────────
        DB::table('users')->where('id', $user->id)->update(['last_login_at' => now()]);
        $request->session()->regenerate();

        // ── 7. Auto-generate kuota jika belum ada untuk tahun ini ────────────
        // Lazy approach: generate di sini karena sudah pasti semua guard
        if ($user->role !== 'super_admin' && $user->status === 'approved') {
            try {
                $cutiTahunan = \App\Models\LeaveType::where('is_annual_leave', true)
                    ->where('is_active', true)
                    ->first();

                if (
                    $cutiTahunan &&
                    $user->masaKerjaTahun() >= $cutiTahunan->min_years &&
                    ! \App\Models\UserLeaveBalance::where('user_id', $user->id)
                        ->where('leave_type_id', $cutiTahunan->id)
                        ->where('year', now()->year)
                        ->exists()
                ) {
                    app(LeaveQuotaService::class)->generateForUser($user, now()->year);

                    \Illuminate\Support\Facades\Log::info(
                        "Auto-generate cuti tahunan: user [{$user->id}] {$user->name} "
                            . "masa kerja {$user->masaKerjaTahun()} tahun."
                    );
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error(
                    "Auto-generate cuti tahunan gagal: user [{$user->id}] — "
                        . $e->getMessage()
                );
            }
        }

        // ── 8. Log aktivitas ────────────────────────────────────────────────
        $logMessage = $request->boolean('force_login')
            ? 'Login paksa — sesi aktif di perangkat lain telah dihapus'
            : 'Login berhasil ke sistem';
        ActivityLogger::log('auth.login', $logMessage);

        return redirect('/dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        ActivityLogger::log('auth.logout', 'Keluar dari sistem');

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
