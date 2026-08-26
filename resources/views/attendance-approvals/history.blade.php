<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2
                    class="border-l-4 border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Riwayat Persetujuan Kehadiran') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Riwayat pengajuan kehadiran yang telah Anda proses.
                </p>
            </div>
            <div class="flex items-center space-x-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                <span
                    class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-primary-100 dark:bg-primary-900/50 text-primary-600 dark:text-primary-300">
                    {{ $histories->total() }}
                </span>
                <span>Total Riwayat</span>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">
        @forelse ($histories as $history)
            <div x-data="{ open: false }"
                class="bg-white dark:bg-slate-800 rounded-xl shadow-md overflow-hidden border-l-4 {{ $history->status === 'approved' ? 'border-green-500' : 'border-red-500' }}">
                <button type="button" @click="open = !open"
                    class="w-full flex items-center p-4 text-left hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                    <div class="flex-shrink-0 mr-4">
                        <img loading="lazy" class="h-10 w-10 rounded-full" src="{{ asset('img/user.png') }}"
                            alt="">
                    </div>
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ strtoupper($history->user->name) }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ strtoupper(optional($history->user->position)->nama_jabatan ?? '-') }}</p>
                        </div>
                        <div class="text-sm text-gray-800 dark:text-gray-200">
                            {{ $history->type_label }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $history->date->isoFormat('D MMMM YYYY') }},
                            {{ \Illuminate\Support\Str::of($history->start_time)->substr(0, 5) }}
                            @if ($history->end_time)
                                - {{ \Illuminate\Support\Str::of($history->end_time)->substr(0, 5) }}
                            @endif
                        </div>
                        <div class="flex justify-end">
                            @if ($history->status === 'approved')
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                    Disetujui
                                </span>
                            @elseif ($history->status === 'rejected')
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                    Ditolak
                                </span>
                            @endif
                        </div>
                    </div>
                    <svg class="w-5 h-5 ml-4 text-gray-400 transition-transform duration-300"
                        :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" x-collapse>
                    <div class="px-5 pb-5 pt-2 border-t border-gray-200 dark:border-slate-700">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase mb-3">Detail
                                    Pengajuan</h4>
                                <dl class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="flex items-start">
                                        <dt class="font-medium w-36 flex-shrink-0">Pemohon</dt>
                                        <dd>: {{ $history->user->name }}</dd>
                                    </div>
                                    <div class="flex items-start">
                                        <dt class="font-medium w-36 flex-shrink-0">Kantor</dt>
                                        <dd>: {{ strtoupper(optional($history->user->office)->nama_kantor ?? '-') }}
                                        </dd>
                                    </div>
                                    <div class="flex items-start">
                                        <dt class="font-medium w-36 flex-shrink-0">Jenis</dt>
                                        <dd>: {{ $history->type_label }}</dd>
                                    </div>
                                    <div class="flex items-start">
                                        <dt class="font-medium w-36 flex-shrink-0">Tanggal</dt>
                                        <dd>: {{ $history->date->isoFormat('dddd, D MMMM YYYY') }}</dd>
                                    </div>
                                    <div class="flex items-start">
                                        <dt class="font-medium w-36 flex-shrink-0">Waktu</dt>
                                        <dd>:
                                            {{ \Illuminate\Support\Str::of($history->start_time)->substr(0, 5) }}
                                            @if ($history->end_time)
                                                - {{ \Illuminate\Support\Str::of($history->end_time)->substr(0, 5) }}
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="flex items-start">
                                        <dt class="font-medium w-36 flex-shrink-0">Alasan</dt>
                                        <dd>: {{ $history->reason }}</dd>
                                    </div>
                                </dl>

                                @if ($history->proof_image)
                                    <div class="mt-4">
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-2">
                                            Bukti Gambar</p>
                                        <img src="{{ asset('storage/' . $history->proof_image) }}"
                                            alt="Bukti Kehadiran"
                                            class="max-w-full h-auto max-h-56 rounded-lg shadow-md border border-gray-200 dark:border-gray-600 cursor-pointer hover:opacity-90 transition"
                                            onclick="window.open(this.src, '_blank')">
                                    </div>
                                @endif
                            </div>

                            <div>
                                <h4 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase mb-3">
                                    Tanggapan Anda</h4>
                                <div
                                    class="p-4 rounded-lg {{ $history->status === 'approved' ? 'bg-green-50 border border-green-200 dark:bg-green-900/20 dark:border-green-800' : 'bg-red-50 border border-red-200 dark:bg-red-900/20 dark:border-red-800' }}">
                                    <div class="flex items-center mb-2">
                                        @if ($history->status === 'approved')
                                            <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-2" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span
                                                class="font-semibold text-green-800 dark:text-green-300">Disetujui</span>
                                        @elseif ($history->status === 'rejected')
                                            <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-2" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            <span class="font-semibold text-red-800 dark:text-red-300">Ditolak</span>
                                        @endif
                                        <span class="ml-auto text-xs text-gray-500 dark:text-gray-400">
                                            {{ $history->updated_at->isoFormat('D MMMM YYYY, HH:mm') }}
                                        </span>
                                    </div>
                                    <p
                                        class="text-sm {{ $history->status === 'approved' ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400' }}">
                                        @if ($history->status === 'approved')
                                            Catatan: {{ $history->approval_note ?: '-' }}
                                        @elseif ($history->status === 'rejected')
                                            Alasan Penolakan: {{ $history->rejection_reason ?: '-' }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-16 px-6 bg-white dark:bg-slate-800 rounded-xl shadow-md">
                <p class="text-lg font-semibold text-gray-800 dark:text-gray-100">Belum Ada Riwayat Persetujuan</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Riwayat persetujuan kehadiran akan muncul di
                    sini.</p>
            </div>
        @endforelse

        <div>
            {{ $histories->links() }}
        </div>
    </div>

    <x-toast-notification />
</x-app-layout>
