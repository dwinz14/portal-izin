<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2
                    class="border-l-[5px] border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Ajukan Cuti') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Isi formulir di bawah ini untuk mengajukan cuti Anda.
                </p>
            </div>
        </div>
    </x-slot>


    <div class="flex justify-center bg-gray-50 dark:bg-gray-900 py-2 px-4 sm:px-6 lg:px-8 drop-shadow-xl/50"
        x-data="leaveForm()">

        <div class="w-full max-w-5x2">
            <div
                class="bg-white dark:bg-slate-800 shadow-xl hover:shadow-2xl transition-all duration-300 rounded-xl p-4 sm:p-6 lg:p-8">
                <form method="POST" action="{{ route('cuti.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    {{-- ── Jenis Cuti ──────────────────────────────────────────── --}}
                    <div
                        class="bg-gradient-to-r from-primary-50 to-primary-100 dark:from-slate-700 dark:to-slate-600 p-3 rounded-lg">
                        <label for="leave_type_id"
                            class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">
                            <svg class="w-4 h-4 inline mr-1 text-primary-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Pilih Jenis Cuti
                        </label>
                        <select id="leave_type_id" name="leave_type_id" @change="onLeaveTypeChange($event.target)"
                            class="block w-full px-3 py-2 bg-white dark:bg-slate-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm font-medium">
                            <option value="">-- Pilih Jenis Cuti --</option>
                            @foreach ($leaveTypes as $type)
                                <option value="{{ $type->id }}" @selected(old('leave_type_id') == $type->id)>
                                    {{ strtoupper($type->name) }}
                                    @if ($type->quota > 0 && isset($userLeaveBalances[$type->id]))
                                        (Sisa: {{ $userLeaveBalances[$type->id]->remaining }} hari)
                                    @elseif ($type->quota > 0)
                                        (Kuota: {{ $type->quota }} hari)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('leave_type_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- ── Form Fields ──────────────────────────────────────────── --}}
                    <div x-show="showForm" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0" class="space-y-5">

                        {{-- Legenda zona warna (hanya cuti biasa) --}}
                        <div x-show="!isSickLeave" x-transition
                            class="flex flex-wrap items-center gap-x-5 gap-y-2 px-3 py-2 rounded-lg bg-gray-50 dark:bg-slate-700/60 border border-gray-200 dark:border-slate-600 text-xs text-gray-600 dark:text-gray-400">
                            <span class="font-semibold text-gray-700 dark:text-gray-300">Keterangan warna
                                tanggal:</span>
                            <span class="flex items-center gap-1.5">
                                <span
                                    class="inline-block w-4 h-4 rounded-full bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-500"></span>
                                Tanggal lampau
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span
                                    class="inline-block w-4 h-4 rounded-full bg-orange-50 dark:bg-orange-900/30 border border-orange-300 dark:border-orange-700"></span>
                                Zona mendadak (hari ini s/d H+6)
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span
                                    class="inline-block w-4 h-4 rounded-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-500"></span>
                                Normal
                            </span>
                        </div>

                        {{-- Grid utama --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                            {{-- Kolom Kiri: Pengganti & Atasan --}}
                            <div class="space-y-3">
                                @if ($requiresReplacement)
                                    <div class="w-3/4">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            <svg class="w-4 h-4 inline mr-1 text-primary-600" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            Pengganti
                                        </label>
                                        <x-select-dropdown name="pengganti_id" label="" :options="$penggantiList->map(
                                            fn($u) => [
                                                'id' => $u->id,
                                                'name' => strtoupper($u->name . ' (' . $u->role . ')'),
                                            ],
                                        )"
                                            :selected="old('pengganti_id')" placeholder="-- Pilih Pengganti --" searchable="true" />
                                        @error('pengganti_id')
                                        @enderror
                                    </div>
                                @endif

                                @if ($requiresAtasan)
                                    <div class="w-3/4">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            <svg class="w-4 h-4 inline mr-1 text-primary-600" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Atasan Langsung
                                        </label>
                                        <x-select-dropdown name="atasan_id" label="" :options="$atasanList->map(
                                            fn($u) => [
                                                'id' => $u->id,
                                                'name' => strtoupper($u->name . ' (' . $u->role . ')'),
                                            ],
                                        )"
                                            :selected="old('atasan_id')" placeholder="-- Pilih Atasan --" searchable="true" />
                                        @error('atasan_id')
                                        @enderror
                                    </div>
                                @endif
                            </div>

                            {{-- Kolom Kanan: Date Pickers --}}
                            <div class="space-y-4">

                                {{-- ── Tanggal Mulai ──────────────────────────── --}}
                                <div class="w-3/4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        <svg class="w-4 h-4 inline mr-1 text-primary-600" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Tanggal Mulai
                                    </label>

                                    {{-- Wrapper relative: click.outside menutup popup --}}
                                    <div class="relative" @click.outside="startOpen = false">

                                        {{-- Trigger: tampil seperti input --}}
                                        <button type="button" @click="startOpen = !startOpen"
                                            :class="startOpen
                                                ?
                                                'ring-2 ring-primary-500 border-primary-500' :
                                                'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500'"
                                            class="relative w-full flex items-center justify-between px-3 py-2 bg-white dark:bg-slate-700 border rounded-md shadow-sm text-sm text-left focus:outline-none transition-all duration-150">
                                            <span
                                                :class="startDate ? 'text-gray-900 dark:text-gray-100' :
                                                    'text-gray-400 dark:text-gray-500'"
                                                x-text="startDate ? formatDisplay(startDate) : 'Pilih tanggal mulai'"></span>
                                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0 ml-2" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </button>

                                        {{-- Hidden input untuk form submission --}}
                                        <input type="hidden" name="start_date" :value="startDate">

                                        {{-- Kalender popup --}}
                                        <div x-show="startOpen" x-transition:enter="transition ease-out duration-150"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-100"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95"
                                            class="absolute left-0 top-full mt-1 z-50 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-600 rounded-xl shadow-2xl p-3 w-72 origin-top-left"
                                            style="display:none">

                                            {{-- Navigasi bulan --}}
                                            <div class="flex items-center justify-between mb-3 px-1">
                                                <button type="button" @click="prevMonth('start')"
                                                    class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 text-gray-500 dark:text-gray-400 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 19l-7-7 7-7" />
                                                    </svg>
                                                </button>
                                                <span class="text-sm font-semibold text-gray-800 dark:text-gray-200"
                                                    x-text="MONTHS[startViewMonth] + ' ' + startViewYear"></span>
                                                <button type="button" @click="nextMonth('start')"
                                                    class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 text-gray-500 dark:text-gray-400 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </button>
                                            </div>

                                            {{-- Header nama hari --}}
                                            <div class="grid grid-cols-7 mb-1">
                                                <template x-for="d in DAYS_SHORT" :key="d">
                                                    <div class="h-7 flex items-center justify-center text-xs font-semibold text-gray-400 dark:text-gray-500"
                                                        x-text="d"></div>
                                                </template>
                                            </div>

                                            {{-- Grid hari --}}
                                            <div class="grid grid-cols-7 gap-y-1">
                                                <template x-for="day in startDays" :key="day.key">
                                                    <div class="flex items-center justify-center">
                                                        <button x-show="!day.pad" type="button"
                                                            @click="selectStart(day)" :class="dayClass(day, 'start')"
                                                            x-text="day.d">
                                                        </button>
                                                        <span x-show="day.pad" class="w-9 h-9 block"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Badge Mendadak --}}
                                    <div x-show="isMendadak" x-transition
                                        class="mt-2 flex items-start gap-2 px-3 py-2 rounded-lg bg-orange-50 dark:bg-orange-900/20 border border-orange-300 dark:border-orange-700">
                                        <svg class="w-4 h-4 flex-shrink-0 mt-0.5 text-orange-500" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                        </svg>
                                        <span class="text-xs text-orange-700 dark:text-orange-300">
                                            <span class="font-bold">⚡ Pengajuan Mendadak</span>
                                            — Kurang dari 1 minggu dari hari ini. Akan dicatat sebagai
                                            <span class="font-semibold">cuti/izin mendadak</span>.
                                        </span>
                                    </div>

                                    @error('start_date')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">Anda harus memilih tanggal
                                            mulai cuti</p>
                                    @enderror
                                </div>

                                {{-- ── Tanggal Selesai ─────────────────────────── --}}
                                <div class="w-3/4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        <svg class="w-4 h-4 inline mr-1 text-primary-600" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Tanggal Selesai
                                    </label>

                                    <div class="relative" @click.outside="endOpen = false">
                                        <button type="button" @click="startDate && (endOpen = !endOpen)"
                                            :class="[
                                                !startDate ?
                                                'opacity-50 cursor-not-allowed bg-gray-50 dark:bg-slate-800 border-gray-200 dark:border-gray-700' :
                                                endOpen ?
                                                'ring-2 ring-primary-500 border-primary-500 bg-white dark:bg-slate-700' :
                                                'bg-white dark:bg-slate-700 border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500'
                                            ]"
                                            class="relative w-full flex items-center justify-between px-3 py-2 border rounded-md shadow-sm text-sm text-left focus:outline-none transition-all duration-150">
                                            <span
                                                :class="endDate ? 'text-gray-900 dark:text-gray-100' :
                                                    'text-gray-400 dark:text-gray-500'"
                                                x-text="endDate ? formatDisplay(endDate) : (startDate ? 'Pilih tanggal selesai' : 'Pilih tanggal mulai dulu')"></span>
                                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0 ml-2" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </button>

                                        <input type="hidden" name="end_date" :value="endDate">

                                        <div x-show="endOpen" x-transition:enter="transition ease-out duration-150"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-100"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95"
                                            class="absolute left-0 top-full mt-1 z-50 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-600 rounded-xl shadow-2xl p-3 w-72 origin-top-left"
                                            style="display:none">

                                            {{-- Navigasi bulan --}}
                                            <div class="flex items-center justify-between mb-3 px-1">
                                                <button type="button" @click="prevMonth('end')"
                                                    class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 text-gray-500 dark:text-gray-400 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 19l-7-7 7-7" />
                                                    </svg>
                                                </button>
                                                <span class="text-sm font-semibold text-gray-800 dark:text-gray-200"
                                                    x-text="MONTHS[endViewMonth] + ' ' + endViewYear"></span>
                                                <button type="button" @click="nextMonth('end')"
                                                    class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 text-gray-500 dark:text-gray-400 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </button>
                                            </div>

                                            {{-- Header nama hari --}}
                                            <div class="grid grid-cols-7 mb-1">
                                                <template x-for="d in DAYS_SHORT" :key="d">
                                                    <div class="h-7 flex items-center justify-center text-xs font-semibold text-gray-400 dark:text-gray-500"
                                                        x-text="d"></div>
                                                </template>
                                            </div>

                                            {{-- Grid hari --}}
                                            <div class="grid grid-cols-7 gap-y-1">
                                                <template x-for="day in endDays" :key="day.key">
                                                    <div class="flex items-center justify-center">
                                                        <button x-show="!day.pad" type="button"
                                                            @click="selectEnd(day)" :class="dayClass(day, 'end')"
                                                            x-text="day.d">
                                                        </button>
                                                        <span x-show="day.pad" class="w-9 h-9 block"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    @error('end_date')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">Anda harus memilih tanggal
                                            selesai cuti</p>
                                    @enderror
                                </div>

                                {{-- Preview durasi --}}
                                <div x-show="workdays > 0" x-transition
                                    class="text-sm text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-slate-700 px-3 py-2 rounded-md flex items-center gap-2">
                                    <svg class="w-4 h-4 flex-shrink-0 text-primary-500" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Durasi cuti:
                                    <span class="font-semibold text-primary-600 dark:text-primary-400"
                                        x-text="workdays"></span>
                                    hari kerja
                                </div>
                            </div>
                        </div>

                        {{-- Alasan --}}
                        <div>
                            <label for="alasan"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <svg class="w-4 h-4 inline mr-1 text-primary-600" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Alasan Cuti
                            </label>
                            <textarea id="alasan" name="alasan" rows="3" placeholder="Jelaskan alasan cuti Anda secara detail..."
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-gray-200 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm resize-none">{{ old('alasan') }}</textarea>
                            @error('alasan')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">Anda harus mengisi alasan cuti</p>
                            @enderror
                        </div>

                        {{-- Bukti Gambar --}}
                        <div x-show="showProof" x-transition>
                            <label for="proof_image"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <svg class="w-4 h-4 inline mr-1 text-primary-600" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Bukti Gambar <span class="text-red-500">*</span>
                            </label>
                            <input type="file" id="proof_image" name="proof_image" accept="image/*"
                                @change="onProofChange($event)"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-gray-200 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                            <button type="button" x-show="proofPreviewUrl" @click="openImagePreview()"
                                class="mt-2 text-primary-600 hover:text-primary-800 text-sm font-medium flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Lihat Gambar
                            </button>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Format: JPG, PNG, GIF. Maksimal
                                2MB.</p>
                            @error('proof_image')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">Anda harus menyertakan bukti surat
                                    dokter</p>
                            @enderror
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="flex items-center justify-end space-x-3 pt-2">
                            <a href="{{ route('cuti.index') }}"
                                class="inline-flex items-center justify-center px-4 py-2 bg-red-400 dark:bg-slate-600 border border-gray-300 dark:border-gray-500 rounded-full font-semibold text-xs text-slate-50 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-rose-300 dark:hover:bg-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:ring-offset-slate-800 transition ease-in-out duration-150">
                                Batal
                            </a>
                            <x-primary-button x-data="{ submitting: false }"
                                x-on:click="submitting = true; setTimeout(() => submitting = false, 5000)">
                                <span x-show="!submitting">Simpan</span>
                                <span x-show="submitting">
                                    <svg class="animate-spin h-4 w-4 inline" ...>...</svg>
                                    Memproses...
                                </span>
                            </x-primary-button>

                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- Modal error kuota --}}
    @if ($errors->has('msg'))
        <div x-data="{ show: true }" x-show="show" x-transition
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-slate-800 p-4 rounded-lg shadow-lg max-w-md w-full mx-4">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <h3 class="text-lg font-semibold text-red-600 dark:text-red-400">Peringatan</h3>
                </div>
                <p class="mt-4 text-gray-700 dark:text-gray-300">{{ $errors->first('msg') }}</p>
                <div class="mt-6 flex justify-end">
                    <button @click="show = false"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition ease-in-out duration-150">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal preview gambar bukti --}}
    <div x-data="{ open: false, src: '' }" x-on:open-image-preview.window="open = true; src = $event.detail.src" x-show="open"
        x-transition @click.self="open = false"
        class="fixed inset-0 bg-black/60 flex items-center justify-center z-50" style="display:none">
        <div class="bg-white dark:bg-slate-800 p-4 rounded-lg shadow-lg max-w-lg w-full relative mx-4">
            <button @click="open = false"
                class="absolute top-2 right-2 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100 p-1 rounded-full hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <img :src="src" alt="Preview" class="w-full rounded-md mt-4">
        </div>
    </div>

    <script>
        /**
         * ================================================================
         * leaveForm() — Alpine.js component
         *
         * Mengelola seluruh form pengajuan cuti/izin:
         * - Visibilitas form berdasarkan jenis cuti
         * - Custom date picker dengan pewarnaan zona mendadak
         * - Deteksi & badge cuti mendadak (tanggal <= H+6)
         * - Kalkulasi hari kerja otomatis
         * - Preview gambar bukti
         *
         * Tidak ada dependency eksternal. Berjalan di atas Alpine.js
         * dan Tailwind yang sudah tersedia dari Breeze.
         * ================================================================
         */
        function leaveForm() {

            // ── Hitung konstanta tanggal sekali saja ─────────────────────────
            const _now = new Date();
            _now.setHours(0, 0, 0, 0);

            const _yesterday = new Date(_now);
            _yesterday.setDate(_yesterday.getDate() - 1);
            const _mendadakEnd = new Date(_now);
            _mendadakEnd.setDate(_mendadakEnd.getDate() + 6);

            /**
             * Format Date → 'YYYY-MM-DD' menggunakan nilai lokal,
             * bukan UTC, untuk menghindari off-by-one akibat timezone.
             */
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

            const MONTHS = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            const DAYS_SHORT = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
            const DAYS_LONG = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

            return {

                // ── State ─────────────────────────────────────────────────────
                showForm: {{ $errors->any() ? 'true' : 'false' }},
                isSickLeave: false,
                showProof: false,

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

                // ── Computed ──────────────────────────────────────────────────

                /**
                 * Mendadak: bukan izin sakit DAN start_date ≤ H+6
                 * Contoh: pengajuan 8 Apr → mendadak s/d 14 Apr; normal ab 15 Apr
                 */
                get isMendadak() {
                    return !this.isSickLeave &&
                        !!this.startDate &&
                        this.startDate <= C.mendadakEndStr;
                },

                /** Hari kerja (Senin–Jumat) antara start dan end */
                get workdays() {
                    if (!this.startDate || !this.endDate || this.endDate < this.startDate) return 0;
                    let n = 0;
                    const cur = new Date(this.startDate + 'T00:00:00');
                    const end = new Date(this.endDate + 'T00:00:00');
                    while (cur <= end) {
                        const d = cur.getDay();
                        if (d !== 0 && d !== 6) n++;
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

                // ── Builder kalender ──────────────────────────────────────────
                /**
                 * Menghasilkan array sel kalender untuk satu bulan.
                 * Sel padding (awal & akhir) menggunakan { pad:true }
                 * agar grid 7-kolom selalu penuh tanpa kolom kosong.
                 *
                 * Zone (hanya untuk cuti biasa):
                 *   'past'     → sebelum hari ini  (abu-abu)
                 *   'mendadak' → hari ini – H+6    (oranye)
                 *   'normal'   → H+7 ke atas       (putih)
                 */
                _buildDays(year, month, selectedDate) {
                    const cells = [];
                    const first = new Date(year, month, 1);
                    const last = new Date(year, month + 1, 0);

                    // Padding awal (Senin = 0 … Minggu = 6)
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

                        // Disabled: weekend, atau izin sakit hanya boleh tanggal lampau
                        const isDisabled = isWeekend ||
                            (this.isSickLeave && dStr >= C.todayStr);

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
                        });
                    }

                    // Padding akhir agar total selalu kelipatan 7
                    const rem = (7 - (cells.length % 7)) % 7;
                    for (let i = 0; i < rem; i++) cells.push({
                        pad: true,
                        key: `pe${month}-${i}`
                    });

                    return cells;
                },

                // ── Seleksi ───────────────────────────────────────────────────
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

                // ── Navigasi bulan ────────────────────────────────────────────
                prevMonth(p) {
                    if (p === 'start') {
                        this.startViewMonth === 0 ?
                            (this.startViewMonth = 11, this.startViewYear--) :
                            this.startViewMonth--;
                    } else {
                        this.endViewMonth === 0 ?
                            (this.endViewMonth = 11, this.endViewYear--) :
                            this.endViewMonth--;
                    }
                },

                nextMonth(p) {
                    if (p === 'start') {
                        this.startViewMonth === 11 ?
                            (this.startViewMonth = 0, this.startViewYear++) :
                            this.startViewMonth++;
                    } else {
                        this.endViewMonth === 11 ?
                            (this.endViewMonth = 0, this.endViewYear++) :
                            this.endViewMonth++;
                    }
                },

                // ── Format display di tombol trigger ──────────────────────────
                formatDisplay(dateStr) {
                    if (!dateStr) return '';
                    const d = new Date(dateStr + 'T00:00:00');
                    return `${DAYS_LONG[d.getDay()]}, ${d.getDate()} ${MONTHS[d.getMonth()]} ${d.getFullYear()}`;
                },

                // ── Class Tailwind per sel hari ───────────────────────────────
                /**
                 * Mengembalikan string class Tailwind untuk tombol hari di kalender.
                 * Urutan prioritas: disabled → selected → today → zone
                 */
                dayClass(day, picker) {
                    const base =
                        'w-9 h-9 rounded-full flex items-center justify-center text-xs font-medium transition-colors duration-150 select-none';

                    // Disabled (weekend atau di luar range)
                    const isOutOfRange = picker === 'end' && this.startDate && day.dStr < this.startDate;
                    if (day.isDisabled || isOutOfRange) {
                        return `${base} text-gray-300 dark:text-gray-600 cursor-not-allowed`;
                    }

                    // Selected
                    if (day.isSelected) {
                        return `${base} bg-primary-600 text-white shadow-md cursor-pointer`;
                    }

                    // Ring hari ini (belum dipilih)
                    const ring = day.isToday ?
                        ' ring-2 ring-primary-400 ring-offset-1 dark:ring-offset-slate-800' :
                        '';

                    // Zone coloring (hanya cuti biasa)
                    if (!this.isSickLeave) {
                        if (day.zone === 'past') {
                            return `${base}${ring} bg-gray-100 dark:bg-slate-700/50 text-gray-400 dark:text-gray-500 hover:bg-gray-200 dark:hover:bg-slate-600 cursor-pointer`;
                        }
                        if (day.zone === 'mendadak') {
                            return `${base}${ring} bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-400 border border-orange-200 dark:border-orange-800/50 hover:bg-orange-100 dark:hover:bg-orange-900/40 cursor-pointer`;
                        }
                    }

                    return `${base}${ring} text-gray-700 dark:text-gray-300 hover:bg-primary-50 dark:hover:bg-slate-600 cursor-pointer`;
                },

                // ── Event: ganti jenis cuti ───────────────────────────────────
                onLeaveTypeChange(selectEl) {
                    const name = (selectEl.options[selectEl.selectedIndex]?.text || '').toLowerCase();
                    const wasSick = this.isSickLeave;
                    const isSickWith = name.includes('izin sakit dengan surat dokter');
                    const isSickOut = name.includes('izin sakit tanpa surat dokter');

                    this.isSickLeave = isSickWith || isSickOut;
                    this.showProof = isSickWith;
                    this.showForm = !!selectEl.value;

                    // Reset tanggal saat beralih antara izin sakit & cuti biasa
                    if (wasSick !== this.isSickLeave) {
                        this.startDate = '';
                        this.endDate = '';
                    }

                    // Kembalikan kalender ke bulan berjalan
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
