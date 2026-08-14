<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2
                    class="border-l-4 border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Jadwal Pengganti') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Lihat jadwal anda sebagai pengganti.
                </p>
            </div>
            <div class="mt-2 sm:mt-0 flex items-center space-x-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                <span
                    class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-primary-100 dark:bg-primary-900/50 text-primary-600 dark:text-primary-300">
                    {{ $leaves->count() }}
                </span>
                <span>
                    Total Jadwal
                </span>
            </div>
        </div>
    </x-slot>

    <div
        class="space-y-4 bg-white dark:bg-slate-800 shadow-xl hover:shadow-xl/30 hover:-translate-y-1 transition-all duration-300 rounded-xl overflow-hidden">
        @forelse ($leaves as $leave)
            @php
                $today = now()->toDateString();
                $start = \Carbon\Carbon::parse($leave->start_date)->toDateString();
                $end = \Carbon\Carbon::parse($leave->end_date)->toDateString();
                if ($today < $start) {
                    $status = 'dijadwalkan';
                    $badgeClass = 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300';
                    $statusIcon =
                        '<svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0H21" /></svg>';
                } elseif ($today >= $start && $today <= $end) {
                    $status = 'berlangsung';
                    $badgeClass = 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300';
                    $statusIcon =
                        '<svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" /></svg>';
                } elseif ($today > $end) {
                    $status = 'selesai';
                    $badgeClass = 'bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-300';
                    $statusIcon =
                        '<svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
                } else {
                    $status = 'tidak diketahui';
                    $badgeClass = 'bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-300';
                    $statusIcon =
                        '<svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>';
                }
            @endphp
            <div x-data="{ open: false }"
                class="bg-white dark:bg-slate-800 rounded-xl shadow-md transition-all duration-300">
                <div @click="open = !open"
                    class="flex items-center p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700/50 rounded-xl {{ $badgeClass }} {{ $status === 'dijadwalkan' ? 'bg-blue-50 dark:bg-blue-900/20' : ($status === 'berlangsung' ? 'bg-green-50 dark:bg-green-900/20' : 'bg-gray-50 dark:bg-gray-900/20') }}">
                    <div class="flex-shrink-0 mr-4">
                        <img loading="lazy" class="h-10 w-10 rounded-full" src="{{ asset('img/user.png') }}"
                            alt="">
                    </div>
                    <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 items-center">
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100"> Pengganti Untuk :
                                {{ Str::title($leave->user->name) }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ Strtoupper($leave->user->position->nama_jabatan) }}
                                @if ($leave->is_mendadak)
                                    <span
                                        class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">
                                        ⚡ Mendadak
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div class="hidden sm:block md:hidden">
                            <p class="text-sm text-gray-800 dark:text-gray-200 truncate" title="{{ $leave->alasan }}">
                                {{ $leave->alasan }}
                            </p>
                        </div>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                            <div class="text-sm text-gray-500 dark:text-gray-400 flex items-center">
                                <svg class="w-4 h-4 mr-1.5 inline flex-shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0H21" />
                                </svg>
                                <span
                                    class="truncate">{{ \Carbon\Carbon::parse($leave->start_date)->isoFormat('D MMM') }}
                                    - {{ \Carbon\Carbon::parse($leave->end_date)->isoFormat('D MMM') }}</span>
                            </div>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                {!! $statusIcon !!}
                                {{ ucfirst($status) }}
                            </span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <svg class="w-5 h-5 text-gray-400 transition-transform duration-300"
                            :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                <div x-show="open" x-collapse>
                    <div class="px-5 pb-5 pt-2 border-t border-gray-200 dark:border-slate-700">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <div class="mb-4 sm:mb-0">
                                <h4 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase mb-2">Detail
                                    Pengganti</h4>
                                <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                    @php
                                        $start = \Carbon\Carbon::parse($leave->start_date);
                                        $end = \Carbon\Carbon::parse($leave->end_date);
                                        $now = now();
                                        // $daysUntilStart = $now->diffInDays($start, false);
                                        // $daysElapsed = $now->diffInDays($start);
                                        // $daysRemaining = $end->diffInDays($now, false);
                                    @endphp
                                    <div class="space-y-1">
                                        <p><span class="font-medium">Tanggal:</span>
                                            <strong>{{ $start->isoFormat('dddd, D MMMM YYYY') }}</strong> s/d
                                            <strong>{{ $end->isoFormat('dddd, D MMMM YYYY') }}</strong>
                                        </p>
                                        {{-- @if ($status === 'dijadwalkan')
                                            <p class="text-blue-600 dark:text-blue-400"><span class="font-medium">Mulai
                                                    dalam:</span> {{ $daysUntilStart }} hari</p>
                                        @elseif($status === 'berlangsung')
                                            <p class="text-green-600 dark:text-green-400"><span class="font-medium">Hari
                                                    ke:</span> {{ $daysElapsed + 1 }} dari {{ $leave->total_hari }}
                                                (Sisa: {{ $daysRemaining }} hari)
                                            </p>
                                        @elseif($status === 'selesai')
                                            <p class="text-gray-600 dark:text-gray-400"><span
                                                    class="font-medium">Selesai:</span> {{ $now->diffForHumans($end) }}
                                            </p>
                                        @endif --}}
                                        <p><span class="font-medium">Jenis Cuti:</span><strong>
                                                {{ Str::title($leave->leaveType->name) ?? 'N/A' }}</strong></p>
                                        <p><span class="font-medium">Total:</span> <strong>{{ $leave->total_hari }}
                                                hari</strong></p>
                                        <p class="italic text-gray-600 dark:text-gray-400"><span
                                                class="font-medium not-italic">Alasan:</span> "{{ $leave->alasan }}"
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-16 px-6 bg-white dark:bg-slate-800 rounded-xl shadow-md">
                <div class="flex flex-col items-center">
                    <svg class="w-16 h-16 text-blue-400 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    <p class="text-lg font-semibold text-gray-800 dark:text-gray-100">Belum Ada Riwayat Pengganti</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Anda belum pernah ditugaskan sebagai
                        pengganti untuk cuti orang lain.</p>
                </div>
            </div>
        @endforelse
    </div>

    @if ($leaves->hasPages())
        <div class="mt-6">
            {{ $leaves->links() }}
        </div>
    @endif
</x-app-layout>
