<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2
                    class="border-l-4 border-primary-700 pl-4 font-bold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Master Data User') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 pl-4">
                    Kelola data akun, hak akses role, dan kredensial karyawan.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.users.create') }}"
                    class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-wider hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Tambah User
                </a>

                <button type="button" @click="$dispatch('open-reset-all-modal')"
                    class="inline-flex items-center justify-center px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-semibold text-xs uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Reset Password
                </button>

                <button type="button" @click="$dispatch('open-destroy-all-modal')"
                    class="inline-flex items-center justify-center px-3.5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold text-xs uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Hapus Semua
                </button>
            </div>
        </div>
    </x-slot>

    @php
        $hasActiveFilter =
            !empty($search) || !empty($role) || !empty($divisionId) || !empty($positionId) || !empty($officeId);
        $avatarColors = [
            'from-blue-500 to-indigo-600',
            'from-emerald-500 to-teal-600',
            'from-violet-500 to-purple-600',
            'from-amber-500 to-orange-600',
            'from-rose-500 to-pink-600',
            'from-cyan-500 to-blue-600',
        ];
    @endphp

    <div class="space-y-5" x-data="userManagementMaster({
        hasActiveFilter: {{ $hasActiveFilter ? 'true' : 'false' }}
    })" @open-reset-all-modal.window="confirmResetAll = true"
        @open-destroy-all-modal.window="confirmDestroyAll = true" @keydown.escape.window="closeAllModalsAndMenu()">

        {{-- Alerts --}}
        @if (session('success'))
            <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-200 px-4 py-3.5 rounded-xl flex items-center shadow-sm"
                x-data="{ show: true }" x-show="show" x-transition>
                <div class="p-1 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 mr-3 flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="flex-1 text-sm font-medium">
                    {{ session('success') }}
                </div>
                <button type="button" @click="show = false"
                    class="text-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800/60 text-red-800 dark:text-red-200 px-4 py-3.5 rounded-xl flex items-center shadow-sm"
                x-data="{ show: true }" x-show="show" x-transition>
                <div class="p-1 rounded-lg bg-red-100 dark:bg-red-900/50 mr-3 flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1 text-sm font-medium">
                    {{ session('error') }}
                </div>
                <button type="button" @click="show = false"
                    class="text-red-500 hover:text-red-700 dark:hover:text-red-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div
                class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 px-4 py-3.5 flex items-center gap-3.5 shadow-sm">
                <div
                    class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="text-xl font-bold text-gray-900 dark:text-gray-100 leading-none">{{ $users->total() }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">Total User Terdaftar</div>
                </div>
            </div>

            <div
                class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 px-4 py-3.5 flex items-center gap-3.5 shadow-sm">
                <div
                    class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="text-xl font-bold text-gray-900 dark:text-gray-100 leading-none">
                        {{ $divisions->count() }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">Divisi Aktif</div>
                </div>
            </div>

            <div
                class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 px-4 py-3.5 flex items-center gap-3.5 shadow-sm">
                <div
                    class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="text-xl font-bold text-gray-900 dark:text-gray-100 leading-none">
                        {{ $positions->count() }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">Jabatan Terdaftar</div>
                </div>
            </div>

            <div
                class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 px-4 py-3.5 flex items-center gap-3.5 shadow-sm">
                <div
                    class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="text-xl font-bold text-gray-900 dark:text-gray-100 leading-none">
                        {{ $offices->count() }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">Kantor</div>
                </div>
            </div>
        </div>

        {{-- Main Table Container --}}
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">

            {{-- Toolbar & Filter Toggle --}}
            <div
                class="px-5 py-4 border-b border-gray-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-3 bg-gray-50/50 dark:bg-slate-800/50">
                <div class="flex items-center gap-3">
                    <button type="button" @click="showFilters = !showFilters"
                        class="inline-flex items-center px-3.5 py-2 rounded-xl text-xs font-semibold border transition-all duration-150"
                        :class="showFilters || hasActiveFilter ?
                            'bg-primary-50 dark:bg-primary-950/50 border-primary-300 dark:border-primary-800 text-primary-700 dark:text-primary-300 ring-2 ring-primary-500/10' :
                            'bg-white dark:bg-slate-700 border-gray-300 dark:border-slate-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-600'">
                        <svg class="w-4 h-4 mr-2 text-primary-600 dark:text-primary-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span>Filter & Pencarian</span>
                        @if ($hasActiveFilter)
                            <span
                                class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-primary-600 text-white leading-none">
                                Aktif
                            </span>
                        @endif
                        <svg class="w-3.5 h-3.5 ml-2 transition-transform duration-200"
                            :class="showFilters ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div class="text-xs text-gray-500 dark:text-gray-400 hidden sm:block">
                        Menampilkan <span
                            class="font-semibold text-gray-800 dark:text-gray-200">{{ $users->count() }}</span> dari
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $users->total() }}</span>
                        total akun
                    </div>
                </div>

                @if ($hasActiveFilter)
                    <a href="{{ route('admin.users.index') }}"
                        class="inline-flex items-center text-xs font-medium text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Reset Semua Filter
                    </a>
                @endif
            </div>

            {{-- Filter Panel (Collapsible) --}}
            <div x-show="showFilters" x-collapse style="display: none;"
                class="border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900/40 p-4 sm:p-5">
                <form method="GET" action="{{ route('admin.users.index') }}">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
                        {{-- Cari Nama --}}
                        <div class="sm:col-span-2 lg:col-span-1">
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Cari Nama
                                / Email</label>
                            <div class="relative">
                                <div
                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input type="text" name="search" placeholder="Ketik nama..."
                                    value="{{ $search }}"
                                    class="block w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-xs pl-9 pr-3 py-2.5" />
                            </div>
                        </div>

                        {{-- Role --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Role
                                Akun</label>
                            <select name="role"
                                class="block w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-xs px-3 py-2.5">
                                <option value="">Semua Role</option>
                                @foreach (['super_admin' => 'Super Admin', 'hrd' => 'HRD', 'kabag-pincab' => 'Kabag / Pincab', 'kasie' => 'Kasie', 'staff' => 'Staff', 'direksi' => 'Direksi'] as $roleKey => $roleLabel)
                                    <option value="{{ $roleKey }}" {{ $roleKey == $role ? 'selected' : '' }}>
                                        {{ strtoupper($roleLabel) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Divisi --}}
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Divisi</label>
                            <select name="division_id"
                                class="block w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-xs px-3 py-2.5">
                                <option value="">Semua Divisi</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}"
                                        {{ $division->id == $divisionId ? 'selected' : '' }}>
                                        {{ strtoupper($division->nama_divisi) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Jabatan --}}
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Jabatan</label>
                            <select name="position_id"
                                class="block w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-xs px-3 py-2.5">
                                <option value="">Semua Jabatan</option>
                                @foreach ($positions as $position)
                                    <option value="{{ $position->id }}"
                                        {{ $position->id == $positionId ? 'selected' : '' }}>
                                        {{ strtoupper($position->nama_jabatan) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Kantor --}}
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Kantor</label>
                            <select name="office_id"
                                class="block w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-xs px-3 py-2.5">
                                <option value="">Semua Kantor</option>
                                @foreach ($offices as $office)
                                    <option value="{{ $office->id }}"
                                        {{ $office->id == $officeId ? 'selected' : '' }}>
                                        {{ strtoupper($office->nama_kantor) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-end gap-2 mt-4 pt-3 border-t border-gray-200 dark:border-slate-700/60">
                        <a href="{{ route('admin.users.index') }}"
                            class="px-4 py-2 bg-white dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-600 transition">
                            Reset
                        </a>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold transition shadow-sm">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Terapkan Filter
                        </button>
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                    <thead>
                        <tr class="bg-gray-50/80 dark:bg-slate-700/50">
                            <th scope="col"
                                class="w-12 px-4 py-3.5 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                #
                            </th>
                            <th scope="col"
                                class="px-4 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[200px]">
                                Pengguna
                            </th>
                            <th scope="col"
                                class="px-4 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                NIK
                            </th>
                            <th scope="col"
                                class="px-4 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Role
                            </th>
                            <th scope="col"
                                class="px-4 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">
                                Divisi
                            </th>
                            <th scope="col"
                                class="px-4 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">
                                Jabatan
                            </th>
                            <th scope="col"
                                class="px-4 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">
                                Kantor
                            </th>
                            <th scope="col"
                                class="w-16 px-4 py-3.5 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-100 dark:divide-slate-700/60">
                        @forelse ($users as $index => $user)
                            @php
                                $avatarColor = $avatarColors[crc32($user->name) % count($avatarColors)];
                                $initials = strtoupper(substr($user->name, 0, 2));
                                $userPayload = [
                                    'id' => $user->id,
                                    'name' => addslashes(Str::title($user->name)),
                                    'edit_url' => route('admin.users.edit', $user->id),
                                    'destroy_url' => route('admin.users.destroy', $user->id),
                                    'reset_url' => route('admin.users.resetPassword', $user->id),
                                ];
                            @endphp
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors"
                                :class="activeUser?.id === {{ $user->id }} && actionMenuOpen ?
                                    'bg-primary-50/40 dark:bg-primary-950/20' : ''">

                                {{-- Nomor Urut --}}
                                <td
                                    class="px-4 py-3.5 text-center text-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ $users->firstItem() + $index }}
                                </td>

                                {{-- User & Email --}}
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex-shrink-0 w-9 h-9 bg-gradient-to-br {{ $avatarColor }} rounded-xl flex items-center justify-center text-white font-bold text-xs shadow-sm">
                                            {{ $initials }}
                                        </div>
                                        <div class="min-w-0">
                                            <div
                                                class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                                                {{ Str::title($user->name) }}
                                            </div>
                                            <div class="text-xs text-gray-400 dark:text-gray-500 truncate">
                                                {{ $user->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- NIK --}}
                                <td class="px-4 py-3.5 text-xs font-mono font-medium text-gray-600 dark:text-gray-300">
                                    {{ $user->nik ?? '-' }}
                                </td>

                                {{-- Role Badge --}}
                                <td class="px-4 py-3.5 text-xs whitespace-nowrap">
                                    @php
                                        $roleBadgeClasses = match ($user->role) {
                                            'super_admin'
                                                => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800/60',
                                            'hrd'
                                                => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800/60',
                                            'kabag-pincab'
                                                => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/60',
                                            'kasie'
                                                => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/60',
                                            'staff'
                                                => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800/60',
                                            'direksi'
                                                => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800/60',
                                            default
                                                => 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
                                        };
                                    @endphp
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-semibold border {{ $roleBadgeClasses }}">
                                        {{ strtoupper(str_replace('_', ' ', $user->role)) }}
                                    </span>
                                </td>

                                {{-- Divisi --}}
                                <td class="px-4 py-3.5 text-xs text-gray-700 dark:text-gray-300 hidden md:table-cell">
                                    {{ $user->division?->nama_divisi ? strtoupper($user->division->nama_divisi) : '-' }}
                                </td>

                                {{-- Jabatan --}}
                                <td class="px-4 py-3.5 text-xs text-gray-700 dark:text-gray-300 hidden lg:table-cell">
                                    {{ $user->position?->nama_jabatan ? strtoupper($user->position->nama_jabatan) : '-' }}
                                </td>

                                {{-- Kantor --}}
                                <td class="px-4 py-3.5 text-xs text-gray-700 dark:text-gray-300 hidden sm:table-cell">
                                    <span class="inline-flex items-center text-xs">
                                        <svg class="w-3.5 h-3.5 mr-1 text-gray-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        {{ $user->office?->nama_kantor ? strtoupper($user->office->nama_kantor) : '-' }}
                                    </span>
                                </td>

                                {{-- Tombol Trigger Aksi (Fixed Floating Trigger) --}}
                                <td class="px-4 py-3.5 text-center">
                                    <button type="button"
                                        @click="toggleActionMenu($event, {{ json_encode($userPayload) }})"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors"
                                        title="Opsi Aksi">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                        <div
                                            class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-slate-700/60 flex items-center justify-center text-gray-400 dark:text-gray-500 mb-3">
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">Tidak ada
                                            data user ditemukan</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            @if ($hasActiveFilter)
                                                Kriteria pencarian tidak cocok dengan data apapun.
                                            @else
                                                Belum ada user yang tersimpan di sistem.
                                            @endif
                                        </p>
                                        @if ($hasActiveFilter)
                                            <a href="{{ route('admin.users.index') }}"
                                                class="mt-3 px-3.5 py-1.5 rounded-xl bg-primary-50 dark:bg-primary-950/40 text-primary-700 dark:text-primary-300 text-xs font-semibold border border-primary-200 dark:border-primary-800">
                                                Hapus Filter Pencarian
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            @if ($users->hasPages())
                <div
                    class="px-5 py-3.5 bg-gray-50/70 dark:bg-slate-700/30 border-t border-gray-200 dark:border-slate-700 flex items-center justify-between">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

        {{-- =========================================================================
             FLOATING ACTION MENU (FIXED POSITION DI DEPAN TABEL, BEBAS CLIPPING OVERFLOW)
        ========================================================================= --}}
        <div x-show="actionMenuOpen" x-cloak @click.outside="actionMenuOpen = false"
            @scroll.window="actionMenuOpen = false" @resize.window="actionMenuOpen = false"
            x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            :style="`position: fixed; top: ${menuPos.top}px; left: ${menuPos.left}px; z-index: 60;`"
            class="w-52 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-slate-700 py-1.5 focus:outline-none backdrop-blur-md overflow-hidden"
            role="menu">

            <div
                class="px-3.5 py-2 border-b border-gray-100 dark:border-slate-700/60 bg-gray-50/50 dark:bg-slate-700/30">
                <p class="text-[10px] uppercase font-bold text-gray-400 dark:text-gray-500 tracking-wider">Aksi User
                </p>
                <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 truncate mt-0.5"
                    x-text="activeUser?.name"></p>
            </div>

            <div class="py-1">
                {{-- Edit User --}}
                <a :href="activeUser?.edit_url"
                    class="flex items-center px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-primary-50 dark:hover:bg-primary-950/40 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
                    role="menuitem">
                    <svg class="w-4 h-4 mr-2.5 text-gray-400 dark:text-gray-500 group-hover:text-primary-500"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Data User
                </a>

                {{-- Reset Password --}}
                <button type="button" @click="triggerResetPasswordModal()"
                    class="flex items-center w-full px-3.5 py-2 text-xs font-semibold text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/30 transition-colors"
                    role="menuitem">
                    <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    Reset Password
                </button>

                {{-- Hapus User --}}
                <button type="button" @click="triggerDeleteModal()"
                    class="flex items-center w-full px-3.5 py-2 text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors border-t border-gray-100 dark:border-slate-700/60 mt-1 pt-1.5"
                    role="menuitem">
                    <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Hapus Akun User
                </button>
            </div>
        </div>

        {{-- =========================================================================
             MODAL: HAPUS USER (INDIVIDUAL)
        ========================================================================= --}}
        <div x-show="confirmDelete" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4"
            x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="confirmDelete = false"></div>
            <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
                x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">
                <div class="h-1.5 bg-red-600 w-full"></div>
                <div class="p-6">
                    <div class="flex gap-4">
                        <div
                            class="flex-shrink-0 w-11 h-11 rounded-xl bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Hapus Akun Pengguna?</h3>
                            <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                Apakah Anda yakin ingin menghapus akun <strong
                                    class="text-gray-800 dark:text-gray-200 font-semibold"
                                    x-text="selectedUser?.name"></strong>? Tindakan ini akan menghapus data akses user
                                terkait.
                            </p>
                        </div>
                    </div>
                    <form :action="selectedUser?.destroy_url" method="POST" class="mt-6 flex justify-end gap-2">
                        @csrf
                        @method('DELETE')
                        <button type="button" @click="confirmDelete = false"
                            class="px-4 py-2.5 rounded-xl border border-gray-300 dark:border-slate-600 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-semibold transition shadow-sm">
                            Ya, Hapus User
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- =========================================================================
             MODAL: RESET PASSWORD (INDIVIDUAL)
        ========================================================================= --}}
        <div x-show="confirmReset" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4"
            x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="confirmReset = false"></div>
            <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
                x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">
                <div class="h-1.5 bg-amber-500 w-full"></div>
                <div class="p-6">
                    <div class="flex gap-4">
                        <div
                            class="flex-shrink-0 w-11 h-11 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Reset Password User?</h3>
                            <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                Password user <strong class="text-gray-800 dark:text-gray-200 font-semibold"
                                    x-text="selectedUser?.name"></strong> akan direset ke password default sistem
                                (<code
                                    class="bg-gray-100 dark:bg-slate-700 px-1 py-0.5 rounded text-primary-600 dark:text-primary-400 font-mono">{{ config('app.default_user_password', 'password123') }}</code>).
                                User akan diminta ganti password saat login berikutnya.
                            </p>
                        </div>
                    </div>
                    <form :action="selectedUser?.reset_url" method="POST" class="mt-6 flex justify-end gap-2">
                        @csrf
                        <button type="button" @click="confirmReset = false"
                            class="px-4 py-2.5 rounded-xl border border-gray-300 dark:border-slate-600 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold transition shadow-sm">
                            Ya, Reset Password
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- =========================================================================
             MODAL: RESET SEMUA PASSWORD (MASS ACTION)
        ========================================================================= --}}
        <div x-show="confirmResetAll" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4"
            x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="confirmResetAll = false"></div>
            <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
                x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">
                <div class="h-1.5 bg-amber-500 w-full"></div>
                <div class="p-6">
                    <div class="flex gap-4">
                        <div
                            class="flex-shrink-0 w-11 h-11 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Reset Semua Password User?
                            </h3>
                            <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                Semua user non-super admin akan direset passwordnya ke default (<code
                                    class="bg-gray-100 dark:bg-slate-700 px-1 py-0.5 rounded text-amber-600 dark:text-amber-400 font-mono">{{ config('app.default_user_password', 'password123') }}</code>)
                                dan diwajibkan mengganti password saat login berikutnya.
                            </p>
                        </div>
                    </div>
                    <form action="{{ route('admin.users.resetAllPasswords') }}" method="POST"
                        class="mt-6 flex justify-end gap-2">
                        @csrf
                        <button type="button" @click="confirmResetAll = false"
                            class="px-4 py-2.5 rounded-xl border border-gray-300 dark:border-slate-600 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold transition shadow-sm">
                            Ya, Reset Semua Password
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- =========================================================================
             MODAL: HAPUS SEMUA USER (MASS ACTION)
        ========================================================================= --}}
        <div x-show="confirmDestroyAll" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4"
            x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="confirmDestroyAll = false"></div>
            <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
                x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">
                <div class="h-1.5 bg-red-600 w-full"></div>
                <div class="p-6">
                    <div class="flex gap-4">
                        <div
                            class="flex-shrink-0 w-11 h-11 rounded-xl bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-bold text-red-600 dark:text-red-400">Peringatan: Hapus Semua
                                User!</h3>
                            <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                Apakah Anda yakin ingin menghapus <strong
                                    class="text-red-600 dark:text-red-400 font-semibold">SEMUA USER</strong> non-super
                                admin? Data yang dihapus tidak dapat dipulihkan kembali. Super admin akan tetap aman.
                            </p>
                        </div>
                    </div>
                    <form action="{{ route('admin.users.destroyAll') }}" method="POST"
                        class="mt-6 flex justify-end gap-2">
                        @csrf
                        @method('DELETE')
                        <button type="button" @click="confirmDestroyAll = false"
                            class="px-4 py-2.5 rounded-xl border border-gray-300 dark:border-slate-600 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-semibold transition shadow-sm">
                            Ya, Hapus Semua User
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>

    {{-- Script Alpine --}}
    <script>
        function userManagementMaster(config) {
            return {
                showFilters: config.hasActiveFilter || false,
                hasActiveFilter: config.hasActiveFilter || false,

                // Floating Action Menu State
                actionMenuOpen: false,
                activeUser: null,
                menuPos: {
                    top: 0,
                    left: 0
                },

                // Modal States
                confirmDelete: false,
                confirmReset: false,
                confirmResetAll: false,
                confirmDestroyAll: false,
                selectedUser: null,

                toggleActionMenu(event, user) {
                    if (this.actionMenuOpen && this.activeUser?.id === user.id) {
                        this.actionMenuOpen = false;
                        this.activeUser = null;
                        return;
                    }

                    const triggerRect = event.currentTarget.getBoundingClientRect();
                    const menuWidth = 208; // w-52 (13rem)
                    const menuEstimatedHeight = 150;

                    // Horizontal position: align right of menu to right of button
                    let left = triggerRect.right - menuWidth;
                    if (left < 12) {
                        left = 12;
                    }
                    if (left + menuWidth > window.innerWidth - 12) {
                        left = window.innerWidth - menuWidth - 12;
                    }

                    // Vertical position: drop below by default, or pop above if near screen bottom
                    let top = triggerRect.bottom + 6;
                    const spaceBelow = window.innerHeight - triggerRect.bottom;
                    if (spaceBelow < menuEstimatedHeight + 20 && triggerRect.top > menuEstimatedHeight) {
                        top = triggerRect.top - menuEstimatedHeight - 6;
                    }

                    this.menuPos = {
                        top,
                        left
                    };
                    this.activeUser = user;
                    this.actionMenuOpen = true;
                },

                triggerDeleteModal() {
                    this.selectedUser = this.activeUser;
                    this.actionMenuOpen = false;
                    this.confirmDelete = true;
                },

                triggerResetPasswordModal() {
                    this.selectedUser = this.activeUser;
                    this.actionMenuOpen = false;
                    this.confirmReset = true;
                },

                closeAllModalsAndMenu() {
                    this.actionMenuOpen = false;
                    this.confirmDelete = false;
                    this.confirmReset = false;
                    this.confirmResetAll = false;
                    this.confirmDestroyAll = false;
                }
            };
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <x-toast-notification />
</x-app-layout>
