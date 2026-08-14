<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2
                    class=" border-l-[5px] border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Pengajuan Cuti Saya') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Daftar pengajuan cuti yang telah Anda ajukan.
                </p>
            </div>
            <div class="flex justify-end">
                <a href="{{ route('cuti.create') }}"
                    class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-500 active:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:ring-offset-slate-800 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Ajukan Cuti Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- Alert untuk Revisi Pending --}}
        @php
            $revisionsPending = $leaves->filter(fn($leave) => $leave->is_revision_pending);
        @endphp

        @if ($revisionsPending->count() > 0)
            @foreach ($revisionsPending as $revisionLeave)
                <div x-data="{ open: true }" x-show="open" x-transition
                    class="bg-gradient-to-r from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 border-2 border-yellow-400 dark:border-yellow-600 rounded-xl shadow-lg overflow-hidden">

                    <div class="p-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div
                                    class="flex items-center justify-center w-12 h-12 rounded-full bg-yellow-500 dark:bg-yellow-600">
                                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="text-lg font-bold text-yellow-900 dark:text-yellow-100">
                                            Permintaan Revisi Tanggal Cuti
                                        </h3>
                                        <p class="mt-1 text-sm text-yellow-800 dark:text-yellow-200">
                                            <span
                                                class="font-semibold">{{ $revisionLeave->revisionApproval->approver->name }}</span>
                                            meminta perubahan tanggal untuk pengajuan cuti anda.
                                        </p>
                                    </div>
                                </div>

                                {{-- Perbandingan Tanggal --}}
                                <div class="mt-4 grid md:grid-cols-2 gap-4">
                                    {{-- Tanggal Lama --}}
                                    <div
                                        class="bg-white dark:bg-slate-800 rounded-lg p-4 border border-red-200 dark:border-red-800">
                                        <div class="flex items-center mb-2">
                                            <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-2" fill="none"
                                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            <span
                                                class="text-xs font-semibold text-red-700 dark:text-red-400 uppercase">Tanggal
                                                Pengajuan Anda</span>
                                        </div>
                                        <p
                                            class="text-sm font-medium text-gray-700 dark:text-gray-300 line-through decoration-2 decoration-red-500">
                                            {{ \Carbon\Carbon::parse($revisionLeave->start_date)->isoFormat('D MMMM YYYY') }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">sampai</p>
                                        <p
                                            class="text-sm font-medium text-gray-700 dark:text-gray-300 line-through decoration-2 decoration-red-500">
                                            {{ \Carbon\Carbon::parse($revisionLeave->end_date)->isoFormat('D MMMM YYYY') }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                            <span class="font-semibold">{{ $revisionLeave->total_hari }}</span> hari
                                            kerja
                                        </p>
                                    </div>

                                    {{-- Tanggal Baru (Revisi) --}}
                                    <div
                                        class="bg-white dark:bg-slate-800 rounded-lg p-4 border-2 border-green-400 dark:border-green-600 relative">
                                        <div
                                            class="absolute -top-3 -right-3 bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                                            BARU
                                        </div>
                                        <div class="flex items-center mb-2">
                                            <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-2" fill="none"
                                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span
                                                class="text-xs font-semibold text-green-700 dark:text-green-400 uppercase">Usulan
                                                Tanggal Baru</span>
                                        </div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                            {{ \Carbon\Carbon::parse($revisionLeave->revisionApproval->revised_start_date)->isoFormat('D MMMM YYYY') }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">sampai</p>
                                        <p class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                            {{ \Carbon\Carbon::parse($revisionLeave->revisionApproval->revised_end_date)->isoFormat('D MMMM YYYY') }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                            <span
                                                class="font-semibold">{{ $revisionLeave->revisionApproval->revised_total_hari }}</span>
                                            hari kerja
                                        </p>
                                    </div>
                                </div>

                                {{-- Info Tambahan --}}
                                <div
                                    class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2 mt-0.5 flex-shrink-0"
                                            fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                        </svg>
                                        <div class="text-xs text-blue-800 dark:text-blue-200">
                                            <p class="font-semibold mb-1">Jenis Cuti:
                                                {{ Str::title($revisionLeave->leaveType->name) }}</p>
                                            <p><span class="font-semibold">Alasan Cuti Anda:</span>
                                                "{{ $revisionLeave->alasan }}"</p>
                                            <p class="mt-2 text-blue-700 dark:text-blue-300">
                                                Silakan tinjau perubahan tanggal di atas. Jika Anda setuju, cuti akan
                                                langsung disetujui. Jika menolak, pengajuan akan dibatalkan.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Action Buttons Wrapper --}}
                                <div x-data="{
                                    modalOpen: false,
                                    actionUrl: '',
                                    actionType: '', // 'accept' or 'reject'
                                    modalTitle: '',
                                    modalMessage: '',
                                
                                    confirmAction(type, url) {
                                        this.actionType = type;
                                        this.actionUrl = url;
                                        this.modalOpen = true;
                                
                                        if (type === 'accept') {
                                            this.modalTitle = 'Terima Revisi & Setujui Cuti?';
                                            this.modalMessage = 'Apakah Anda yakin menerima revisi tanggal ini? Cuti akan langsung disetujui.';
                                        } else {
                                            this.modalTitle = 'Tolak & Batalkan Pengajuan?';
                                            this.modalMessage = 'Tolak revisi tanggal? Pengajuan cuti Anda akan dibatalkan secara otomatis.';
                                        }
                                    }
                                }" class="mt-5">

                                    {{-- 1. Tombol Pemicu (Trigger Buttons) --}}
                                    <div class="flex flex-col sm:flex-row gap-3">
                                        {{-- Tombol Terima --}}
                                        <button type="button"
                                            @click="confirmAction('accept', '{{ route('cuti.accept-revision', $revisionLeave) }}')"
                                            class="flex-1 w-full inline-flex items-center justify-center px-6 py-3 border border-transparent text-sm font-semibold rounded-full text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-slate-800 shadow-lg hover:shadow-xl transition-all duration-200">
                                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Terima Revisi & Setujui Cuti
                                        </button>

                                        {{-- Tombol Tolak --}}
                                        <button type="button"
                                            @click="confirmAction('reject', '{{ route('cuti.reject-revision', $revisionLeave) }}')"
                                            class="flex-1 w-full inline-flex items-center justify-center px-6 py-3 border-2 border-red-600 dark:border-red-500 text-sm font-semibold rounded-full text-red-600 dark:text-red-400 bg-white dark:bg-slate-800 hover:bg-red-50 dark:hover:bg-red-900/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-slate-800 transition-all duration-200">
                                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Tolak & Batalkan Pengajuan
                                        </button>
                                    </div>

                                    {{-- 2. Modal Confirmation --}}
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
                                                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10 transition-colors duration-200"
                                                            :class="actionType === 'accept' ?
                                                                'bg-green-100 dark:bg-green-900/30' :
                                                                'bg-red-100 dark:bg-red-900/30'">

                                                            <svg x-show="actionType === 'accept'"
                                                                class="h-6 w-6 text-green-600 dark:text-green-400"
                                                                fill="none" viewBox="0 0 24 24"
                                                                stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M5 13l4 4L19 7" />
                                                            </svg>

                                                            <svg x-show="actionType === 'reject'"
                                                                class="h-6 w-6 text-red-600 dark:text-red-400"
                                                                fill="none" viewBox="0 0 24 24"
                                                                stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                            </svg>
                                                        </div>

                                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white"
                                                                id="modal-title" x-text="modalTitle"></h3>
                                                            <div class="mt-2">
                                                                <p class="text-sm text-gray-500 dark:text-gray-400"
                                                                    x-text="modalMessage"></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div
                                                    class="bg-gray-50 dark:bg-slate-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                                                    <form :action="actionUrl" method="POST"
                                                        class="w-full sm:w-auto">
                                                        @csrf @method('PATCH')
                                                        <button type="submit"
                                                            class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:text-sm transition-colors duration-200"
                                                            :class="actionType === 'accept'
                                                                ?
                                                                'bg-green-600 hover:bg-green-700 focus:ring-green-500' :
                                                                'bg-red-600 hover:bg-red-700 focus:ring-red-500'">
                                                            <span
                                                                x-text="actionType === 'accept' ? 'Ya, Terima Revisi' : 'Ya, Tolak'"></span>
                                                        </button>
                                                    </form>

                                                    <button type="button" @click="modalOpen = false"
                                                        class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-slate-600 shadow-sm px-4 py-2 bg-white dark:bg-slate-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors duration-200">
                                                        Batal
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif

        {{-- Tabel Pengajuan --}}
        <div
            class="bg-white dark:bg-slate-800 shadow-xl hover:shadow-xl/30 hover:-translate-y-1 transition-all duration-300 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-blue-100 dark:bg-blue-900 inset-shadow-sm inset-shadow-indigo-500">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">
                                Jenis Cuti</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">
                                Periode Cuti</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">
                                Total</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">
                                Alasan</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">
                                Status</th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($leaves as $leave)
                            <tr
                                class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition duration-150 {{ $leave->is_revision_pending ? 'bg-yellow-50 dark:bg-yellow-900/10' : '' }}">
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">
                                    {{ strtoupper($leave->leaveType->name) }}
                                    @if ($leave->is_mendadak)
                                        <span
                                            class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">
                                            ⚡ Mendadak
                                        </span>
                                    @endif
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-gray-100">
                                    {{ \Carbon\Carbon::parse($leave->start_date)->isoFormat('D MMM YYYY') }} -
                                    {{ \Carbon\Carbon::parse($leave->end_date)->isoFormat('D MMM YYYY') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $leave->total_hari }} hari</td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">
                                    {{ $leave->alasan }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($leave->is_revision_pending)
                                        <span
                                            class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                            <svg class="w-3 h-3 mr-1 mt-0.5" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Revisi Pending
                                        </span>
                                    @else
                                        @php
                                            $statusClass = '';
                                            if (strtolower($leave->status_final) === 'approved') {
                                                $statusClass = 'bg-status-success-bg text-status-success-text';
                                            } elseif (strtolower($leave->status_final) === 'pending') {
                                                $statusClass = 'bg-status-warning-bg text-status-warning-text';
                                            } elseif (strtolower($leave->status_final) === 'rejected') {
                                                $statusClass = 'bg-status-danger-bg text-status-danger-text';
                                            }
                                        @endphp
                                        <span
                                            class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                            {{ $leave->status_final }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if ($leave->is_revision_pending)
                                        <span class="text-yellow-600 dark:text-yellow-400 text-xs font-medium">
                                            Lihat detail di atas ↑
                                        </span>
                                    @else
                                        @switch(strtolower($leave->status_final))
                                            @case('pending')
                                                <form action="{{ route('cuti.destroy', $leave) }}" method="POST"
                                                    class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="px-3 py-1 text-sm rounded bg-red-600 text-white hover:bg-red-700"
                                                        onclick="return confirm('Yakin ingin membatalkan pengajuan cuti ini?');">
                                                        Batalkan
                                                    </button>
                                                </form>
                                            @break

                                            @case('approved')
                                                <a href="{{ route('cuti.print', $leave) }}" target="_blank"
                                                    class="px-3 py-1 text-sm rounded border border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white"
                                                    title="Cetak Form Cuti">
                                                    <i class="bi bi-printer"></i> Cetak
                                                </a>
                                            @break

                                            @default
                                                <span class="text-gray-400 dark:text-gray-500">-</span>
                                        @endswitch
                                    @endif
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center text-gray-500 dark:text-gray-400">
                                        <div class="flex flex-col items-center justify-center w-full">
                                            <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mb-4"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5 4.5l-2.25-2.25m0 0l2.25-2.25m-2.25 2.25l2.25 2.25M12 18.75l-2.25-2.25m2.25 2.25l2.25-2.25m-2.25 2.25l-2.25-2.25M6.34 7.5h11.32a2.25 2.25 0 012.25 2.25v7.5a2.25 2.25 0 01-2.25 2.25H6.34a2.25 2.25 0 01-2.25-2.25v-7.5a2.25 2.25 0 012.25-2.25z" />
                                            </svg>
                                            <p class="text-lg font-semibold mb-1 text-gray-800 dark:text-gray-100">Belum
                                                ada
                                                data pengajuan</p>
                                            <p class="text-sm">Silakan ajukan cuti baru untuk memulai.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 bg-gray-50 dark:bg-slate-700/50">
                    {{ $leaves->links() }}
                </div>
            </div>
        </div>

        <x-toast-notification />
    </x-app-layout>
