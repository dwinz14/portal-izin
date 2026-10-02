<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2
                        class="border-l-[5px] border-primary-600 pl-4 font-display font-bold text-xl md:text-2xl text-slate-800 dark:text-slate-100 tracking-tight leading-tight">
                        {{ __('Monitoring Izin & Cuti') }}
                    </h2>
                    <span
                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live Monitoring
                    </span>
                </div>
                <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1 pl-5">
                    Pantau ketersediaan, ketidakhadiran, dan status cuti seluruh karyawan secara real-time.
                </p>
            </div>

            <div
                class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400 bg-white/80 dark:bg-slate-800/80 px-3.5 py-2 rounded-xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs backdrop-blur-sm">
                <svg class="w-4 h-4 text-primary-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Hari ini: <strong class="text-slate-800 dark:text-slate-200 font-medium">{{
                        now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</strong></span>
            </div>
        </div>
    </x-slot>

    @php
    // Metrik Ringkasan dari data halaman saat ini
    $collection = $users->getCollection();
    $ongoingCount = $collection->where('monitoring_state', 'ongoing')->count();
    $upcomingCount = $collection->where('monitoring_state', 'upcoming')->count();
    $hasActiveFilter = !empty($search) || !empty($officeId) || !empty($positionId) || (!empty($perPage) && $perPage !==
    '10');

    // Helper warna avatar berdasarkan inisial nama
    $getAvatarColors = function ($name) {
    $colors = [
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
    return $colors[abs($hash) % count($colors)];
    };
    @endphp

    <div class="space-y-6" x-data="{
        viewMode: localStorage.getItem('hrd_monitoring_view_mode') || 'table',
        setView(mode) {
            this.viewMode = mode;
            localStorage.setItem('hrd_monitoring_view_mode', mode);
        }
    }">
        <x-toast-notification />

        {{-- ═══ 1. KPI SUMMARY CARDS ═══ --}}
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-3.5 md:gap-4">
            {{-- Sedang Izin / Cuti --}}
            <div
                class="relative overflow-hidden bg-white dark:bg-slate-800 rounded-2xl p-4 md:p-5 border border-rose-100 dark:border-rose-900/30 shadow-xs hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-rose-600 dark:text-rose-400 uppercase tracking-wider">
                            Sedang Izin / Cuti
                        </p>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span
                                class="text-2xl md:text-3xl font-display font-bold text-slate-800 dark:text-slate-100">
                                {{ $ongoingCount }}
                            </span>
                            <span class="text-xs text-slate-400">karyawan</span>
                        </div>
                    </div>
                    <div
                        class="w-11 h-11 rounded-xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 border border-rose-100 dark:border-rose-500/20">
                        <span class="relative flex h-3 w-3">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                        </span>
                    </div>
                </div>
                <div class="mt-2.5 flex items-center text-[11px] text-rose-600/80 dark:text-rose-400/80">
                    <span>Status tidak hadir hari ini</span>
                </div>
            </div>

            {{-- Akan Izin / Cuti --}}
            <div
                class="relative overflow-hidden bg-white dark:bg-slate-800 rounded-2xl p-4 md:p-5 border border-amber-100 dark:border-amber-900/30 shadow-xs hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider">
                            Izin Akan Datang
                        </p>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span
                                class="text-2xl md:text-3xl font-display font-bold text-slate-800 dark:text-slate-100">
                                {{ $upcomingCount }}
                            </span>
                            <span class="text-xs text-slate-400">jadwal</span>
                        </div>
                    </div>
                    <div
                        class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 border border-amber-100 dark:border-amber-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2.5 flex items-center text-[11px] text-amber-600/80 dark:text-amber-400/80">
                    <span>Pengajuan cuti terjadwal</span>
                </div>
            </div>

            {{-- Total Karyawan --}}
            <div
                class="relative overflow-hidden bg-white dark:bg-slate-800 rounded-2xl p-4 md:p-5 border border-slate-200 dark:border-slate-700 shadow-xs hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Total Karyawan
                        </p>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span
                                class="text-2xl md:text-3xl font-display font-bold text-slate-800 dark:text-slate-100">
                                {{ $users->total() }}
                            </span>
                            <span class="text-xs text-slate-400">terdata</span>
                        </div>
                    </div>
                    <div
                        class="w-11 h-11 rounded-xl bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400 flex items-center justify-center shrink-0 border border-primary-100 dark:border-primary-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2.5 flex items-center text-[11px] text-slate-500 dark:text-slate-400">
                    <span>Semua kantor & divisi</span>
                </div>
            </div>
        </div>

        {{-- ═══ 2. SMART FILTER & TOOLBAR ═══ --}}
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/90 dark:border-slate-700/80 shadow-sm p-4 md:p-5 transition-all">
            <form id="monitoringFilterForm" method="GET" action="{{ route('hrd.monitoring.index') }}">
                {{-- Preserve Per Page Selection in Filter Form --}}
                <input type="hidden" name="per_page" value="{{ $perPage }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5">
                    {{-- Input Pencarian Nama/NIK --}}
                    <div class="sm:col-span-2 lg:col-span-5">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Cari Karyawan
                        </label>
                        <div class="relative" x-data="{ query: '{{ $search ?? '' }}' }">
                            <span
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                            <input type="text" name="search" x-model="query"
                                placeholder="Ketik nama atau NIK karyawan..."
                                class="w-full pl-10 pr-9 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50/50 dark:bg-slate-700/60 text-slate-800 dark:text-slate-100 text-xs md:text-sm placeholder-slate-400 focus:bg-white dark:focus:bg-slate-700 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all">

                            <button type="button" x-show="query.length > 0"
                                @click="query = ''; $nextTick(() => $el.closest('form').submit())"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
                                title="Hapus pencarian">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Filter Kantor --}}
                    <div class="lg:col-span-3">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Lokasi Kantor
                        </label>
                        <div class="relative">
                            <span
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </span>
                            <select name="office_id" onchange="this.form.submit()"
                                class="w-full pl-10 pr-8 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50/50 dark:bg-slate-700/60 text-slate-800 dark:text-slate-100 text-xs md:text-sm focus:bg-white dark:focus:bg-slate-700 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all cursor-pointer">
                                <option value="">Semua Kantor</option>
                                @foreach ($offices as $office)
                                <option value="{{ $office->id }}" @selected($officeId==$office->id)>
                                    {{ strtoupper($office->nama_kantor) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Filter Jabatan --}}
                    <div class="lg:col-span-3">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Posisi / Jabatan
                        </label>
                        <div class="relative">
                            <span
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <select name="position_id" onchange="this.form.submit()"
                                class="w-full pl-10 pr-8 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50/50 dark:bg-slate-700/60 text-slate-800 dark:text-slate-100 text-xs md:text-sm focus:bg-white dark:focus:bg-slate-700 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all cursor-pointer">
                                <option value="">Semua Jabatan</option>
                                @foreach ($positions as $position)
                                <option value="{{ $position->id }}" @selected($positionId==$position->id)>
                                    {{ strtoupper($position->nama_jabatan) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Action Button & Reset --}}
                    <div class="sm:col-span-2 lg:col-span-1 flex lg:flex-col items-end justify-end gap-2">
                        <button type="submit"
                            class="w-full h-[42px] inline-flex items-center justify-center gap-1.5 px-4 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold shadow-xs hover:shadow transition-all duration-150 active:scale-98">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            <span class="lg:hidden">Filter</span>
                        </button>
                    </div>
                </div>

                {{-- Active Filter Badges (Chips) & Layout Toolbar --}}
                <div
                    class="mt-4 pt-3.5 border-t border-slate-100 dark:border-slate-700/70 flex flex-wrap items-center justify-between gap-3">
                    {{-- Left: Filter Chips --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Status Filter:</span>
                        @if ($hasActiveFilter)
                        @if (!empty($search))
                        <span
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-primary-50 dark:bg-primary-500/10 text-primary-700 dark:text-primary-300 border border-primary-200/60 dark:border-primary-500/20">
                            <span>Pencarian: "<strong>{{ $search }}</strong>"</span>
                            <a href="{{ route('hrd.monitoring.index', array_merge(request()->except('search'), ['search' => null])) }}"
                                class="ml-1 text-primary-400 hover:text-primary-700 dark:hover:text-primary-200"
                                title="Hapus filter pencarian">
                                &times;
                            </a>
                        </span>
                        @endif

                        @if (!empty($officeId))
                        @php $selectedOffice = $offices->firstWhere('id', $officeId); @endphp
                        <span
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-500/20">
                            <span>Kantor:
                                <strong>{{ $selectedOffice ? strtoupper($selectedOffice->nama_kantor) : $officeId
                                    }}</strong></span>
                            <a href="{{ route('hrd.monitoring.index', array_merge(request()->except('office_id'), ['office_id' => null])) }}"
                                class="ml-1 text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-200"
                                title="Hapus filter kantor">
                                &times;
                            </a>
                        </span>
                        @endif

                        @if (!empty($positionId))
                        @php $selectedPos = $positions->firstWhere('id', $positionId); @endphp
                        <span
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-teal-50 dark:bg-teal-500/10 text-teal-700 dark:text-teal-300 border border-teal-200/60 dark:border-teal-500/20">
                            <span>Jabatan:
                                <strong>{{ $selectedPos ? strtoupper($selectedPos->nama_jabatan) : $positionId
                                    }}</strong></span>
                            <a href="{{ route('hrd.monitoring.index', array_merge(request()->except('position_id'), ['position_id' => null])) }}"
                                class="ml-1 text-teal-400 hover:text-teal-700 dark:hover:text-teal-200"
                                title="Hapus filter jabatan">
                                &times;
                            </a>
                        </span>
                        @endif

                        @if (!empty($perPage) && $perPage !== '10')
                        <span
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                            <span>Tampilkan: <strong>{{ $perPage === 'all' ? 'Semua Data' : $perPage . ' Baris'
                                    }}</strong></span>
                            <a href="{{ route('hrd.monitoring.index', array_merge(request()->except('per_page'), ['per_page' => '10', 'page' => 1])) }}"
                                class="ml-1 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200"
                                title="Kembalikan ke 10 data per halaman">
                                &times;
                            </a>
                        </span>
                        @endif

                        <a href="{{ route('hrd.monitoring.index') }}"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Reset Semua
                        </a>
                        @else
                        <span class="text-xs text-slate-400 dark:text-slate-500 italic">Menampilkan data default (10 per
                            halaman).</span>
                        @endif
                    </div>

                    {{-- Right Controls: Per Page Selector & View Mode Toggle --}}
                    <div class="flex flex-wrap items-center gap-2.5">
                        {{-- Per Page Selector --}}
                        <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-700/60 p-1 rounded-xl">
                            <span
                                class="text-xs text-slate-500 dark:text-slate-400 font-medium pl-2 pr-1 hidden sm:inline">
                                Tampilkan:
                            </span>
                            @foreach (['10' => '10', '20' => '20', '30' => '30', 'all' => 'Semua'] as $val => $label)
                            <a href="{{ route('hrd.monitoring.index', array_merge(request()->query(), ['per_page' => $val, 'page' => 1])) }}"
                                class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-all {{ ($perPage == $val || ($val == '10' && empty($perPage))) ? 'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 shadow-xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}"
                                title="Tampilkan {{ $label }} data per halaman">
                                {{ $label }}
                            </a>
                            @endforeach
                        </div>

                        {{-- View Mode Toggle (Table / Card Grid) --}}
                        <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-700/60 p-1 rounded-xl">
                            <button type="button" @click="setView('table')"
                                :class="viewMode === 'table' ?
                                    'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 shadow-xs font-semibold' :
                                    'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs transition-all"
                                title="Tampilan Tabel">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                                <span class="hidden sm:inline">Tabel</span>
                            </button>
                            <button type="button" @click="setView('cards')"
                                :class="viewMode === 'cards' ?
                                    'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 shadow-xs font-semibold' :
                                    'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs transition-all"
                                title="Tampilan Kartu Grid">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                                <span class="hidden sm:inline">Kartu</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- ═══ 3. DATA CONTAINER ═══ --}}

        {{-- [A] TABULAR VIEW (Default Desktop & Ergonomic) --}}
        <div x-show="viewMode === 'table'"
            class="bg-white dark:bg-slate-800 shadow-sm rounded-2xl border border-slate-200/90 dark:border-slate-700/80 overflow-hidden transition-all">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/80 text-sm">
                    <thead>
                        <tr
                            class="bg-slate-50/80 dark:bg-slate-900/40 text-left text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            <th scope="col" class="px-6 py-4">Karyawan</th>
                            <th scope="col" class="px-6 py-4">Penempatan & Jabatan</th>
                            <th scope="col" class="px-6 py-4">Status Izin / Cuti Terkini</th>
                            <th scope="col" class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse ($users as $user)
                        @php
                        $colors = $getAvatarColors($user->name);
                        $initials = collect(explode(' ', $user->name))
                        ->map(fn($part) => mb_substr($part, 0, 1))
                        ->take(2)
                        ->join('');
                        @endphp
                        <tr class="hover:bg-slate-50/90 dark:hover:bg-slate-700/30 transition-colors group">
                            {{-- Karyawan Profil --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3.5">
                                    <div class="relative shrink-0">
                                        <div
                                            class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs {{ $colors['bg'] }} {{ $colors['text'] }} border {{ $colors['border'] }} shadow-xs">
                                            {{ strtoupper($initials) }}
                                        </div>
                                        @if ($user->monitoring_state === 'ongoing')
                                        <span
                                            class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full bg-rose-500 border-2 border-white dark:border-slate-800"
                                            title="Sedang Izin/Cuti"></span>
                                        @elseif ($user->monitoring_state === 'upcoming')
                                        <span
                                            class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full bg-amber-500 border-2 border-white dark:border-slate-800"
                                            title="Izin Mendatang"></span>
                                        @endif
                                    </div>
                                    <div>
                                        <div
                                            class="font-semibold text-slate-900 dark:text-slate-100 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                            {{ ucwords($user->name) }}
                                        </div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span
                                                class="font-mono text-xs text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700/60 px-1.5 py-0.5 rounded">
                                                {{ $user->nik ?? '-' }}
                                            </span>
                                            @if ($user->division)
                                            <span class="text-xs text-slate-400 dark:text-slate-500">&middot;</span>
                                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                                {{ $user->division->nama_divisi }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Penempatan & Jabatan --}}
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <div
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-800 dark:text-slate-200">
                                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        <span>{{ $user->office->nama_kantor ?? '-' }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <span>{{ $user->position->nama_jabatan ?? '-' }}</span>
                                    </div>
                                </div>
                            </td>

                            {{-- Status Izin / Cuti --}}
                            <td class="px-6 py-4">
                                @switch($user->monitoring_state)
                                @case('ongoing')
                                <div class="space-y-1">
                                    <div
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300 border border-rose-200/80 dark:border-rose-500/30">
                                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                                        Sedang Cuti / Izin
                                    </div>
                                    <div class="text-xs text-slate-600 dark:text-slate-300 font-medium pl-1">
                                        <span class="font-semibold text-rose-700 dark:text-rose-400">{{
                                            $user->monitoring_leave->leaveType->name ?? 'Izin' }}</span>
                                        <span class="text-slate-400 dark:text-slate-500">&middot;</span>
                                        <span>s/d
                                            {{
                                            \Carbon\Carbon::parse($user->monitoring_leave->end_date)->translatedFormat('d
                                            M Y') }}</span>
                                    </div>
                                </div>
                                @break

                                @case('upcoming')
                                <div class="space-y-1">
                                    <div
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300 border border-amber-200/80 dark:border-amber-500/30">
                                        <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Akan Datang
                                    </div>
                                    <div class="text-xs text-slate-600 dark:text-slate-300 pl-1">
                                        <span class="font-medium text-amber-800 dark:text-amber-300">{{
                                            $user->monitoring_leave->leaveType->name ?? 'Izin' }}</span>:
                                        <span class="font-mono text-slate-700 dark:text-slate-300">{{
                                            \Carbon\Carbon::parse($user->monitoring_leave->start_date)->translatedFormat('d
                                            M') }}
                                            &ndash;
                                            {{
                                            \Carbon\Carbon::parse($user->monitoring_leave->end_date)->translatedFormat('d
                                            M Y') }}</span>
                                    </div>
                                </div>
                                @break

                                @case('completed')
                                <div class="space-y-1">
                                    <div
                                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300">
                                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        Cuti Terakhir Selesai
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 pl-1">
                                        {{ $user->monitoring_leave->leaveType->name ?? 'Izin' }} &middot; s/d
                                        {{ \Carbon\Carbon::parse($user->monitoring_leave->end_date)->translatedFormat('d
                                        M Y') }}
                                    </div>
                                </div>
                                @break

                                @default
                                <div
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Aktif Bekerja
                                </div>
                                @endswitch
                            </td>

                            {{-- Aksi --}}
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('hrd.monitoring.show', $user) }}"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-primary-50 dark:bg-primary-500/10 text-primary-700 dark:text-primary-300 text-xs font-semibold hover:bg-primary-600 hover:text-white dark:hover:bg-primary-600 dark:hover:text-white border border-primary-200/80 dark:border-primary-500/20 shadow-xs transition-all duration-150 group-hover:border-primary-400">
                                    <span>Detail</span>
                                    <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-14 text-center">
                                <div class="max-w-xs mx-auto text-center space-y-3">
                                    <div
                                        class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-700/60 flex items-center justify-center text-slate-400">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-slate-800 dark:text-slate-200 text-sm">Tidak
                                            ada karyawan ditemukan</h4>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                            Coba sesuaikan kata kunci pencarian atau bersihkan filter yang aktif.
                                        </p>
                                    </div>
                                    @if ($hasActiveFilter)
                                    <a href="{{ route('hrd.monitoring.index') }}"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                                        Bersihkan Filter
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- [B] CARDS GRID VIEW (Responsive / Visual Mode) --}}
        <div x-show="viewMode === 'cards'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
            style="display: none;">
            @forelse ($users as $user)
            @php
            $colors = $getAvatarColors($user->name);
            $initials = collect(explode(' ', $user->name))
            ->map(fn($part) => mb_substr($part, 0, 1))
            ->take(2)
            ->join('');
            @endphp
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/90 dark:border-slate-700/80 p-5 shadow-xs hover:shadow-md transition-all duration-200 flex flex-col justify-between gap-4">
                {{-- Header Kartu: Avatar, Nama, NIK --}}
                <div>
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-11 h-11 rounded-xl flex items-center justify-center font-bold text-sm {{ $colors['bg'] }} {{ $colors['text'] }} border {{ $colors['border'] }} shadow-xs shrink-0">
                                {{ strtoupper($initials) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm leading-snug">
                                    {{ ucwords($user->name) }}
                                </h3>
                                <div
                                    class="flex items-center gap-1.5 mt-0.5 font-mono text-xs text-slate-500 dark:text-slate-400">
                                    <span>NIK: {{ $user->nik ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- State Indicator Badge --}}
                        @switch($user->monitoring_state)
                        @case('ongoing')
                        <span
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 border border-rose-200 dark:border-rose-500/30 shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                            Sedang Cuti
                        </span>
                        @break

                        @case('upcoming')
                        <span
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30 shrink-0">
                            Akan Datang
                        </span>
                        @break

                        @case('completed')
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300 shrink-0">
                            Selesai
                        </span>
                        @break

                        @default
                        <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-500/20 shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Aktif
                        </span>
                        @endswitch
                    </div>

                    {{-- Metadata: Kantor, Jabatan, Divisi --}}
                    <div class="mt-4 pt-3.5 border-t border-slate-100 dark:border-slate-700/60 space-y-2 text-xs">
                        <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                            <span class="flex items-center gap-1.5 text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                Kantor
                            </span>
                            <span class="font-medium text-slate-800 dark:text-slate-200">{{ $user->office->nama_kantor
                                ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                            <span class="flex items-center gap-1.5 text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Jabatan
                            </span>
                            <span class="font-medium text-slate-800 dark:text-slate-200">{{
                                $user->position->nama_jabatan ?? '-' }}</span>
                        </div>
                        @if ($user->division)
                        <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                            <span class="text-slate-400">Divisi</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200">{{ $user->division->nama_divisi
                                }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Box Info Detail Cuti --}}
                    @if ($user->monitoring_leave)
                    <div
                        class="mt-3 p-2.5 rounded-xl {{ $user->monitoring_state === 'ongoing' ? 'bg-rose-50/70 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/40' : 'bg-slate-50 dark:bg-slate-700/50 border border-slate-200/60 dark:border-slate-600/50' }} text-xs">
                        <div
                            class="font-semibold {{ $user->monitoring_state === 'ongoing' ? 'text-rose-700 dark:text-rose-300' : 'text-slate-700 dark:text-slate-200' }}">
                            {{ $user->monitoring_leave->leaveType->name ?? 'Izin / Cuti' }}
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                            @if ($user->monitoring_state === 'ongoing')
                            Berakhir: <strong class="text-rose-600 dark:text-rose-400">{{
                                \Carbon\Carbon::parse($user->monitoring_leave->end_date)->translatedFormat('d MMMM Y')
                                }}</strong>
                            @elseif ($user->monitoring_state === 'upcoming')
                            Jadwal:
                            <strong>{{ \Carbon\Carbon::parse($user->monitoring_leave->start_date)->translatedFormat('d
                                M') }}
                                &ndash;
                                {{ \Carbon\Carbon::parse($user->monitoring_leave->end_date)->translatedFormat('d M Y')
                                }}</strong>
                            @else
                            Selesai:
                            {{ \Carbon\Carbon::parse($user->monitoring_leave->end_date)->translatedFormat('d M Y') }}
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Footer Kartu: CTA Button --}}
                <div class="pt-2">
                    <a href="{{ route('hrd.monitoring.show', $user) }}"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-700/70 hover:bg-primary-600 hover:text-white dark:hover:bg-primary-600 dark:hover:text-white text-slate-700 dark:text-slate-200 text-xs font-semibold border border-slate-200 dark:border-slate-600/80 transition-all duration-150">
                        <span>Lihat Detail & Kuota</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-full py-12 text-center">
                <div class="max-w-xs mx-auto space-y-3">
                    <div
                        class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-700/60 flex items-center justify-center text-slate-400">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h4 class="font-semibold text-slate-800 dark:text-slate-200 text-sm">Tidak ada karyawan
                        ditemukan</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Tidak ada data yang sesuai dengan kriteria filter saat ini.
                    </p>
                </div>
            </div>
            @endforelse
        </div>

        {{-- ═══ 4. PAGINATION FOOTER ═══ --}}
        @if ($users->hasPages() || $users->total() > 0)
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/90 dark:border-slate-700/80 px-5 py-4 flex flex-col md:flex-row items-center justify-between gap-4 shadow-xs">
            {{-- Left: Record counter & Quick per-page info --}}
            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                <div>
                    @if ($perPage === 'all' || $users->total() <= $users->count())
                        Menampilkan seluruh <span class="font-bold text-slate-800 dark:text-slate-100">{{
                            $users->total() }}</span> karyawan
                        @else
                        Menampilkan <span class="font-semibold text-slate-700 dark:text-slate-200">{{
                            $users->firstItem() ?? 0 }}</span>
                        &ndash; <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $users->lastItem() ??
                            0 }}</span>
                        dari total <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $users->total()
                            }}</span> karyawan
                        @endif
                </div>

                <span class="text-slate-300 dark:text-slate-600 hidden sm:inline">|</span>

                {{-- Quick Per-Page Switcher in Footer --}}
                <div class="flex items-center gap-1.5">
                    <span class="text-slate-500 dark:text-slate-400">Baris:</span>
                    <div class="inline-flex items-center gap-1 bg-slate-100 dark:bg-slate-700/60 p-0.5 rounded-lg">
                        @foreach (['10', '20', '30', 'all'] as $opt)
                        <a href="{{ route('hrd.monitoring.index', array_merge(request()->query(), ['per_page' => $opt, 'page' => 1])) }}"
                            class="px-2 py-0.5 rounded text-[11px] font-semibold transition-all {{ ($perPage == $opt || ($opt == '10' && empty($perPage))) ? 'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 shadow-xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}"
                            title="Tampilkan {{ $opt === 'all' ? 'Semua' : $opt }} data per halaman">
                            {{ $opt === 'all' ? 'Semua' : $opt }}
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Right: Pagination Links (when applicable) --}}
            @if ($users->hasPages())
            <div>
                {{ $users->links() }}
            </div>
            @endif
        </div>
        @endif
    </div>
</x-app-layout>