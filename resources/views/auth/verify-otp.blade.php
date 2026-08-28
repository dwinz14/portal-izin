<x-guest-layout>
    <div x-data="{
        cooldown: {{ $cooldown }},
        init() { if (this.cooldown > 0) this.startCountdown(); },
        startCountdown() {
            const t = setInterval(() => {
                this.cooldown--;
                if (this.cooldown <= 0) {
                    this.cooldown = 0;
                    clearInterval(t);
                }
            }, 1000);
        }
    }" class="w-full space-y-6">

        {{-- Header --}}
        <div class="mb-6">
            <div
                class="flex items-center justify-center w-14 h-14 rounded-2xl bg-primary-100 dark:bg-primary-900/30 mb-4">
                <svg class="w-7 h-7 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Verifikasi Email</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Kode OTP 6 digit telah dikirim ke
                <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $maskedEmail }}</span>
            </p>
        </div>

        {{-- Status / Error --}}
        @if (session('status'))
            <div class="p-3 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                <p class="text-sm text-green-700 dark:text-green-400">{{ session('status') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                <p class="text-sm text-red-700 dark:text-red-400">{{ $errors->first() }}</p>
            </div>
        @endif

        {{-- Form OTP --}}
        <form method="POST" action="{{ route('register.verify.submit') }}" class="space-y-5">
            @csrf

            <div>
                <label for="otp" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Kode OTP
                </label>
                <input type="text" id="otp" name="otp" inputmode="numeric" pattern="\d{6}" maxlength="6"
                    autocomplete="one-time-code" autofocus placeholder="000000"
                    class="block w-full px-4 py-4 text-center text-3xl font-bold tracking-[0.5em]
                           bg-white dark:bg-slate-900
                           border border-slate-300 dark:border-slate-600
                           text-slate-900 dark:text-white
                           placeholder-slate-300 dark:placeholder-slate-600
                           focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20
                           rounded-xl transition-all duration-200 shadow-sm" />
            </div>

            <x-primary-button class="w-full justify-center py-3.5 text-base font-semibold rounded-xl">
                Verifikasi Akun
            </x-primary-button>
        </form>

        {{-- Resend --}}
        <div class="text-center pt-2 border-t border-slate-100 dark:border-slate-800">
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-2">Tidak menerima kode?</p>

            <form id="resend-form" method="POST" action="{{ route('register.verify.resend') }}">
                @csrf
            </form>

            <button form="resend-form" type="submit" x-bind:disabled="cooldown > 0"
                x-bind:class="cooldown > 0 ?
                    'text-slate-400 dark:text-slate-600 cursor-not-allowed' :
                    'text-primary-600 dark:text-primary-400 hover:underline cursor-pointer'"
                class="text-sm font-medium transition-colors">
                <span x-show="cooldown > 0">
                    Kirim ulang dalam <span class="font-bold" x-text="cooldown"></span> detik
                </span>
                <span x-show="cooldown === 0">Kirim ulang kode OTP</span>
            </button>
        </div>

        <p class="text-center text-xs text-slate-400 dark:text-slate-600">
            Kode berlaku selama 10 menit &bull; Maks. 3 percobaan per kode
        </p>

        <div class="text-center">
            <a href="{{ route('login') }}"
                class="text-sm text-slate-500 hover:text-primary-600 dark:text-slate-400 dark:hover:text-primary-400 transition-colors">
                ← Kembali ke halaman login
            </a>
        </div>
    </div>
</x-guest-layout>
