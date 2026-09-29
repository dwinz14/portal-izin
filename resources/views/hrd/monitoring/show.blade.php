<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <a href="{{ route('hrd.monitoring.index') }}"
                    class="p-2.5 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 shadow-xs transition-all duration-150 group"
                    title="Kembali ke Daftar Monitoring">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h2
                            class="border-l-[5px] border-primary-600 pl-3.5 font-display font-bold text-xl text-slate-800 dark:text-slate-100 leading-tight">
                            Detail Karyawan
                        </h2>
                        <span class="text-xs text-slate-400 dark:text-slate-500">/</span>
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                            {{ $user->nik ?? '-' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 pl-3.5">
                        Profil lengkap, kuota cuti tahun berjalan, dan riwayat presensi yang telah disetujui.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('hrd.monitoring.index') }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition-colors">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    <span>Daftar Monitoring</span>
                </a>
            </div>
        </div>
    </x-slot>

    @php
        // Helper inisial dan warna avatar
        $colors = (function ($name) {
            $palette = [
                [
                    'bg' => 'bg-indigo-100 dark:bg-indigo-950/60',
                    'text' => 'text-indigo-700 dark:text-indigo-300',
                    'border' => 'border-indigo-200 dark:border-indigo-800',
                ],
                [
                    'bg' => 'bg-blue-100 dark:bg-blue-950/60',
                    'text' => 'text-blue-700 dark:text-blue-300',
                    'border' => 'border-blue-200 dark:border-blue-800',
                ],
                [
                    'bg' => 'bg-teal-100 dark:bg-teal-950/60',
                    'text' => 'text-teal-700 dark:text-teal-300',
                    'border' => 'border-teal-200 dark:border-teal-800',
                ],
                [
                    'bg' => 'bg-violet-100 dark:bg-violet-950/60',
                    'text' => 'text-violet-700 dark:text-violet-300',
                    'border' => 'border-violet-200 dark:border-violet-800',
                ],
                [
                    'bg' => 'bg-emerald-100 dark:bg-emerald-950/60',
                    'text' => 'text-emerald-700 dark:text-emerald-300',
                    'border' => 'border-emerald-200 dark:border-emerald-800',
                ],
                [
                    'bg' => 'bg-amber-100 dark:bg-amber-950/60',
                    'text' => 'text-amber-700 dark:text-amber-300',
                    'border' => 'border-amber-200 dark:border-amber-800',
                ],
                [
                    'bg' => 'bg-rose-100 dark:bg-rose-950/60',
                    'text' => 'text-rose-700 dark:text-rose-300',
                    'border' => 'border-rose-200 dark:border-rose-800',
                ],
            ];
            $hash = crc32($name);
            return $palette[abs($hash) % count($palette)];
        })($user->name);

        $initials = collect(explode(' ', $user->name))
            ->map(fn($part) => mb_substr($part, 0, 1))
            ->take(2)
            ->join('');

        // Tentukan active tab awal berdasarkan request URL
        $initialTab = request()->has('attendance_page') ? 'attendance' : 'leaves';
    @endphp

    <div class="space-y-6" x-data="{ activeTab: '{{ $initialTab }}' }">
        <x-toast-notification />

        {{-- ═══ 1. EXECUTIVE PROFILE HERO CARD ═══ --}}
        <div
            class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/90 dark:border-slate-700/80 shadow-sm overflow-hidden transition-all">
            {{-- Top Accent Strip --}}
            <div class="h-2.5 bg-gradient-to-r from-primary-600 via-indigo-600 to-sky-500"></div>

            <div class="p-6 md:p-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    {{-- User Identity Details --}}
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
                        <div class="relative shrink-0">
                            <div
                                class="w-20 h-20 md:w-22 md:h-22 rounded-2xl flex items-center justify-center font-display font-bold text-2xl md:text-3xl {{ $colors['bg'] }} {{ $colors['text'] }} border-2 {{ $colors['border'] }} shadow-sm">
                                {{ strtoupper($initials) }}
                            </div>
                            @if ($status['state'] === 'ongoing')
                                <span class="absolute -bottom-1 -right-1 flex h-5 w-5">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                    <span
                                        class="relative inline-flex rounded-full h-5 w-5 bg-rose-500 border-2 border-white dark:border-slate-800"></span>
                                </span>
                            @endif
                        </div>

                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2.5">
                                <h1
                                    class="text-xl md:text-2xl font-bold font-display text-slate-900 dark:text-slate-100">
                                    {{ ucwords($user->name) }}
                                </h1>
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 uppercase tracking-wide border border-slate-200 dark:border-slate-600">
                                    {{ strtoupper(str_replace('-', ' ', $user->role)) }}
                                </span>
                            </div>

                            <div
                                class="flex flex-wrap items-center gap-3 text-xs md:text-sm text-slate-500 dark:text-slate-400">
                                <span
                                    class="inline-flex items-center gap-1.5 font-mono font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700/60 px-2 py-0.5 rounded-md">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                    </svg>
                                    NIK: {{ $user->nik ?? '-' }}
                                </span>

                                @if ($user->email)
                                    <span class="inline-flex items-center gap-1 text-slate-500 dark:text-slate-400">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                        </svg>
                                        {{ $user->email }}
                                    </span>
                                @endif
                            </div>

                            {{-- Location & Department Badges --}}
                            <div class="flex flex-wrap items-center gap-2 pt-1 text-xs">
                                <div
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-50 dark:bg-slate-700/50 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-600/70">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span>Kantor: <strong
                                            class="text-slate-900 dark:text-slate-100">{{ strtoupper($user->office->nama_kantor ?? '-') }}</strong></span>
                                </div>

                                <div
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-50 dark:bg-slate-700/50 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-600/70">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <span>Jabatan: <strong
                                            class="text-slate-900 dark:text-slate-100">{{ $user->position->nama_jabatan ?? '-' }}</strong></span>
                                </div>

                                @if ($user->division)
                                    <div
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-50 dark:bg-slate-700/50 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-600/70">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <span>Divisi: <strong
                                                class="text-slate-900 dark:text-slate-100">{{ strtoupper($user->division->nama_divisi ?? '-') }}</strong></span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Live Status Realtime Hero Banner --}}
                    <div class="lg:max-w-xs w-full">
                        @switch($status['state'])
                            @case('ongoing')
                                <div
                                    class="p-4 rounded-2xl bg-rose-50/90 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 shadow-xs space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="inline-flex items-center gap-2 text-rose-700 dark:text-rose-400 font-bold text-xs uppercase tracking-wider">
                                            <span class="relative flex h-2.5 w-2.5">
                                                <span
                                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
                                            </span>
                                            Sedang Cuti / Izin
                                        </span>
                                        <span
                                            class="text-[11px] font-semibold text-rose-600 dark:text-rose-400 bg-rose-100 dark:bg-rose-900/60 px-2 py-0.5 rounded-full">
                                            Hari Ini
                                        </span>
                                    </div>
                                    <div class="text-sm font-bold text-slate-800 dark:text-slate-100">
                                        {{ $status['leave']->leaveType->name ?? 'Izin' }}
                                    </div>
                                    <div class="text-xs text-rose-800 dark:text-rose-300">
                                        Hingga:
                                        <strong>{{ \Carbon\Carbon::parse($status['leave']->end_date)->translatedFormat('d F Y') }}</strong>
                                        <span class="text-slate-400 dark:text-slate-500">&middot;</span>
                                        <span>({{ $status['leave']->total_hari }} hari)</span>
                                    </div>
                                </div>
                            @break

                            @case('upcoming')
                                <div
                                    class="p-4 rounded-2xl bg-amber-50/90 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 shadow-xs space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="inline-flex items-center gap-1.5 text-amber-800 dark:text-amber-400 font-bold text-xs uppercase tracking-wider">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            Izin Mendatang
                                        </span>
                                        <span
                                            class="text-[11px] font-semibold text-amber-700 dark:text-amber-400 bg-amber-100 dark:bg-amber-900/60 px-2 py-0.5 rounded-full">
                                            Terjadwal
                                        </span>
                                    </div>
                                    <div class="text-sm font-bold text-slate-800 dark:text-slate-100">
                                        {{ $status['leave']->leaveType->name ?? 'Izin' }}
                                    </div>
                                    <div class="text-xs text-amber-800 dark:text-amber-300">
                                        {{ \Carbon\Carbon::parse($status['leave']->start_date)->translatedFormat('d M') }}
                                        &ndash;
                                        {{ \Carbon\Carbon::parse($status['leave']->end_date)->translatedFormat('d M Y') }}
                                        <span class="text-slate-400 dark:text-slate-500">&middot;</span>
                                        <span>({{ $status['leave']->total_hari }} hari)</span>
                                    </div>
                                </div>
                            @break

                            @case('completed')
                                <div
                                    class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600/70 shadow-xs space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="text-slate-600 dark:text-slate-300 font-bold text-xs uppercase tracking-wider">
                                            Cuti Terakhir Selesai
                                        </span>
                                        <span
                                            class="text-[11px] font-medium text-slate-500 dark:text-slate-400 bg-slate-200/80 dark:bg-slate-600 px-2 py-0.5 rounded-full">
                                            Riwayat
                                        </span>
                                    </div>
                                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                                        {{ $status['leave']->leaveType->name ?? 'Izin' }}
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">
                                        Berakhir:
                                        {{ \Carbon\Carbon::parse($status['leave']->end_date)->translatedFormat('d MMMM Y') }}
                                    </div>
                                </div>
                            @break

                            @default
                                <div
                                    class="p-4 rounded-2xl bg-emerald-50/80 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/50 shadow-xs space-y-1.5">
                                    <div
                                        class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400 font-bold text-xs uppercase tracking-wider">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        Status Kehadiran
                                    </div>
                                    <div class="text-sm font-bold text-emerald-900 dark:text-emerald-300">
                                        Aktif Bekerja
                                    </div>
                                    <div class="text-xs text-emerald-700/80 dark:text-emerald-400/80">
                                        Tidak ada pengajuan cuti yang sedang aktif.
                                    </div>
                                </div>
                        @endswitch
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ 2. KUOTA CUTI TAHUN BERJALAN ═══ --}}
        <div>
            <div class="flex items-center justify-between mb-3.5">
                <div class="flex items-center gap-2">
                    <h3 class="font-display font-bold text-base text-slate-800 dark:text-slate-100">
                        Kuota Cuti Karyawan
                    </h3>
                    <span
                        class="px-2 py-0.5 rounded-md text-xs font-semibold bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300 border border-primary-200/70 dark:border-primary-500/20">
                        Tahun {{ now()->year }}
                    </span>
                </div>
                <span class="text-xs text-slate-400 dark:text-slate-500">
                    Real-time Balance
                </span>
            </div>

            @if ($balances->isEmpty())
                <div
                    class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/90 dark:border-slate-700/80 p-8 text-center text-slate-400 dark:text-slate-500 text-sm shadow-xs">
                    <div
                        class="w-12 h-12 mx-auto rounded-xl bg-slate-100 dark:bg-slate-700/60 flex items-center justify-center text-slate-400 mb-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                    </div>
                    Belum ada data alokasi kuota cuti untuk tahun ini.
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach ($balances as $balance)
                        @php
                            $percentUsed =
                                $balance->total_quota > 0
                                    ? min(100, round(($balance->used / $balance->total_quota) * 100))
                                    : 0;
                            $percentRemaining = 100 - $percentUsed;

                            // Warna progress bar berdasarkan sisa kuota
                            $barColor =
                                $balance->remaining <= 2 && $balance->total_quota > 0
                                    ? 'bg-rose-500'
                                    : ($balance->remaining <= 5 && $balance->total_quota > 0
                                        ? 'bg-amber-500'
                                        : 'bg-primary-600');
                        @endphp
                        <div
                            class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/90 dark:border-slate-700/80 p-5 shadow-xs hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between gap-2">
                                    <span
                                        class="text-xs text-wrap font-bold text-slate-800 dark:text-slate-200 truncate"
                                        title="{{ $balance->leaveType->name ?? '-' }}">
                                        {{ $balance->leaveType->name ?? 'Jenis Cuti' }}
                                    </span>
                                    <span
                                        class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded">
                                        {{ $percentUsed }}% terpakai
                                    </span>
                                </div>

                                <div class="mt-3 flex items-baseline justify-between">
                                    <div class="flex items-baseline gap-1.5">
                                        <span
                                            class="text-3xl font-display font-extrabold text-primary-600 dark:text-primary-400">
                                            {{ $balance->remaining }}
                                        </span>
                                        <span class="text-xs font-semibold text-slate-400">
                                            / {{ $balance->total_quota }} hari sisa
                                        </span>
                                    </div>
                                </div>

                                {{-- Progress Bar Gauge --}}
                                <div
                                    class="w-full h-2 bg-slate-100 dark:bg-slate-700/80 rounded-full mt-3 overflow-hidden">
                                    <div class="h-full {{ $barColor }} rounded-full transition-all duration-500"
                                        style="width: {{ $percentUsed }}%"></div>
                                </div>
                            </div>

                            <div
                                class="mt-3.5 pt-2.5 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                                <span>Terpakai: <strong
                                        class="text-slate-700 dark:text-slate-200">{{ $balance->used }}</strong>
                                    hari</span>
                                <span>Total: <strong
                                        class="text-slate-700 dark:text-slate-200">{{ $balance->total_quota }}</strong>
                                    hari</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ═══ 3. TABBED HISTORY SECTION (APPROVED LEAVES & ATTENDANCE) ═══ --}}
        <div
            class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/90 dark:border-slate-700/80 shadow-sm overflow-hidden transition-all">
            {{-- Tabs Navigation Bar --}}
            <div
                class="px-6 pt-5 border-b border-slate-100 dark:border-slate-700/80 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <button type="button" @click="activeTab = 'leaves'"
                        :class="activeTab === 'leaves' ?
                            'border-primary-600 text-primary-600 dark:text-primary-400 font-bold bg-primary-50/50 dark:bg-primary-950/20' :
                            'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:border-slate-300 font-medium'"
                        class="inline-flex items-center gap-2.5 px-4 py-3 rounded-t-xl border-b-2 text-sm transition-all duration-150">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Riwayat Izin & Cuti</span>
                        <span
                            class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                            {{ $leaves->total() }}
                        </span>
                    </button>

                    <button type="button" @click="activeTab = 'attendance'"
                        :class="activeTab === 'attendance' ?
                            'border-primary-600 text-primary-600 dark:text-primary-400 font-bold bg-primary-50/50 dark:bg-primary-950/20' :
                            'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:border-slate-300 font-medium'"
                        class="inline-flex items-center gap-2.5 px-4 py-3 rounded-t-xl border-b-2 text-sm transition-all duration-150">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Riwayat Kehadiran</span>
                        <span
                            class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                            {{ $attendances->total() }}
                        </span>
                    </button>
                </div>

                <div class="pb-3 text-xs text-slate-400 dark:text-slate-500 hidden sm:block">
                    Hanya menampilkan data dengan status <strong
                        class="text-emerald-600 dark:text-emerald-400">Disetujui (Approved)</strong>
                </div>
            </div>

            {{-- ── TAB 1: RIWAYAT IZIN / CUTI APPROVED ── --}}
            <div x-show="activeTab === 'leaves'" class="transition-opacity duration-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/80 text-sm">
                        <thead
                            class="bg-slate-50/75 dark:bg-slate-900/30 text-left text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            <tr>
                                <th scope="col" class="px-6 py-3.5">Jenis Cuti</th>
                                <th scope="col" class="px-6 py-3.5">Periode Tanggal</th>
                                <th scope="col" class="px-6 py-3.5">Durasi</th>
                                <th scope="col" class="px-6 py-3.5">Alasan / Keperluan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @forelse ($leaves as $leave)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-slate-800 dark:text-slate-100">
                                            {{ $leave->leaveType->name ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-slate-700 dark:text-slate-300">
                                        <div class="flex items-center gap-1.5 font-medium">
                                            <span>{{ \Carbon\Carbon::parse($leave->start_date)->translatedFormat('d M Y') }}</span>
                                            <span class="text-slate-400">&ndash;</span>
                                            <span>{{ \Carbon\Carbon::parse($leave->end_date)->translatedFormat('d M Y') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300 border border-primary-200/60 dark:border-primary-500/20">
                                            {{ $leave->total_hari }} Hari
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 dark:text-slate-300 max-w-sm">
                                        <div class="text-xs line-clamp-2 leading-relaxed"
                                            title="{{ $leave->alasan }}">
                                            {{ $leave->alasan ?: '-' }}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4"
                                        class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                        <div class="max-w-xs mx-auto space-y-2">
                                            <div
                                                class="w-12 h-12 mx-auto rounded-xl bg-slate-100 dark:bg-slate-700/60 flex items-center justify-center text-slate-400">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <div class="text-sm font-semibold text-slate-700 dark:text-slate-300">Belum
                                                ada riwayat izin/cuti</div>
                                            <p class="text-xs text-slate-400">Belum ada pengajuan izin atau cuti yang
                                                disetujui untuk karyawan ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($leaves->hasPages())
                    <div
                        class="px-6 py-4 border-t border-slate-100 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-900/20">
                        {{ $leaves->appends(['attendance_page' => request('attendance_page')])->links() }}
                    </div>
                @endif
            </div>

            {{-- ── TAB 2: RIWAYAT KEHADIRAN APPROVED ── --}}
            <div x-show="activeTab === 'attendance'" class="transition-opacity duration-200" style="display: none;">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/80 text-sm">
                        <thead
                            class="bg-slate-50/75 dark:bg-slate-900/30 text-left text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            <tr>
                                <th scope="col" class="px-6 py-3.5">Jenis Pengajuan</th>
                                <th scope="col" class="px-6 py-3.5">Tanggal</th>
                                <th scope="col" class="px-6 py-3.5">Jam / Waktu</th>
                                <th scope="col" class="px-6 py-3.5">Alasan / Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @forelse ($attendances as $attendance)
                                @php
                                    // Badge color semantik untuk tipe kehadiran
                                    $typeBadge = match ($attendance->type) {
                                        'late_arrival'
                                            => 'bg-amber-50 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300 border-amber-200 dark:border-amber-500/30',
                                        'early_departure'
                                            => 'bg-orange-50 text-orange-800 dark:bg-orange-500/15 dark:text-orange-300 border-orange-200 dark:border-orange-500/30',
                                        'leave_during_work'
                                            => 'bg-rose-50 text-rose-800 dark:bg-rose-500/15 dark:text-rose-300 border-rose-200 dark:border-rose-500/30',
                                        'update_attendance'
                                            => 'bg-sky-50 text-sky-800 dark:bg-sky-500/15 dark:text-sky-300 border-sky-200 dark:border-sky-500/30',
                                        default
                                            => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-600',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="space-y-1">
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border {{ $typeBadge }}">
                                                {{ $attendance->type_label }}
                                            </span>
                                            @if ($attendance->type === 'update_attendance' && $attendance->update_type)
                                                <div class="text-[11px] text-slate-400 pl-1">
                                                    ({{ $attendance->update_type_label }})
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-slate-700 dark:text-slate-300 font-medium">
                                        {{ $attendance->date->translatedFormat('d F Y') }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap font-mono text-xs text-slate-600 dark:text-slate-300">
                                        <div
                                            class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-slate-100 dark:bg-slate-700">
                                            <span>{{ \Illuminate\Support\Str::of($attendance->start_time)->substr(0, 5) }}</span>
                                            @if ($attendance->end_time)
                                                <span class="text-slate-400">&ndash;</span>
                                                <span>{{ \Illuminate\Support\Str::of($attendance->end_time)->substr(0, 5) }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 dark:text-slate-300 max-w-sm">
                                        <div class="text-xs line-clamp-2 leading-relaxed"
                                            title="{{ $attendance->reason }}">
                                            {{ $attendance->reason ?: '-' }}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4"
                                        class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                        <div class="max-w-xs mx-auto space-y-2">
                                            <div
                                                class="w-12 h-12 mx-auto rounded-xl bg-slate-100 dark:bg-slate-700/60 flex items-center justify-center text-slate-400">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <div class="text-sm font-semibold text-slate-700 dark:text-slate-300">Belum
                                                ada riwayat kehadiran</div>
                                            <p class="text-xs text-slate-400">Belum ada pengajuan kehadiran atau
                                                penyesuaian absensi yang disetujui.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($attendances->hasPages())
                    <div
                        class="px-6 py-4 border-t border-slate-100 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-900/20">
                        {{ $attendances->appends(['leaves_page' => request('leaves_page')])->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
