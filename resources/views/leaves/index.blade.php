<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="border-l-4 border-primary-700 pl-4 font-bold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Pengajuan Cuti Saya') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 pl-4">
                    Pantau status riwayat pengajuan cuti dan tindak lanjuti usulan revisi tanggal.
                </p>
            </div>
            <div class="flex justify-end">
                <a href="{{ route('cuti.create') }}"
                    class="inline-flex items-center justify-center px-4 py-2.5 bg-primary-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-wider hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:ring-offset-slate-800 transition shadow-sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Ajukan Cuti Baru
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $revisionsPending = $leaves->filter(fn($leave) => $leave->is_revision_pending);
        $totalPending = $leaves->filter(fn($l) => strtolower($l->status_final) === 'pending')->count();
        $totalApproved = $leaves->filter(fn($l) => strtolower($l->status_final) === 'approved')->count();
        $totalRejected = $leaves->filter(fn($l) => strtolower($l->status_final) === 'rejected')->count();
    @endphp

    <div class="space-y-6" x-data="{
        cancelModalOpen: false,
        cancelActionUrl: '',
        cancelLeaveType: '',
        openCancelModal(url, typeName) {
            this.cancelActionUrl = url;
            this.cancelLeaveType = typeName;
            this.cancelModalOpen = true;
        }
    }">

        {{-- Stat Summary Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 px-4 py-3.5 flex items-center gap-3.5 shadow-sm">
                <div class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="text-xl font-bold text-gray-900 dark:text-gray-100 leading-none">{{ $leaves->total() }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">Total Pengajuan Cuti</div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 px-4 py-3.5 flex items-center gap-3.5 shadow-sm">
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="text-xl font-bold text-amber-600 dark:text-amber-400 leading-none">{{ $totalPending }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">Menunggu Persetujuan</div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 px-4 py-3.5 flex items-center gap-3.5 shadow-sm">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400 leading-none">{{ $totalApproved }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">Disetujui (Approved)</div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 px-4 py-3.5 flex items-center gap-3.5 shadow-sm">
                <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="text-xl font-bold text-rose-600 dark:text-rose-400 leading-none">{{ $totalRejected }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">Ditolak / Dibatalkan</div>
                </div>
            </div>
        </div>

        {{-- Alert untuk Permintaan Revisi Tanggal Cuti --}}
        @if ($revisionsPending->count() > 0)
            @foreach ($revisionsPending as $revisionLeave)
                <div x-data="{ open: true }" x-show="open" x-transition
                    class="bg-gradient-to-br from-amber-50 via-amber-50/70 to-orange-50 dark:from-amber-950/40 dark:via-slate-800 dark:to-orange-950/30 border-2 border-amber-400/80 dark:border-amber-600 rounded-2xl shadow-md overflow-hidden">
                    
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-11 h-11 rounded-2xl bg-amber-500 text-white flex items-center justify-center shadow-md">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                </svg>
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                    <h3 class="text-base font-bold text-amber-950 dark:text-amber-100">
                                        Permintaan Revisi Tanggal Cuti
                                    </h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-200/70 dark:bg-amber-900/60 text-amber-900 dark:text-amber-200">
                                        Perlu Tindakan Anda
                                    </span>
                                </div>
                                <p class="mt-1 text-xs sm:text-sm text-amber-800/90 dark:text-amber-200 leading-relaxed">
                                    Atasan <strong class="font-semibold text-gray-900 dark:text-white">{{ $revisionLeave->revisionApproval->approver->name }}</strong> mengusulkan perubahan tanggal untuk pengajuan cuti Anda.
                                </p>

                                {{-- Perbandingan Tanggal: Lama vs Usulan Baru --}}
                                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {{-- Tanggal Lama --}}
                                    <div class="bg-white/90 dark:bg-slate-800 rounded-xl p-4 border border-rose-200 dark:border-rose-900/60 shadow-sm">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-xs font-bold text-rose-700 dark:text-rose-400 uppercase tracking-wider flex items-center">
                                                <svg class="w-4 h-4 text-rose-500 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                Tanggal Pengajuan Anda
                                            </span>
                                            <span class="text-[11px] font-semibold text-rose-600 bg-rose-50 dark:bg-rose-950/40 px-2 py-0.5 rounded-md">
                                                Lama
                                            </span>
                                        </div>
                                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-200 line-through decoration-2 decoration-rose-500">
                                            {{ \Carbon\Carbon::parse($revisionLeave->start_date)->isoFormat('D MMMM YYYY') }} — {{ \Carbon\Carbon::parse($revisionLeave->end_date)->isoFormat('D MMMM YYYY') }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Durasi: <strong class="font-semibold text-gray-700 dark:text-gray-300">{{ $revisionLeave->total_hari }} hari kerja</strong>
                                        </div>
                                    </div>

                                    {{-- Tanggal Baru (Revisi) --}}
                                    <div class="bg-white/90 dark:bg-slate-800 rounded-xl p-4 border-2 border-emerald-500 dark:border-emerald-500 shadow-sm relative">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider flex items-center">
                                                <svg class="w-4 h-4 text-emerald-500 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Usulan Tanggal Baru
                                            </span>
                                            <span class="text-[11px] font-bold text-emerald-700 bg-emerald-100 dark:bg-emerald-950 dark:text-emerald-300 px-2.5 py-0.5 rounded-full shadow-sm">
                                                USULAN ATASAN
                                            </span>
                                        </div>
                                        <div class="text-sm font-bold text-emerald-900 dark:text-emerald-100">
                                            {{ \Carbon\Carbon::parse($revisionLeave->revisionApproval->revised_start_date)->isoFormat('D MMMM YYYY') }} — {{ \Carbon\Carbon::parse($revisionLeave->revisionApproval->revised_end_date)->isoFormat('D MMMM YYYY') }}
                                        </div>
                                        <div class="text-xs text-emerald-700 dark:text-emerald-400 mt-1">
                                            Durasi Baru: <strong class="font-bold">{{ $revisionLeave->revisionApproval->revised_total_hari }} hari kerja</strong>
                                        </div>
                                    </div>
                                </div>

                                {{-- Catatan / Info Detail --}}
                                <div class="mt-3.5 p-3.5 bg-blue-50/80 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800/60 rounded-xl text-xs text-blue-900 dark:text-blue-200">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div class="space-y-0.5">
                                            <div><strong>Jenis Cuti:</strong> {{ Str::title($revisionLeave->leaveType->name) }}</div>
                                            <div><strong>Alasan Awal Anda:</strong> &ldquo;{{ $revisionLeave->alasan }}&rdquo;</div>
                                            <div class="pt-1 text-blue-700 dark:text-blue-300 text-[11px]">
                                                Jika Anda setuju, cuti akan langsung disetujui sesuai tanggal usulan baru. Jika menolak, pengajuan akan dibatalkan.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Action Buttons --}}
                                <div x-data="{
                                    modalOpen: false,
                                    actionUrl: '',
                                    actionType: '',
                                    modalTitle: '',
                                    modalMessage: '',
                                    confirmAction(type, url) {
                                        this.actionType = type;
                                        this.actionUrl = url;
                                        this.modalOpen = true;
                                        if (type === 'accept') {
                                            this.modalTitle = 'Terima Usulan Revisi Tanggal?';
                                            this.modalMessage = 'Pengajuan cuti Anda akan langsung disetujui sesuai dengan rentang tanggal baru yang diusulkan atasan.';
                                        } else {
                                            this.modalTitle = 'Tolak & Batalkan Pengajuan?';
                                            this.modalMessage = 'Pengajuan cuti ini akan dibatalkan secara otomatis. Anda dapat mengajukan permohonan cuti baru jika diperlukan.';
                                        }
                                    }
                                }" class="mt-4">
                                    <div class="flex flex-col sm:flex-row items-center gap-2.5">
                                        <button type="button"
                                            @click="confirmAction('accept', '{{ route('cuti.accept-revision', $revisionLeave) }}')"
                                            class="w-full sm:flex-1 inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Terima Revisi & Setujui Cuti
                                        </button>

                                        <button type="button"
                                            @click="confirmAction('reject', '{{ route('cuti.reject-revision', $revisionLeave) }}')"
                                            class="w-full sm:flex-1 inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-xs font-bold text-rose-700 dark:text-rose-300 bg-white dark:bg-slate-800 border border-rose-300 dark:border-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/30 focus:outline-none focus:ring-2 focus:ring-rose-500 transition">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Tolak & Batalkan Pengajuan
                                        </button>
                                    </div>

                                    {{-- Modal Konfirmasi Revisi --}}
                                    <div x-show="modalOpen" x-cloak
                                        class="fixed inset-0 z-50 flex items-center justify-center p-4"
                                        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
                                        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
                                        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                                        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="modalOpen = false"></div>
                                        <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
                                            x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100">
                                            <div class="h-1.5 w-full" :class="actionType === 'accept' ? 'bg-emerald-600' : 'bg-rose-600'"></div>
                                            <div class="p-6">
                                                <div class="flex gap-4">
                                                    <div class="flex-shrink-0 w-11 h-11 rounded-xl flex items-center justify-center"
                                                        :class="actionType === 'accept' ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400' : 'bg-rose-100 dark:bg-rose-950 text-rose-600 dark:text-rose-400'">
                                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path x-show="actionType === 'accept'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                            <path x-show="actionType === 'reject'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100" x-text="modalTitle"></h3>
                                                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 leading-relaxed" x-text="modalMessage"></p>
                                                    </div>
                                                </div>
                                                <div class="mt-6 flex justify-end gap-2">
                                                    <button type="button" @click="modalOpen = false"
                                                        class="px-4 py-2.5 rounded-xl border border-gray-300 dark:border-slate-600 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition">
                                                        Batal
                                                    </button>
                                                    <form :action="actionUrl" method="POST">
                                                        @csrf @method('PATCH')
                                                        <button type="submit"
                                                            class="px-4 py-2.5 rounded-xl text-xs font-semibold text-white shadow-sm transition"
                                                            :class="actionType === 'accept' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'">
                                                            <span x-text="actionType === 'accept' ? 'Ya, Terima Revisi' : 'Ya, Tolak & Batalkan'"></span>
                                                        </button>
                                                    </form>
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

        {{-- Tabel Pengajuan Cuti --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-slate-700 flex items-center justify-between bg-gray-50/50 dark:bg-slate-800/50">
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">Daftar Pengajuan Cuti</h3>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-primary-100 dark:bg-primary-950 text-primary-700 dark:text-primary-300">
                        {{ $leaves->total() }} pengajuan
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                    <thead>
                        <tr class="bg-gray-50/80 dark:bg-slate-700/50">
                            <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Jenis Cuti
                            </th>
                            <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Periode Cuti
                            </th>
                            <th scope="col" class="px-4 py-3.5 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Durasi
                            </th>
                            <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider max-w-xs">
                                Alasan
                            </th>
                            <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-5 py-3.5 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-100 dark:divide-slate-700/60">
                        @forelse ($leaves as $leave)
                            @php
                                $statusFinal = strtolower($leave->status_final);
                            @endphp
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors {{ $leave->is_revision_pending ? 'bg-amber-50/50 dark:bg-amber-950/20' : '' }}">
                                
                                {{-- Jenis Cuti --}}
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            {{ Str::title($leave->leaveType->name) }}
                                        </span>
                                        @if ($leave->is_mendadak)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">
                                                ⚡ Mendadak
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">
                                        Diajukan pada {{ $leave->created_at->isoFormat('D MMM YYYY, HH:mm') }}
                                    </div>
                                </td>

                                {{-- Periode Cuti --}}
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ \Carbon\Carbon::parse($leave->start_date)->isoFormat('D MMM YYYY') }} — {{ \Carbon\Carbon::parse($leave->end_date)->isoFormat('D MMM YYYY') }}
                                    </div>
                                </td>

                                {{-- Durasi --}}
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold bg-gray-100 dark:bg-slate-700 text-gray-800 dark:text-gray-200">
                                        {{ $leave->total_hari }} hari
                                    </span>
                                </td>

                                {{-- Alasan --}}
                                <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300 max-w-xs truncate" title="{{ $leave->alasan }}">
                                    {{ $leave->alasan }}
                                </td>

                                {{-- Status --}}
                                <td class="px-5 py-4 whitespace-nowrap">
                                    @if ($leave->is_revision_pending)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/60">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Revisi Pending
                                        </span>
                                    @elseif ($statusFinal === 'approved')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/60">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Disetujui
                                        </span>
                                    @elseif ($statusFinal === 'rejected')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/60">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Ditolak
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/60">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Menunggu Approval
                                        </span>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="px-5 py-4 whitespace-nowrap text-right text-xs font-semibold">
                                    @if ($leave->is_revision_pending)
                                        <span class="text-amber-600 dark:text-amber-400 font-medium">
                                            Tinjau di atas ↑
                                        </span>
                                    @elseif ($statusFinal === 'pending')
                                        <button type="button"
                                            @click="openCancelModal('{{ route('cuti.destroy', $leave) }}', '{{ addslashes(Str::title($leave->leaveType->name)) }}')"
                                            class="inline-flex items-center px-3 py-1.5 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-950/40 dark:text-rose-300 dark:hover:bg-rose-900/60 border border-rose-200 dark:border-rose-800/60 transition">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Batalkan
                                        </button>
                                    @elseif ($statusFinal === 'approved')
                                        <a href="{{ route('cuti.print', $leave) }}" target="_blank"
                                            class="inline-flex items-center px-3 py-1.5 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-950/40 dark:text-blue-300 dark:hover:bg-blue-900/60 border border-blue-200 dark:border-blue-800/60 transition"
                                            title="Cetak Surat Izin Cuti">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                            Cetak
                                        </a>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                        <div class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-slate-700/60 flex items-center justify-center text-gray-400 dark:text-gray-500 mb-3">
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">Belum ada pengajuan cuti</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Anda belum memiliki riwayat permohonan cuti aktif.</p>
                                        <a href="{{ route('cuti.create') }}"
                                            class="mt-4 inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold transition shadow-sm">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" />
                                            </svg>
                                            Ajukan Cuti Baru
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            @if ($leaves->hasPages())
                <div class="px-5 py-3.5 bg-gray-50/70 dark:bg-slate-700/30 border-t border-gray-200 dark:border-slate-700 flex items-center justify-between">
                    {{ $leaves->links() }}
                </div>
            @endif
        </div>

        {{-- Modal Konfirmasi Batalkan Pengajuan --}}
        <div x-show="cancelModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="cancelModalOpen = false"></div>
            <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
                x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">
                <div class="h-1.5 bg-rose-600 w-full"></div>
                <div class="p-6">
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-11 h-11 rounded-xl bg-rose-100 dark:bg-rose-950 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Batalkan Pengajuan Cuti?</h3>
                            <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                Apakah Anda yakin ingin membatalkan pengajuan <strong class="text-gray-800 dark:text-gray-200 font-semibold" x-text="cancelLeaveType"></strong> ini? Pengajuan yang dibatalkan akan dihapus dari antrean persetujuan atasan.
                            </p>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" @click="cancelModalOpen = false"
                            class="px-4 py-2.5 rounded-xl border border-gray-300 dark:border-slate-600 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition">
                            Kembali
                        </button>
                        <form :action="cancelActionUrl" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold transition shadow-sm">
                                Ya, Batalkan Cuti
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <x-toast-notification />
</x-app-layout>
