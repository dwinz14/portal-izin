<x-app-layout>
    <x-slot name="header">
        <div>
            <h2
                class="flex items-center gap-2 font-bold text-xl sm:text-2xl text-gray-900 dark:text-gray-100 leading-tight">
                <span class="w-1.5 h-5 bg-primary-600 rounded-full"></span>
                {{ __('Ajukan Kehadiran') }}
            </h2>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1 ml-3.5">
                Lengkapi data kehadiran dan pilih atasan langsung sebagai approver.
            </p>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 bg-gray-50/50 dark:bg-gray-900/50 min-h-screen flex justify-center"
        x-data="attendanceForm()">
        <div class="w-full max-w-3xl">
            <div
                class="bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden">
                <form method="POST" action="{{ route('kehadiran.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div
                        class="p-5 sm:p-6 border-b border-gray-100 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-800/50">
                        <label for="type" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Jenis Pengajuan <span class="text-red-500">*</span>
                        </label>
                        <select id="type" name="type" x-model="type"
                            class="block w-full max-w-xl rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">-- Silakan Pilih --</option>
                            @foreach ($typeLabels as $value => $label)
                                <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-show="type" style="display: none;" x-transition class="p-5 sm:p-6 space-y-6">
                        <section>
                            <h3
                                class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Waktu Kehadiran
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal
                                        <span class="text-red-500">*</span></label>
                                    <input type="date" name="date"
                                        value="{{ old('date', now()->format('Y-m-d')) }}" required
                                        class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500">
                                    @error('date')
                                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        <span x-text="startTimeLabel"></span> <span class="text-red-500">*</span>
                                    </label>
                                    <input type="time" name="start_time" value="{{ old('start_time') }}" required
                                        class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500">
                                    @error('start_time')
                                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div x-show="showEndTime" style="display: none;">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        <span x-text="endTimeLabel"></span> <span class="text-red-500">*</span>
                                    </label>
                                    <input type="time" name="end_time" value="{{ old('end_time') }}"
                                        :required="showEndTime"
                                        class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500">
                                    @error('end_time')
                                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        <section>
                            <h3
                                class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 8h10M7 12h6m-6 4h10M5 5h14v14H5z" />
                                </svg>
                                Detail Pengajuan
                            </h3>
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pilih
                                        Atasan <span class="text-red-500">*</span></label>
                                    <select name="approver_id" required
                                        class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500">
                                        <option value="">-- Pilih Atasan Langsung --</option>
                                        @foreach ($approverList as $approver)
                                            <option value="{{ $approver->id }}" @selected(old('approver_id') == $approver->id)>
                                                {{ strtoupper($approver->name) }} - {{ strtoupper($approver->role) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('approver_id')
                                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alasan
                                        <span class="text-red-500">*</span></label>
                                    <textarea name="reason" rows="4" required maxlength="500"
                                        class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500"
                                        placeholder="Tuliskan alasan pengajuan secara singkat dan jelas.">{{ old('reason') }}</textarea>
                                    @error('reason')
                                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bukti
                                    Gambar
                                    <input type="file" name="proof_image" accept="image/*"
                                        class="block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-slate-700 dark:file:text-gray-100">
                                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Format JPG, PNG,
                                        JPEG,
                                        atau GIF. Maksimal 2MB.</p>
                                    @error('proof_image')
                                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                            </div>
                        </section>

                        <div
                            class="pt-5 border-t border-gray-100 dark:border-slate-700 flex items-center justify-end gap-3">
                            <a href="{{ route('kehadiran.index') }}"
                                class="px-4 py-2 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-lg font-medium text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 dark:focus:ring-slate-900 transition">
                                Batal
                            </a>
                            <button type="submit"
                                class="inline-flex items-center justify-center px-5 py-2 bg-primary-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-slate-900 transition shadow-sm">
                                Kirim Pengajuan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if ($errors->has('msg'))
        <div x-data="{ show: true }" x-show="show" x-transition
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div @click.outside="show = false"
                class="bg-white dark:bg-slate-800 p-5 rounded-xl shadow-2xl max-w-sm w-full">
                <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Gagal Mengajukan</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $errors->first('msg') }}</p>
                <button @click="show = false"
                    class="mt-5 w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-gray-800 dark:text-gray-200 rounded-lg font-medium text-sm transition">
                    Mengerti, Tutup
                </button>
            </div>
        </div>
    @endif

    <script>
        function attendanceForm() {
            return {
                type: @json(old('type', '')),
                get showEndTime() {
                    return this.type === 'leave_during_work' || this.type === 'update_attendance';
                },
                get startTimeLabel() {
                    switch (this.type) {
                        case 'late_arrival':
                            return 'Perkiraan Jam Datang';
                        case 'early_departure':
                            return 'Jam Pulang / Keluar';
                        case 'leave_during_work':
                            return 'Jam Keluar';
                        case 'update_attendance':
                            return 'Jam Check-in Sebenarnya';
                        default:
                            return 'Waktu';
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
            };
        }
    </script>
</x-app-layout>
