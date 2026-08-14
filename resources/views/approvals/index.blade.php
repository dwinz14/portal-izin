<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2
                    class="border-l-4 border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Persetujuan Cuti') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Tinjau dan proses pengajuan cuti yang masuk.
                </p>
            </div>
            <div class="mt-2 sm:mt-0 flex items-center space-x-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                <span
                    class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-primary-100 dark:bg-primary-900/50 text-primary-600 dark:text-primary-300">
                    {{ $approvals->count() }}
                </span>
                <span>
                    Menunggu Tindakan
                </span>
            </div>
        </div>
    </x-slot>

    <div
        class="space-y-4 bg-white dark:bg-slate-800 shadow-xl hover:shadow-xl/30 hover:-translate-y-1 transition-all duration-300 rounded-xl overflow-hidden">
        @forelse ($approvals as $approval)
            @php
                $hasRevision = $approval->revised_at !== null;
                $isAtasan = $approval->step === 2;
            @endphp

            <div x-data="{ open: false }"
                class="bg-white dark:bg-slate-800 rounded-xl shadow-md transition-all duration-300 {{ $hasRevision ? 'border-2 border-yellow-400 dark:border-yellow-600' : '' }}">

                {{-- Header Card --}}
                <div @click="open = !open"
                    class="flex items-center p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700/50 rounded-xl">
                    <div class="flex-shrink-0 mr-4">
                        <img loading="lazy" class="h-10 w-10 rounded-full" src="{{ asset('img/user.png') }}"
                            alt="">
                    </div>
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ strtoupper($approval->leave->user->name) }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ strtoupper($approval->leave->user->position->nama_jabatan) }}</p>
                        </div>
                        <div class="hidden md:block">
                            <p class="text-sm text-gray-800 dark:text-gray-200 truncate">
                                {{ strtoupper($approval->leave->leaveType->name) }}
                                @if ($approval->leave->is_mendadak)
                                    <span
                                        class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">
                                        ⚡ Mendadak
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div class="hidden md:flex items-center justify-between">
                            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                                <span>{{ \Carbon\Carbon::parse($approval->leave->start_date)->isoFormat('D MMM') }} -
                                    {{ \Carbon\Carbon::parse($approval->leave->end_date)->isoFormat('D MMM') }}</span>
                            </div>

                            {{-- Badge Status Revisi --}}
                            @if ($hasRevision)
                                <span
                                    class="ml-3 inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Revisi Dikirim
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="ml-4">
                        <svg class="w-5 h-5 text-gray-400 transition-transform duration-300"
                            :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                {{-- Detail Card (Expanded) --}}
                <div x-show="open" x-collapse>
                    <div class="px-5 pb-5 pt-2 border-t border-gray-200 dark:border-slate-700">

                        {{-- Alert Info Revisi --}}
                        @if ($hasRevision)
                            <div
                                class="mb-4 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-600 p-4 rounded-r-lg">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 mr-3 flex-shrink-0"
                                        fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                    </svg>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-semibold text-yellow-800 dark:text-yellow-300 mb-1">
                                            Revisi Tanggal Telah Dikirim
                                        </h4>
                                        <p class="text-sm text-yellow-700 dark:text-yellow-400">
                                            Anda telah mengirim permintaan revisi tanggal pada <span
                                                class="font-semibold">{{ $approval->revised_at->isoFormat('D MMMM YYYY, HH:mm') }}</span>
                                        </p>
                                        <div class="mt-3 p-3 bg-white dark:bg-slate-800 rounded-lg">
                                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                Tanggal yang Anda usulkan:</p>
                                            <div class="flex items-center text-sm">
                                                <svg class="w-4 h-4 mr-2 text-blue-600 dark:text-blue-400"
                                                    fill="none" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                                </svg>
                                                <span class="font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ \Carbon\Carbon::parse($approval->revised_start_date)->isoFormat('D MMMM YYYY') }}
                                                    <span class="text-gray-500 dark:text-gray-400 mx-2">—</span>
                                                    {{ \Carbon\Carbon::parse($approval->revised_end_date)->isoFormat('D MMMM YYYY') }}
                                                </span>
                                                <span
                                                    class="ml-2 px-2 py-0.5 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 text-xs font-medium rounded-full">
                                                    {{ $approval->revised_total_hari }} hari
                                                </span>
                                            </div>
                                        </div>
                                        <p class="mt-3 text-xs text-yellow-600 dark:text-yellow-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Menunggu tanggapan dari {{ $approval->leave->user->name }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                            {{-- Detail Pengajuan --}}
                            <div class="flex-1">
                                <h4 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase mb-3">Detail
                                    Pengajuan</h4>
                                <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="flex items-start">
                                        <span class="font-medium w-32 flex-shrink-0">Approval sebagai</span>
                                        <span class="flex-1">: {{ $isAtasan ? 'Atasan' : 'Pengganti' }}</span>
                                    </div>
                                    <div class="flex items-start">
                                        <span class="font-medium w-32 flex-shrink-0">Tanggal</span>
                                        <span class="flex-1">:
                                            {{ \Carbon\Carbon::parse($approval->leave->start_date)->isoFormat('dddd, D MMMM YYYY') }}
                                            <span class="text-gray-400 dark:text-gray-500">s/d</span>
                                            {{ \Carbon\Carbon::parse($approval->leave->end_date)->isoFormat('dddd, D MMMM YYYY') }}
                                        </span>
                                    </div>
                                    <div class="flex items-start">
                                        <span class="font-medium w-32 flex-shrink-0">Total</span>
                                        <span class="flex-1">: {{ $approval->leave->total_hari }} hari kerja</span>
                                    </div>
                                    <div class="flex items-start">
                                        <span class="font-medium w-32 flex-shrink-0">Jenis Cuti</span>
                                        <span class="flex-1">:
                                            {{ strtoupper($approval->leave->leaveType->name) ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex items-start">
                                        <span class="font-medium w-32 flex-shrink-0">Alasan</span>
                                        <span class="flex-1">: "{{ $approval->leave->alasan }}"</span>
                                    </div>
                                </div>

                                @if ($approval->leave->proof_image)
                                    <div class="mt-4">
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-2">
                                            Bukti Gambar</p>
                                        <div class="flex justify-center">
                                            <img src="{{ asset('storage/' . $approval->leave->proof_image) }}"
                                                alt="Bukti Cuti"
                                                class="max-w-full h-auto max-h-48 rounded-lg shadow-md border border-gray-200 dark:border-gray-600 cursor-pointer hover:opacity-90 transition"
                                                onclick="window.open(this.src, '_blank')">
                                        </div>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 text-center">Klik
                                            gambar untuk memperbesar</p>
                                    </div>
                                @endif
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex flex-col space-y-3 lg:w-48 flex-shrink-0">
                                @if ($hasRevision)
                                    {{-- Disabled State dengan Info --}}
                                    <div class="relative">
                                        <button disabled
                                            class="w-full text-white bg-gray-400 cursor-not-allowed font-medium rounded-full text-sm px-5 py-2.5 text-center opacity-60"
                                            title="Revisi sudah dikirim, menunggu tanggapan pemohon">
                                            <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                            </svg>
                                            Tolak
                                        </button>
                                    </div>
                                    <div class="relative">
                                        <button disabled
                                            class="w-full text-white bg-gray-400 cursor-not-allowed font-medium rounded-full text-sm px-5 py-2.5 text-center opacity-60"
                                            title="Revisi sudah dikirim, menunggu tanggapan pemohon">
                                            <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                            </svg>
                                            Revisi Tanggal
                                        </button>
                                    </div>
                                    <div class="relative">
                                        <button disabled
                                            class="w-full text-white bg-gray-400 cursor-not-allowed font-medium rounded-full text-sm px-5 py-2.5 text-center opacity-60"
                                            title="Revisi sudah dikirim, menunggu tanggapan pemohon">
                                            <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                            </svg>
                                            Setujui
                                        </button>
                                    </div>
                                @else
                                    {{-- Active Buttons --}}
                                    <button type="button"
                                        @click="$dispatch('open-reject-modal', { approvalId: {{ $approval->id }} })"
                                        class="w-full text-white bg-red-700 hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-300 font-medium rounded-full text-sm px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900 transition">
                                        <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24"
                                            stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Tolak
                                    </button>

                                    @if ($isAtasan)
                                        <button type="button"
                                            @click="$dispatch('open-revision-modal', {
                                            approvalId: {{ $approval->id }},
                                            leaveId: {{ $approval->leave->id }},
                                            currentStart: '{{ $approval->leave->start_date }}',
                                            currentEnd: '{{ $approval->leave->end_date }}',
                                            userName: '{{ $approval->leave->user->name }}'
                                        })"
                                            class="w-full text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-4 focus:ring-yellow-300 font-medium rounded-full text-sm px-5 py-2.5 text-center dark:bg-yellow-500 dark:hover:bg-yellow-600 dark:focus:ring-yellow-800 transition">
                                            <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                            </svg>
                                            Revisi Tanggal
                                        </button>
                                    @endif

                                    <form action="{{ route('approval.approve', $approval) }}" method="POST"
                                        class="w-full">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                            class="w-full text-white bg-green-700 hover:bg-green-800 focus:outline-none focus:ring-4 focus:ring-green-300 font-medium rounded-full text-sm px-5 py-2.5 text-center dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800 transition">
                                            <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                            Setujui
                                        </button>
                                    </form>

                                    {{-- Modal Confirmation --}}
                                    <div x-show="modalOpen" style="display: none;"
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                        x-transition:leave="transition ease-in duration-200"
                                        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                        class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
                                        role="dialog" aria-modal="true">
                                        <div
                                            class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                            <div @click="modalOpen = false"
                                                class="fixed inset-0 bg-gray-500/75 dark:bg-slate-900/80 transition-opacity"
                                                aria-hidden="true"></div>
                                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                                                aria-hidden="true">&#8203;</span>
                                            <div x-transition:enter="transition ease-out duration-300"
                                                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                                x-transition:leave="transition ease-in duration-200"
                                                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                class="relative inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                                                <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                    <div class="sm:flex sm:items-start">
                                                        <div
                                                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                                            <svg class="h-6 w-6 text-red-600 dark:text-red-400"
                                                                fill="none" viewBox="0 0 24 24"
                                                                stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                            </svg>
                                                        </div>
                                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white"
                                                                id="modal-title">Tolak Pengajuan Cuti?</h3>
                                                            <div class="mt-2">
                                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                                    Apakah Anda yakin ingin menolak pengajuan ini?</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div
                                                    class="bg-gray-50 dark:bg-slate-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                                                    <form action="{{ route('approval.reject', $approval) }}"
                                                        method="POST" class="w-full sm:w-auto">
                                                        @csrf @method('PATCH')
                                                        <button type="submit"
                                                            class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm">
                                                            Ya, Tolak
                                                        </button>
                                                    </form>
                                                    <button type="button" @click="modalOpen = false"
                                                        class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-slate-600 shadow-sm px-4 py-2 bg-white dark:bg-slate-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:w-auto sm:text-sm">
                                                        Batal
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-16 px-6 bg-white dark:bg-slate-800 rounded-xl shadow-md">
                <div class="flex flex-col items-center">
                    <svg class="w-16 h-16 text-green-400 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-lg font-semibold text-gray-800 dark:text-gray-100">Inbox Persetujuan Kosong</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Semua pengajuan telah diproses.</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Modal Tolak Pengajuan --}}
    <div x-data="rejectModal()" @open-reject-modal.window="openModal($event.detail)" x-show="isOpen" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div @click="closeModal()" class="fixed inset-0 bg-gray-500/75 dark:bg-slate-900/80 transition-opacity"
                aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                Tolak Pengajuan Cuti?</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Apakah Anda yakin ingin menolak
                                    pengajuan ini?</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-slate-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <form :action="formAction" method="POST" class="w-full sm:w-auto">
                        @csrf @method('PATCH')
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm">
                            Ya, Tolak
                        </button>
                    </form>
                    <button type="button" @click="closeModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-slate-600 shadow-sm px-4 py-2 bg-white dark:bg-slate-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Revisi Tanggal --}}
    <div x-data="revisionModal()" @open-revision-modal.window="openModal($event.detail)" x-show="isOpen" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Overlay --}}
            <div x-show="isOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="closeModal()"
                class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75">
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            {{-- Modal Content --}}
            <div x-show="isOpen" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                <form :action="formAction" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="bg-white dark:bg-slate-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 dark:bg-yellow-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                                    Revisi Tanggal Cuti
                                </h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Untuk: <span class="font-semibold" x-text="userName"></span>
                                </p>

                                <div class="mt-4 space-y-4">
                                    <div
                                        class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                                        <p class="text-xs font-semibold text-blue-800 dark:text-blue-300 mb-1">
                                            Tanggal Pengajuan Saat Ini:
                                        </p>
                                        <p class="text-sm text-blue-900 dark:text-blue-200 font-medium"
                                            x-text="currentDateRange"></p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Tanggal Mulai Baru <span class="text-red-500">*</span>
                                        </label>
                                        <input type="date" name="revised_start_date" x-model="revisedStartDate"
                                            required
                                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Tanggal Selesai Baru <span class="text-red-500">*</span>
                                        </label>
                                        <input type="date" name="revised_end_date" x-model="revisedEndDate"
                                            required
                                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500">
                                    </div>

                                    <div
                                        class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-600 p-3 rounded-r-lg">
                                        <div class="flex">
                                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2 flex-shrink-0"
                                                fill="none" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                            </svg>
                                            <p class="text-xs text-yellow-700 dark:text-yellow-300">
                                                Pemohon akan menerima notifikasi dan diminta untuk menyetujui atau
                                                menolak perubahan tanggal ini.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-slate-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                            class="w-full inline-flex justify-center items-center rounded-full border border-transparent shadow-sm px-5 py-2.5 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm transition">
                            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                            </svg>
                            Kirim Permintaan Revisi
                        </button>
                        <button type="button" @click="closeModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-full border border-gray-300 dark:border-gray-600 shadow-sm px-5 py-2.5 bg-white dark:bg-slate-600 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:w-auto sm:text-sm transition">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function rejectModal() {
            return {
                isOpen: false,
                formAction: '',

                openModal(detail) {
                    this.formAction = `/approval/${detail.approvalId}/reject`;
                    this.isOpen = true;
                },

                closeModal() {
                    this.isOpen = false;
                }
            }
        }

        function revisionModal() {
            return {
                isOpen: false,
                formAction: '',
                currentDateRange: '',
                userName: '',
                revisedStartDate: '',
                revisedEndDate: '',

                openModal(detail) {
                    this.formAction = `/approval/${detail.approvalId}/request-revision`;
                    this.currentDateRange =
                        `${this.formatDate(detail.currentStart)} - ${this.formatDate(detail.currentEnd)}`;
                    this.userName = detail.userName;
                    this.revisedStartDate = detail.currentStart;
                    this.revisedEndDate = detail.currentEnd;
                    this.isOpen = true;
                },

                closeModal() {
                    this.isOpen = false;
                },

                formatDate(dateString) {
                    const options = {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    };
                    return new Date(dateString).toLocaleDateString('id-ID', options);
                }
            }
        }
    </script>

    <x-toast-notification />
</x-app-layout>
