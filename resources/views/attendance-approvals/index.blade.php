<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2
                    class="border-l-4 border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Persetujuan Kehadiran') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Tinjau dan proses pengajuan kehadiran bawahan.
                </p>
            </div>
            <div class="flex items-center space-x-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                <span
                    class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-primary-100 dark:bg-primary-900/50 text-primary-600 dark:text-primary-300">
                    {{ $attendanceRequests->total() }}
                </span>
                <span>Menunggu Tindakan</span>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">
        @forelse ($attendanceRequests as $attendanceRequest)
            <div x-data="{ open: false }" class="bg-white dark:bg-slate-800 rounded-xl shadow-md overflow-hidden">
                <button type="button" @click="open = !open"
                    class="w-full flex items-center p-4 text-left hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                    <div class="flex-shrink-0 mr-4">
                        <img loading="lazy" class="h-10 w-10 rounded-full" src="{{ asset('img/user.png') }}"
                            alt="">
                    </div>
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ strtoupper($attendanceRequest->user->name) }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ strtoupper(optional($attendanceRequest->user->position)->nama_jabatan ?? '-') }}</p>
                        </div>
                        <div class="text-sm text-gray-800 dark:text-gray-200">
                            {{ $attendanceRequest->type_label }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $attendanceRequest->date->isoFormat('D MMMM YYYY') }},
                            {{ \Illuminate\Support\Str::of($attendanceRequest->start_time)->substr(0, 5) }}
                            @if ($attendanceRequest->end_time)
                                - {{ \Illuminate\Support\Str::of($attendanceRequest->end_time)->substr(0, 5) }}
                            @endif
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open }"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" x-collapse>
                    <div class="px-5 pb-5 pt-2 border-t border-gray-200 dark:border-slate-700">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div class="lg:col-span-2">
                                <h4 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase mb-3">Detail
                                    Pengajuan</h4>
                                <dl class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="flex items-start">
                                        <dt class="font-medium w-36 flex-shrink-0">Pemohon</dt>
                                        <dd>: {{ $attendanceRequest->user->name }}</dd>
                                    </div>
                                    <div class="flex items-start">
                                        <dt class="font-medium w-36 flex-shrink-0">Kantor</dt>
                                        <dd>: {{ strtoupper($attendanceRequest->user?->office?->nama_kantor ?? '-') }}
                                        </dd>
                                    </div>
                                    <div class="flex items-start">
                                        <dt class="font-medium w-36 flex-shrink-0">Jenis</dt>
                                        <dd>: {{ $attendanceRequest->type_label }}</dd>
                                    </div>
                                    <div class="flex items-start">
                                        <dt class="font-medium w-36 flex-shrink-0">Tanggal</dt>
                                        <dd>: {{ $attendanceRequest->date->isoFormat('dddd, D MMMM YYYY') }}</dd>
                                    </div>
                                    <div class="flex items-start">
                                        <dt class="font-medium w-36 flex-shrink-0">Waktu</dt>
                                        <dd>:
                                            {{ \Illuminate\Support\Str::of($attendanceRequest->start_time)->substr(0, 5) }}
                                            @if ($attendanceRequest->end_time)
                                                -
                                                {{ \Illuminate\Support\Str::of($attendanceRequest->end_time)->substr(0, 5) }}
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="flex items-start">
                                        <dt class="font-medium w-36 flex-shrink-0">Alasan</dt>
                                        <dd>: " {{ $attendanceRequest->reason }} "</dd>
                                    </div>
                                </dl>

                                @if ($attendanceRequest->proof_image)
                                    <div class="mt-4">
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-2">
                                            Bukti Gambar</p>
                                        <img src="{{ asset('storage/' . $attendanceRequest->proof_image) }}"
                                            alt="Bukti Kehadiran"
                                            class="max-w-full h-auto max-h-56 rounded-lg shadow-md border border-gray-200 dark:border-gray-600 cursor-pointer hover:opacity-90 transition"
                                            onclick="window.open(this.src, '_blank')">
                                    </div>
                                @endif
                            </div>

                            <div class="space-y-4">
                                <form action="{{ route('approval-kehadiran.approve', $attendanceRequest) }}"
                                    method="POST" class="space-y-3">
                                    @csrf
                                    @method('PATCH')
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan
                                        Persetujuan</label>
                                    <textarea name="approval_note" rows="3" maxlength="500"
                                        class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500"
                                        placeholder="Opsional"></textarea>
                                    <button type="submit"
                                        class="w-full inline-flex items-center justify-center px-5 py-2.5 rounded-full bg-green-600 text-white text-sm font-semibold hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800 transition">
                                        Setujui
                                    </button>
                                </form>

                                <form action="{{ route('approval-kehadiran.reject', $attendanceRequest) }}"
                                    method="POST" class="space-y-3">
                                    @csrf
                                    @method('PATCH')
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alasan
                                        Penolakan <span class="text-red-500">*</span></label>
                                    <textarea name="rejection_reason" rows="3" maxlength="500" required
                                        class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500"
                                        placeholder="Wajib diisi jika ditolak"></textarea>
                                    <button type="submit"
                                        class="w-full inline-flex items-center justify-center px-5 py-2.5 rounded-full bg-red-600 text-white text-sm font-semibold hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800 transition">
                                        Tolak
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-16 px-6 bg-white dark:bg-slate-800 rounded-xl shadow-md">
                <p class="text-lg font-semibold text-gray-800 dark:text-gray-100">Inbox Persetujuan Kehadiran Kosong</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Semua pengajuan kehadiran telah diproses.</p>
            </div>
        @endforelse

        <div>
            {{ $attendanceRequests->links() }}
        </div>
    </div>

    <x-toast-notification />
</x-app-layout>
