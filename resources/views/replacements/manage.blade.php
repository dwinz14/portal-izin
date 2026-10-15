@php
$restored = [
'base' => url('cuti'),
];

// Metrik ringkasan dari koleksi data cuti yang dikelola
$collection = $leaves->getCollection();
$todayStr = today()->toDateString();
$ongoingCount = $collection->filter(fn($l) => $l->start_date <= $todayStr && $l->end_date >= $todayStr)->count();
    $upcomingCount = $collection->filter(fn($l) => $l->start_date > $todayStr)->count();
    $pastCount = $collection->filter(fn($l) => $l->end_date < $todayStr)->count();

        // Helper warna avatar berdasarkan inisial nama
        $getAvatarColors = function ($name) {
        $colors = [
        ['bg' => 'bg-indigo-100 dark:bg-indigo-950/60', 'text' => 'text-indigo-700 dark:text-indigo-300', 'border' =>
        'border-indigo-200 dark:border-indigo-800'],
        ['bg' => 'bg-blue-100 dark:bg-blue-950/60', 'text' => 'text-blue-700 dark:text-blue-300', 'border' =>
        'border-blue-200 dark:border-blue-800'],
        ['bg' => 'bg-teal-100 dark:bg-teal-950/60', 'text' => 'text-teal-700 dark:text-teal-300', 'border' =>
        'border-teal-200 dark:border-teal-800'],
        ['bg' => 'bg-violet-100 dark:bg-violet-950/60', 'text' => 'text-violet-700 dark:text-violet-300', 'border' =>
        'border-violet-200 dark:border-violet-800'],
        ['bg' => 'bg-emerald-100 dark:bg-emerald-950/60', 'text' => 'text-emerald-700 dark:text-emerald-300', 'border'
        => 'border-emerald-200 dark:border-emerald-800'],
        ['bg' => 'bg-amber-100 dark:bg-amber-950/60', 'text' => 'text-amber-700 dark:text-amber-300', 'border' =>
        'border-amber-200 dark:border-amber-800'],
        ['bg' => 'bg-rose-100 dark:bg-rose-950/60', 'text' => 'text-rose-700 dark:text-rose-300', 'border' =>
        'border-rose-200 dark:border-rose-800'],
        ];
        $hash = crc32($name);
        return $colors[abs($hash) % count($colors)];
        };
        @endphp

        <x-app-layout>
            <x-slot name="header">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <h2
                                class="border-l-[5px] border-primary-600 pl-4 font-display font-bold text-xl md:text-2xl text-slate-800 dark:text-slate-100 tracking-tight leading-tight">
                                {{ __('Kelola Izin/Cuti Disetujui') }}
                            </h2>
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300 border border-primary-200 dark:border-primary-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                                Penanggung Jawab Approval
                            </span>
                        </div>
                        <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1 pl-5">
                            Penyesuaian izin/cuti final : ubah pengganti, reschedule tanggal, atau
                            pembatalan.
                        </p>
                    </div>

                    <div
                        class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400 bg-white/80 dark:bg-slate-800/80 px-3.5 py-2 rounded-xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs backdrop-blur-sm self-start md:self-auto">
                        <svg class="w-4 h-4 text-primary-500 shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Hari ini: <strong class="text-slate-800 dark:text-slate-200 font-medium">{{
                                now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</strong></span>
                    </div>
                </div>
            </x-slot>

            <div x-data="manageLeaves({{ \Illuminate\Support\Js::from($restored) }})" class="space-y-6 pb-12">

                <x-toast-notification />

                @if ($errors->any())
                <div
                    class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 shadow-xs flex items-start gap-3">
                    <div
                        class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold text-rose-800 dark:text-rose-200 mb-1">Terjadi kesalahan pada
                            permohonan</h4>
                        <ul class="text-xs text-rose-700 dark:text-rose-300 space-y-1 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                {{-- ═══ 1. KPI SUMMARY CARDS ═══ --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 md:gap-4">
                    {{-- Total Cuti Dikelola --}}
                    <div
                        class="relative overflow-hidden bg-white dark:bg-slate-800 rounded-2xl p-4 md:p-5 border border-slate-200/90 dark:border-slate-700/80 shadow-xs hover:shadow-md transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    Total Dikelola
                                </p>
                                <div class="mt-2 flex items-baseline gap-2">
                                    <span
                                        class="text-2xl md:text-3xl font-display font-bold text-slate-800 dark:text-slate-100">
                                        {{ $leaves->total() }}
                                    </span>
                                    <span class="text-xs text-slate-400">pengajuan</span>
                                </div>
                            </div>
                            <div
                                class="w-11 h-11 rounded-xl bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400 flex items-center justify-center shrink-0 border border-primary-100 dark:border-primary-500/20">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-2.5 flex items-center text-[11px] text-slate-500 dark:text-slate-400">
                            <span>Tanggung jawab approval Anda</span>
                        </div>
                    </div>

                    {{-- Sedang Berlangsung Hari Ini --}}
                    <div
                        class="relative overflow-hidden bg-white dark:bg-slate-800 rounded-2xl p-4 md:p-5 border border-rose-100 dark:border-rose-900/30 shadow-xs hover:shadow-md transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-xs font-semibold text-rose-600 dark:text-rose-400 uppercase tracking-wider">
                                    Sedang Berlangsung
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
                            <span>Pengajuan aktif saat ini</span>
                        </div>
                    </div>

                    {{-- Akan Datang --}}
                    <div
                        class="relative overflow-hidden bg-white dark:bg-slate-800 rounded-2xl p-4 md:p-5 border border-amber-100 dark:border-amber-900/30 shadow-xs hover:shadow-md transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider">
                                    Akan Datang
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
                            <span>Pengajuan yang akan datang</span>
                        </div>
                    </div>

                    {{-- Sudah Lewat (Koreksi) --}}
                    <div
                        class="relative overflow-hidden bg-white dark:bg-slate-800 rounded-2xl p-4 md:p-5 border border-slate-200 dark:border-slate-700 shadow-xs hover:shadow-md transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    Sudah Lewat
                                </p>
                                <div class="mt-2 flex items-baseline gap-2">
                                    <span
                                        class="text-2xl md:text-3xl font-display font-bold text-slate-800 dark:text-slate-100">
                                        {{ $pastCount }}
                                    </span>
                                    <span class="text-xs text-slate-400">riwayat</span>
                                </div>
                            </div>
                            <div
                                class="w-11 h-11 rounded-xl bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0 border border-slate-200 dark:border-slate-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-2.5 flex items-center text-[11px] text-slate-500 dark:text-slate-400">
                            <span>Aksi dicatat sebagai koreksi data</span>
                        </div>
                    </div>
                </div>

                {{-- ═══ 2. SMART TOOLBAR: SEARCH, STATUS FILTER & VIEW TOGGLE ═══ --}}
                <div
                    class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/90 dark:border-slate-700/80 shadow-sm p-4 md:p-5">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        {{-- Left: Live Client Search & Status Quick Filter --}}
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1">
                            {{-- Search Input --}}
                            <div class="relative flex-1 max-w-md">
                                <span
                                    class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </span>
                                <input type="text" x-model="searchQuery"
                                    placeholder="Cari pemohon, NIK, atau pengganti..."
                                    class="w-full pl-10 pr-9 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50/50 dark:bg-slate-700/60 text-slate-800 dark:text-slate-100 text-xs md:text-sm placeholder-slate-400 focus:bg-white dark:focus:bg-slate-700 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all">
                                <button type="button" x-show="searchQuery.length > 0" @click="searchQuery = ''"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
                                    title="Hapus pencarian">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Quick Status Filter Pills --}}
                            <div
                                class="flex items-center gap-1.5 bg-slate-100 dark:bg-slate-700/60 p-1 rounded-xl self-start sm:self-auto overflow-x-auto">
                                <button type="button" @click="statusFilter = 'all'"
                                    :class="statusFilter === 'all' ? 'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 font-bold shadow-xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 font-medium'"
                                    class="px-3 py-1.5 rounded-lg text-xs transition-all whitespace-nowrap">
                                    Semua
                                </button>
                                <button type="button" @click="statusFilter = 'active'"
                                    :class="statusFilter === 'active' ? 'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 font-bold shadow-xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 font-medium'"
                                    class="px-3 py-1.5 rounded-lg text-xs transition-all whitespace-nowrap flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>Aktif</span>
                                </button>
                                <button type="button" @click="statusFilter = 'past'"
                                    :class="statusFilter === 'past' ? 'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 font-bold shadow-xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 font-medium'"
                                    class="px-3 py-1.5 rounded-lg text-xs transition-all whitespace-nowrap">
                                    Sudah Lewat
                                </button>
                            </div>
                        </div>

                        {{-- Right: View Switcher (Table vs Card Grid) --}}
                        <div class="flex items-center gap-2 self-end lg:self-auto">
                            <span class="text-xs text-slate-400 hidden sm:inline">Tampilan:</span>
                            <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-700/60 p-1 rounded-xl">
                                <button type="button" @click="setView('table')"
                                    :class="viewMode === 'table' ? 'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 shadow-xs font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs transition-all"
                                    title="Tampilan Tabel">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                    </svg>
                                    <span class="hidden sm:inline">Tabel</span>
                                </button>
                                <button type="button" @click="setView('cards')"
                                    :class="viewMode === 'cards' ? 'bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 shadow-xs font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs transition-all"
                                    title="Tampilan Kartu">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                    <span class="hidden sm:inline">Kartu</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ═══ 3. DATA CONTAINER (TABEL & KARTU) ═══ --}}

                {{-- [A] TABULAR VIEW --}}
                <div x-show="viewMode === 'table'"
                    class="bg-white dark:bg-slate-800 shadow-sm rounded-2xl border border-slate-200/90 dark:border-slate-700/80 overflow-hidden transition-all">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/80 text-sm">
                            <thead>
                                <tr
                                    class="bg-slate-50/80 dark:bg-slate-900/40 text-left text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    <th scope="col" class="px-6 py-4">Karyawan Pemohon</th>
                                    <th scope="col" class="px-6 py-4">Jenis Cuti & Durasi</th>
                                    <th scope="col" class="px-6 py-4">Periode & Status</th>
                                    <th scope="col" class="px-6 py-4">Pengganti Ditugaskan</th>
                                    <th scope="col" class="px-6 py-4 text-right">Aksi Penyesuaian</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                @forelse ($leaves as $leave)
                                @php
                                $start = \Carbon\Carbon::parse($leave->start_date);
                                $end = \Carbon\Carbon::parse($leave->end_date);
                                $todayDate = today();
                                $isPast = $end->copy()->startOfDay()->lt($todayDate);
                                $isOngoing = $start->copy()->startOfDay()->lte($todayDate) &&
                                $end->copy()->startOfDay()->gte($todayDate);
                                $isUpcoming = $start->copy()->startOfDay()->gt($todayDate);

                                $stateKey = $isPast ? 'past' : 'active';
                                $colors = $getAvatarColors($leave->user->name);
                                $initials = collect(explode(' ', $leave->user->name))
                                ->map(fn($part) => mb_substr($part, 0, 1))
                                ->take(2)
                                ->join('');

                                // Data baris untuk Alpine (di-escape aman oleh Js::from)
                                $rowData = [
                                'id' => $leave->id,
                                'label' => ucwords($leave->user->name) . ' (' . $start->format('d/m/Y') . ' - ' .
                                $end->format('d/m/Y') . ')',
                                'past' => $isPast,
                                'start' => $start->toDateString(),
                                'end' => $end->toDateString(),
                                'jenis' => $leave->leaveType->name ?? '-',
                                'employee_name' => ucwords($leave->user->name),
                                'employee_nik' => $leave->user->nik ?? '-',
                                'pengganti_name' => ucwords($leave->pengganti->name ?? '-'),
                                'total_hari' => $leave->total_hari,
                                ];
                                @endphp
                                <tr x-show="matchesRow('{{ strtolower(addslashes($leave->user->name . ' ' . ($leave->user->nik ?? '') . ' ' . ($leave->pengganti->name ?? ''))) }}', '{{ $stateKey }}')"
                                    class="hover:bg-slate-50/90 dark:hover:bg-slate-700/30 transition-colors group">
                                    {{-- Pemohon --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3.5">
                                            <div class="relative shrink-0">
                                                <div
                                                    class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs {{ $colors['bg'] }} {{ $colors['text'] }} border {{ $colors['border'] }} shadow-xs">
                                                    {{ strtoupper($initials) }}
                                                </div>
                                                @if ($isOngoing)
                                                <span
                                                    class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full bg-rose-500 border-2 border-white dark:border-slate-800"
                                                    title="Sedang Cuti Berjalan"></span>
                                                @elseif ($isUpcoming)
                                                <span
                                                    class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full bg-amber-500 border-2 border-white dark:border-slate-800"
                                                    title="Cuti Mendatang"></span>
                                                @endif
                                            </div>
                                            <div>
                                                <div
                                                    class="font-semibold text-slate-900 dark:text-slate-100 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                                    {{ ucwords($leave->user->name) }}
                                                </div>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span
                                                        class="font-mono text-xs text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700/60 px-1.5 py-0.5 rounded">
                                                        {{ $leave->user->nik ?? '-' }}
                                                    </span>
                                                    @if ($leave->alasan)
                                                    <span
                                                        class="text-xs text-slate-400 dark:text-slate-500 truncate max-w-[140px]"
                                                        title="{{ $leave->alasan }}">
                                                        &middot; {{ $leave->alasan }}
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Jenis Cuti & Durasi --}}
                                    <td class="px-6 py-4">
                                        <div class="space-y-1">
                                            <div class="font-semibold text-slate-800 dark:text-slate-200">
                                                {{ $leave->leaveType->name ?? 'Izin / Cuti' }}
                                            </div>
                                            <div
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300 border border-primary-200/60 dark:border-primary-500/20">
                                                <svg class="w-3 h-3 text-primary-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>{{ $leave->total_hari }} Hari</span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Periode & Status --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="space-y-1.5">
                                            <div
                                                class="flex items-center gap-1.5 font-medium text-slate-700 dark:text-slate-300">
                                                <span>{{ $start->translatedFormat('d M Y') }}</span>
                                                <span class="text-slate-400">&ndash;</span>
                                                <span>{{ $end->translatedFormat('d M Y') }}</span>
                                            </div>
                                            <div>
                                                @if ($isOngoing)
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300 border border-rose-200/80 dark:border-rose-500/30">
                                                    <span
                                                        class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                                    Sedang Berjalan
                                                </span>
                                                @elseif ($isUpcoming)
                                                <span
                                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300 border border-amber-200/80 dark:border-amber-500/30">
                                                    <svg class="w-3 h-3 text-amber-600" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    Akan Datang
                                                </span>
                                                @else
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300"
                                                    title="Aksi penyesuaian akan dicatat sebagai koreksi data">
                                                    <svg class="w-3 h-3 text-slate-400" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Sudah Lewat (Koreksi)
                                                </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Pengganti Ditugaskan --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 shrink-0">
                                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-medium text-slate-800 dark:text-slate-200 text-xs">
                                                    {{ ucwords($leave->pengganti->name ?? '-') }}
                                                </div>
                                                <div class="text-[10px] text-slate-400">Petugas Pengganti</div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Aksi Penyesuaian --}}

                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap items-center justify-end gap-2">

                                            @can('changePengganti', $leave)
                                            <button type="button"
                                                @click="openPengganti({{ \Illuminate\Support\Js::from($rowData) }})"
                                                title="Ubah user pengganti"
                                                class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-600 transition-colors duration-150 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-1 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-slate-600 dark:hover:bg-slate-700 dark:hover:text-white dark:focus-visible:ring-offset-slate-900">
                                                <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.8"
                                                        d="M16 3l4 4-4 4M4 7h16M8 21l-4-4 4-4m12 4H4" />
                                                </svg>
                                                <span>Pengganti</span>
                                            </button>
                                            @endcan

                                            <button type="button"
                                                @click="openTanggal({{ \Illuminate\Support\Js::from($rowData) }})"
                                                title="Ubah jadwal tanggal cuti"
                                                class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-600 transition-colors duration-150 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-1 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-slate-600 dark:hover:bg-slate-700 dark:hover:text-white dark:focus-visible:ring-offset-slate-900">
                                                <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.8"
                                                        d="M8 3v4m8-4v4M4 11h16M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" />
                                                </svg>
                                                <span>Tanggal</span>
                                            </button>

                                            <button type="button"
                                                @click="openBatal({{ \Illuminate\Support\Js::from($rowData) }})"
                                                title="Batalkan cuti dan kembalikan kuota"
                                                class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded-lg border border-rose-200 bg-rose-50/70 px-2.5 py-1.5 text-xs font-medium text-rose-700 transition-colors duration-150 hover:border-rose-300 hover:bg-rose-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 focus-visible:ring-offset-1 dark:border-rose-900/60 dark:bg-rose-950/30 dark:text-rose-300 dark:hover:border-rose-800 dark:hover:bg-rose-950/60 dark:focus-visible:ring-offset-slate-900">
                                                <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.8"
                                                        d="M4 7h16M10 11v6m4-6v6M5 7l1 13h12l1-13M9 7V4h6v3" />
                                                </svg>
                                                <span>Batalkan</span>
                                            </button>

                                        </div>
                                    </td>

                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-14 text-center">
                                        <div class="max-w-xs mx-auto space-y-3">
                                            <div
                                                class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-700/60 flex items-center justify-center text-slate-400">
                                                <svg class="w-7 h-7" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <h4 class="font-semibold text-slate-800 dark:text-slate-200 text-sm">Tidak
                                                ada izin/cuti untuk dikelola</h4>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                Belum ada izin/cuti final approved yang berada di bawah tanggung jawab
                                                approval Anda.
                                            </p>
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
                        {{ $leaves->links() }}
                    </div>
                    @endif
                </div>

                {{-- [B] CARDS GRID VIEW (RESPONSIVE) --}}
                <div x-show="viewMode === 'cards'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
                    style="display: none;">
                    @forelse ($leaves as $leave)
                    @php
                    $start = \Carbon\Carbon::parse($leave->start_date);
                    $end = \Carbon\Carbon::parse($leave->end_date);
                    $todayDate = today();
                    $isPast = $end->copy()->startOfDay()->lt($todayDate);
                    $isOngoing = $start->copy()->startOfDay()->lte($todayDate) &&
                    $end->copy()->startOfDay()->gte($todayDate);
                    $isUpcoming = $start->copy()->startOfDay()->gt($todayDate);

                    $stateKey = $isPast ? 'past' : 'active';
                    $colors = $getAvatarColors($leave->user->name);
                    $initials = collect(explode(' ', $leave->user->name))
                    ->map(fn($part) => mb_substr($part, 0, 1))
                    ->take(2)
                    ->join('');

                    $rowData = [
                    'id' => $leave->id,
                    'label' => ucwords($leave->user->name) . ' (' . $start->format('d/m/Y') . ' - ' .
                    $end->format('d/m/Y') . ')',
                    'past' => $isPast,
                    'start' => $start->toDateString(),
                    'end' => $end->toDateString(),
                    'jenis' => $leave->leaveType->name ?? '-',
                    'employee_name' => ucwords($leave->user->name),
                    'employee_nik' => $leave->user->nik ?? '-',
                    'pengganti_name' => ucwords($leave->pengganti->name ?? '-'),
                    'total_hari' => $leave->total_hari,
                    ];
                    @endphp
                    <div x-show="matchesRow('{{ strtolower(addslashes($leave->user->name . ' ' . ($leave->user->nik ?? '') . ' ' . ($leave->pengganti->name ?? ''))) }}', '{{ $stateKey }}')"
                        class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/90 dark:border-slate-700/80 p-5 shadow-xs hover:shadow-md transition-all duration-200 flex flex-col justify-between gap-4">

                        <div>
                            {{-- Card Header: Avatar, Name, Status Pill --}}
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-11 h-11 rounded-xl flex items-center justify-center font-bold text-sm {{ $colors['bg'] }} {{ $colors['text'] }} border {{ $colors['border'] }} shadow-xs shrink-0">
                                        {{ strtoupper($initials) }}
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm leading-snug">
                                            {{ ucwords($leave->user->name) }}
                                        </h3>
                                        <div class="font-mono text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                            NIK: {{ $leave->user->nik ?? '-' }}
                                        </div>
                                    </div>
                                </div>

                                @if ($isOngoing)
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 border border-rose-200 dark:border-rose-500/30 shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                    Aktif
                                </span>
                                @elseif ($isUpcoming)
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30 shrink-0">
                                    Mendatang
                                </span>
                                @else
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300 shrink-0">
                                    Lewat
                                </span>
                                @endif
                            </div>

                            {{-- Metadata List --}}
                            <div
                                class="mt-4 pt-3.5 border-t border-slate-100 dark:border-slate-700/60 space-y-2.5 text-xs">
                                <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                                    <span class="text-slate-400">Jenis Cuti</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{
                                        $leave->leaveType->name ?? '-' }}</span>
                                </div>

                                <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                                    <span class="text-slate-400">Durasi</span>
                                    <span class="font-semibold text-primary-600 dark:text-primary-400">{{
                                        $leave->total_hari }} Hari Kerja</span>
                                </div>

                                <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                                    <span class="text-slate-400">Periode</span>
                                    <span class="font-medium text-slate-800 dark:text-slate-200 text-right">
                                        {{ $start->translatedFormat('d M Y') }} &ndash; {{ $end->translatedFormat('d M
                                        Y') }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                                    <span class="text-slate-400">Pengganti</span>
                                    <span class="font-medium text-slate-800 dark:text-slate-200">{{
                                        ucwords($leave->pengganti->name ?? '-') }}</span>
                                </div>

                                @if ($leave->alasan)
                                <div
                                    class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-700/50 text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                                    <span class="font-semibold text-slate-600 dark:text-slate-300">Alasan:</span> {{
                                    $leave->alasan }}
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Card Actions --}}
                        <div class="pt-3 border-t border-slate-100 dark:border-slate-700/60 flex flex-col gap-2">
                            @can('changePengganti', $leave)
                            <button type="button" @click="openPengganti({{ \Illuminate\Support\Js::from($rowData) }})"
                                class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-amber-50 hover:bg-amber-100 dark:bg-amber-500/10 dark:hover:bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-200/80 dark:border-amber-500/20 text-xs font-semibold rounded-xl transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                <span>Ubah Pengganti</span>
                            </button>
                            @endcan

                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" @click="openTanggal({{ \Illuminate\Support\Js::from($rowData) }})"
                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-blue-50 hover:bg-blue-100 dark:bg-blue-500/10 dark:hover:bg-blue-500/20 text-blue-700 dark:text-blue-300 border border-blue-200/80 dark:border-blue-500/20 text-xs font-semibold rounded-xl transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span>Reschedule</span>
                                </button>

                                <button type="button" @click="openBatal({{ \Illuminate\Support\Js::from($rowData) }})"
                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-rose-50 hover:bg-rose-100 dark:bg-rose-500/10 dark:hover:bg-rose-500/20 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-500/20 text-xs font-semibold rounded-xl transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <span>Batalkan</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-full py-12 text-center">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Tidak ada data cuti.</p>
                    </div>
                    @endforelse
                </div>

                {{-- ═══ MODAL 1: UBAH PENGGANTI ═══ --}}
                <div x-show="modal === 'pengganti'" x-cloak x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-[200] flex items-center justify-center p-4 sm:p-6 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-md"
                    x-on:keydown.escape.window="close()">

                    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden relative border border-slate-200 dark:border-slate-700"
                        @click.stop x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 translate-y-4">

                        {{-- Header Accent --}}
                        <div class="h-2 bg-gradient-to-r from-amber-500 to-amber-600"></div>

                        <div class="p-6 md:p-7">
                            <div class="flex items-start justify-between gap-4 mb-5">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 border border-amber-200/60 dark:border-amber-500/20 shadow-xs">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-display font-bold text-lg text-slate-900 dark:text-slate-100">
                                            Ubah Petugas Pengganti
                                        </h3>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                            Tetapkan karyawan pengganti baru yang memenuhi syarat.
                                        </p>
                                    </div>
                                </div>
                                <button type="button" @click="close()"
                                    class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Context Summary Card --}}
                            <div
                                class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200/80 dark:border-slate-600/60 mb-5 space-y-2 text-xs">
                                <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                                    <span>Pemohon:</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200"
                                        x-text="row.employee_name || row.label"></span>
                                </div>
                                <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                                    <span>Jenis & Periode:</span>
                                    <span class="font-medium text-slate-700 dark:text-slate-300"
                                        x-text="`${row.jenis} (${formatDateIndo(row.start)} - ${formatDateIndo(row.end)})`"></span>
                                </div>
                                <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                                    <span>Pengganti Saat Ini:</span>
                                    <span class="font-semibold text-amber-700 dark:text-amber-400"
                                        x-text="row.pengganti_name || '-'"></span>
                                </div>
                            </div>

                            {{-- Notice: Koreksi Data jika Periode sudah lewat --}}
                            <div x-show="row.past" x-cloak
                                class="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2.5 mb-5">
                                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Periode cuti ini telah berakhir. Penyesuaian akan dicatat sebagai <strong>koreksi
                                        data</strong> dan notifikasi tetap dikirimkan ke semua pihak.</span>
                            </div>

                            {{-- Form Inputs --}}
                            <div class="space-y-4">
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                        Pilih Pengganti Baru <span class="text-rose-500">*</span>
                                    </label>

                                    <template x-if="loadingCandidates">
                                        <div
                                            class="flex items-center gap-2 p-3 rounded-xl bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 text-xs text-slate-500">
                                            <svg class="w-4 h-4 animate-spin text-amber-600" fill="none"
                                                viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                            <span>Memuat daftar calon pengganti yang memenuhi kualifikasi...</span>
                                        </div>
                                    </template>

                                    <template x-if="!loadingCandidates && candidates.length === 0">
                                        <div
                                            class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 text-xs text-rose-700 dark:text-rose-300">
                                            Tidak ditemukan calon pengganti yang memenuhi aturan kualifikasi penempatan
                                            untuk pemohon ini.
                                        </div>
                                    </template>

                                    <select x-show="!loadingCandidates && candidates.length > 0"
                                        x-model="selectedPenggantiId"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-100 text-xs md:text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all cursor-pointer shadow-xs">
                                        <option value="">-- Pilih Calon Pengganti Baru --</option>
                                        <template x-for="c in candidates" :key="c.id">
                                            <option :value="c.id" x-text="c.name"></option>
                                        </template>
                                    </select>
                                </div>

                                {{-- Security Confirmation Box --}}
                                <div
                                    class="p-4 rounded-2xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/60 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-semibold text-amber-900 dark:text-amber-200">
                                            Konfirmasi Keamanan
                                        </span>
                                        <button type="button" @click="confirmText = 'GANTI'"
                                            class="text-[11px] font-semibold text-amber-700 dark:text-amber-400 hover:underline">
                                            Salin Kata Kunci
                                        </button>
                                    </div>
                                    <p class="text-[11px] text-amber-800 dark:text-amber-300">
                                        Ketik kata konfirmasi <strong
                                            class="px-1.5 py-0.5 rounded bg-amber-200/80 dark:bg-amber-900/80 text-amber-900 dark:text-amber-200 font-mono font-bold">GANTI</strong>
                                        di bawah ini untuk mengonfirmasi:
                                    </p>
                                    <input type="text" x-model="confirmText" placeholder="Ketik: GANTI"
                                        autocomplete="off"
                                        class="w-full px-4 py-2.5 rounded-xl border border-amber-300 dark:border-amber-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 text-center font-mono font-bold text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all">

                                    <div class="min-h-[18px]">
                                        <template x-if="confirmText === 'GANTI'">
                                            <span
                                                class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 flex items-center justify-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Kata konfirmasi sesuai
                                            </span>
                                        </template>
                                        <template x-if="confirmText.length > 0 && confirmText !== 'GANTI'">
                                            <span
                                                class="text-[11px] font-medium text-rose-600 dark:text-rose-400 flex items-center justify-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                Harus persis huruf kapital: GANTI
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div
                                class="mt-6 flex flex-col-reverse sm:flex-row items-center justify-end gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-700/80">
                                <button type="button" @click="close()"
                                    class="w-full sm:w-auto px-4 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">
                                    Batal
                                </button>

                                <form :action="`${base}/${row.id}/replace-pengganti`" method="POST"
                                    class="w-full sm:w-auto" @submit="submitting = true">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="new_pengganti_id" :value="selectedPenggantiId">
                                    <input type="hidden" name="confirmation" :value="confirmText">
                                    <button type="submit" :disabled="!canConfirmPengganti || submitting"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-xs transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                                        <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none"
                                            viewBox="0 0 24 24" x-cloak>
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        <span x-text="submitting ? 'Menyimpan...' : 'Konfirmasi Pengganti'"></span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ═══ MODAL 2: UBAH TANGGAL (RESCHEDULE) ═══ --}}
                <div x-show="modal === 'tanggal'" x-cloak x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-[200] flex items-center justify-center p-4 sm:p-6 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-md overflow-y-auto"
                    x-on:keydown.escape.window="close()">

                    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden relative border border-slate-200 dark:border-slate-700 my-8"
                        @click.stop x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 translate-y-4">

                        {{-- Header Accent --}}
                        <div class="h-2 bg-gradient-to-r from-blue-500 to-indigo-600"></div>

                        <div class="p-6 md:p-7">
                            <div class="flex items-start justify-between gap-4 mb-5">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 border border-blue-200/60 dark:border-blue-500/20 shadow-xs">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-display font-bold text-lg text-slate-900 dark:text-slate-100">
                                            Ubah Jadwal Tanggal Cuti
                                        </h3>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                            Sesuaikan tanggal mulai & selesai cuti yang telah approved.
                                        </p>
                                    </div>
                                </div>
                                <button type="button" @click="close()"
                                    class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Context Summary Card --}}
                            <div
                                class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200/80 dark:border-slate-600/60 mb-5 space-y-2 text-xs">
                                <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                                    <span>Pemohon:</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200"
                                        x-text="row.employee_name || row.label"></span>
                                </div>
                                <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                                    <span>Jadwal Lama:</span>
                                    <span class="font-semibold text-slate-700 dark:text-slate-300"
                                        x-text="`${formatDateIndo(row.start)} s/d ${formatDateIndo(row.end)} (${row.total_hari || '-'} hari)`"></span>
                                </div>
                                <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                                    <span>Petugas Pengganti:</span>
                                    <span class="font-medium text-slate-800 dark:text-slate-200"
                                        x-text="row.pengganti_name || '-'"></span>
                                </div>
                            </div>

                            {{-- Notice: Koreksi Data & Dampak Kuota --}}
                            <div class="space-y-2.5 mb-5">
                                <div x-show="row.past" x-cloak
                                    class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2">
                                    <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>Periode cuti ini sudah lewat. Penyesuaian akan dicatat sebagai <strong>koreksi
                                            data</strong>.</span>
                                </div>
                                <div
                                    class="p-3 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/60 text-xs text-blue-800 dark:text-blue-300 flex items-start gap-2">
                                    <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Validasi bentrok jadwal & penyesuaian kuota cuti otomatis dijalankan.
                                        Notifikasi dikirim ke pemohon & pengganti.</span>
                                </div>
                            </div>

                            {{-- Form Inputs --}}
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                            Tanggal Mulai Baru <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="date" x-model="startDate"
                                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-100 text-xs md:text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all shadow-xs cursor-pointer">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                            Tanggal Selesai Baru <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="date" x-model="endDate" :min="startDate"
                                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-100 text-xs md:text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all shadow-xs cursor-pointer">
                                    </div>
                                </div>

                                {{-- Date Validation Warning --}}
                                <div x-show="startDate && endDate && endDate < startDate" x-cloak
                                    class="text-xs font-medium text-rose-600 dark:text-rose-400">
                                    Tanggal selesai tidak boleh sebelum tanggal mulai.
                                </div>

                                {{-- Reason Textarea --}}
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label
                                            class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                            Alasan Perubahan <span class="text-rose-500">*</span>
                                        </label>
                                        <span class="text-[11px] font-mono"
                                            :class="alasan.length >= 10 ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-slate-400'">
                                            <span x-text="alasan.length"></span> / 500 (min 10)
                                        </span>
                                    </div>
                                    <textarea x-model="alasan" rows="3" maxlength="500"
                                        placeholder="Jelaskan alasan perubahan jadwal (minimal 10 karakter)..."
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-100 text-xs md:text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all shadow-xs"></textarea>
                                    <p class="text-[10px] text-slate-400 mt-1">Boleh huruf, angka, spasi, dan tanda . ,
                                        ( ) / -</p>
                                </div>

                                {{-- Security Confirmation Box --}}
                                <div
                                    class="p-4 rounded-2xl bg-blue-50/70 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-900/60 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-semibold text-blue-900 dark:text-blue-200">
                                            Konfirmasi Keamanan
                                        </span>
                                        <button type="button" @click="confirmText = 'UBAH TANGGAL'"
                                            class="text-[11px] font-semibold text-blue-700 dark:text-blue-400 hover:underline">
                                            Salin Kata Kunci
                                        </button>
                                    </div>
                                    <p class="text-[11px] text-blue-800 dark:text-blue-300">
                                        Ketik kata konfirmasi <strong
                                            class="px-1.5 py-0.5 rounded bg-blue-200/80 dark:bg-blue-900/80 text-blue-900 dark:text-blue-200 font-mono font-bold">UBAH
                                            TANGGAL</strong> di bawah ini:
                                    </p>
                                    <input type="text" x-model="confirmText" placeholder="Ketik: UBAH TANGGAL"
                                        autocomplete="off"
                                        class="w-full px-4 py-2.5 rounded-xl border border-blue-300 dark:border-blue-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 text-center font-mono font-bold text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">

                                    <div class="min-h-[18px]">
                                        <template x-if="confirmText === 'UBAH TANGGAL'">
                                            <span
                                                class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 flex items-center justify-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Kata konfirmasi sesuai
                                            </span>
                                        </template>
                                        <template x-if="confirmText.length > 0 && confirmText !== 'UBAH TANGGAL'">
                                            <span
                                                class="text-[11px] font-medium text-rose-600 dark:text-rose-400 flex items-center justify-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                Harus persis huruf kapital: UBAH TANGGAL
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div
                                class="mt-6 flex flex-col-reverse sm:flex-row items-center justify-end gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-700/80">
                                <button type="button" @click="close()"
                                    class="w-full sm:w-auto px-4 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">
                                    Batal
                                </button>

                                <form :action="`${base}/${row.id}/reschedule`" method="POST" class="w-full sm:w-auto"
                                    @submit="submitting = true">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="start_date" :value="startDate">
                                    <input type="hidden" name="end_date" :value="endDate">
                                    <input type="hidden" name="alasan" :value="alasan">
                                    <input type="hidden" name="confirmation" :value="confirmText">
                                    <button type="submit" :disabled="!canConfirmTanggal || submitting"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                                        <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none"
                                            viewBox="0 0 24 24" x-cloak>
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        <span x-text="submitting ? 'Menyimpan...' : 'Simpan Perubahan Tanggal'"></span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ═══ MODAL 3: BATALKAN CUTI (APPROVER CANCEL) ═══ --}}
                <div x-show="modal === 'batal'" x-cloak x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-[200] flex items-center justify-center p-4 sm:p-6 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-md overflow-y-auto"
                    x-on:keydown.escape.window="close()">

                    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden relative border border-slate-200 dark:border-slate-700 my-8"
                        @click.stop x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 translate-y-4">

                        {{-- Header Accent Destructive --}}
                        <div class="h-2 bg-gradient-to-r from-rose-500 to-red-600"></div>

                        <div class="p-6 md:p-7">
                            <div class="flex items-start justify-between gap-4 mb-5">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 border border-rose-200/60 dark:border-rose-500/20 shadow-xs">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-display font-bold text-lg text-slate-900 dark:text-slate-100">
                                            Batalkan Izin / Cuti
                                        </h3>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                            Hapus pengajuan cuti yang telah final approved secara permanen.
                                        </p>
                                    </div>
                                </div>
                                <button type="button" @click="close()"
                                    class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Context Summary Card --}}
                            <div
                                class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200/80 dark:border-slate-600/60 mb-5 space-y-2 text-xs">
                                <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                                    <span>Pemohon:</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200"
                                        x-text="row.employee_name || row.label"></span>
                                </div>
                                <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                                    <span>Jenis & Periode:</span>
                                    <span class="font-semibold text-slate-700 dark:text-slate-300"
                                        x-text="`${row.jenis} (${formatDateIndo(row.start)} - ${formatDateIndo(row.end)})`"></span>
                                </div>
                                <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                                    <span>Pengganti Saat Ini:</span>
                                    <span class="font-medium text-slate-800 dark:text-slate-200"
                                        x-text="row.pengganti_name || '-'"></span>
                                </div>
                            </div>

                            {{-- Notice: Konsekuensi Pembatalan --}}
                            <div class="space-y-2.5 mb-5">
                                <div x-show="row.past" x-cloak
                                    class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2">
                                    <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>Periode cuti ini telah berakhir. Pembatalan dicatat sebagai <strong>koreksi
                                            data</strong>.</span>
                                </div>

                                <div
                                    class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 text-xs text-rose-800 dark:text-rose-300 space-y-1">
                                    <p class="font-bold">Konsekuensi Pembatalan:</p>
                                    <ul class="list-disc list-inside space-y-0.5 text-rose-700 dark:text-rose-400">
                                        <li>Kuota cuti dikembalikan otomatis ke saldo karyawan (jika berkuota).</li>
                                        <li>Tugas pengganti dibebaskan dan pengajuan dihapus dari status aktif.</li>
                                        <li>Notifikasi pembatalan resmi dikirimkan ke pemohon & pengganti.</li>
                                        <li>Tindakan ini terekam permanen dalam log aktivitas.</li>
                                    </ul>
                                </div>
                            </div>

                            {{-- Form Inputs --}}
                            <div class="space-y-4">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label
                                            class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                            Alasan Pembatalan <span class="text-rose-500">*</span>
                                        </label>
                                        <span class="text-[11px] font-mono"
                                            :class="alasan.length >= 10 ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-slate-400'">
                                            <span x-text="alasan.length"></span> / 500 (min 10)
                                        </span>
                                    </div>
                                    <textarea x-model="alasan" rows="3" maxlength="500"
                                        placeholder="Jelaskan alasan pembatalan izin/cuti ini (minimal 10 karakter)..."
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-100 text-xs md:text-sm focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 transition-all shadow-xs"></textarea>
                                    <p class="text-[10px] text-slate-400 mt-1">Boleh huruf, angka, spasi, dan tanda . ,
                                        ( ) / -</p>
                                </div>

                                {{-- Security Confirmation Box --}}
                                <div
                                    class="p-4 rounded-2xl bg-rose-50/70 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/60 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-semibold text-rose-900 dark:text-rose-200">
                                            Konfirmasi Keamanan Penting
                                        </span>
                                        <button type="button" @click="confirmText = 'BATALKAN'"
                                            class="text-[11px] font-semibold text-rose-700 dark:text-rose-400 hover:underline">
                                            Salin Kata Kunci
                                        </button>
                                    </div>
                                    <p class="text-[11px] text-rose-800 dark:text-rose-300">
                                        Ketik kata konfirmasi <strong
                                            class="px-1.5 py-0.5 rounded bg-rose-200/80 dark:bg-rose-900/80 text-rose-900 dark:text-rose-200 font-mono font-bold">BATALKAN</strong>
                                        di bawah ini untuk melanjutkan:
                                    </p>
                                    <input type="text" x-model="confirmText" placeholder="Ketik: BATALKAN"
                                        autocomplete="off"
                                        class="w-full px-4 py-2.5 rounded-xl border border-rose-300 dark:border-rose-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 text-center font-mono font-bold text-sm focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 transition-all">

                                    <div class="min-h-[18px]">
                                        <template x-if="confirmText === 'BATALKAN'">
                                            <span
                                                class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 flex items-center justify-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Kata konfirmasi sesuai
                                            </span>
                                        </template>
                                        <template x-if="confirmText.length > 0 && confirmText !== 'BATALKAN'">
                                            <span
                                                class="text-[11px] font-medium text-rose-600 dark:text-rose-400 flex items-center justify-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                Harus persis huruf kapital: BATALKAN
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div
                                class="mt-6 flex flex-col-reverse sm:flex-row items-center justify-end gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-700/80">
                                <button type="button" @click="close()"
                                    class="w-full sm:w-auto px-4 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">
                                    Batal
                                </button>

                                <form :action="`${base}/${row.id}/approver-cancel`" method="POST"
                                    class="w-full sm:w-auto" @submit="submitting = true">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="alasan" :value="alasan">
                                    <input type="hidden" name="confirmation" :value="confirmText">
                                    <button type="submit" :disabled="!canConfirmBatal || submitting"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-xs transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                                        <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none"
                                            viewBox="0 0 24 24" x-cloak>
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        <span x-text="submitting ? 'Membatalkan...' : 'Batalkan Izin/Cuti Ini'"></span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('alpine:init', () => {
            Alpine.data('manageLeaves', (restored = {}) => ({
                base: restored.base || '{{ url("cuti") }}',
                modal: null, // null | 'pengganti' | 'tanggal' | 'batal'
                row: {
                    id: null,
                    label: '',
                    past: false,
                    start: '',
                    end: '',
                    jenis: '',
                    employee_name: '',
                    employee_nik: '',
                    pengganti_name: '',
                    total_hari: ''
                },

                // Filter & Toolbar States
                searchQuery: '',
                statusFilter: 'all', // 'all' | 'active' | 'past'
                viewMode: localStorage.getItem('hrd_manage_leaves_view_mode') || 'table',

                setView(mode) {
                    this.viewMode = mode;
                    localStorage.setItem('hrd_manage_leaves_view_mode', mode);
                },

                matchesRow(searchString, stateKey) {
                    const matchesSearch = !this.searchQuery || searchString.includes(this.searchQuery.toLowerCase().trim());
                    const matchesStatus = this.statusFilter === 'all' || this.statusFilter === stateKey;
                    return matchesSearch && matchesStatus;
                },

                formatDateIndo(dateStr) {
                    if (!dateStr) return '-';
                    try {
                        const parts = dateStr.split('-');
                        if (parts.length === 3) {
                            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                            const d = parseInt(parts[2], 10);
                            const m = months[parseInt(parts[1], 10) - 1];
                            const y = parts[0];
                            return `${d} ${m} ${y}`;
                        }
                    } catch (e) {}
                    return dateStr;
                },

                // Ubah pengganti
                candidates: [],
                loadingCandidates: false,
                selectedPenggantiId: '',

                // Ubah tanggal
                startDate: '',
                endDate: '',

                // Dipakai bersama
                alasan: '',
                confirmText: '',
                submitting: false,

                get alasanValid() {
                    const v = this.alasan.trim();
                    return v.length >= 10 && v.length <= 500 && /^[a-zA-Z0-9\s.,()\/-]+$/.test(v);
                },

                get canConfirmPengganti() {
                    return this.confirmText.trim() === 'GANTI' && this.selectedPenggantiId !== '';
                },

                get canConfirmTanggal() {
                    const datesValid = this.startDate !== '' && this.endDate !== '' && this.endDate >= this.startDate;
                    const changed = this.startDate !== this.row.start || this.endDate !== this.row.end;
                    return this.confirmText.trim() === 'UBAH TANGGAL' && datesValid && changed && this.alasanValid;
                },

                get canConfirmBatal() {
                    return this.confirmText.trim() === 'BATALKAN' && this.alasanValid;
                },

                resetShared() {
                    this.alasan = '';
                    this.confirmText = '';
                    this.submitting = false;
                },

                close() {
                    if (!this.submitting) this.modal = null;
                },

                openPengganti(row) {
                    this.row = row;
                    this.resetShared();
                    this.candidates = [];
                    this.selectedPenggantiId = '';
                    this.modal = 'pengganti';
                    this.loadingCandidates = true;
                    fetch(`${this.base}/${row.id}/pengganti/eligible`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                        })
                        .then(r => r.json())
                        .then(data => {
                            this.candidates = data;
                            this.loadingCandidates = false;
                        })
                        .catch(() => { this.loadingCandidates = false; });
                },

                openTanggal(row) {
                    this.row = row;
                    this.resetShared();
                    this.startDate = row.start;
                    this.endDate = row.end;
                    this.modal = 'tanggal';
                },

                openBatal(row) {
                    this.row = row;
                    this.resetShared();
                    this.modal = 'batal';
                },
            }));
        });
            </script>
        </x-app-layout>
