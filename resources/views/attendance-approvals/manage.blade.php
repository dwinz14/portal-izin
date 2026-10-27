@php
// State yang dipulihkan bila validasi form Hapus gagal (modal dibuka lagi).
// Kata konfirmasi sengaja TIDAK dipulihkan: harus diketik ulang.
$hasFilter = collect($filters)->filter()->isNotEmpty();

$restored = [
'base' => url('approval-kehadiran'),
'showFilter' => $hasFilter,
'modal' => old('_modal') === 'hapus',
'row' => [
'id' => old('_ctx_id') ? (int) old('_ctx_id') : null,
'label' => old('_ctx_label', ''),
'past' => (bool) old('_ctx_past'),
],
'alasan' => old('alasan', ''),
];

$inputClass =
'w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-gray-200 shadow-sm
focus:border-primary-500 focus:ring-primary-500 sm:text-xs';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2
                    class="border-l-4 border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Kelola Kehadiran Disetujui') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Penyesuaian Kehadiran: hapus pengajuan kehadiran yang sudah disetujui.
                </p>
            </div>
            <div class="flex items-center space-x-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                <span
                    class="inline-flex items-center justify-center h-6 px-2 rounded-full bg-primary-100 dark:bg-primary-900/50 text-primary-600 dark:text-primary-300">
                    {{ $attendanceRequests->total() }}
                </span>
                <span>{{ $hasFilter ? 'Hasil Filter' : 'Total Pengajuan' }}</span>
            </div>
        </div>
    </x-slot>

    <div x-data="manageAttendance({{ \Illuminate\Support\Js::from($restored) }})"
        class="max-w-6xl mx-auto pb-10 space-y-4">

        <x-toast-notification />

        @if ($errors->any())
        <div
            class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- ═══ FILTER ═══ --}}
        <div class="bg-white dark:bg-slate-800 shadow-lg rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Filter & Pencarian</h3>
                <button type="button" @click="showFilter = !showFilter"
                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 focus:outline-none">
                    <svg class="w-4 h-4 transition-transform duration-200" :class="showFilter ? 'rotate-180' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>

            <div x-show="showFilter" x-collapse x-cloak class="mt-3">
                <form method="GET" action="{{ route('approval-kehadiran.manage') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div>
                            <label for="q" class="block text-xs font-medium mb-1 text-gray-700 dark:text-gray-300">Nama
                                Pemohon</label>
                            <input type="text" id="q" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100"
                                placeholder="Cari nama..." class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label for="type"
                                class="block text-xs font-medium mb-1 text-gray-700 dark:text-gray-300">Jenis</label>
                            <select id="type" name="type" class="{{ $inputClass }}">
                                <option value="">-- Semua Jenis --</option>
                                @foreach ($typeLabels as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['type'] ?? '' )===$value)>{{ $label }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="date_from"
                                class="block text-xs font-medium mb-1 text-gray-700 dark:text-gray-300">Dari
                                Tanggal</label>
                            <input type="date" id="date_from" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                                class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label for="date_to"
                                class="block text-xs font-medium mb-1 text-gray-700 dark:text-gray-300">Sampai
                                Tanggal</label>
                            <input type="date" id="date_to" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                                class="{{ $inputClass }}">
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-2">
                        <button type="submit"
                            class="px-4 py-2 bg-primary-600 text-white text-xs font-bold rounded-lg hover:bg-primary-700 transition-colors">
                            Terapkan
                        </button>
                        @if ($hasFilter)
                        <a href="{{ route('approval-kehadiran.manage') }}"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                            Reset
                        </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- ═══ TABEL DAFTAR PENGAJUAN ═══ --}}
        <div class="bg-white dark:bg-slate-800 shadow-xl rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-slate-700/50">
                        <tr
                            class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <th class="px-6 py-3">Pemohon</th>
                            <th class="px-6 py-3">Jenis</th>
                            <th class="px-6 py-3">Tanggal & Waktu</th>
                            <th class="px-6 py-3">Alasan</th>
                            <th class="px-6 py-3">Disetujui</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                        @forelse ($attendanceRequests as $item)
                        @php
                        $jam = collect([$item->start_time, $item->end_time])
                        ->filter()
                        ->map(fn($t) => substr($t, 0, 5))
                        ->implode(' - ');

                        // Data baris untuk Alpine (di-escape aman oleh Js::from)
                        $rowData = [
                        'id' => $item->id,
                        'label' =>
                        ucwords($item->user->name ?? '-') .
                        ' - ' .
                        $item->type_label .
                        ' (' .
                        $item->date->format('d/m/Y') .
                        ')',
                        'past' => $item->date->copy()->startOfDay()->lt(today()),
                        ];
                        @endphp
                        <tr>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-800 dark:text-gray-100">
                                    {{ ucwords($item->user->name ?? '-') }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ strtoupper(optional($item->user->position)->nama_jabatan ?? '-') }}</p>
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                {{ $item->type_label }}
                                @if ($item->update_type)
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $item->update_type_label }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                {{ $item->date->translatedFormat('d M Y') }}
                                @if ($jam !== '')
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $jam }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300 max-w-xs">
                                <p title="{{ $item->reason }}">{{ \Illuminate\Support\Str::limit($item->reason, 60) }}
                                </p>
                                @if ($item->proof_image)
                                <a href="{{ asset('storage/' . $item->proof_image) }}" target="_blank" rel="noopener"
                                    class="text-xs text-blue-700 dark:text-blue-400 hover:underline">Lihat
                                    bukti</a>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                {{ $item->approved_at?->translatedFormat('d M Y, H:i') ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end">
                                    <button type="button"
                                        @click="openHapus({{ \Illuminate\Support\Js::from($rowData) }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 text-xs font-bold rounded-lg hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-400 dark:text-gray-500">
                                {{ $hasFilter ? 'Tidak ada pengajuan yang cocok dengan filter.' : 'Belum ada pengajuan
                                kehadiran yang Anda setujui.' }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($attendanceRequests->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-slate-700">
                {{ $attendanceRequests->links() }}
            </div>
            @endif
        </div>

        {{-- ═══ MODAL: HAPUS ═══ --}}
        <div x-show="modal" x-cloak x-transition
            class="fixed inset-0 z-[200] flex items-center justify-center p-4 sm:p-6 bg-slate-900/70 dark:bg-slate-950/80 backdrop-blur-md overflow-y-auto"
            x-on:keydown.escape.window="close()">

            <div class="bg-white dark:bg-slate-800 rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden relative border border-red-500/30 dark:border-red-500/40"
                @click.stop x-transition:enter="transition ease-out duration-300 delay-100"
                x-transition:enter-start="opacity-0 scale-95 translate-y-8"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-8">

                {{-- Header --}}
                <div class="bg-red-600 px-8 py-6">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center flex-shrink-0 backdrop-blur-sm border border-white/30">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-black text-white text-xl tracking-wide">Hapus Pengajuan Kehadiran</h3>
                            <p class="text-red-100 text-sm font-medium mt-0.5 truncate" x-text="row.label"></p>
                        </div>
                    </div>
                </div>

                <div class="px-8 py-6 space-y-5">
                    <div x-show="row.past" x-cloak
                        class="rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-300">
                        Tanggal kehadiran ini sudah lewat. Penghapusan akan dicatat sebagai koreksi data.
                    </div>

                    <div
                        class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-500/40 dark:bg-red-500/10 dark:text-red-300">
                        <p class="font-bold mb-1">Tindakan ini tidak dapat dibatalkan.</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            <li>Pengajuan dihapus permanen beserta foto buktinya.</li>
                            <li>Pemohon diberi notifikasi dan dapat mengajukan ulang.</li>
                            <li>Penghapusan tercatat di log aktivitas Anda.</li>
                        </ul>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-gray-200 mb-2">
                            Alasan Penghapusan
                        </label>
                        <textarea x-model="alasan" rows="3" maxlength="500"
                            placeholder="Minimal 10 karakter. Boleh huruf, angka, spasi, dan tanda . , ( ) / -"
                            class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-100 rounded-xl focus:outline-none focus:border-red-500 focus:ring-4 focus:ring-red-500/20 transition-all"></textarea>
                    </div>

                    {{-- Input verifikasi ketik HAPUS --}}
                    <div class="bg-slate-900 rounded-2xl p-5 border border-slate-800 shadow-inner">
                        <label class="block text-sm font-medium text-slate-300 mb-3 text-center">
                            Ketik kata konfirmasi <span
                                class="text-red-400 font-bold font-mono text-base bg-red-400/10 px-2 py-0.5 rounded border border-red-400/20 mx-1">HAPUS</span>
                            di bawah ini untuk melanjutkan:
                        </label>
                        <input type="text" x-model="confirmText" placeholder="Ketik persis huruf kapital..."
                            autocomplete="off"
                            class="w-full px-4 py-3 bg-slate-800 border-2 border-slate-700 rounded-xl text-center text-xl font-bold font-mono text-white placeholder:text-slate-600 focus:outline-none focus:border-red-500 focus:ring-4 focus:ring-red-500/20 transition-all">
                    </div>
                </div>

                {{-- Actions --}}
                <div
                    class="px-8 py-5 bg-slate-50 dark:bg-slate-700/50 border-t border-slate-200 dark:border-slate-600 flex flex-col-reverse sm:flex-row items-center gap-3">
                    <button type="button" @click="close()"
                        class="w-full sm:w-1/3 px-5 py-3.5 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-gray-300 text-sm font-bold rounded-xl hover:bg-slate-100 dark:hover:bg-slate-600 focus:ring-4 focus:ring-slate-100 dark:focus:ring-slate-700 transition-colors">
                        Kembali
                    </button>

                    <form :action="`${base}/${row.id}`" method="POST" class="w-full sm:w-2/3"
                        @submit="submitting = true">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="alasan" :value="alasan">
                        <input type="hidden" name="confirmation" :value="confirmText">
                        {{-- Konteks untuk membuka modal lagi bila validasi gagal --}}
                        <input type="hidden" name="_modal" value="hapus">
                        <input type="hidden" name="_ctx_id" :value="row.id">
                        <input type="hidden" name="_ctx_label" :value="row.label">
                        <input type="hidden" name="_ctx_past" :value="row.past ? 1 : 0">
                        <button type="submit" :disabled="!canConfirm || submitting"
                            class="w-full flex items-center justify-center gap-2 px-6 py-3.5 bg-red-600 text-white text-sm font-black tracking-wide rounded-xl hover:bg-red-700 shadow-lg shadow-red-200 dark:shadow-red-900 focus:ring-4 focus:ring-red-100 dark:focus:ring-red-900 transition-all disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none">
                            <svg x-show="submitting" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"
                                x-cloak>
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            <span x-text="submitting ? 'MEMPROSES...' : 'HAPUS PENGAJUAN'"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('manageAttendance', (restored = {}) => ({
                base: restored.base,
                showFilter: restored.showFilter,
                modal: false,
                row: { id: null, label: '', past: false },
                alasan: '',
                confirmText: '',
                submitting: false,

                init() {
                    // Validasi server gagal: buka lagi modalnya dengan alasan yang sudah diisi
                    if (restored.modal) {
                        this.row = restored.row;
                        this.alasan = restored.alasan;
                        this.modal = true;
                    }
                },

                get alasanValid() {
                    const v = this.alasan.trim();
                    return v.length >= 10 && v.length <= 500 && /^[a-zA-Z0-9\s.,()\/-]+$/.test(v);
                },
                get canConfirm() {
                    return this.confirmText === 'HAPUS' && this.alasanValid;
                },

                openHapus(row) {
                    this.row = row;
                    this.alasan = '';
                    this.confirmText = '';
                    this.submitting = false;
                    this.modal = true;
                },
                close() {
                    if (!this.submitting) this.modal = false;
                },
            }));
        });
    </script>
</x-app-layout>