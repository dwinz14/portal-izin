<x-guest-layout>
    <div class="w-full space-y-6">

        <div class="mb-6">
            <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-green-100 dark:bg-green-900/30 mb-4">
                <svg class="w-7 h-7 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Buat Password Baru</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Verifikasi berhasil. Buat password baru yang kuat untuk akun Anda.
            </p>
        </div>

        @if ($errors->any())
            <div class="p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                @foreach ($errors->all() as $error)
                    <p class="text-sm text-red-700 dark:text-red-400">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-5" x-data="{
            password: '',
            get strength() {
                let s = 0;
                if (this.password.length >= 8) s++;
                if (/[A-Z]/.test(this.password)) s++;
                if (/\d/.test(this.password)) s++;
                if (/[^A-Za-z0-9]/.test(this.password)) s++;
                return s;
            },
            get strengthLabel() {
                return ['', 'Lemah', 'Cukup', 'Kuat', 'Sangat Kuat'][this.strength];
            },
            get strengthColor() {
                return ['', 'bg-red-500', 'bg-amber-500', 'bg-blue-500', 'bg-green-500'][this.strength];
            }
        }">
            @csrf

            {{-- Password Baru --}}
            <div>
                <x-input-label for="password" value="Password Baru" class="mb-1.5 font-medium" />
                <div class="relative">
                    <x-text-input id="password" type="password" name="password"
                        class="block w-full px-4 py-3 rounded-xl pr-12" x-model="password" autofocus
                        autocomplete="new-password" placeholder="••••••••" />
                </div>

                {{-- Password Strength Bar --}}
                <div x-show="password.length > 0" class="mt-2 space-y-1">
                    <div class="flex gap-1">
                        <template x-for="i in 4" :key="i">
                            <div class="h-1 flex-1 rounded-full transition-all duration-300"
                                :class="i <= strength ? strengthColor : 'bg-slate-200 dark:bg-slate-700'"></div>
                        </template>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Kekuatan: <span class="font-medium" x-text="strengthLabel"></span>
                    </p>
                </div>

                <x-input-error :messages="$errors->get('password')" class="mt-2" />

                <ul class="mt-2 space-y-0.5 text-xs text-slate-400 dark:text-slate-500">
                    <li :class="/^[A-Z]/.test(password) ? 'text-green-600 dark:text-green-400' : ''">✓ Diawali huruf
                        kapital</li>
                    <li :class="password.length >= 8 ? 'text-green-600 dark:text-green-400' : ''">✓ Minimal 8 karakter
                    </li>
                    <li :class="/\d/.test(password) ? 'text-green-600 dark:text-green-400' : ''">✓ Mengandung angka</li>
                    <li :class="/[^A-Za-z0-9]/.test(password) ? 'text-green-600 dark:text-green-400' : ''">✓ Mengandung
                        karakter khusus (!@#$...)</li>
                </ul>
            </div>

            {{-- Konfirmasi Password --}}
            <div>
                <x-input-label for="password_confirmation" value="Konfirmasi Password" class="mb-1.5 font-medium" />
                <x-text-input id="password_confirmation" type="password" name="password_confirmation"
                    class="block w-full px-4 py-3 rounded-xl" autocomplete="new-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <x-primary-button class="w-full justify-center py-3.5 text-base font-semibold rounded-xl">
                Simpan Password Baru
            </x-primary-button>
        </form>
    </div>
</x-guest-layout>
