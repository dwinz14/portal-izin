<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="text-center mb-6">
        <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">Welcome Back</h2>
        <p class="text-gray-600 dark:text-gray-400">Sign in to your account</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- NIK -->
        <div>
            <x-input-label for="nik" :value="__('NIK')" class="text-gray-700 dark:text-gray-300" />
            <x-text-input id="nik"
                class="block mt-1 w-full bg-gray-50 dark:bg-slate-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:border-primary-500 dark:focus:border-primary-400 focus:ring-primary-500 dark:focus:ring-primary-400"
                type="text" name="nik" :value="old('nik')" required autofocus autocomplete="nik"
                placeholder="Masukkan NIK..." />
            <x-input-error :messages="$errors->get('nik')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" class="text-gray-700 dark:text-gray-300" />

            <x-text-input id="password"
                class="block mt-1 w-full bg-gray-50 dark:bg-slate-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:border-primary-500 dark:focus:border-primary-400 focus:ring-primary-500 dark:focus:ring-primary-400"
                type="password" name="password" required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            @if (Route::has('register'))
                <a class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-500 dark:hover:text-primary-300 underline focus:outline-none focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-400 rounded-md"
                    href="{{ route('register') }}">
                    {{ __('Belum Punya Akun?') }}
                </a>
            @endif
        </div>

        <div class="flex items-center justify-end">
            <x-primary-button
                class="w-full justify-center py-3 px-4 bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 focus:ring-primary-500 dark:focus:ring-primary-400 transition-colors duration-200">
                {{ __('Sign In') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
