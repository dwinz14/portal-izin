<x-guest-layout>
    <div x-data="authForm()" class="w-full space-y-8">

        <!-- Tab Navigation - Segmented Control style -->
        <div class="relative flex p-1.5 bg-slate-100 dark:bg-slate-800/80 rounded-xl mb-8">
            <button @click="setMode('login')"
                :class="mode === 'login' ? 'text-slate-900 dark:text-white font-semibold' :
                    'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 font-medium'"
                class="relative flex-1 py-2.5 text-sm rounded-lg transition-colors duration-300 z-10 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                Sign In
            </button>
            <button @click="setMode('register')"
                :class="mode === 'register' ? 'text-slate-900 dark:text-white font-semibold' :
                    'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 font-medium'"
                class="relative flex-1 py-2.5 text-sm rounded-lg transition-colors duration-300 z-10 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                Sign Up
            </button>
            <!-- Animated Background Pill -->
            <div class="absolute top-1.5 bottom-1.5 w-[calc(50%-6px)] bg-white dark:bg-slate-700 rounded-lg shadow-sm transition-transform duration-300 ease-in-out z-0"
                :class="mode === 'register' ? 'translate-x-full left-[4px]' : 'translate-x-0 left-1.5'">
            </div>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Login Form -->
        <div x-show="mode === 'login'" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            style="display: none;" class="space-y-6">

            <div class="mb-8">
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight mb-2">Selamat
                    Datang</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Silakan masuk dengan akun anda</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                {{-- Force login flag: aktif hanya saat ada konflik sesi --}}
                @if ($errors->has('session_conflict'))
                    <input type="hidden" name="force_login" value="1">
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
                    <x-input-label for="nik" :value="__('Nomor Induk Karyawan (NIK)')"
                        class="mb-1.5 text-slate-700 dark:text-slate-300 font-medium" />
                    <x-text-input id="nik"
                        class="block w-full px-4 py-3 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 rounded-xl transition-all duration-200 uppercase shadow-sm"
                        type="text" name="nik" :value="old('nik')" required autofocus autocomplete="nik"
                        placeholder="Contoh: AP123456789" maxlength="11" />
                    <x-input-error :messages="$errors->get('nik')" class="mt-2" />
                </div>

                <!-- Password -->
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <x-input-label for="password" :value="__('Password')"
                            class="mb-0 text-slate-700 dark:text-slate-300 font-medium" />
                        @if (Route::has('password.request'))
                            <a class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 transition-colors"
                                href="{{ route('password.request') }}">
                                Lupa password?
                            </a>
                        @endif
                    </div>
                    <div class="relative group">
                        <x-text-input id="password"
                            class="block w-full px-4 py-3 pr-12 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 rounded-xl transition-all duration-200 shadow-sm"
                            type="password" name="password" required autocomplete="current-password"
                            placeholder="••••••••" />
                        <button type="button" id="togglePasswordBtn"
                            class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors focus:outline-none rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                            <svg id="eye-open" class="w-5 h-5 hidden" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eye-closed" class="w-5 h-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Remember Me -->
                <div class="block mt-4">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer">
                        <input id="remember_me" type="checkbox"
                            class="rounded dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-primary-600 shadow-sm focus:ring-primary-500 dark:focus:ring-primary-600 dark:focus:ring-offset-slate-900"
                            name="remember">
                        <span
                            class="ml-2 text-sm text-slate-600 dark:text-slate-400 font-medium select-none">{{ __('Ingat sesi saya') }}</span>
                    </label>
                </div>

                <div class="pt-2">
                    <x-primary-button
                        class="w-full justify-center py-3.5 px-4 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white rounded-xl shadow-sm hover:shadow focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-slate-900 transition-all duration-200 font-semibold text-base">
                        @if ($errors->has('session_conflict'))
                            ⚡ Paksa Login — Perangkat Lain Akan Logout
                        @else
                            {{ __('Masuk') }}
                        @endif
                    </x-primary-button>
                </div>
            </form>
        </div>

        <!-- Register Form - Multi-step -->
        <div x-show="mode === 'register'" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-data="{ step: 1, maxStep: 2 }" style="display: none;" class="space-y-6">

            <div class="mb-8 text-center sm:text-left">
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight mb-2">Buat Akun
                    Baru</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Lengkapi data diri anda untuk pendaftaran akun.
                </p>
            </div>

            <!-- Progress Stepper -->
            <div class="mb-8 px-2">
                <div class="flex items-center justify-between relative">
                    <div
                        class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-slate-200 dark:bg-slate-700/80 rounded-full z-0">
                    </div>
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-primary-600 dark:bg-primary-500 rounded-full z-0 transition-all duration-500 ease-out"
                        :style="'width: ' + ((step - 1) / (maxStep - 1) * 100) + '%'"></div>

                    <!-- Step 1 -->
                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold transition-colors duration-300"
                            :class="step >= 1 ? 'bg-primary-600 text-white shadow-md shadow-primary-500/30' :
                                'bg-slate-200 text-slate-500 dark:bg-slate-700 dark:text-slate-400'">
                            1
                        </div>
                        <span
                            class="absolute top-10 text-xs font-medium whitespace-nowrap transition-colors duration-300"
                            :class="step >= 1 ? 'text-slate-900 dark:text-slate-200' : 'text-slate-500 dark:text-slate-400'">Data
                            Diri</span>
                    </div>

                    <!-- Step 2 -->
                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold transition-colors duration-300"
                            :class="step >= 2 ? 'bg-primary-600 text-white shadow-md shadow-primary-500/30' :
                                'bg-slate-200 text-slate-500 dark:bg-slate-700 dark:text-slate-400'">
                            2
                        </div>
                        <span
                            class="absolute top-10 text-xs font-medium whitespace-nowrap transition-colors duration-300"
                            :class="step >= 2 ? 'text-slate-900 dark:text-slate-200' : 'text-slate-500 dark:text-slate-400'">Data
                            Pekerjaan</span>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-5 mt-12">
                @csrf

                <!-- Step 1: Personal Info -->
                <div x-show="step === 1" x-transition:enter="transition ease-in-out duration-300 delay-150"
                    x-transition:enter-start="opacity-0 translate-x-8"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in-out duration-300"
                    x-transition:leave-start="opacity-100 translate-x-0"
                    x-transition:leave-end="opacity-0 -translate-x-8" class="space-y-5 pt-4">

                    <!-- Hidden PIN (auto-filled dari DB karyawan) -->
                    <input type="hidden" id="pin_register" name="pin" value="{{ old('pin') }}">

                    <!-- NIK dengan Autocomplete -->
                    <div class="relative">
                        <x-input-label for="nik_register" :value="__('Nomor Induk Karyawan (NIK)')"
                            class="mb-1.5 text-slate-700 dark:text-slate-300 font-medium" />
                        <x-text-input id="nik_register"
                            class="block w-full px-4 py-3 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 rounded-xl transition-all duration-200 uppercase shadow-sm"
                            type="text" name="nik" :value="old('nik')" required autocomplete="off"
                            placeholder="Ketik NIK untuk mencari..." />
                        <x-input-error :messages="$errors->get('nik')" class="mt-2" />
                        <div id="nik-validation" class="mt-2 text-xs font-medium" style="display: none;"></div>

                        <!-- Dropdown hasil autocomplete -->
                        <div id="nik-dropdown"
                            class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg"
                            style="display: none;">
                        </div>
                    </div>

                    <!-- Name (readonly, auto-filled) -->
                    <div>
                        <x-input-label for="name" :value="__('Nama Lengkap')"
                            class="mb-1.5 text-slate-700 dark:text-slate-300 font-medium" />
                        <x-text-input id="name"
                            class="block w-full px-4 py-3 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 rounded-xl transition-all duration-200 shadow-sm bg-slate-50 dark:bg-slate-800/70"
                            type="text" name="name" :value="old('name')" required autocomplete="off"
                            placeholder="Terisi otomatis setelah memilih NIK" readonly />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <!-- Email -->
                    <div>
                        <x-input-label for="email" :value="__('Alamat Email')"
                            class="mb-1.5 text-slate-700 dark:text-slate-300 font-medium" />
                        <x-text-input id="email"
                            class="block w-full px-4 py-3 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 rounded-xl transition-all duration-200 shadow-sm"
                            type="email" name="email" :value="old('email')" required autocomplete="username"
                            placeholder="email@bprarthapamenang.co.id" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Nomor WhatsApp -->
                    <div>
                        <x-input-label for="phone_register" value="Nomor WhatsApp"
                            class="mb-1.5 text-slate-700 dark:text-slate-300 font-medium" />
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-green-500" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                                    <path
                                        d="M12 0C5.373 0 0 5.373 0 12c0 2.117.55 4.103 1.513 5.829L0 24l6.335-1.505A11.945 11.945 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.818 9.818 0 01-5.01-1.376l-.36-.213-3.76.893.952-3.664-.234-.376A9.818 9.818 0 1121.818 12 9.828 9.828 0 0112 21.818z" />
                                </svg>
                            </div>
                            <x-text-input id="phone_register"
                                class="block w-full px-4 pl-12 py-3 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:border-green-500 focus:ring-green-500/20 rounded-xl transition-all duration-200 shadow-sm"
                                type="tel" name="phone" :value="old('phone')" inputmode="numeric"
                                placeholder="08xxxxxxxxxx" maxlength="16" autocomplete="tel" />
                        </div>
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>

                    <!-- Gender -->
                    <div>
                        <x-select-dropdown name="gender" label="Jenis Kelamin" :options="[['id' => 'L', 'name' => 'Laki-laki'], ['id' => 'P', 'name' => 'Perempuan']]" :selected="old('gender')"
                            placeholder="Pilih Jenis Kelamin" />
                    </div>

                    <div class="pt-4">
                        <button type="button" @click="step = 2"
                            class="w-full py-3.5 px-4 bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 dark:text-slate-900 text-white font-semibold rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 dark:focus:ring-offset-slate-900 shadow-sm text-base flex justify-center items-center gap-2">
                            Lanjutkan ke Data Pekerjaan
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Work Info & Password -->
                <div x-show="step === 2" x-transition:enter="transition ease-in-out duration-300 delay-150"
                    x-transition:enter-start="opacity-0 translate-x-8"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in-out duration-300"
                    x-transition:leave-start="opacity-100 translate-x-0"
                    x-transition:leave-end="opacity-0 -translate-x-8" style="display: none;" class="space-y-5 pt-4">

                    <!-- Role, Division, Position & Office in Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Role -->
                        <x-select-dropdown name="role" label="Role Sistem" :options="[
                            ['id' => 'staff', 'name' => 'STAFF'],
                            ['id' => 'kasie', 'name' => 'KASIE'],
                            ['id' => 'kabag-pincab', 'name' => 'KABAG-PINCAB'],
                            ['id' => 'hrd', 'name' => 'HRD'],
                            ['id' => 'direksi', 'name' => 'DIREKSI'],
                        ]" :selected="old('role')"
                            placeholder="Pilih Role" />

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
                                ->toArray()" :selected="old('division_id')"
                                placeholder="Pilih Divisi" />
                            <span x-show="autoFilled" x-transition
                                class="absolute top-0 right-0 mt-0.5 text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-1.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800">
                                otomatis
                            </span>
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
                                ->toArray()"
                                :selected="old('position_id')" placeholder="Pilih Jabatan" />
                            <span x-show="autoFilled" x-transition
                                class="absolute top-0 right-0 mt-0.5 text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-1.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800">
                                otomatis
                            </span>
                        </div>

                        <!-- Office -->
                        <div class="relative" x-data="{
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
                                ->toArray()"
                                :selected="old('office_id')" placeholder="Pilih Kantor" />
                            <span x-show="autoFilled" x-transition
                                class="absolute top-0 right-0 mt-0.5 text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-1.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800">
                                otomatis
                            </span>
                        </div>
                    </div>

                    {{-- masa kerja --}}
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
                        <x-input-label for="tanggal_aktif_kerja" value="Tanggal Aktif Bekerja"
                            class="mb-1.5 text-slate-700 dark:text-slate-300 font-medium" />
                        <span x-show="autoFilled" x-transition
                            class="absolute top-0 right-0 mt-0.5 text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-1.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800">
                            otomatis
                        </span>
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
                                class="block w-full pl-11 pr-4 py-3 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 rounded-xl transition-all duration-200 shadow-sm [&::-webkit-calendar-picker-indicator]:dark:filter [&::-webkit-calendar-picker-indicator]:dark:invert cursor-pointer" />
                        </div>
                        <x-input-error :messages="$errors->get('tanggal_aktif_kerja')" class="mt-2" />
                    </div>

                    <div class="border-t border-slate-200 dark:border-slate-700/60 pt-5 mt-3">
                        <!-- Password -->
                        <div class="mb-5">
                            <x-input-label for="password_register" :value="__('Password Akun')"
                                class="mb-1.5 text-slate-700 dark:text-slate-300 font-medium" />
                            <div class="relative group">
                                <x-text-input id="password_register"
                                    class="block w-full px-4 py-3 pr-12 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 rounded-xl transition-all duration-200 shadow-sm"
                                    type="password" name="password" required autocomplete="new-password"
                                    placeholder="Buat password yang kuat" />
                                <button type="button" id="togglePasswordRegisterBtn"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors focus:outline-none rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                                    <svg id="eye-open-register" class="w-5 h-5 hidden" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg id="eye-closed-register" class="w-5 h-5" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />

                            <!-- Password Strength Indicator -->
                            <div id="password-validation"
                                class="mt-3 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700/60"
                                style="display: none;">
                                <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 mb-3">Persyaratan
                                    Keamanan Password:</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
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

                        <!-- Confirm Password -->
                        <div>
                            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')"
                                class="mb-1.5 text-slate-700 dark:text-slate-300 font-medium" />
                            <div class="relative group">
                                <x-text-input id="password_confirmation"
                                    class="block w-full px-4 py-3 pr-12 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 rounded-xl transition-all duration-200 shadow-sm"
                                    type="password" name="password_confirmation" required autocomplete="new-password"
                                    placeholder="Ulangi password di atas" />
                                <button type="button" id="togglePasswordConfirmBtn"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors focus:outline-none rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                                    <svg id="eye-open-confirm" class="w-5 h-5 hidden" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg id="eye-closed-confirm" class="w-5 h-5" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="step = 1"
                            class="px-5 py-3.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-200 dark:focus:ring-offset-slate-900 shadow-sm text-sm">
                            Kembali
                        </button>
                        <x-primary-button
                            class="flex-1 justify-center py-3.5 px-4 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-semibold rounded-xl transition-all duration-200 shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-slate-900 text-base">
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
                    // Reset step when switching to register
                    if (newMode === 'register' && typeof this.step !== 'undefined') {
                        this.step = 1;
                    }
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
                nikValidation.style.display = 'flex';
                const icons = {
                    success: '<svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
                    error: '<svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
                    loading: '<svg class="w-4 h-4 mr-1.5 flex-shrink-0 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>',
                    warning: '<svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>',
                };
                const colors = {
                    success: 'mt-2 text-xs font-medium text-emerald-600 dark:text-emerald-400 flex items-center',
                    error: 'mt-2 text-xs font-medium text-red-500 dark:text-red-400 flex items-center',
                    loading: 'mt-2 text-xs font-medium text-slate-500 dark:text-slate-400 flex items-center',
                    warning: 'mt-2 text-xs font-medium text-amber-600 dark:text-amber-400 flex items-center',
                };
                nikValidation.className = colors[type];
                nikValidation.innerHTML = (icons[type] || '') + '<span>' + message + '</span>';
            }

            function resetNikField() {
                nikVerified = false;
                nameInput.value = '';
                nameInput.readOnly = true;
                pinInput.value = '';
                // Reset badge autofill saat NIK diketik ulang
                window.dispatchEvent(new CustomEvent('reset-autofill'));
            }

            function showDropdown(results) {
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
                    // Reset autofill badge jika NIK sudah terdaftar
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
                        setNikStatus('error', 'Pilih NIK dari daftar yang muncul.');
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
                                    'flex items-center gap-2 text-xs text-emerald-600 dark:text-emerald-400 font-medium transition-colors duration-200';
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
                        this.setCustomValidity('Format NIK harus AP diikuti 9 angka (Contoh: AP123456789)');
                    }
                });
            }

            // Password Show/Hide Toggle (Standard Click)
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
