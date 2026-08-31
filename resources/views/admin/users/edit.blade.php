<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('admin.users.index') }}"
                        class="text-xs font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-700 flex items-center transition">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali ke Master User
                    </a>
                </div>
                <h2 class="border-l-4 border-primary-700 pl-4 font-bold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Edit Data User') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 pl-4">
                    Perbarui informasi profil, NIK, peran akses, dan unit penempatan karyawan.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-semibold bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-slate-600">
                    ID User: #{{ $user->id }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-4 max-w-5xl mx-auto">
        <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 shadow-sm rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-900/30 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">Perbarui Profil: {{ Str::title($user->name) }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pastikan seluruh data yang diperbarui sesuai dengan dokumen kepegawaian.</p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.users.update', $user->id) }}" class="p-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    {{-- Kolom Kiri: Informasi Pribadi & Akun --}}
                    <div class="space-y-5">
                        <div class="flex items-center gap-2 pb-2 border-b border-gray-100 dark:border-slate-700/60 text-xs font-bold uppercase tracking-wider text-primary-700 dark:text-primary-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>1. Data Identitas & Kredensial</span>
                        </div>

                        {{-- NIK with Smart Format Handler --}}
                        <div x-data="nikInputHandler('{{ old('nik', $user->nik) }}')">
                            <label for="nik" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                Nomor Induk Karyawan (NIK) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                    </svg>
                                </div>
                                <input type="text"
                                    id="nik"
                                    name="nik"
                                    x-model="nik"
                                    @input="handleInput($event)"
                                    @paste="handlePaste($event)"
                                    @blur="handleBlur($event)"
                                    maxlength="11"
                                    placeholder="Contoh: AP123456789"
                                    required
                                    class="block w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-100 font-mono tracking-wider text-sm pl-10 pr-16 py-2.5 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition"
                                    :class="isValid ? 'border-emerald-500 focus:border-emerald-500 focus:ring-emerald-500' : (nik.length > 0 && !isValid ? 'border-amber-500 focus:border-amber-500 focus:ring-amber-500' : '')" />
                                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-[11px] font-mono"
                                    :class="isValid ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-gray-400'">
                                    <span x-text="`${nik.length}/11`"></span>
                                </div>
                            </div>
                            
                            {{-- Visual Feedback Indicator --}}
                            <div class="mt-1.5 flex items-center justify-between text-xs">
                                <template x-if="isValid">
                                    <span class="text-emerald-600 dark:text-emerald-400 flex items-center font-medium">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Format NIK valid
                                    </span>
                                </template>
                                <template x-if="!isValid && nik.length > 0">
                                    <span class="text-amber-600 dark:text-amber-400 flex items-center font-medium">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        Harus AP + 9 digit angka (sisa <span x-text="11 - nik.length"></span> digit)
                                    </span>
                                </template>
                                <template x-if="nik.length === 0">
                                    <span class="text-gray-400 dark:text-gray-500">
                                        Format: AP + 9 digit angka (Contoh: AP123456789)
                                    </span>
                                </template>
                            </div>
                            @error('nik')
                                <p class="mt-1 text-xs font-semibold text-red-600 dark:text-red-400 flex items-center">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Nama Lengkap --}}
                        <div>
                            <label for="name" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <input type="text"
                                    id="name"
                                    name="name"
                                    value="{{ old('name', $user->name) }}"
                                    placeholder="Contoh: Budi Santoso"
                                    required
                                    class="block w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-100 text-sm pl-10 pr-4 py-2.5 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition" />
                            </div>
                            @error('name')
                                <p class="mt-1 text-xs font-semibold text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                Alamat Email <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <input type="email"
                                    id="email"
                                    name="email"
                                    value="{{ old('email', $user->email) }}"
                                    placeholder="contoh: user@perusahaan.com"
                                    required
                                    class="block w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-100 text-sm pl-10 pr-4 py-2.5 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition" />
                            </div>
                            @error('email')
                                <p class="mt-1 text-xs font-semibold text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Jenis Kelamin --}}
                        <div>
                            <label for="gender" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                Jenis Kelamin
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center p-3 rounded-xl border border-gray-300 dark:border-slate-600 cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                                    <input type="radio" name="gender" value="L" {{ old('gender', $user->gender) == 'L' ? 'checked' : '' }} class="text-primary-600 focus:ring-primary-500">
                                    <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-200">Laki-laki</span>
                                </label>
                                <label class="flex items-center p-3 rounded-xl border border-gray-300 dark:border-slate-600 cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                                    <input type="radio" name="gender" value="P" {{ old('gender', $user->gender) == 'P' ? 'checked' : '' }} class="text-primary-600 focus:ring-primary-500">
                                    <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-200">Perempuan</span>
                                </label>
                            </div>
                            @error('gender')
                                <p class="mt-1 text-xs font-semibold text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Reset Password Shortcut Note --}}
                        <div class="bg-gray-50 dark:bg-slate-700/40 border border-gray-200 dark:border-slate-600/80 rounded-xl p-3.5 flex items-start gap-3">
                            <div class="p-1 rounded-lg bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                            </div>
                            <div class="text-xs text-gray-600 dark:text-gray-300">
                                <span class="font-bold text-gray-800 dark:text-gray-200">Kelola Password:</span> Untuk mereset kata sandi karyawan ini ke default, gunakan tombol <em>Reset Password</em> pada menu aksi di halaman utama Master User.
                            </div>
                        </div>
                    </div>

                    {{-- Kolom Kanan: Posisi & Penempatan Kerja --}}
                    <div class="space-y-5">
                        <div class="flex items-center gap-2 pb-2 border-b border-gray-100 dark:border-slate-700/60 text-xs font-bold uppercase tracking-wider text-primary-700 dark:text-primary-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span>2. Penempatan & Hak Akses</span>
                        </div>

                        {{-- Role --}}
                        <div>
                            <label for="role" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                Role Akses <span class="text-red-500">*</span>
                            </label>
                            <select id="role" name="role" required
                                class="block w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-100 text-sm px-3.5 py-2.5 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition">
                                <option value="">-- Pilih Role Pengguna --</option>
                                @foreach (['super_admin' => 'Super Admin', 'hrd' => 'HRD', 'kabag-pincab' => 'Kabag / Pincab', 'kasie' => 'Kasie', 'staff' => 'Staff', 'direksi' => 'Direksi'] as $roleKey => $roleLabel)
                                    <option value="{{ $roleKey }}" {{ old('role', $user->role) == $roleKey ? 'selected' : '' }}>
                                        {{ $roleLabel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <p class="mt-1 text-xs font-semibold text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Divisi --}}
                        <div>
                            <label for="division_id" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                Divisi
                            </label>
                            <select id="division_id" name="division_id"
                                class="block w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-100 text-sm px-3.5 py-2.5 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition">
                                <option value="">-- Pilih Divisi (Opsional) --</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}" {{ old('division_id', $user->division_id) == $division->id ? 'selected' : '' }}>
                                        {{ strtoupper($division->nama_divisi) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('division_id')
                                <p class="mt-1 text-xs font-semibold text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Jabatan --}}
                        <div>
                            <label for="position_id" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                Jabatan
                            </label>
                            <select id="position_id" name="position_id"
                                class="block w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-100 text-sm px-3.5 py-2.5 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition">
                                <option value="">-- Pilih Jabatan (Opsional) --</option>
                                @foreach ($positions as $position)
                                    <option value="{{ $position->id }}" {{ old('position_id', $user->position_id) == $position->id ? 'selected' : '' }}>
                                        {{ strtoupper($position->nama_jabatan) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('position_id')
                                <p class="mt-1 text-xs font-semibold text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Kantor --}}
                        <div>
                            <label for="office_id" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                Kantor Penempatan
                            </label>
                            <select id="office_id" name="office_id"
                                class="block w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-100 text-sm px-3.5 py-2.5 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition">
                                <option value="">-- Pilih Kantor (Opsional) --</option>
                                @foreach ($offices as $office)
                                    <option value="{{ $office->id }}" {{ old('office_id', $user->office_id) == $office->id ? 'selected' : '' }}>
                                        {{ strtoupper($office->nama_kantor) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('office_id')
                                <p class="mt-1 text-xs font-semibold text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tanggal Aktif Kerja --}}
                        <div>
                            <label for="tanggal_aktif_kerja" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                Tanggal Aktif Kerja
                            </label>
                            <div class="relative">
                                <input type="date"
                                    id="tanggal_aktif_kerja"
                                    name="tanggal_aktif_kerja"
                                    value="{{ old('tanggal_aktif_kerja', $user->tanggal_aktif_kerja ? \Carbon\Carbon::parse($user->tanggal_aktif_kerja)->format('Y-m-d') : '') }}"
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="block w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-100 text-sm px-3.5 py-2.5 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition" />
                            </div>
                            <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">Digunakan untuk perhitungan masa kerja dan kuota cuti tahunan.</p>
                            @error('tanggal_aktif_kerja')
                                <p class="mt-1 text-xs font-semibold text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Action Buttons Footer --}}
                <div class="mt-8 pt-5 border-t border-gray-200 dark:border-slate-700 flex flex-col sm:flex-row items-center justify-end gap-3">
                    <a href="{{ route('admin.users.index') }}"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-gray-400 transition">
                        <svg class="w-4 h-4 mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Batal
                    </a>
                    <button type="submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Perbarui Data User
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Frontend Script untuk NIK Format AP + 9 Digit --}}
    <script>
        function nikInputHandler(initialValue = '') {
            return {
                nik: initialValue,
                get isValid() {
                    return /^AP\d{9}$/.test(this.nik);
                },
                handleInput(event) {
                    let val = event.target.value.toUpperCase();
                    
                    // Bersihkan karakter selain huruf dan angka
                    val = val.replace(/[^A-Z0-9]/g, '');

                    // Jika user langsung mengetik angka, otomatis tambahkan prefix 'AP'
                    if (/^\d/.test(val)) {
                        val = 'AP' + val;
                    }

                    // Pastikan 2 digit pertama adalah 'AP' / huruf dan sisanya hanya angka
                    if (val.length > 0) {
                        let prefix = val.substring(0, 2).replace(/[^A-Z]/g, '');
                        let digits = val.substring(2).replace(/\D/g, '');
                        val = (prefix + digits).substring(0, 11);
                    }

                    this.nik = val;
                    event.target.value = val;
                    event.target.setCustomValidity('');
                },
                handlePaste(event) {
                    setTimeout(() => {
                        this.handleInput(event);
                    }, 0);
                },
                handleBlur(event) {
                    if (this.nik && !this.isValid) {
                        event.target.setCustomValidity('Format NIK harus AP diikuti 9 angka (Contoh: AP123456789)');
                    } else {
                        event.target.setCustomValidity('');
                    }
                }
            };
        }
    </script>
</x-app-layout>
