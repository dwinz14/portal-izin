<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Batas maksimal percobaan login gagal (per kombinasi NIK + IP)
     */
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 300;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nik' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $nik = $this->input('nik');

        // Check if NIK exists
        $user = User::where('nik', $nik)->first();

        if (!$user) {
            RateLimiter::hit(
                $this->throttleKey(),
                self::DECAY_SECONDS
            );
            ActivityLogger::logFailedLogin($nik, 'NIK tidak ditemukan');
            $this->flashAttemptsLeft();

            throw ValidationException::withMessages([
                'nik' => 'NIK tidak ditemukan.',
            ]);
        }

        // Check if password is correct
        if (!Auth::attempt($this->only('nik', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit(
                $this->throttleKey(),
                self::DECAY_SECONDS
            );
            ActivityLogger::logFailedLogin($nik, 'Password salah');
            $this->flashAttemptsLeft();

            throw ValidationException::withMessages([
                'password' => 'Password salah.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        // Dipakai frontend untuk menampilkan countdown lockout secara real-time.
        $this->session()->flash('login_locked_seconds', $seconds);

        throw ValidationException::withMessages([
            'login_locked' => [
                'Login temporarily locked.',
            ],
        ]);
    }

    /**
     * Flash sisa jatah percobaan
     */
    private function flashAttemptsLeft(): void
    {
        $remaining = max(0, self::MAX_ATTEMPTS - RateLimiter::attempts($this->throttleKey()));

        if ($remaining > 0) {
            $this->session()->flash('login_attempts_left', $remaining);
        }
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('nik')) . '|' . $this->ip());
    }
}
