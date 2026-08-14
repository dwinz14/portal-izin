<x-guest-layout>
    <div x-data="authForm()" class="w-full space-y-4">
        <!-- Mobile Title -->
        <div class="text-center lg:hidden mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">PORTAL-IZIN</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">PT BPR Artha Pamenang</p>
        </div>

        <!-- Tab Navigation - Modern Pills -->
        <div class="flex gap-2 p-1.5 bg-primary-600 dark:bg-slate-800 rounded-xl">
            <button @click="setMode('login')"
                :class="mode === 'login' ? 'bg-white dark:bg-slate-700 text-gray-900 dark:text-white shadow-sm' :
                    'text-gray-600 dark:text-gray-400 hover:text-white dark:hover:text-white'"
                class="flex-1 py-2.5 px-4 text-sm font-semibold rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500/50">
                Sign In
            </button>
            <button @click="setMode('register')"
                :class="mode === 'register' ? 'bg-white dark:bg-slate-700 text-gray-900 dark:text-white shadow-sm' :
                    'text-gray-600 dark:text-gray-400 hover:text-white dark:hover:text-white'"
                class="flex-1 py-2.5 px-4 text-sm font-semibold rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500/50">
                Sign Up
            </button>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Login Form -->
        <div x-show="mode === 'login'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" class="space-y-6">

            <div class="text-center">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-2">Selamat Datang</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">Silahkan login dengan akun Anda</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <!-- NIK -->
                <div>
                    <x-input-label for="nik" :value="__('NIK')"
                        class="mb-2 text-gray-800 dark:text-gray-200 font-medium" />
                    <x-text-input id="nik"
                        class="block w-full px-4 py-3 bg-white dark:bg-slate-800 border-2 border-gray-200 dark:border-slate-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-primary-500 dark:focus:border-primary-400 focus:ring-2 focus:ring-primary-500/20 dark:focus:ring-primary-400/20 rounded-xl transition-all duration-200 uppercase"
                        type="text" name="nik" :value="old('nik')" required autofocus autocomplete="nik"
                        placeholder="Masukkan NIK..." maxlength="11" />
                    <x-input-error :messages="$errors->get('nik')" class="mt-2" />
                </div>

                <!-- Password -->
                <div>
                    <x-input-label for="password" :value="__('Password')"
                        class="mb-2 text-gray-800 dark:text-gray-200 font-medium" />
                    <div class="relative">
                        <x-text-input id="password"
                            class="block w-full px-4 py-3 pr-12 bg-white dark:bg-slate-800 border-2 border-gray-200 dark:border-slate-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-primary-500 dark:focus:border-primary-400 focus:ring-2 focus:ring-primary-500/20 dark:focus:ring-primary-400/20 rounded-xl transition-all duration-200"
                            type="password" name="password" required autocomplete="current-password"
                            placeholder="Masukkan password..." />
                        <button type="button" id="togglePasswordBtn"
                            class="absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors select-none">
                            <svg id="eye-open" class="w-5 h-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eye-closed" class="w-5 h-5 hidden" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <x-primary-button
                    class="w-full justify-center py-3.5 px-4 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 dark:bg-primary-500 dark:hover:bg-primary-600 focus:ring-4 focus:ring-primary-500/30 dark:focus:ring-primary-400/30 transition-all duration-200 rounded-full shadow-lg shadow-primary-500/20 font-semibold drop-shadow-lg drop-shadow-indigo-500/50">
                    {{ __('Sign In') }}
                </x-primary-button>
            </form>
        </div>

        <!-- Register Form - Multi-step for better UX -->
        <div x-show="mode === 'register'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" x-data="{ step: 1, maxStep: 2 }" class="space-y-6">

            <div class="text-center">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-2">Buat Akun Baru</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">Lengkapi data diri untuk registrasi</p>
            </div>

            <!-- Progress Indicator -->
            <div class="flex items-center justify-center gap-2">
                <div :class="step >= 1 ? 'bg-primary-600 dark:bg-primary-500' : 'bg-gray-300 dark:bg-slate-700'"
                    class="h-2 flex-1 rounded-full transition-all duration-300"></div>
                <div :class="step >= 2 ? 'bg-primary-600 dark:bg-primary-500' : 'bg-gray-300 dark:bg-slate-700'"
                    class="h-2 flex-1 rounded-full transition-all duration-300"></div>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <!-- Step 1: Personal Info -->
                <div x-show="step === 1" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="space-y-5">

                    <!-- NIK -->
                    <div>
                        <x-input-label for="nik_register" :value="__('NIK')"
                            class="mb-2 text-gray-800 dark:text-gray-200 font-medium" />
                        <x-text-input id="nik_register"
                            class="block w-full px-4 py-3 bg-white dark:bg-slate-800 border-2 border-gray-200 dark:border-slate-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-primary-500 dark:focus:border-primary-400 focus:ring-2 focus:ring-primary-500/20 dark:focus:ring-primary-400/20 rounded-xl transition-all duration-200"
                            type="text" name="nik" :value="old('nik')" required autocomplete="nik"
                            placeholder="AP123456789" />
                        <x-input-error :messages="$errors->get('nik')" class="mt-2" />
                        <div id="nik-validation" class="mt-2 text-sm font-medium" style="display: none;"></div>
                    </div>

                    <!-- Name -->
                    <div>
                        <x-input-label for="name" :value="__('Nama Lengkap')"
                            class="mb-2 text-gray-800 dark:text-gray-200 font-medium" />
                        <x-text-input id="name"
                            class="block w-full px-4 py-3 bg-white dark:bg-slate-800 border-2 border-gray-200 dark:border-slate-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-primary-500 dark:focus:border-primary-400 focus:ring-2 focus:ring-primary-500/20 dark:focus:ring-primary-400/20 rounded-xl transition-all duration-200"
                            type="text" name="name" :value="old('name')" required autocomplete="name"
                            placeholder="Nama sesuai KTP" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <!-- Email -->
                    <div>
                        <x-input-label for="email" :value="__('Email')"
                            class="mb-2 text-gray-800 dark:text-gray-200 font-medium" />
                        <x-text-input id="email"
                            class="block w-full px-4 py-3 bg-white dark:bg-slate-800 border-2 border-gray-200 dark:border-slate-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-primary-500 dark:focus:border-primary-400 focus:ring-2 focus:ring-primary-500/20 dark:focus:ring-primary-400/20 rounded-xl transition-all duration-200"
                            type="email" name="email" :value="old('email')" required autocomplete="username"
                            placeholder="email@example.com" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Gender -->
                    <x-select-dropdown name="gender" label="Gender" :options="[['id' => 'L', 'name' => 'Laki-laki'], ['id' => 'P', 'name' => 'Perempuan']]" :selected="old('gender')"
                        placeholder="Pilih Gender" />

                    <button type="button" @click="step = 2"
                        class="w-full py-2.5 px-4 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 dark:bg-primary-500 dark:hover:bg-primary-600 text-white font-medium rounded-full transition-all duration-200 shadow-md shadow-primary-500/20 focus:outline-none focus:ring-2 focus:ring-primary-500/30 text-sm">
                        Lanjutkan
                    </button>
                </div>

                <!-- Step 2: Work Info & Password -->
                <div x-show="step === 2" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="space-y-5">

                    <!-- Role, Division, Position & Office in Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Role -->
                        <x-select-dropdown name="role" label="Role" :options="[
                            ['id' => 'staff', 'name' => 'STAFF'],
                            ['id' => 'kasie', 'name' => 'KASIE'],
                            ['id' => 'kabag-pincab', 'name' => 'KABAG-PINCAB'],
                            ['id' => 'hrd', 'name' => 'HRD'],
                            ['id' => 'direksi', 'name' => 'DIREKSI'],
                        ]" :selected="old('role')"
                            placeholder="Pilih Role" />

                        <!-- Division -->
                        <x-select-dropdown name="division_id" label="Divisi" :options="collect(\App\Models\Division::all())
                            ->map(fn($d) => ['id' => $d->id, 'name' => strtoupper($d->nama_divisi)])
                            ->toArray()" :selected="old('division_id')"
                            placeholder="Pilih Divisi" />

                        <!-- Position -->
                        <x-select-dropdown name="position_id" label="Jabatan" :options="collect(\App\Models\Position::all())
                            ->map(fn($p) => ['id' => $p->id, 'name' => strtoupper($p->nama_jabatan)])
                            ->toArray()" :selected="old('position_id')"
                            placeholder="Pilih Jabatan" />

                        <!-- Office -->
                        <x-select-dropdown name="office_id" label="Kantor" :options="collect(\App\Models\Office::all())
                            ->map(fn($o) => ['id' => $o->id, 'name' => strtoupper($o->nama_kantor)])
                            ->toArray()" :selected="old('office_id')"
                            placeholder="Pilih Kantor" />
                    </div>

                    {{-- masa kerja --}}
                    <div>
                        <x-input-label for="tanggal_aktif_kerja" value="Tanggal Aktif Kerja"
                            class="mb-2 text-gray-800 dark:text-gray-200 font-medium" />

                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400 dark:text-gray-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>

                            <x-text-input id="tanggal_aktif_kerja" type="date" name="tanggal_aktif_kerja"
                                :value="old('tanggal_aktif_kerja')" required
                                class="block w-full pl-12 pr-4 py-3 bg-white dark:bg-slate-800 border-2 border-gray-200 dark:border-slate-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-primary-500 dark:focus:border-primary-400 focus:ring-2 focus:ring-primary-500/20 dark:focus:ring-primary-400/20 rounded-xl transition-all duration-200 cursor-pointer" />
                        </div>

                        <x-input-error :messages="$errors->get('tanggal_aktif_kerja')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <x-input-label for="password_register" :value="__('Password')"
                            class="mb-2 text-gray-800 dark:text-gray-200 font-medium" />
                        <x-text-input id="password_register"
                            class="block w-full px-4 py-3 bg-white dark:bg-slate-800 border-2 border-gray-200 dark:border-slate-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-primary-500 dark:focus:border-primary-400 focus:ring-2 focus:ring-primary-500/20 dark:focus:ring-primary-400/20 rounded-xl transition-all duration-200"
                            type="password" name="password" required autocomplete="new-password"
                            placeholder="Minimal 8 karakter" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />

                        <!-- Password Strength Indicator -->
                        <div id="password-validation"
                            class="mt-3 p-3 bg-gray-50 dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700"
                            style="display: none;">
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Persyaratan
                                Password:</p>
                            <div class="space-y-1.5">
                                <div id="password-rule-1" class="flex items-center gap-2 text-xs">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>Huruf besar di awal</span>
                                </div>
                                <div id="password-rule-2" class="flex items-center gap-2 text-xs">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>Minimal 8 karakter</span>
                                </div>
                                <div id="password-rule-3" class="flex items-center gap-2 text-xs">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>Mengandung angka</span>
                                </div>
                                <div id="password-rule-4" class="flex items-center gap-2 text-xs">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>Mengandung simbol (!@#$%...)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')"
                            class="mb-2 text-gray-800 dark:text-gray-200 font-medium" />
                        <x-text-input id="password_confirmation"
                            class="block w-full px-4 py-3 bg-white dark:bg-slate-800 border-2 border-gray-200 dark:border-slate-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-primary-500 dark:focus:border-primary-400 focus:ring-2 focus:ring-primary-500/20 dark:focus:ring-primary-400/20 rounded-xl transition-all duration-200"
                            type="password" name="password_confirmation" required autocomplete="new-password"
                            placeholder="Ketik ulang password" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3">
                        <button type="button" @click="step = 1"
                            class="px-4 py-2.5 bg-gray-200 hover:bg-gray-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-gray-800 dark:text-gray-200 font-medium rounded-full transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-300/50 dark:focus:ring-slate-500/50 text-sm">
                            Kembali
                        </button>
                        <x-primary-button
                            class="flex-1 justify-center py-2.5 px-4 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 dark:bg-primary-500 dark:hover:bg-primary-600 focus:ring-2 focus:ring-primary-500/30 dark:focus:ring-primary-400/30 transition-all duration-200 rounded-full shadow-md shadow-primary-500/20 font-medium text-sm">
                            {{ __('Daftar Sekarang') }}
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

        document.addEventListener('DOMContentLoaded', function() {
            // NIK validation with better UX
            const nikInput = document.getElementById('nik_register');
            if (nikInput) {
                nikInput.addEventListener('input', function() {
                    const nik = this.value;
                    const validationDiv = document.getElementById('nik-validation');

                    if (nik.trim() === '') {
                        validationDiv.style.display = 'none';
                        return;
                    }

                    validationDiv.style.display = 'block';

                    if (/^AP\d{9}$/.test(nik)) {
                        validationDiv.innerHTML =
                            '<span class="flex items-center gap-1.5"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>NIK valid</span>';
                        validationDiv.className =
                            'mt-2 text-sm font-medium text-green-600 dark:text-green-400';
                    } else {
                        validationDiv.innerHTML =
                            '<span class="flex items-center gap-1.5"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>Format: AP + 9 digit angka</span>';
                        validationDiv.className = 'mt-2 text-sm font-medium text-red-600 dark:text-red-400';
                    }
                });
            }

            // Password validation with better visual feedback
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
                            regex: /^[A-Z]/,
                            valid: false
                        },
                        {
                            id: 'password-rule-2',
                            regex: /.{8,}/,
                            valid: false
                        },
                        {
                            id: 'password-rule-3',
                            regex: /\d/,
                            valid: false
                        },
                        {
                            id: 'password-rule-4',
                            regex: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/,
                            valid: false
                        }
                    ];

                    rules.forEach(rule => {
                        const element = document.getElementById(rule.id);
                        if (element) {
                            const isValid = rule.regex.test(password);
                            if (isValid) {
                                element.className =
                                    'flex items-center gap-2 text-xs text-green-600 dark:text-green-400 font-medium';
                            } else {
                                element.className =
                                    'flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400';
                            }
                        }
                    });
                });
            }
        });

        // Auto uppercase untuk NIK input
        (function() {
            const nikInput = document.getElementById('nik');

            if (nikInput) {
                // Konversi ke uppercase saat user mengetik
                nikInput.addEventListener('input', function(e) {
                    const start = this.selectionStart;
                    const end = this.selectionEnd;

                    this.value = this.value.toUpperCase();

                    // Maintain cursor position setelah uppercase
                    this.setSelectionRange(start, end);
                });

                // Konversi ke uppercase saat paste
                nikInput.addEventListener('paste', function(e) {
                    e.preventDefault();

                    const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                    const upperText = pastedText.toUpperCase();

                    // Insert uppercase text di cursor position
                    const start = this.selectionStart;
                    const end = this.selectionEnd;
                    const currentValue = this.value;

                    this.value = currentValue.substring(0, start) + upperText + currentValue.substring(end);

                    // Set cursor position setelah pasted text
                    const newPosition = start + upperText.length;
                    this.setSelectionRange(newPosition, newPosition);

                    // Trigger input event untuk validasi lainnya jika ada
                    this.dispatchEvent(new Event('input', {
                        bubbles: true
                    }));
                });

                // Validasi format NIK (2 huruf + 9 angka)
                nikInput.addEventListener('blur', function() {
                    const nikPattern = /^[A-Z]{2}[0-9]{9}$/;
                    const value = this.value.trim();

                    if (value && !nikPattern.test(value)) {
                        // Bisa ditambahkan custom validation message
                        this.setCustomValidity(
                            'Format NIK harus AP diikuti 9 angka (Contoh: AP123456789)');
                    } else {
                        this.setCustomValidity('');
                    }
                });

                // Clear custom validity saat user mulai mengetik lagi
                nikInput.addEventListener('input', function() {
                    this.setCustomValidity('');
                });
            }
        })();

        // Click & Hold untuk unhide password
        (function() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.getElementById('togglePasswordBtn');
            const eyeOpen = document.getElementById('eye-open');
            const eyeClosed = document.getElementById('eye-closed');

            let isShowing = false;

            // Fungsi untuk show password
            function showPassword() {
                if (!isShowing) {
                    passwordInput.type = 'text';
                    eyeOpen.classList.add('hidden');
                    eyeClosed.classList.remove('hidden');
                    isShowing = true;
                }
            }

            // Fungsi untuk hide password
            function hidePassword() {
                if (isShowing) {
                    passwordInput.type = 'password';
                    eyeOpen.classList.remove('hidden');
                    eyeClosed.classList.add('hidden');
                    isShowing = false;
                }
            }

            // Event listeners untuk mouse
            toggleBtn.addEventListener('mousedown', function(e) {
                e.preventDefault(); // Mencegah focus hilang dari input
                showPassword();
            });

            toggleBtn.addEventListener('mouseup', hidePassword);
            toggleBtn.addEventListener('mouseleave', hidePassword);

            // Event listeners untuk touch (mobile)
            toggleBtn.addEventListener('touchstart', function(e) {
                e.preventDefault();
                showPassword();
            });

            toggleBtn.addEventListener('touchend', hidePassword);
            toggleBtn.addEventListener('touchcancel', hidePassword);

            // Mencegah context menu saat long press di mobile
            toggleBtn.addEventListener('contextmenu', function(e) {
                e.preventDefault();
            });
        })();
    </script>
</x-guest-layout>
