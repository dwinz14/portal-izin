<x-guest-layout>
    <div x-data="authForm()" class="w-full space-y-8">

        <!-- Tab Navigation - Segmented Control style -->
        <div class="relative flex p-1.5 bg-slate-100 dark:bg-slate-800/80 rounded-xl mb-8">
            <button @click="setMode('login')" :class="mode === 'login' ? 'text-slate-900 dark:text-white font-semibold' :
                    'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 font-medium'"
                class="relative flex-1 py-2.5 text-sm rounded-lg transition-colors duration-300 z-10 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                Masuk
            </button>
            <button @click="setMode('register')" :class="mode === 'register' ? 'text-slate-900 dark:text-white font-semibold' :
                    'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 font-medium'"
                class="relative flex-1 py-2.5 text-sm rounded-lg transition-colors duration-300 z-10 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                Daftar Akun
            </button>
            <!-- Animated Background Pill -->
            <div class="absolute top-1.5 bottom-1.5 w-[calc(50%-6px)] bg-white dark:bg-slate-700 rounded-lg shadow-sm transition-transform duration-300 ease-in-out z-0"
                :class="mode === 'register' ? 'translate-x-full left-[4px]' : 'translate-x-0 left-1.5'">
            </div>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <!-- LOGIN FORM                                                          -->
        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <div x-show="mode === 'login'" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            style="display: none;" class="space-y-6">

            <div class="mb-8">
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight mb-2">
                    Selamat Datang
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Silakan masuk menggunakan akun NIK dan password Anda
                </p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-5"
                x-data="{ lockedSeconds: {{ (int) session('login_locked_seconds', 0) }} }" x-init="if (lockedSeconds > 0) {
                    const timer = setInterval(() => {
                        lockedSeconds--;
                        if (lockedSeconds <= 0) clearInterval(timer);
                    }, 1000);
                }">
                @csrf

                {{-- Force login flag: aktif hanya saat ada konflik sesi --}}
                @if ($errors->has('session_conflict'))
                <input type="hidden" name="force_login" value="1">
                @endif

                {{-- Banner: akun dikunci sementara (brute-force protection), countdown real-time --}}
                <template x-if="lockedSeconds > 0">
                    <div
                        class="p-4 rounded-xl bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 flex-shrink-0 mt-0.5" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-rose-800 dark:text-rose-300">
                                    Akun Dikunci Sementara
                                </p>
                                <p class="text-xs text-rose-700 dark:text-rose-400 mt-0.5">
                                    Terlalu banyak percobaan login gagal. Coba lagi dalam
                                    <span class="font-bold" x-text="lockedSeconds"></span> detik.
                                </p>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Banner: peringatan dini sisa percobaan (belum sampai lockout) --}}
                @if (session('login_attempts_left') && !session('login_locked_seconds'))
                <div
                    class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p class="text-xs text-amber-700 dark:text-amber-400">
                            NIK atau password yang Anda masukkan salah. Sisa
                            <span class="font-bold">{{ session('login_attempts_left') }}</span>
                            percobaan lagi sebelum akun dikunci sementara.
                        </p>
                    </div>
                </div>
                @endif

                {{-- Banner: konflik sesi aktif --}}
                @if ($errors->has('session_conflict'))
                <div
                    class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">
                                Sesi Aktif di Perangkat Lain
                            </p>
                            <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">
                                {{ $errors->first('session_conflict') }}
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- NIK -->
                <div>
                    <x-input-label for="nik" class="mb-1.5 text-slate-700 dark:text-slate-300 font-medium">
                        {{ __('Nomor Induk Karyawan (NIK)') }} <span class="text-rose-500 font-bold">*</span>
                    </x-input-label>
                    <x-text-input id="nik"
                        class="block w-full px-4 py-3 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 rounded-xl transition-all duration-200 uppercase shadow-sm font-mono text-sm"
                        type="text" name="nik" :value="old('nik')" required autofocus autocomplete="nik"
                        placeholder="AP123456789" maxlength="11" />
                    <x-input-error :messages="$errors->get('nik')" class="mt-2" />
                </div>

                <!-- Password -->
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <x-input-label for="password" class="mb-0 text-slate-700 dark:text-slate-300 font-medium">
                            {{ __('Password') }} <span class="text-rose-500 font-bold">*</span>
                        </x-input-label>
                    </div>
                    <div class="relative group">
                        <x-text-input id="password"
                            class="block w-full px-4 py-3 pr-12 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 rounded-xl transition-all duration-200 shadow-sm"
                            type="password" name="password" required autocomplete="current-password"
                            placeholder="••••••••" />
                        <button type="button" id="togglePasswordBtn"
                            class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors focus:outline-none rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800"
                            aria-label="Tampilkan password">
                            <svg id="eye-open" class="w-5 h-5 hidden" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eye-closed" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between mt-4">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer">
                        <input id="remember_me" type="checkbox"
                            class="rounded dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-primary-600 shadow-sm focus:ring-primary-500 dark:focus:ring-primary-600 dark:focus:ring-offset-slate-900"
                            name="remember">

                        <span class="ml-2 text-sm text-slate-600 dark:text-slate-400 font-medium select-none">
                            {{ __('Ingat sesi saya') }}
                        </span>
                    </label>

                    @if (Route::has('password.request'))
                    <a class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 transition-colors"
                        href="{{ route('password.request') }}">
                        Lupa password?
                    </a>
                    @endif
                </div>


                <div class="pt-2">
                    <x-primary-button x-bind:disabled="lockedSeconds > 0"
                        class="w-full justify-center py-3.5 px-4 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white rounded-xl shadow-sm hover:shadow focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-slate-900 transition-all duration-200 font-semibold text-base disabled:opacity-50 disabled:cursor-not-allowed">
                        @if ($errors->has('session_conflict'))
                        ⚡ Paksa Login — Perangkat Lain Akan Logout
                        @else
                        <span x-show="lockedSeconds <= 0">{{ __('Masuk') }}</span>
                        <span x-show="lockedSeconds > 0">Tunggu <span x-text="lockedSeconds"></span> detik...</span>
                        @endif
                    </x-primary-button>
                </div>
            </form>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <!-- REGISTER FORM - MULTI-STEP                                          -->
        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <div x-show="mode === 'register'" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-data="registerForm()" style="display: none;" class="space-y-6">

            <div class="mb-6 text-center sm:text-left">
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight mb-2">
                    Buat Akun Baru
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Lengkapi data diri dan penempatan kerja Anda untuk registrasi akun.
                </p>
            </div>

            <!-- Header Info Banner: Step & Wajib Diisi -->
            <div
                class="flex items-center justify-between text-xs text-slate-600 dark:text-slate-400 bg-slate-100/90 dark:bg-slate-800/70 px-4 py-2.5 rounded-xl border border-slate-200/80 dark:border-slate-700/80">
                <div class="flex items-center gap-1.5 font-medium">
                    <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                    <span>Langkah <strong class="text-slate-900 dark:text-slate-100" x-text="step"></strong> dari 2:
                        <strong class="text-primary-600 dark:text-primary-400"
                            x-text="step === 1 ? 'Data Diri' : 'Data Pekerjaan & Keamanan'"></strong></span>
                </div>
                <div class="flex items-center gap-1 text-[11px] text-slate-500 dark:text-slate-400">
                    <span class="text-rose-500 font-bold">*</span>
                    <span>Kolom wajib diisi</span>
                </div>
            </div>

            <!-- Progress Stepper -->
            <div class="mb-8 px-2">
                <div class="flex items-center justify-between relative">
                    <div
                        class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-slate-200 dark:bg-slate-700/80 rounded-full z-0">
                    </div>
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-primary-600 dark:bg-primary-500 rounded-full z-0 transition-all duration-500 ease-out"
                        :style="'width: ' + ((step - 1) / (maxStep - 1) * 100) + '%'"></div>

                    <!-- Step 1 Indicator -->
                    <div class="relative z-10 flex flex-col items-center">
                        <button type="button" @click="step = 1"
                            class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold transition-all duration-300 focus:outline-none"
                            :class="step >= 1 ? 'bg-primary-600 text-white shadow-md shadow-primary-500/30 ring-2 ring-white dark:ring-slate-900' :
                                'bg-slate-200 text-slate-500 dark:bg-slate-700 dark:text-slate-400'">
                            1
                        </button>
                        <span
                            class="absolute top-11 text-xs font-semibold whitespace-nowrap transition-colors duration-300"
                            :class="step >= 1 ? 'text-primary-600 dark:text-primary-400' : 'text-slate-500 dark:text-slate-400'">
                            Data Diri
                        </span>
                    </div>

                    <!-- Step 2 Indicator -->
                    <div class="relative z-10 flex flex-col items-center">
                        <button type="button" @click="goToStep2()"
                            class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold transition-all duration-300 focus:outline-none"
                            :class="step >= 2 ? 'bg-primary-600 text-white shadow-md shadow-primary-500/30 ring-2 ring-white dark:ring-slate-900' :
                                'bg-slate-200 text-slate-500 dark:bg-slate-700 dark:text-slate-400'">
                            2
                        </button>
                        <span
                            class="absolute top-11 text-xs font-semibold whitespace-nowrap transition-colors duration-300"
                            :class="step >= 2 ? 'text-primary-600 dark:text-primary-400' : 'text-slate-500 dark:text-slate-400'">
                            Data Pekerjaan
                        </span>
                    </div>
                </div>
            </div>

            <form id="registerFormElement" method="POST" action="{{ route('register') }}"
                @submit="handleFormSubmit($event)" class="space-y-5 mt-10">
                @csrf

                <!-- ═══════════════════════════════════════════════════════════ -->
                <!-- STEP 1: PERSONAL INFO                                       -->
                <!-- ═══════════════════════════════════════════════════════════ -->
                <div x-show="step === 1" x-transition:enter="transition ease-in-out duration-300 delay-150"
                    x-transition:enter-start="opacity-0 translate-x-8"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in-out duration-300"
                    x-transition:leave-start="opacity-100 translate-x-0"
                    x-transition:leave-end="opacity-0 -translate-x-8" class="space-y-5 pt-2">

                    <!-- Alert Banner: Notifikasi Validasi Step 1 -->
                    <div x-show="step1Error" x-cloak x-transition
                        class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 flex items-start gap-3">
                        <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-rose-800 dark:text-rose-300" x-text="step1Error"></p>
                        </div>
                    </div>

                    <!-- Hidden PIN (auto-filled dari DB karyawan) -->
                    <input type="hidden" id="pin_register" name="pin" value="{{ old('pin') }}">

                    <!-- NIK dengan Autocomplete -->
                    <div class="relative">
                        <div class="flex items-center justify-between mb-1.5">
                            <x-input-label for="nik_register"
                                class="mb-0 text-slate-700 dark:text-slate-300 font-medium">
                                {{ __('Nomor Induk Karyawan (NIK)') }} <span class="text-rose-500 font-bold"
                                    title="Wajib diisi">*</span>
                            </x-input-label>
                            <span class="text-[11px] text-slate-400 dark:text-slate-500">Ketik untuk mencari</span>
                        </div>
                        <div class="relative">
                            <x-text-input id="nik_register"
                                class="block w-full px-4 py-3 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 rounded-xl transition-all duration-200 uppercase shadow-sm font-mono text-sm"
                                type="text" name="nik" :value="old('nik')" required autocomplete="off"
                                placeholder="AP123456789" />
                            <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">
                            Ketik NIK lalu pilih data anda dari daftar yang muncul.
                        </p>

                        <x-input-error :messages="$errors->get('nik')" class="mt-2" />
                        <div id="nik-validation" class="mt-2 text-xs font-medium" style="display: none;"></div>

                        <!-- Dropdown hasil autocomplete -->
                        <div id="nik-dropdown"
                            class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl max-h-56 overflow-y-auto"
                            style="display: none;">
                        </div>
                    </div>

                    <!-- Name (readonly, auto-filled) -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <x-input-label for="name" class="mb-0 text-slate-700 dark:text-slate-300 font-medium">
                                {{ __('Nama Lengkap Karyawan') }} <span class="text-rose-500 font-bold"
                                    title="Wajib diisi">*</span>
                            </x-input-label>
                            <span class="text-[11px] text-slate-400 dark:text-slate-500">Otomatis dari NIK</span>
                        </div>
                        <div class="relative">
                            <x-text-input id="name"
                                class="block w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/80 border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 rounded-xl transition-all duration-200 shadow-sm cursor-not-allowed font-medium text-sm"
                                type="text" name="name" :value="old('name')" required autocomplete="off"
                                placeholder="Terisi otomatis setelah mengisi NIK" readonly />
                            <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <!-- Email -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <x-input-label for="email" class="mb-0 text-slate-700 dark:text-slate-300 font-medium">
                                {{ __('Alamat Email Aktif') }} <span class="text-rose-500 font-bold"
                                    title="Wajib diisi">*</span>
                            </x-input-label>
                        </div>
                        <div class="relative">
                            <x-text-input id="email"
                                class="block w-full px-4 py-3 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 rounded-xl transition-all duration-200 shadow-sm text-sm"
                                type="email" name="email" :value="old('email')" required autocomplete="username"
                                placeholder="example@mail.com" />
                            <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Nomor WhatsApp -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <x-input-label for="phone_register"
                                class="mb-0 text-slate-700 dark:text-slate-300 font-medium">
                                {{ __('Nomor WhatsApp') }}<span class="text-rose-500 font-bold"
                                    title="Wajib diisi">*</span>
                            </x-input-label>
                        </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-emerald-500" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                                    <path
                                        d="M12 0C5.373 0 0 5.373 0 12c0 2.117.55 4.103 1.513 5.829L0 24l6.335-1.505A11.945 11.945 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.818 9.818 0 01-5.01-1.376l-.36-.213-3.76.893.952-3.664-.234-.376A9.818 9.818 0 1121.818 12 9.828 9.828 0 0112 21.818z" />
                                </svg>
                            </div>
                            <x-text-input id="phone_register"
                                class="block w-full px-4 pl-11 py-3 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:border-emerald-500 focus:ring-emerald-500/20 rounded-xl transition-all duration-200 shadow-sm text-sm"
                                type="tel" name="phone" :value="old('phone')" inputmode="numeric"
                                placeholder="08xxxxxxxxxx" maxlength="16" autocomplete="tel" />
                        </div>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">
                            Pastikan nomor aktif untuk menerima kode verifikasi OTP dan notifikasi.
                        </p>
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>

                    <!-- Gender -->
                    <div>
                        <x-select-dropdown name="gender" label="Jenis Kelamin" :required="true"
                            :options="[['id' => 'L', 'name' => 'Laki-laki'], ['id' => 'P', 'name' => 'Perempuan']]"
                            :selected="old('gender')" placeholder="Pilih Jenis Kelamin" />
                        <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                    </div>

                    <div class="pt-3">
                        <button type="button" @click="goToStep2()"
                            class="w-full py-3.5 px-4 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-semibold rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-slate-900 shadow-sm hover:shadow text-sm sm:text-base flex justify-center items-center gap-2">
                            <span>Lanjutkan ke Data Pekerjaan</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- ═══════════════════════════════════════════════════════════ -->
                <!-- STEP 2: WORK INFO & SECURITY                                -->
                <!-- ═══════════════════════════════════════════════════════════ -->
                <div x-show="step === 2" x-transition:enter="transition ease-in-out duration-300 delay-150"
                    x-transition:enter-start="opacity-0 translate-x-8"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in-out duration-300"
                    x-transition:leave-start="opacity-100 translate-x-0"
                    x-transition:leave-end="opacity-0 -translate-x-8" style="display: none;" class="space-y-5 pt-2">

                    <!-- Alert Banner: Notifikasi Validasi Step 2 -->
                    <div x-show="step2Error" x-cloak x-transition
                        class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 flex items-start gap-3">
                        <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-rose-800 dark:text-rose-300" x-text="step2Error"></p>
                        </div>
                    </div>

                    <!-- Role, Division, Position & Office in Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Role -->
                        <div class="sm:col-span-2">
                            <x-select-dropdown name="role" label="Role Sistem" :required="true" :options="[
                                ['id' => 'staff', 'name' => 'STAFF'],
                                ['id' => 'kasie', 'name' => 'KASIE'],
                                ['id' => 'kabag-pincab', 'name' => 'KABAG-PINCAB'],
                                ['id' => 'hrd', 'name' => 'HRD'],
                                ['id' => 'direksi', 'name' => 'DIREKSI'],
                            ]" :selected="old('role')" placeholder="Pilih Role Sistem" />
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        <!-- Division -->
                        <div class="relative" x-data="{
                            autoFilled: false,
                            init() {
                                window.addEventListener('autofill-register', (e) => {
                                    if (e.detail.division_id) {
                                        this.autoFilled = true;
                                        this.$nextTick(() => {
                                            const el = this.$el.querySelector('[x-data]');
                                            if (el && el._x_dataStack) {
                                                const alpineData = el._x_dataStack[0];
                                                const opt = alpineData.options.find(o => o.id == e.detail.division_id);
                                                if (opt) alpineData.select(opt);
                                            }
                                        });
                                    } else {
                                        this.autoFilled = false;
                                    }
                                });
                                window.addEventListener('reset-autofill', () => { this.autoFilled = false; });
                            }
                        }">
                            <x-select-dropdown name="division_id" label="Divisi" :options="collect(\App\Models\Division::all())
                                ->map(fn($d) => ['id' => $d->id, 'name' => strtoupper($d->nama_divisi)])
                                ->toArray()" :selected="old('division_id')" placeholder="Pilih Divisi" />
                            <span x-show="autoFilled" x-transition
                                class="absolute top-0 right-0 mt-0.5 text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-1.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800">
                            </span>
                            <x-input-error :messages="$errors->get('division_id')" class="mt-2" />
                        </div>

                        <!-- Position -->
                        <div class="relative" x-data="{
                            autoFilled: false,
                            init() {
                                window.addEventListener('autofill-register', (e) => {
                                    if (e.detail.position_id) {
                                        this.autoFilled = true;
                                        this.$nextTick(() => {
                                            const el = this.$el.querySelector('[x-data]');
                                            if (el && el._x_dataStack) {
                                                const alpineData = el._x_dataStack[0];
                                                const opt = alpineData.options.find(o => o.id == e.detail.position_id);
                                                if (opt) alpineData.select(opt);
                                            }
                                        });
                                    } else {
                                        this.autoFilled = false;
                                    }
                                });
                                window.addEventListener('reset-autofill', () => { this.autoFilled = false; });
                            }
                        }">
                            <x-select-dropdown name="position_id" label="Jabatan" :options="collect(\App\Models\Position::all())
                                ->map(fn($p) => ['id' => $p->id, 'name' => strtoupper($p->nama_jabatan)])
                                ->toArray()" :selected="old('position_id')" placeholder="Pilih Jabatan" />
                            <span x-show="autoFilled" x-transition
                                class="absolute top-0 right-0 mt-0.5 text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-1.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800">
                            </span>
                            <x-input-error :messages="$errors->get('position_id')" class="mt-2" />
                        </div>

                        <!-- Office -->
                        <div class="sm:col-span-2 relative" x-data="{
                            autoFilled: false,
                            init() {
                                window.addEventListener('autofill-register', (e) => {
                                    if (e.detail.office_id) {
                                        this.autoFilled = true;
                                        this.$nextTick(() => {
                                            const el = this.$el.querySelector('[x-data]');
                                            if (el && el._x_dataStack) {
                                                const alpineData = el._x_dataStack[0];
                                                const opt = alpineData.options.find(o => o.id == e.detail.office_id);
                                                if (opt) alpineData.select(opt);
                                            }
                                        });
                                    } else {
                                        this.autoFilled = false;
                                    }
                                });
                                window.addEventListener('reset-autofill', () => { this.autoFilled = false; });
                            }
                        }">
                            <x-select-dropdown name="office_id" label="Kantor Penempatan" :options="collect(\App\Models\Office::all())
                                ->map(fn($o) => ['id' => $o->id, 'name' => strtoupper($o->nama_kantor)])
                                ->toArray()" :selected="old('office_id')" placeholder="Pilih Kantor" />
                            <span x-show="autoFilled" x-transition
                                class="absolute top-0 right-0 mt-0.5 text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-1.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800">
                            </span>
                            <x-input-error :messages="$errors->get('office_id')" class="mt-2" />
                        </div>
                    </div>

                    <!-- Tanggal Aktif Bekerja -->
                    <div class="relative" x-data="{
                        autoFilled: false,
                        init() {
                            window.addEventListener('autofill-register', (e) => {
                                if (e.detail.tgl_mulai_kerja) {
                                    this.autoFilled = true;
                                    const input = document.getElementById('tanggal_aktif_kerja');
                                    if (input) input.value = e.detail.tgl_mulai_kerja;
                                } else {
                                    this.autoFilled = false;
                                }
                            });
                            window.addEventListener('reset-autofill', () => {
                                this.autoFilled = false;
                                const input = document.getElementById('tanggal_aktif_kerja');
                                if (input) input.value = '';
                            });
                        }
                    }">
                        <div class="flex items-center justify-between mb-1.5">
                            <x-input-label for="tanggal_aktif_kerja"
                                class="mb-0 text-slate-700 dark:text-slate-300 font-medium">
                                {{ __('Tanggal Aktif Bekerja') }} <span class="text-rose-500 font-bold"
                                    title="Wajib diisi">*</span>
                            </x-input-label>
                            <span x-show="autoFilled" x-transition
                                class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-1.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800">
                            </span>
                        </div>
                        <div class="relative group">
                            <div
                                class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400 group-focus-within:text-primary-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                            <x-text-input id="tanggal_aktif_kerja" type="date" name="tanggal_aktif_kerja"
                                :value="old('tanggal_aktif_kerja')" required
                                class="block w-full pl-11 pr-4 py-3 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 rounded-xl transition-all duration-200 shadow-sm [&::-webkit-calendar-picker-indicator]:dark:filter [&::-webkit-calendar-picker-indicator]:dark:invert cursor-pointer text-sm" />
                        </div>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">
                            Tanggal resmi awal Anda bekerja di perusahaan untuk perhitungan kuota cuti.
                        </p>
                        <x-input-error :messages="$errors->get('tanggal_aktif_kerja')" class="mt-2" />
                    </div>

                    <!-- Separator Keamanan Akun -->
                    <div class="border-t border-slate-200 dark:border-slate-700/60 pt-5 mt-4 space-y-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                Keamanan Password
                            </h4>
                        </div>

                        <!-- Password -->
                        <div>
                            <x-input-label for="password_register"
                                class="mb-1.5 text-slate-700 dark:text-slate-300 font-medium">
                                {{ __('Password Akun') }} <span class="text-rose-500 font-bold"
                                    title="Wajib diisi">*</span>
                            </x-input-label>
                            <div class="relative group">
                                <x-text-input id="password_register" x-model="password" @input="checkPasswordMatch()"
                                    class="block w-full px-4 py-3 pr-12 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 rounded-xl transition-all duration-200 shadow-sm text-sm"
                                    type="password" name="password" required autocomplete="new-password"
                                    placeholder="Buat password yang kuat" />
                                <button type="button" id="togglePasswordRegisterBtn"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors focus:outline-none rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800"
                                    aria-label="Tampilkan password">
                                    <svg id="eye-open-register" class="w-5 h-5 hidden" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg id="eye-closed-register" class="w-5 h-5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />

                            <!-- Password Strength Indicator Checklist -->
                            <div id="password-validation"
                                class="mt-3 p-3.5 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700/60"
                                style="display: none;">
                                <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2.5">
                                    Persyaratan Keamanan Password:
                                </p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <div id="password-rule-1"
                                        class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 transition-colors duration-200">
                                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span>Huruf besar di awal</span>
                                    </div>
                                    <div id="password-rule-2"
                                        class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 transition-colors duration-200">
                                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span>Minimal 8 karakter</span>
                                    </div>
                                    <div id="password-rule-3"
                                        class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 transition-colors duration-200">
                                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span>Mengandung angka</span>
                                    </div>
                                    <div id="password-rule-4"
                                        class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 transition-colors duration-200">
                                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span>Simbol unik (!@#...)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Confirm Password with Real-time Match Feedback -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <x-input-label for="password_confirmation"
                                    class="mb-0 text-slate-700 dark:text-slate-300 font-medium">
                                    {{ __('Konfirmasi Password') }} <span class="text-rose-500 font-bold"
                                        title="Wajib diisi">*</span>
                                </x-input-label>
                            </div>
                            <div class="relative group">
                                <x-text-input id="password_confirmation" x-model="passwordConfirmation"
                                    @input="checkPasswordMatch()" :class="{
                                        'border-emerald-500 dark:border-emerald-500 focus:border-emerald-500 focus:ring-emerald-500/20': confirmStatus === 'match',
                                        'border-rose-500 dark:border-rose-500 focus:border-rose-500 focus:ring-rose-500/20': confirmStatus === 'mismatch'
                                    }"
                                    class="block w-full px-4 py-3 pr-12 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 rounded-xl transition-all duration-200 shadow-sm text-sm"
                                    type="password" name="password_confirmation" required autocomplete="new-password"
                                    placeholder="Ulangi password yang telah dibuat" />

                                <button type="button" id="togglePasswordConfirmBtn"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors focus:outline-none rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800"
                                    aria-label="Tampilkan konfirmasi password">
                                    <svg id="eye-open-confirm" class="w-5 h-5 hidden" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg id="eye-closed-confirm" class="w-5 h-5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Realtime Feedback State -->
                            <div class="mt-2 min-h-[20px]">
                                <template x-if="confirmStatus === 'match'">
                                    <div
                                        class="flex items-center gap-1.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span>Konfirmasi password cocok dan sesuai.</span>
                                    </div>
                                </template>
                                <template x-if="confirmStatus === 'mismatch'">
                                    <div
                                        class="flex items-center gap-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">
                                        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        <span>Konfirmasi password belum sesuai dengan password di atas.</span>
                                    </div>
                                </template>
                                <template x-if="confirmStatus === 'idle'">
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500">
                                        Ketik ulang password yang sama untuk konfirmasi.
                                    </p>
                                </template>
                            </div>

                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 pt-3">
                        <button type="button" @click="step = 1"
                            class="px-5 py-3.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-200 dark:focus:ring-offset-slate-900 shadow-sm text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>Kembali</span>
                        </button>

                        <x-primary-button
                            class="flex-1 justify-center py-3.5 px-4 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-semibold rounded-xl transition-all duration-200 shadow-sm hover:shadow focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-slate-900 text-sm sm:text-base">
                            {{ __('Selesaikan Registrasi') }}
                        </x-primary-button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function authForm() {
            return {
                mode: '{{ $mode ?? 'login' }}',
                setMode(newMode) {
                    this.mode = newMode;
                }
            }
        }

        function registerForm() {
            return {
                step: 1,
                maxStep: 2,
                step1Error: '',
                step2Error: '',
                password: '',
                passwordConfirmation: '',
                confirmStatus: 'idle', // 'idle' | 'match' | 'mismatch'

                checkPasswordMatch() {
                    const pass = this.password;
                    const confirm = this.passwordConfirmation;

                    if (!confirm || confirm.length === 0) {
                        this.confirmStatus = 'idle';
                        return;
                    }

                    if (pass === confirm) {
                        this.confirmStatus = 'match';
                    } else {
                        this.confirmStatus = 'mismatch';
                    }
                },

                goToStep2() {
                    this.step1Error = '';

                    const nikInput = document.getElementById('nik_register');
                    const nameInput = document.getElementById('name');
                    const emailInput = document.getElementById('email');
                    const phoneInput = document.getElementById('phone_register');
                    const genderInput = document.querySelector('input[name="gender"]');

                    // Validasi NIK
                    if (!nikInput || !nikInput.value.trim() || !nameInput || !nameInput.value.trim()) {
                        this.step1Error = 'Harap cari dan pilih NIK Anda dari daftar karyawan.';
                        if (nikInput) nikInput.focus();
                        return;
                    }

                    // Validasi Email
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailInput || !emailInput.value.trim()) {
                        this.step1Error = 'Alamat email wajib diisi.';
                        if (emailInput) emailInput.focus();
                        return;
                    }
                    if (!emailRegex.test(emailInput.value.trim())) {
                        this.step1Error = 'Format alamat email tidak valid (contoh: example@mail.com).';
                        if (emailInput) emailInput.focus();
                        return;
                    }
                    
                    const phoneRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!phoneInput || !phoneInput.value.trim()) {
                        this.step1Error = 'Nomor Whatsapp wajib diisi.';
                        if (phoneInput) phoneInput.focus();
                        return;
                    }

                    // Validasi Jenis Kelamin
                    if (!genderInput || !genderInput.value.trim()) {
                        this.step1Error = 'Harap pilih jenis kelamin Anda.';
                        return;
                    }

                    // Jika lolos validasi Step 1
                    this.step1Error = '';
                    this.step = 2;
                },

                handleFormSubmit(e) {
                    this.step2Error = '';

                    const roleInput = document.querySelector('input[name="role"]');
                    const tglInput = document.getElementById('tanggal_aktif_kerja');
                    const passInput = document.getElementById('password_register');
                    const confirmInput = document.getElementById('password_confirmation');

                    // 1. Validasi Role
                    if (!roleInput || !roleInput.value.trim()) {
                        e.preventDefault();
                        this.step2Error = 'Harap pilih Role Sistem Anda.';
                        return;
                    }

                    // 2. Validasi Tanggal Aktif Kerja
                    if (!tglInput || !tglInput.value.trim()) {
                        e.preventDefault();
                        this.step2Error = 'Tanggal aktif bekerja wajib diisi.';
                        if (tglInput) tglInput.focus();
                        return;
                    }

                    // 3. Validasi Password
                    const pass = passInput ? passInput.value : '';
                    const ruleUppercase = /^[A-Z]/.test(pass);
                    const ruleMinLength = pass.length >= 8;
                    const ruleDigit = /\d/.test(pass);
                    const ruleSymbol = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(pass);

                    if (!ruleUppercase || !ruleMinLength || !ruleDigit || !ruleSymbol) {
                        e.preventDefault();
                        this.step2Error = 'Password belum memenuhi seluruh persyaratan keamanan di bawah.';
                        if (passInput) passInput.focus();
                        return;
                    }

                    // 4. Validasi Konfirmasi Password
                    const confirm = confirmInput ? confirmInput.value : '';
                    if (pass !== confirm) {
                        e.preventDefault();
                        this.step2Error = 'Konfirmasi password tidak cocok dengan password yang telah dibuat.';
                        if (confirmInput) confirmInput.focus();
                        return;
                    }

                    // Form valid dan siap dikirim
                    this.step2Error = '';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // NIK Autocomplete Logic
            const nikInput = document.getElementById('nik_register');
            const nikDropdown = document.getElementById('nik-dropdown');
            const nameInput = document.getElementById('name');
            const pinInput = document.getElementById('pin_register');
            const nikValidation = document.getElementById('nik-validation');

            let nikLookupTimeout = null;
            let nikVerified = false;

            function setNikStatus(type, message) {
                if (!nikValidation) return;
                nikValidation.style.display = 'flex';
                const icons = {
                    success: '<svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
                    error: '<svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
                    loading: '<svg class="w-4 h-4 mr-1.5 flex-shrink-0 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>',
                    warning: '<svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>',
                };
                const colors = {
                    success: 'mt-2 text-xs font-medium text-emerald-600 dark:text-emerald-400 flex items-center',
                    error: 'mt-2 text-xs font-medium text-rose-500 dark:text-rose-400 flex items-center',
                    loading: 'mt-2 text-xs font-medium text-slate-500 dark:text-slate-400 flex items-center',
                    warning: 'mt-2 text-xs font-medium text-amber-600 dark:text-amber-400 flex items-center',
                };
                nikValidation.className = colors[type];
                nikValidation.innerHTML = (icons[type] || '') + '<span>' + message + '</span>';
            }

            function resetNikField() {
                nikVerified = false;
                if (nameInput) {
                    nameInput.value = '';
                    nameInput.readOnly = true;
                }
                if (pinInput) pinInput.value = '';
                // Reset badge autofill saat NIK diketik ulang
                window.dispatchEvent(new CustomEvent('reset-autofill'));
            }

            function showDropdown(results) {
                if (!nikDropdown) return;
                nikDropdown.innerHTML = '';
                if (results.length === 0) {
                    nikDropdown.style.display = 'none';
                    return;
                }
                results.forEach(function(item) {
                    const div = document.createElement('div');
                    div.className =
                        'px-4 py-3 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/60 transition-colors first:rounded-t-xl last:rounded-b-xl border-b border-slate-100 dark:border-slate-700/40 last:border-0';
                    div.innerHTML =
                        '<span class="block text-sm font-semibold text-slate-800 dark:text-slate-200">' +
                        item.nik + '</span>' +
                        '<span class="block text-xs text-slate-500 dark:text-slate-400 mt-0.5">' + item
                        .nama + '</span>';
                    div.addEventListener('mousedown', function(e) {
                        e.preventDefault();
                        nikDropdown.style.display = 'none';
                        setNikStatus('loading', 'Memverifikasi data karyawan...');

                        fetch('/register/validate-nik?nik=' + encodeURIComponent(item.nik))
                            .then(function(res) {
                                return res.json();
                            })
                            .then(function(data) {
                                if (data.error) {
                                    setNikStatus('error', data.error);
                                    return;
                                }
                                if (!data.valid) {
                                    setNikStatus('error', 'NIK tidak valid di data karyawan.');
                                    return;
                                }
                                selectPegawai({
                                    nik: data.data.nik,
                                    pin: data.data.pin,
                                    nama: data.data.nama,
                                    sudah_terdaftar: data.sudah_terdaftar,
                                    autofill: data.autofill,
                                });
                            })
                            .catch(function() {
                                setNikStatus('error',
                                    'Layanan validasi tidak tersedia sementara.');
                            });
                    });
                    nikDropdown.appendChild(div);
                });
                nikDropdown.style.display = 'block';
            }

            function selectPegawai(item) {
                nikInput.value = item.nik;
                nameInput.value = item.nama;
                pinInput.value = item.pin;
                nikVerified = true;
                nikDropdown.style.display = 'none';

                if (item.sudah_terdaftar) {
                    setNikStatus('warning', 'NIK ini sudah terdaftar di portal cuti.');
                    nikVerified = false;
                    window.dispatchEvent(new CustomEvent('reset-autofill'));
                } else {
                    setNikStatus('success', 'NIK ditemukan: ' + item.nama);

                    // Trigger autofill dropdown divisi, jabatan, kantor, dan tanggal
                    if (item.autofill) {
                        window.dispatchEvent(new CustomEvent('autofill-register', {
                            detail: {
                                position_id: item.autofill.position_id,
                                division_id: item.autofill.division_id,
                                office_id: item.autofill.office_id,
                                tgl_mulai_kerja: item.autofill.tgl_mulai_kerja,
                            }
                        }));
                    }
                }
            }

            if (nikInput) {
                nikInput.addEventListener('input', function() {
                    const start = this.selectionStart;
                    const end = this.selectionEnd;
                    this.value = this.value.toUpperCase();
                    this.setSelectionRange(start, end);

                    resetNikField();

                    const keyword = this.value.trim();

                    if (keyword.length < 3) {
                        nikDropdown.style.display = 'none';
                        nikValidation.style.display = 'none';
                        return;
                    }

                    setNikStatus('loading', 'Mencari data karyawan...');
                    clearTimeout(nikLookupTimeout);

                    nikLookupTimeout = setTimeout(function() {
                        fetch('/register/lookup-nik?q=' + encodeURIComponent(keyword))
                            .then(function(res) {
                                return res.json();
                            })
                            .then(function(data) {
                                if (data.error) {
                                    setNikStatus('error', data.error);
                                    nikDropdown.style.display = 'none';
                                    return;
                                }
                                if (!data.found || data.data.length === 0) {
                                    setNikStatus('error',
                                        'NIK tidak ditemukan di data karyawan.');
                                    nikDropdown.style.display = 'none';
                                    return;
                                }
                                nikValidation.style.display = 'none';
                                showDropdown(data.data);
                            })
                            .catch(function() {
                                setNikStatus('error',
                                    'Layanan pencarian tidak tersedia sementara.');
                                nikDropdown.style.display = 'none';
                            });
                    }, 400); // debounce 400ms
                });

                nikInput.addEventListener('paste', function() {
                    setTimeout(function() {
                        nikInput.value = nikInput.value.toUpperCase();
                        nikInput.dispatchEvent(new Event('input'));
                    }, 0);
                });

                // Tutup dropdown saat klik di luar
                document.addEventListener('click', function(e) {
                    if (!nikInput.contains(e.target) && !nikDropdown.contains(e.target)) {
                        nikDropdown.style.display = 'none';
                    }
                });

                nikInput.addEventListener('blur', function() {
                    // Jika NIK diisi manual tapi tidak dipilih dari dropdown
                    if (!nikVerified && this.value.trim().length > 0) {
                        setNikStatus('error', 'Harap pilih NIK dari daftar rekomendasi.');
                    }
                });
            }

            // Password Validation Logic
            const passwordInput = document.getElementById('password_register');
            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    const password = this.value;
                    const validationDiv = document.getElementById('password-validation');

                    if (password.trim() === '') {
                        validationDiv.style.display = 'none';
                        return;
                    }

                    validationDiv.style.display = 'block';

                    const rules = [{
                            id: 'password-rule-1',
                            regex: /^[A-Z]/
                        },
                        {
                            id: 'password-rule-2',
                            regex: /.{8,}/
                        },
                        {
                            id: 'password-rule-3',
                            regex: /\d/
                        },
                        {
                            id: 'password-rule-4',
                            regex: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/
                        }
                    ];

                    rules.forEach(rule => {
                        const element = document.getElementById(rule.id);
                        if (element) {
                            const isValid = rule.regex.test(password);
                            if (isValid) {
                                element.className =
                                    'flex items-center gap-2 text-xs text-emerald-600 dark:text-emerald-400 font-semibold transition-colors duration-200';
                            } else {
                                element.className =
                                    'flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 transition-colors duration-200';
                            }
                        }
                    });
                });
            }

            // NIK Login Auto Uppercase & Validation
            const nikLoginInput = document.getElementById('nik');
            if (nikLoginInput) {
                nikLoginInput.addEventListener('input', function(e) {
                    const start = this.selectionStart;
                    const end = this.selectionEnd;
                    this.value = this.value.toUpperCase();
                    this.setSelectionRange(start, end);
                    this.setCustomValidity('');
                });

                nikLoginInput.addEventListener('paste', function(e) {
                    setTimeout(() => {
                        this.value = this.value.toUpperCase();
                    }, 0);
                });

                nikLoginInput.addEventListener('blur', function() {
                    const nikPattern = /^[A-Z]{2}[0-9]{9}$/;
                    const value = this.value.trim();
                    if (value && !nikPattern.test(value)) {
                        this.setCustomValidity('Format NIK harus AP diikuti 9 angka (AP123456789)');
                    }
                });
            }

            // Password Show/Hide Toggle (Login)
            const passwordLoginInput = document.getElementById('password');
            const toggleBtn = document.getElementById('togglePasswordBtn');
            const eyeOpen = document.getElementById('eye-open');
            const eyeClosed = document.getElementById('eye-closed');

            if (toggleBtn && passwordLoginInput) {
                toggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (passwordLoginInput.type === 'password') {
                        passwordLoginInput.type = 'text';
                        eyeClosed.classList.add('hidden');
                        eyeOpen.classList.remove('hidden');
                        this.setAttribute('aria-label', 'Sembunyikan password');
                    } else {
                        passwordLoginInput.type = 'password';
                        eyeOpen.classList.add('hidden');
                        eyeClosed.classList.remove('hidden');
                        this.setAttribute('aria-label', 'Tampilkan password');
                    }
                });
            }

            // Password Show/Hide Toggle for Register
            const togglePasswordFields = [{
                    input: document.getElementById('password_register'),
                    btn: document.getElementById('togglePasswordRegisterBtn'),
                    eyeOpen: document.getElementById('eye-open-register'),
                    eyeClosed: document.getElementById('eye-closed-register')
                },
                {
                    input: document.getElementById('password_confirmation'),
                    btn: document.getElementById('togglePasswordConfirmBtn'),
                    eyeOpen: document.getElementById('eye-open-confirm'),
                    eyeClosed: document.getElementById('eye-closed-confirm')
                }
            ];

            togglePasswordFields.forEach(field => {
                if (field.btn && field.input && field.eyeOpen && field.eyeClosed) {
                    field.btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (field.input.type === 'password') {
                            field.input.type = 'text';
                            field.eyeClosed.classList.add('hidden');
                            field.eyeOpen.classList.remove('hidden');
                            this.setAttribute('aria-label', 'Sembunyikan password');
                        } else {
                            field.input.type = 'password';
                            field.eyeOpen.classList.add('hidden');
                            field.eyeClosed.classList.remove('hidden');
                            this.setAttribute('aria-label', 'Tampilkan password');
                        }
                    });
                }
            });
        });
    </script>
</x-guest-layout>