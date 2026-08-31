<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div>
                <h2
                    class="flex items-center gap-2 font-bold text-xl sm:text-2xl text-gray-900 dark:text-gray-100 leading-tight">
                    <span class="w-1.5 h-5 bg-primary-600 rounded-full"></span>
                    {{ __('Ajukan Cuti') }}
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1 ml-3.5">
                    Lengkapi formulir di bawah ini untuk mengajukan permohonan cuti Anda.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 bg-gray-50/50 dark:bg-gray-900/50 min-h-screen flex justify-center"
        x-data="leaveForm()">
        <div class="w-full max-w-3xl"> {{-- Dikurangi dari max-w-4xl ke max-w-3xl agar lebih proporsional --}}

            {{-- Wrapper Utama --}}
            <div
                class="bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden transition-all duration-300">

                <form method="POST" action="{{ route('cuti.store') }}" enctype="multipart/form-data">
                    @csrf

                    {{-- ── BAGIAN 1: JENIS CUTI ──────────────────────────────────────────── --}}
                    <div
                        class="p-5 sm:p-6 border-b border-gray-100 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-800/50">
                        <label for="leave_type_id"
                            class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Pilih Jenis Cuti / Izin <span class="text-red-500">*</span>
                        </label>
                        <div class="relative max-w-xl">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <select id="leave_type_id" name="leave_type_id" @change="onLeaveTypeChange($event.target)"
                                class="block w-full pl-9 pr-9 py-2.5 text-sm bg-white dark:bg-slate-900 border-gray-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900 dark:text-gray-100 transition-colors cursor-pointer">
                                <option value="" class="text-gray-500">-- Silakan Pilih --</option>
                                @foreach ($leaveTypes as $type)
                                    <option value="{{ $type->id }}" @selected(old('leave_type_id') == $type->id)>
                                        {{ strtoupper($type->name) }}
                                        @if ($type->quota > 0 && isset($userLeaveBalances[$type->id]))
                                            (Sisa Kuota: {{ $userLeaveBalances[$type->id]->remaining }} Hari)
                                        @elseif ($type->quota > 0)
                                            (Total Kuota: {{ $type->quota }} Hari)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('leave_type_id')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- ── FORM DINAMIS (Muncul Setelah Memilih Jenis Cuti) ───────────── --}}
                    <div x-show="showForm" style="display: none;" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 -translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0" class="p-5 sm:p-6 space-y-6">
                        {{-- Padding dan space-y diperkecil --}}

                        {{-- ── BAGIAN 2: WAKTU PELAKSANAAN ─────────────────────────────── --}}
                        <section>
                            <h3
                                class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Waktu Pelaksanaan
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                {{-- Tanggal Mulai --}}
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal
                                        Mulai</label>
                                    <div class="relative" @click.outside="startOpen = false">
                                        <button type="button" @click="startOpen = !startOpen"
                                            :class="startOpen ? 'ring-2 ring-primary-500 border-primary-500' :
                                                'border-gray-300 dark:border-slate-600 hover:border-gray-400 dark:hover:border-gray-500'"
                                            class="relative w-full flex items-center justify-between px-3.5 py-2 bg-white dark:bg-slate-900 border rounded-lg shadow-sm text-sm text-left focus:outline-none transition-all duration-200">
                                            <span
                                                :class="startDate ? 'text-gray-900 dark:text-gray-100 font-medium' :
                                                    'text-gray-400 dark:text-gray-500'"
                                                x-text="startDate ? formatDisplay(startDate) : 'Pilih tanggal mulai'"></span>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                        <input type="hidden" name="start_date" :value="startDate">

                                        {{-- Popup Kalender Mulai --}}
                                        <div x-show="startOpen" x-transition
                                            class="absolute left-0 top-full mt-1.5 z-50 bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700 rounded-xl shadow-xl p-3 w-64 origin-top-left"
                                            style="display:none">
                                            <div class="flex items-center justify-between mb-3">
                                                <button type="button" @click="prevMonth('start')"
                                                    class="p-1 rounded-md hover:bg-gray-100 dark:hover:bg-slate-700 text-gray-500 transition-colors"><svg
                                                        class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 19l-7-7 7-7" />
                                                    </svg></button>
                                                <span class="text-sm font-bold text-gray-800 dark:text-gray-100"
                                                    x-text="MONTHS[startViewMonth] + ' ' + startViewYear"></span>
                                                <button type="button" @click="nextMonth('start')"
                                                    class="p-1 rounded-md hover:bg-gray-100 dark:hover:bg-slate-700 text-gray-500 transition-colors"><svg
                                                        class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg></button>
                                            </div>
                                            <div class="grid grid-cols-7 mb-1.5">
                                                <template x-for="d in DAYS_SHORT" :key="d">
                                                    <div class="h-6 flex items-center justify-center text-[10px] uppercase font-semibold text-gray-400 dark:text-gray-500"
                                                        x-text="d"></div>
                                                </template>
                                            </div>
                                            <div class="grid grid-cols-7 gap-y-1">
                                                <template x-for="day in startDays" :key="day.key">
                                                    <div class="flex items-center justify-center">
                                                        <button x-show="!day.pad" type="button"
                                                            @click="selectStart(day)" :class="dayClass(day, 'start')"
                                                            :title="day.holiday ? day.holiday.name : ''"
                                                            x-text="day.d"></button>
                                                        <span x-show="day.pad" class="w-7 h-7 block"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    @error('start_date')
                                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Tanggal Selesai --}}
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal
                                        Selesai</label>
                                    <div class="relative" @click.outside="endOpen = false">
                                        <button type="button" @click="startDate && (endOpen = !endOpen)"
                                            :class="[!startDate ?
                                                'opacity-50 cursor-not-allowed bg-gray-50 dark:bg-slate-800 border-gray-200 dark:border-slate-700' :
                                                endOpen ?
                                                'ring-2 ring-primary-500 border-primary-500 bg-white dark:bg-slate-900' :
                                                'bg-white dark:bg-slate-900 border-gray-300 dark:border-slate-600 hover:border-gray-400 dark:hover:border-gray-500'
                                            ]"
                                            class="relative w-full flex items-center justify-between px-3.5 py-2 border rounded-lg shadow-sm text-sm text-left focus:outline-none transition-all duration-200">
                                            <span
                                                :class="endDate ? 'text-gray-900 dark:text-gray-100 font-medium' :
                                                    'text-gray-400 dark:text-gray-500'"
                                                x-text="endDate ? formatDisplay(endDate) : (startDate ? 'Pilih tanggal selesai' : 'Pilih tanggal mulai dulu')"></span>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                        <input type="hidden" name="end_date" :value="endDate">

                                        {{-- Popup Kalender Selesai --}}
                                        <div x-show="endOpen" x-transition
                                            class="absolute left-0 top-full mt-1.5 z-50 bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700 rounded-xl shadow-xl p-3 w-64 origin-top-left"
                                            style="display:none">
                                            <div class="flex items-center justify-between mb-3">
                                                <button type="button" @click="prevMonth('end')"
                                                    class="p-1 rounded-md hover:bg-gray-100 dark:hover:bg-slate-700 text-gray-500 transition-colors"><svg
                                                        class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 19l-7-7 7-7" />
                                                    </svg></button>
                                                <span class="text-sm font-bold text-gray-800 dark:text-gray-100"
                                                    x-text="MONTHS[endViewMonth] + ' ' + endViewYear"></span>
                                                <button type="button" @click="nextMonth('end')"
                                                    class="p-1 rounded-md hover:bg-gray-100 dark:hover:bg-slate-700 text-gray-500 transition-colors"><svg
                                                        class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg></button>
                                            </div>
                                            <div class="grid grid-cols-7 mb-1.5">
                                                <template x-for="d in DAYS_SHORT" :key="d">
                                                    <div class="h-6 flex items-center justify-center text-[10px] uppercase font-semibold text-gray-400 dark:text-gray-500"
                                                        x-text="d"></div>
                                                </template>
                                            </div>
                                            <div class="grid grid-cols-7 gap-y-1">
                                                <template x-for="day in endDays" :key="day.key">
                                                    <div class="flex items-center justify-center">
                                                        <button x-show="!day.pad" type="button"
                                                            @click="selectEnd(day)" :class="dayClass(day, 'end')"
                                                            :title="day.holiday ? day.holiday.name : ''"
                                                            x-text="day.d"></button>
                                                        <span x-show="day.pad" class="w-7 h-7 block"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    @error('end_date')
                                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Legenda & Alerts --}}
                            <div class="mt-4 space-y-2.5">
                                {{-- Legenda (Hanya Cuti Biasa) --}}
                                <div x-show="!isSickLeave" x-transition
                                    class="flex flex-wrap items-center gap-3 p-2.5 rounded-lg bg-gray-50 dark:bg-slate-800/80 border border-gray-200 dark:border-slate-700 text-xs text-gray-600 dark:text-gray-400">
                                    <span class="font-bold text-gray-800 dark:text-gray-200">Indikator
                                        Tanggal:</span>
                                    <span class="flex items-center gap-1"><span
                                            class="w-2.5 h-2.5 rounded-full bg-orange-100 border border-orange-300 dark:bg-orange-900/40 dark:border-orange-700"></span>
                                        Zona Mendadak</span>
                                    <span class="flex items-center gap-1"><span
                                            class="w-2.5 h-2.5 rounded-full bg-red-50 border border-red-200 dark:bg-red-900/30 dark:border-red-800"></span>
                                        Libur Nasional</span>
                                    <span class="flex items-center gap-1"><span
                                            class="w-2.5 h-2.5 rounded-full bg-amber-50 border border-amber-200 dark:bg-amber-900/30 dark:border-amber-800"></span>
                                        Cuti Bersama</span>
                                </div>

                                {{-- Kalkulasi Durasi --}}
                                <div x-show="workdays > 0" x-transition
                                    class="flex items-center gap-2.5 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-lg">
                                    <div
                                        class="p-1.5 bg-blue-100 dark:bg-blue-900/50 rounded-md text-blue-600 dark:text-blue-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-blue-900 dark:text-blue-200">Total Durasi
                                            Cuti</p>
                                        <p class="text-xs text-blue-700 dark:text-blue-300">
                                            <span class="font-bold text-sm" x-text="workdays"></span> hari kerja
                                            efektif terpilih.
                                        </p>
                                    </div>
                                </div>

                                {{-- Warning: Libur --}}
                                <div x-show="holidaysInRange > 0" x-transition
                                    class="flex items-start gap-2.5 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                                    <svg class="w-4 h-4 flex-shrink-0 text-amber-500 mt-0.5" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-xs text-amber-800 dark:text-amber-300 leading-relaxed">
                                        Rentang ini mencakup <span class="font-bold" x-text="holidaysInRange"></span>
                                        hari libur yang <span class="font-semibold underline">tidak akan
                                            memotong</span> kuota Anda.
                                    </p>
                                </div>

                                {{-- Warning: Mendadak --}}
                                <div x-show="isMendadak" x-transition
                                    class="flex items-start gap-2.5 p-3 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg">
                                    <svg class="w-4 h-4 flex-shrink-0 text-orange-500 mt-0.5" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                    </svg>
                                    <p class="text-xs text-orange-800 dark:text-orange-300 leading-relaxed">
                                        <span class="font-bold">Pengajuan Mendadak!</span> Jarak kurang dari
                                        1 minggu. Sistem akan mencatat sebagai cuti mendadak.
                                    </p>
                                </div>

                                {{-- Warning: Tidak ada hari kerja --}}
                                <div x-show="startDate && endDate && workdays === 0" x-transition
                                    class="flex items-start gap-2.5 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                    <svg class="w-4 h-4 flex-shrink-0 text-red-500 mt-0.5" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-xs text-red-800 dark:text-red-300">
                                        Rentang tanggal yang dipilih seluruhnya adalah hari libur. Silakan pilih rentang
                                        tanggal lain.
                                    </p>
                                </div>
                            </div>
                        </section>

                        <hr class="border-gray-100 dark:border-slate-700">

                        {{-- ── BAGIAN 3: PERSONIL TERLIBAT (Atasan & Pengganti) ──────── --}}
                        @if ($requiresReplacement || $requiresAtasan)
                            <section>
                                <h3
                                    class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    Personil Terlibat
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    @if ($requiresAtasan)
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Atasan
                                                Langsung (Approver)</label>
                                            <x-select-dropdown name="atasan_id" label="" :options="$atasanList->map(
                                                fn($u) => [
                                                    'id' => $u->id,
                                                    'name' => strtoupper($u->name . ' (' . $u->role . ')'),
                                                ],
                                            )"
                                                :selected="old('atasan_id')" placeholder="-- Cari & Pilih Atasan --"
                                                searchable="true" />
                                            @error('atasan_id')
                                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">
                                                    {{ $message }}</p>
                                            @enderror
                                        </div>
                                    @endif

                                    @if ($requiresReplacement)
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rekan
                                                Pengganti (Backup)</label>
                                            <x-select-dropdown name="pengganti_id" label="" :options="$penggantiList->map(
                                                fn($u) => [
                                                    'id' => $u->id,
                                                    'name' => strtoupper($u->name . ' (' . $u->role . ')'),
                                                ],
                                            )"
                                                :selected="old('pengganti_id')" placeholder="-- Cari & Pilih Rekan --"
                                                searchable="true" />
                                            @error('pengganti_id')
                                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">
                                                    {{ $message }}</p>
                                            @enderror
                                        </div>
                                    @endif
                                </div>
                            </section>
                            <hr class="border-gray-100 dark:border-slate-700">
                        @endif

                        {{-- ── BAGIAN 4: DETAIL & LAMPIRAN ─────────────────────────────── --}}
                        <section>
                            <h3
                                class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Detail Keterangan
                            </h3>

                            <div class="space-y-4">
                                <div>
                                    <label for="alasan"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alasan
                                        Lengkap <span class="text-red-500">*</span></label>
                                    <textarea id="alasan" name="alasan" rows="3"
                                        placeholder="Jelaskan secara detail alasan permohonan cuti/izin Anda di sini..."
                                        class="block w-full px-3.5 py-2.5 bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-600 text-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm resize-none transition-colors">{{ old('alasan') }}</textarea>
                                    @error('alasan')
                                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Upload Bukti (Khusus Izin Sakit dgn Surat) --}}
                                <div x-show="showProof" x-transition>
                                    <label for="proof_image"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Upload
                                        Bukti (Surat Dokter) <span class="text-red-500">*</span></label>
                                    <div class="flex items-center gap-3">
                                        <input type="file" id="proof_image" name="proof_image" accept="image/*"
                                            @change="onProofChange($event)"
                                            class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-slate-700 dark:file:text-primary-400 dark:hover:file:bg-slate-600 transition-all border border-gray-300 dark:border-slate-600 rounded-lg cursor-pointer bg-white dark:bg-slate-900">

                                        <button type="button" x-show="proofPreviewUrl" @click="openImagePreview()"
                                            class="flex-shrink-0 inline-flex items-center px-3 py-2 border border-gray-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-lg text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-slate-700 focus:outline-none transition-colors">
                                            <svg class="w-4 h-4 mr-1.5 text-primary-500" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Preview
                                        </button>
                                    </div>
                                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Format didukung: JPG, PNG, GIF. Maksimal 2MB.
                                    </p>
                                    @error('proof_image')
                                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        {{-- ── ACTIONS ─────────────────────────────────────────────────── --}}
                        <div
                            class="pt-5 border-t border-gray-100 dark:border-slate-700 flex items-center justify-end gap-3">
                            <a href="{{ route('cuti.index') }}"
                                class="px-4 py-2 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-lg font-medium text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 dark:focus:ring-slate-900 transition-all">
                                Batal
                            </a>
                            <button type="submit" x-data="{ submitting: false }"
                                x-on:click="if($el.closest('form').checkValidity()){ submitting = true; setTimeout(() => submitting = false, 5000); }"
                                class="inline-flex items-center justify-center px-5 py-2 bg-primary-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-slate-900 transition-all shadow-sm">
                                <span x-show="!submitting" class="flex items-center gap-2">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                                    </svg>
                                    Kirim Pengajuan
                                </span>
                                <span x-show="submitting" style="display: none;" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Memproses...
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── MODAL ERROR (Kuota Habis, dsb) ────────────────────────────────────────── --}}
    @if ($errors->has('msg'))
        <div x-data="{ show: true }" x-show="show" x-transition
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div @click.outside="show = false"
                class="bg-white dark:bg-slate-800 p-5 rounded-xl shadow-2xl max-w-sm w-full transform transition-all">
                <div class="flex items-center gap-3">
                    <div
                        class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Gagal Mengajukan</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $errors->first('msg') }}</p>
                    </div>
                </div>
                <div class="mt-5">
                    <button @click="show = false"
                        class="w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-gray-800 dark:text-gray-200 rounded-lg font-medium text-sm transition-colors">
                        Mengerti, Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── MODAL PREVIEW GAMBAR BUKTI ────────────────────────────────────────────── --}}
    <div x-data="{ open: false, src: '' }" x-on:open-image-preview.window="open = true; src = $event.detail.src" x-show="open"
        x-transition @click.self="open = false"
        class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm flex items-center justify-center z-[60] p-4"
        style="display:none">
        <div class="bg-white dark:bg-slate-800 p-2 rounded-xl shadow-2xl max-w-2xl w-full relative">
            <button @click="open = false"
                class="absolute -top-4 -right-4 bg-white dark:bg-slate-700 text-gray-500 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white p-2 rounded-full shadow-lg transition-colors border border-gray-100 dark:border-slate-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <img :src="src" alt="Preview Bukti" class="w-full max-h-[80vh] object-contain rounded-lg">
        </div>
    </div>

    {{-- ── SCRIPT ALPINE.JS ──────────────────────────────────────────────────────── --}}
    <script>
        function leaveForm() {
            const _now = new Date();
            _now.setHours(0, 0, 0, 0);

            const _yesterday = new Date(_now);
            _yesterday.setDate(_yesterday.getDate() - 1);
            const _mendadakEnd = new Date(_now);
            _mendadakEnd.setDate(_mendadakEnd.getDate() + 6);

            const toYMD = (d) => [
                d.getFullYear(),
                String(d.getMonth() + 1).padStart(2, '0'),
                String(d.getDate()).padStart(2, '0'),
            ].join('-');

            const C = {
                todayStr: toYMD(_now),
                yesterdayStr: toYMD(_yesterday),
                mendadakEndStr: toYMD(_mendadakEnd),
            };

            const MONTHS = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September',
                'Oktober', 'November', 'Desember'
            ];
            const DAYS_SHORT = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
            const DAYS_LONG = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

            return {
                showForm: {{ $errors->any() ? 'true' : 'false' }},
                isSickLeave: false,
                showProof: false,
                holidays: @json($holidayMap ?? []),
                startDate: @json(old('start_date', '')),
                endDate: @json(old('end_date', '')),
                startOpen: false,
                endOpen: false,
                startViewYear: _now.getFullYear(),
                startViewMonth: _now.getMonth(),
                endViewYear: _now.getFullYear(),
                endViewMonth: _now.getMonth(),
                proofPreviewUrl: '',
                MONTHS,
                DAYS_SHORT,

                get isMendadak() {
                    return !this.isSickLeave && !!this.startDate && this.startDate <= C.mendadakEndStr;
                },

                get workdays() {
                    if (!this.startDate || !this.endDate || this.endDate < this.startDate) return 0;
                    let n = 0;
                    const cur = new Date(this.startDate + 'T00:00:00');
                    const end = new Date(this.endDate + 'T00:00:00');
                    while (cur <= end) {
                        const d = cur.getDay();
                        if (d !== 0 && d !== 6 && !this.holidays[toYMD(cur)]) n++;
                        cur.setDate(cur.getDate() + 1);
                    }
                    return n;
                },

                get holidaysInRange() {
                    if (!this.startDate || !this.endDate || this.endDate < this.startDate) return 0;
                    let n = 0;
                    const cur = new Date(this.startDate + 'T00:00:00');
                    const end = new Date(this.endDate + 'T00:00:00');
                    while (cur <= end) {
                        if (this.holidays[toYMD(cur)]) n++;
                        cur.setDate(cur.getDate() + 1);
                    }
                    return n;
                },

                get startDays() {
                    return this._buildDays(this.startViewYear, this.startViewMonth, this.startDate);
                },
                get endDays() {
                    return this._buildDays(this.endViewYear, this.endViewMonth, this.endDate);
                },

                _buildDays(year, month, selectedDate) {
                    const cells = [];
                    const first = new Date(year, month, 1);
                    const last = new Date(year, month + 1, 0);
                    const padStart = (first.getDay() + 6) % 7;

                    for (let i = 0; i < padStart; i++) cells.push({
                        pad: true,
                        key: `ps${month}-${i}`
                    });

                    for (let d = 1; d <= last.getDate(); d++) {
                        const date = new Date(year, month, d);
                        const dow = date.getDay();
                        const dStr = toYMD(date);
                        const isWeekend = dow === 0 || dow === 6;
                        const isDisabled = isWeekend || (this.isSickLeave && dStr >= C.todayStr);

                        let zone = 'normal';
                        if (!this.isSickLeave) {
                            if (dStr < C.todayStr) zone = 'past';
                            else if (dStr <= C.mendadakEndStr) zone = 'mendadak';
                        }

                        cells.push({
                            pad: false,
                            key: dStr,
                            d,
                            dStr,
                            isWeekend,
                            isDisabled,
                            zone,
                            isSelected: dStr === selectedDate,
                            isToday: dStr === C.todayStr,
                            holiday: this.holidays[dStr] || null
                        });
                    }

                    const rem = (7 - (cells.length % 7)) % 7;
                    for (let i = 0; i < rem; i++) cells.push({
                        pad: true,
                        key: `pe${month}-${i}`
                    });

                    return cells;
                },

                selectStart(day) {
                    if (!day || day.pad || day.isDisabled) return;
                    this.startDate = day.dStr;
                    if (this.endDate && this.endDate < this.startDate) this.endDate = '';
                    this.startOpen = false;
                },

                selectEnd(day) {
                    if (!day || day.pad || day.isDisabled) return;
                    if (this.startDate && day.dStr < this.startDate) return;
                    this.endDate = day.dStr;
                    this.endOpen = false;
                },

                prevMonth(p) {
                    p === 'start' ? (this.startViewMonth === 0 ? (this.startViewMonth = 11, this.startViewYear--) : this
                        .startViewMonth--) : (this.endViewMonth === 0 ? (this.endViewMonth = 11, this.endViewYear--) :
                        this.endViewMonth--);
                },
                nextMonth(p) {
                    p === 'start' ? (this.startViewMonth === 11 ? (this.startViewMonth = 0, this.startViewYear++) : this
                        .startViewMonth++) : (this.endViewMonth === 11 ? (this.endViewMonth = 0, this.endViewYear++) :
                        this.endViewMonth++);
                },

                formatDisplay(dateStr) {
                    if (!dateStr) return '';
                    const d = new Date(dateStr + 'T00:00:00');
                    return `${DAYS_LONG[d.getDay()]}, ${d.getDate()} ${MONTHS[d.getMonth()]} ${d.getFullYear()}`;
                },

                dayClass(day, picker) {
                    // Ukuran tombol tanggal kalender diubah ke w-7 h-7 dan ditengah (mx-auto)
                    const base =
                        'w-7 h-7 mx-auto rounded-full flex items-center justify-center text-xs font-medium transition-all duration-150 select-none';
                    const isOutOfRange = picker === 'end' && this.startDate && day.dStr < this.startDate;

                    if (day.isDisabled || isOutOfRange)
                        return `${base} text-gray-300 dark:text-gray-600 cursor-not-allowed`;
                    if (day.isSelected) return `${base} bg-primary-600 text-white shadow-sm cursor-pointer`;

                    const ring = day.isToday ?
                        ' ring-2 ring-primary-300 ring-offset-1 dark:ring-offset-slate-800 font-bold' : '';

                    if (day.holiday) {
                        if (day.holiday.type === 'national_holiday')
                            return `${base}${ring} bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 cursor-pointer`;
                        return `${base}${ring} bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/40 cursor-pointer`;
                    }

                    if (!this.isSickLeave) {
                        if (day.zone === 'past')
                            return `${base}${ring} bg-gray-50 dark:bg-slate-700/50 text-gray-400 dark:text-gray-500 hover:bg-gray-200 dark:hover:bg-slate-600 cursor-pointer`;
                        if (day.zone === 'mendadak')
                            return `${base}${ring} bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-400 hover:bg-orange-100 dark:hover:bg-orange-900/40 cursor-pointer`;
                    }

                    return `${base}${ring} text-gray-700 dark:text-gray-300 hover:bg-primary-50 dark:hover:bg-slate-700 cursor-pointer`;
                },

                onLeaveTypeChange(selectEl) {
                    const name = (selectEl.options[selectEl.selectedIndex]?.text || '').toLowerCase();
                    const wasSick = this.isSickLeave;
                    const isSickWith = name.includes('izin sakit dengan surat dokter');
                    const isSickOut = name.includes('izin sakit tanpa surat dokter');

                    this.isSickLeave = isSickWith || isSickOut;
                    this.showProof = isSickWith;
                    this.showForm = !!selectEl.value;

                    if (wasSick !== this.isSickLeave) {
                        this.startDate = '';
                        this.endDate = '';
                    }

                    this.startViewYear = _now.getFullYear();
                    this.startViewMonth = _now.getMonth();
                    this.endViewYear = _now.getFullYear();
                    this.endViewMonth = _now.getMonth();
                },

                onProofChange(e) {
                    const file = e.target.files?.[0];
                    this.proofPreviewUrl = file ? URL.createObjectURL(file) : '';
                },

                openImagePreview() {
                    this.$dispatch('open-image-preview', {
                        src: this.proofPreviewUrl
                    });
                },
            };
        }
    </script>
</x-app-layout>
