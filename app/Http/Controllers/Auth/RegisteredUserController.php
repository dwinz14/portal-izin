<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(private OtpService $otpService) {}

    public function create(): View
    {
        return view('auth.auth', ['mode' => 'register']);
    }

    public function store(Request $request): RedirectResponse
    {
        $input = $request->all();
        $input['nik']                  = strtoupper(trim($input['nik'] ?? ''));
        $input['name']                 = strtolower(trim($input['name'] ?? ''));
        $input['email']                = strtolower(trim($input['email'] ?? ''));
        $input['gender']               = trim($input['gender'] ?? '');
        $input['role']                 = trim($input['role'] ?? '');
        $input['division_id']          = $input['division_id'] ?? null;
        $input['position_id']          = $input['position_id'] ?? null;
        $input['office_id']            = $input['office_id'] ?? null;
        $input['password']             = $input['password'] ?? '';
        $input['password_confirmation'] = $input['password_confirmation'] ?? '';

        $validator = Validator::make($input, [
            'nik'   => ['required', 'string', 'size:11', 'regex:/^AP\d{9}$/', 'unique:' . User::class],
            'name'  => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'gender' => ['required', 'in:L,P'],
            'role'   => ['required', 'in:super_admin,hrd,direksi,kabag-pincab,kasie,staff'],
            'division_id'         => ['nullable', 'exists:divisions,id'],
            'position_id'         => ['nullable', 'exists:positions,id'],
            'office_id'           => ['nullable', 'exists:offices,id'],
            'tanggal_aktif_kerja' => ['required', 'date', 'before_or_equal:today'],
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/^[A-Z].*/',
                'regex:/\d/',
                'regex:/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/',
            ],
        ], [
            'name.regex'     => 'Nama hanya boleh berisi huruf dan spasi.',
            'password.regex' => 'Password harus dimulai huruf besar, mengandung angka dan karakter khusus.',
            'nik.regex'      => 'Format NIK tidak valid. Harus diawali "AP" diikuti 9 digit angka.',
            'tanggal_aktif_kerja.before_or_equal' => 'Tanggal aktif kerja tidak boleh melebihi hari ini.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('register')->withErrors($validator)->withInput();
        }

        $user = User::create([
            'nik'                 => $input['nik'],
            'name'                => $input['name'],
            'email'               => $input['email'],
            'gender'              => $input['gender'],
            'password'            => Hash::make($input['password']),
            'role'                => $input['role'],
            'division_id'         => $input['division_id'],
            'position_id'         => $input['position_id'],
            'office_id'           => $input['office_id'],
            'tanggal_aktif_kerja' => $input['tanggal_aktif_kerja'],
            'status'              => 'pending',
        ]);

        event(new Registered($user));

        // Kirim OTP verifikasi email
        $this->otpService->send($user, 'verify_email');

        // Simpan session untuk halaman verifikasi (tanpa auto-login)
        Session::put('verification_user_id', $user->id);
        Session::put('verification_email',   $user->email);

        return redirect()->route('register.verify');
    }
}
