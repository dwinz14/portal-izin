<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.auth', ['mode' => 'login']);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        // Check if user status is approved
        if ($user->status !== 'approved') {
            Auth::logout();
            return redirect()->route('login')->withErrors(['nik' => 'Akun Anda belum disetujui oleh admin.']);
        }

        // Check if user must change password
        if ($user->must_change_password) {
            // Redirect to force change password page
            return redirect()->route('password.force-change');
        }

        DB::table('users')->where('id', $user->id)->update(['last_login_at' => now()]);

        $request->session()->regenerate();

        $role = $user->role;

        // return match ($role) {
        //     'super_admin' => redirect('/admin/dashboard'),
        //     'hrd' => redirect('/hrd/dashboard'),
        //     'kabag' => redirect('/kabag/dashboard'),
        //     'staff' => redirect('/staff/dashboard'),
        //     default => redirect('/dashboard'),
        // };
        return redirect('/dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
