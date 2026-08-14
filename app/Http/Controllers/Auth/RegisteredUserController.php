<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.auth', ['mode' => 'register']);
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        // Trim inputs and normalize data before validation
        $input = $request->all();
        $input['nik'] = trim($input['nik'] ?? '');
        $input['name'] = strtolower(trim($input['name'] ?? ''));
        $input['email'] = strtolower(trim($input['email'] ?? ''));
        $input['gender'] = trim($input['gender'] ?? '');
        $input['role'] = trim($input['role'] ?? '');
        $input['division_id'] = $input['division_id'] ?? null;
        $input['position_id'] = $input['position_id'] ?? null;
        $input['office_id'] = $input['office_id'] ?? null;
        $input['password'] = $input['password'] ?? '';
        $input['password_confirmation'] = $input['password_confirmation'] ?? '';

        $validator = Validator::make($input, [
            'nik' => ['required', 'string', 'size:11', 'regex:/^AP\d{9}$/', 'unique:' . User::class],
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'gender' => ['required', 'in:L,P'],
            'role' => ['required', 'in:super_admin,hrd,direksi,kabag-pincab,kasie,staff'],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'office_id' => ['nullable', 'exists:offices,id'],
            'tanggal_aktif_kerja' => ['required', 'date', 'before_or_equal:today'],
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/^[A-Z].*/', // starts with uppercase letter
                'regex:/\d/',       // contains at least one digit
                'regex:/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/' // contains special char
            ],
        ], [
            'name.regex' => 'Nama hanya boleh berisi huruf dan spasi.',
            'password.regex' => 'Kata sandi harus dimulai dengan huruf besar, mengandung setidaknya satu digit dan satu karakter khusus.',
            'nik.regex' => 'Format NIK tidak valid. Harus diawali dengan "AP" diikuti 9 digit.',
            'tanggal_aktif_kerja.before_or_equal' => 'Tanggal aktif kerja tidak boleh lebih dari hari ini.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('register')->withErrors($validator)->withInput();
        }

        $user = User::create([
            'nik' => $input['nik'],
            'name' => $input['name'],
            'email' => $input['email'],
            'gender' => $input['gender'],
            'password' => Hash::make($input['password']),
            'role' => $input['role'],
            'division_id' => $input['division_id'],
            'position_id' => $input['position_id'],
            'office_id' => $input['office_id'],
            'tanggal_aktif_kerja' => $input['tanggal_aktif_kerja'],
            // 'sisa_cuti' => 12,
            'status' => 'pending',
        ]);

        event(new Registered($user));

        // Do not auto-login, redirect to pending approval page
        return redirect(route('registration.pending', absolute: false));
    }
}
