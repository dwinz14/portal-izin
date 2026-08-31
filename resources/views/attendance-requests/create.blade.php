<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('kehadiran.index') }}"
                        class="text-xs font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 flex items-center transition">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali ke Riwayat Kehadiran
                    </a>
                </div>
                <h2
                    class="border-l-4 border-primary-700 pl-4 font-bold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Ajukan Pengajuan Kehadiran') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 pl-4">
                    Isi rincian penyesuaian waktu kehadiran dan pilih atasan langsung sebagai approver.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto" x-data="attendanceForm()">
        <div
            class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 shadow-sm rounded-2xl overflow-hidden">

            {{-- Form Header Banner --}}
            <div
                class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 bg-gray-50/70 dark:bg-slate-900/40 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-xl bg-primary-50 dark:bg-primary-950/60 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">Formulir Presensi & Kehadiran
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pilih jenis pengajuan di bawah untuk memulai
                            pengisian.</p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('kehadiran.store') }}" enctype="multipart/form-data"
                @submit="isSubmitting = true">
                @csrf

                {{-- Step 1: Pilihan Jenis Pengajuan (Interactive Card Grid) --}}
                <div class="p-6 border-b border-gray-100 dark:border-slate-700/60">
                    <label
                        class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-3">
                        Pilih Jenis Pengajuan <span class="text-red-500">*</span>
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        @php
                            $typesConfig = [
                                'late_arrival' => [
                                    'title' => 'Datang Terlambat',
                                    'desc' => 'Tiba melebihi jam masuk',
                                    'icon' =>
                                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />',
                                    'color' => 'amber',
                                ],
                                'early_departure' => [
                                    'title' => 'Pulang Lebih Awal',
                                    'desc' => 'Pulang sebelum jam usai',
                                    'icon' =>
                                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />',
                                    'color' => 'blue',
                                ],
                                'leave_during_work' => [
                                    'title' => 'Meninggalkan Kerja',
                                    'desc' => 'Keluar kantor sementara',
                                    'icon' =>
                                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />',
                                    'color' => 'purple',
                                ],
                                'update_attendance' => [
                                    'title' => 'Update Absensi',
                                    'desc' => 'Koreksi log presensi',
                                    'icon' =>
                                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />',
                                    'color' => 'emerald',
                                ],
                            ];
                        @endphp

                        @foreach ($typeLabels as $value => $label)
                            @php $cfg = $typesConfig[$value] ?? null; @endphp
                            <label
                                class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all duration-200"
                                :class="type === '{{ $value }}'
                                    ?
                                    'border-primary-600 bg-primary-50/50 dark:bg-primary-950/30 dark:border-primary-500 shadow-sm ring-2 ring-primary-500/10' :
                                    'border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 hover:border-gray-300 dark:hover:border-slate-600'">
                                <input type="radio" name="type" value="{{ $value }}" x-model="type"
                                    class="sr-only" required>

                                <div class="flex items-center justify-between mb-2.5">
                                    <div class="w-8 h-8 rounded-xl flex items-center justify-center"
                                        :class="type === '{{ $value }}' ? 'bg-primary-600 text-white' :
                                            'bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-gray-400'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            {!! $cfg['icon'] ??
                                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />' !!}
                                        </svg>
                                    </div>
                                    <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center"
                                        :class="type === '{{ $value }}' ? 'border-primary-600 bg-primary-600' :
                                            'border-gray-300 dark:border-slate-600'">
                                        <div class="w-1.5 h-1.5 rounded-full bg-white"
                                            x-show="type === '{{ $value }}'"></div>
                                    </div>
                                </div>

                                <div class="font-bold text-xs text-gray-800 dark:text-gray-100">{{ $label }}
                                </div>
                                <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 leading-snug">
                                    {{ $cfg['desc'] ?? '' }}</div>
                            </label>
                        @endforeach
                    </div>

                    @error('type')
                        <p class="mt-2 text-xs font-semibold text-red-600 dark:text-red-400 flex items-center">
                            <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror

                    {{-- Type Helper Info Banner --}}
                    <div x-show="type" x-cloak x-transition
                        class="mt-4 p-3.5 rounded-xl bg-blue-50/70 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800/60 flex items-start gap-3">
                        <div
                            class="p-1 rounded-lg bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="text-xs text-blue-900 dark:text-blue-200">
                            <span class="font-bold" x-text="typeInfo?.title"></span>: <span
                                x-text="typeInfo?.desc"></span>
                        </div>
                    </div>
                </div>

                {{-- Step 2: Form Details (Shown when Type Selected) --}}
                <div x-show="type" x-cloak x-transition class="p-6 space-y-6">

                    {{-- 1. Waktu Kehadiran --}}
                    <section class="space-y-4">
                        <div
                            class="flex items-center gap-2 pb-2 border-b border-gray-100 dark:border-slate-700/60 text-xs font-bold uppercase tracking-wider text-primary-700 dark:text-primary-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>1. Waktu Kehadiran</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Tanggal --}}
                            <div>
                                <label for="date"
                                    class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                    Tanggal Pengajuan <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="date" id="date" name="date"
                                        value="{{ old('date', now()->format('Y-m-d')) }}" required
                                        class="block w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-100 text-sm px-3.5 py-2.5 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition" />
                                </div>
                                @error('date')
                                    <p class="mt-1 text-xs font-semibold text-red-600 dark:text-red-400">
                                        {{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Waktu Mulai / Jam Tiba --}}
                            <div>
                                <label for="start_time"
                                    class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                    <span x-text="startTimeLabel"></span> <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="time" id="start_time" name="start_time" x-model="startTime"
                                        required
                                        class="block w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-100 text-sm px-3.5 py-2.5 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition" />
                                </div>
                                @error('start_time')
                                    <p class="mt-1 text-xs font-semibold text-red-600 dark:text-red-400">
                                        {{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Waktu Selesai (Conditional) --}}
                            <div x-show="showEndTime" x-transition>
                                <label for="end_time"
                                    class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                    <span x-text="endTimeLabel"></span> <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="time" id="end_time" name="end_time" x-model="endTime"
                                        :required="showEndTime"
                                        class="block w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-100 text-sm px-3.5 py-2.5 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition" />
                                </div>
                                @error('end_time')
                                    <p class="mt-1 text-xs font-semibold text-red-600 dark:text-red-400">
                                        {{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    {{-- 2. Detail & Persetujuan --}}
                    <section class="space-y-4">
                        <div
                            class="flex items-center gap-2 pb-2 border-b border-gray-100 dark:border-slate-700/60 text-xs font-bold uppercase tracking-wider text-primary-700 dark:text-primary-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>2. Rincian & Atasan Approver</span>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                            {{-- Pilih Atasan --}}
                            <div>
                                <label for="approver_id"
                                    class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                    Pilih Atasan Langsung <span class="text-red-500">*</span>
                                </label>
                                <select id="approver_id" name="approver_id" required
                                    class="block w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-100 text-sm px-3.5 py-2.5 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition">
                                    <option value="">-- Pilih Atasan Langsung --</option>
                                    @foreach ($approverList as $approver)
                                        <option value="{{ $approver->id }}" @selected(old('approver_id') == $approver->id)>
                                            {{ strtoupper($approver->name) }} —
                                            {{ strtoupper(str_replace('_', ' ', $approver->role)) }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">Notifikasi pengajuan akan
                                    langsung dikirim ke atasan ini.</p>
                                @error('approver_id')
                                    <p class="mt-1 text-xs font-semibold text-red-600 dark:text-red-400">
                                        {{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Bukti Pendukung / Foto --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                    Bukti Pendukung / Foto (Opsional)
                                </label>

                                <div class="relative">
                                    <input type="file" id="proof_image" name="proof_image"
                                        accept="image/jpeg,image/png,image/jpg,image/gif"
                                        @change="handleImageUpload($event)" class="sr-only" />

                                    <label for="proof_image"
                                        class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border-2 border-dashed border-gray-300 dark:border-slate-600 hover:border-primary-500 dark:hover:border-primary-400 bg-gray-50/50 dark:bg-slate-700/30 cursor-pointer transition text-xs font-medium text-gray-600 dark:text-gray-300">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span
                                            x-text="imagePreview ? 'Ganti Foto Bukti' : 'Pilih Foto / Screenshot Bukti'"></span>
                                    </label>
                                </div>
                                <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">Maks. 2MB (Format: JPG,
                                    PNG, JPEG, GIF).</p>

                                @error('proof_image')
                                    <p class="mt-1 text-xs font-semibold text-red-600 dark:text-red-400">
                                        {{ $message }}</p>
                                @enderror

                                {{-- Image Preview Container --}}
                                <template x-if="imagePreview">
                                    <div class="mt-3 relative inline-block">
                                        <img :src="imagePreview" alt="Pratinjau Bukti"
                                            class="h-24 w-auto rounded-xl object-cover border border-gray-200 dark:border-slate-600 shadow-sm" />
                                        <button type="button" @click="removeImage()"
                                            class="absolute -top-2 -right-2 p-1 bg-red-600 text-white rounded-full hover:bg-red-700 shadow-sm focus:outline-none transition"
                                            title="Hapus foto">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Alasan Pengajuan --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="reason"
                                    class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                    Alasan Pengajuan <span class="text-red-500">*</span>
                                </label>
                                <span class="text-[11px] font-mono text-gray-400 dark:text-gray-500"
                                    x-text="`${reason.length}/500`"></span>
                            </div>
                            <textarea id="reason" name="reason" rows="3" maxlength="500" required x-model="reason"
                                placeholder="Jelaskan alasan pengajuan secara jelas dan ringkas (contoh: Kendala mesin absensi / Keperluan keluarga mendesak)..."
                                class="block w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-100 text-sm p-3.5 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition">{{ old('reason') }}</textarea>
                            @error('reason')
                                <p class="mt-1 text-xs font-semibold text-red-600 dark:text-red-400">{{ $message }}
                                </p>
                            @enderror
                        </div>
                    </section>

                    {{-- Footer Action Buttons --}}
                    <div
                        class="pt-5 border-t border-gray-100 dark:border-slate-700/60 flex flex-col sm:flex-row items-center justify-end gap-3">
                        <a href="{{ route('kehadiran.index') }}"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-gray-400 transition">
                            <svg class="w-4 h-4 mr-1.5 text-gray-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Batal
                        </a>
                        <button type="submit" :disabled="isSubmitting"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 transition">
                            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                            </svg>
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none"
                                viewBox="0 0 24 24" x-show="isSubmitting" style="display: none;">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span x-text="isSubmitting ? 'Mengirim Pengajuan...' : 'Kirim Pengajuan Kehadiran'"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Error / Duplicate Notice --}}
    @if ($errors->has('msg'))
        <div x-data="{ show: true }" x-show="show" x-cloak x-transition
            class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div @click.outside="show = false"
                class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-2xl max-w-sm w-full border border-gray-200 dark:border-slate-700"
                x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">
                <div
                    class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Pengajuan Tidak Dapat Diproses</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 leading-relaxed">{{ $errors->first('msg') }}
                </p>
                <div class="mt-5">
                    <button type="button" @click="show = false"
                        class="w-full px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-semibold text-xs transition shadow-sm">
                        Saya Mengerti
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Frontend Handler Script --}}
    <script>
        function attendanceForm() {
            return {
                type: @json(old('type', '')),
                startTime: @json(old('start_time', '')),
                endTime: @json(old('end_time', '')),
                reason: @json(old('reason', '')),
                imagePreview: null,
                isSubmitting: false,

                get showEndTime() {
                    return this.type === 'leave_during_work' || this.type === 'update_attendance';
                },
                get typeInfo() {
                    switch (this.type) {
                        case 'late_arrival':
                            return {
                                title: 'Datang Terlambat',
                                    desc:
                                    'Pengajuan izin saat tiba di kantor melebihi jam operasional kerja yang ditentukan.'
                            };
                        case 'early_departure':
                            return {
                                title: 'Pulang Lebih Awal',
                                    desc: 'Pengajuan izin untuk meninggalkan kantor sebelum jam pulang kerja berakhir.'
                            };
                        case 'leave_during_work':
                            return {
                                title: 'Meninggalkan Pekerjaan',
                                    desc:
                                    'Pengajuan izin keluar kantor sementara pada jam kerja dan kembali bekerja di hari yang sama.'
                            };
                        case 'update_attendance':
                            return {
                                title: 'Update Absensi',
                                    desc:
                                    'Koreksi log catatan jam check-in atau check-out karena kendala sistem presensi.'
                            };
                        default:
                            return null;
                    }
                },
                get startTimeLabel() {
                    switch (this.type) {
                        case 'late_arrival':
                            return 'Perkiraan Jam Tiba';
                        case 'early_departure':
                            return 'Jam Pulang / Keluar Kantor';
                        case 'leave_during_work':
                            return 'Jam Keluar Kantor';
                        case 'update_attendance':
                            return 'Jam Check-in Sebenarnya';
                        default:
                            return 'Waktu Mulai';
                    }
                },
                get endTimeLabel() {
                    switch (this.type) {
                        case 'leave_during_work':
                            return 'Perkiraan Jam Kembali';
                        case 'update_attendance':
                            return 'Jam Check-out Sebenarnya';
                        default:
                            return 'Waktu Selesai';
                    }
                },
                handleImageUpload(event) {
                    const file = event.target.files[0];
                    if (file) {
                        if (file.size > 2048 * 1024) {
                            alert('Ukuran file bukti maksimal 2MB.');
                            event.target.value = '';
                            this.imagePreview = null;
                            return;
                        }
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.imagePreview = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                },
                removeImage() {
                    this.imagePreview = null;
                    const input = document.getElementById('proof_image');
                    if (input) input.value = '';
                }
            };
        }
    </script>
</x-app-layout>
