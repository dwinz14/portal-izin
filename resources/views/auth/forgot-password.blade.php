<x-guest-layout>
    <div class="w-full space-y-6">

        <div class="mb-6">
            <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-amber-100 dark:bg-amber-900/30 mb-4">
                <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Lupa Password</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Masukkan NIK Anda. Kode OTP akan dikirim ke email yang terdaftar pada akun tersebut.
            </p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        @if ($errors->any())
            <div class="p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                <p class="text-sm text-red-700 dark:text-red-400">{{ $errors->first() }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="nik" value="Nomor Induk Karyawan (NIK)" class="mb-1.5 font-medium" />
                <x-text-input id="nik" type="text" name="nik" :value="old('nik')"
                    class="block w-full px-4 py-3 rounded-xl uppercase tracking-widest font-mono"
                    placeholder="AP123456789" maxlength="11" autofocus autocomplete="off" />
                <x-input-error :messages="$errors->get('nik')" class="mt-2" />
            </div>

            <x-primary-button class="w-full justify-center py-3.5 text-base font-semibold rounded-xl">
                Kirim Kode OTP
            </x-primary-button>
        </form>

        <div class="text-center">
            <a href="{{ route('login') }}"
                class="text-sm text-slate-500 hover:text-primary-600 dark:text-slate-400 dark:hover:text-primary-400 transition-colors">
                ← Kembali ke halaman login
            </a>
        </div>
    </div>
</x-guest-layout>
