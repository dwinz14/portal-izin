<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Services\ActivityLogger;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ], [
            'current_password.current_password' => 'Password saat ini berbeda, mohon isi kembali.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'updatePassword');
        }

        $validated = $validator->validated();

        $user = $request->user();

        $wasForced = $user->must_change_password;

        $user->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false, // Reset the flag after password change
        ]);

        if ($wasForced) {
            ActivityLogger::log('auth.password_changed', 'Mengganti password wajib (first login)');
            return redirect()->route('dashboard')->with('success', 'Password berhasil diupdate. Selamat datang kembali!');
        }
        ActivityLogger::log('auth.password_changed', 'Memperbarui password dari halaman profil');
        return back()->with('success', 'password berhasil diupdate');
    }
}
