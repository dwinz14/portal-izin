<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\OtpVerificationController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {

    // ── Registrasi ──────────────────────────────────────────────────────────
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    // ── Verifikasi OTP setelah register (session-based, tanpa auth) ─────────
    Route::get('register/verify',        [OtpVerificationController::class, 'showVerify'])->name('register.verify');
    Route::post('register/verify',       [OtpVerificationController::class, 'verify'])->name('register.verify.submit');
    Route::post('register/verify/resend', [OtpVerificationController::class, 'resend'])->name('register.verify.resend');

    // ── Login ────────────────────────────────────────────────────────────────
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->middleware('prevent-back-history')
        ->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('prevent-back-history');

    // ── Lupa Password — Step 1: Input NIK ───────────────────────────────────
    Route::get('forgot-password',  [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'sendOtp'])->name('password.email');

    // ── Lupa Password — Step 2: Verifikasi OTP ──────────────────────────────
    Route::get('forgot-password/otp',          [PasswordResetController::class, 'otpForm'])->name('password.otp');
    Route::post('forgot-password/otp',         [PasswordResetController::class, 'verifyOtp'])->name('password.otp.verify');
    Route::post('forgot-password/otp/resend',  [PasswordResetController::class, 'resendOtp'])->name('password.otp.resend');

    // ── Lupa Password — Step 3: Password Baru ───────────────────────────────
    Route::get('forgot-password/new',  [PasswordResetController::class, 'newPasswordForm'])->name('password.new');
    Route::post('forgot-password/new', [PasswordResetController::class, 'updatePassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {

    // ── Konfirmasi Password (untuk aksi sensitif) ────────────────────────────
    Route::get('confirm-password',  [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    // ── Ganti Password (dari profil) ─────────────────────────────────────────
    Route::put('password', [PasswordController::class, 'update'])->name('password.update.profile');

    // ── Logout ───────────────────────────────────────────────────────────────
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->middleware('prevent-back-history')
        ->name('logout');
});
