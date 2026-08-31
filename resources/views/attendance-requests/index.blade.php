<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="border-l-4 border-primary-700 pl-4 font-bold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Pengajuan Kehadiran Saya') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 pl-4">
                    Riwayat izin terlambat, pulang awal, meninggalkan kantor, dan koreksi absensi.
                </p>
            </div>
            <div class="flex justify-end">
                <a href="{{ route('kehadiran.create') }}"
                    class="inline-flex items-center justify-center px-4 py-2.5 bg-primary-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-wider hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:ring-offset-slate-800 transition shadow-sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Ajukan Kehadiran
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $totalPending = $attendanceRequests->filter(fn($r) => $r->status === 'pending')->count();
        $totalApproved = $attendanceRequests->filter(fn($r) => $r->status === 'approved')->count();
        $totalRejected = $attendanceRequests->filter(fn($r) => $r->status === 'rejected')->count();
    @endphp

    <div class="space-y-6" x-data="{
        cancelModalOpen: false,
        cancelActionUrl: '',
        cancelTypeLabel: '',
        previewModalOpen: false,
        previewImageUrl: '',
        openCancelModal(url, typeLabel) {
            this.cancelActionUrl = url;
            this.cancelTypeLabel = typeLabel;
            this.cancelModalOpen = true;
        },
        openImagePreview(url) {
            this.previewImageUrl = url;
            this.previewModalOpen = true;
        }
    }">

        {{-- Stat Summary Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 px-4 py-3.5 flex items-center gap-3.5 shadow-sm">
                <div class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="text-xl font-bold text-gray-900 dark:text-gray-100 leading-none">{{ $attendanceRequests->total() }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">Total Pengajuan</div>
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
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">Menunggu Approval</div>
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
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">Disetujui</div>
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
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">Ditolak</div>
                </div>
            </div>
        </div>

        {{-- Main Table Container --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-slate-700 flex items-center justify-between bg-gray-50/50 dark:bg-slate-800/50">
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">Daftar Pengajuan Kehadiran</h3>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-primary-100 dark:bg-primary-950 text-primary-700 dark:text-primary-300">
                        {{ $attendanceRequests->total() }} pengajuan
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                    <thead>
                        <tr class="bg-gray-50/80 dark:bg-slate-700/50">
                            <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Jenis Pengajuan
                            </th>
                            <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Tanggal & Waktu
                            </th>
                            <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Atasan Approver
                            </th>
                            <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[200px]">
                                Alasan & Catatan
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
                        @forelse ($attendanceRequests as $attendanceRequest)
                            @php
                                $typeBadgeClass = match($attendanceRequest->type) {
                                    'late_arrival' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/60',
                                    'early_departure' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800/60',
                                    'leave_during_work' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800/60',
                                    'update_attendance' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/60',
                                    default => 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors">
                                
                                {{-- Jenis Pengajuan --}}
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $typeBadgeClass }}">
                                        {{ $attendanceRequest->type_label }}
                                    </span>
                                    <div class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">
                                        {{ $attendanceRequest->created_at->isoFormat('D MMM YYYY, HH:mm') }}
                                    </div>
                                </td>

                                {{-- Tanggal & Waktu --}}
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ $attendanceRequest->date->isoFormat('D MMMM YYYY') }}
                                    </div>
                                    <div class="text-xs font-mono font-medium text-gray-500 dark:text-gray-400 mt-0.5 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ \Illuminate\Support\Str::of($attendanceRequest->start_time)->substr(0, 5) }}
                                        @if ($attendanceRequest->end_time)
                                            — {{ \Illuminate\Support\Str::of($attendanceRequest->end_time)->substr(0, 5) }}
                                        @endif
                                    </div>
                                </td>

                                {{-- Atasan Approver --}}
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="text-xs font-semibold text-gray-800 dark:text-gray-200">
                                        {{ $attendanceRequest->approver->name ?? '-' }}
                                    </div>
                                    <div class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">
                                        {{ $attendanceRequest->approver ? strtoupper(str_replace('_', ' ', $attendanceRequest->approver->role)) : '-' }}
                                    </div>
                                </td>

                                {{-- Alasan & Lampiran --}}
                                <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300 max-w-xs">
                                    <div class="leading-relaxed">{{ $attendanceRequest->reason }}</div>
                                    
                                    <div class="flex flex-wrap items-center gap-2 mt-1.5">
                                        {{-- Foto Bukti --}}
                                        @if ($attendanceRequest->proof_image)
                                            <button type="button"
                                                @click="openImagePreview('{{ asset('storage/' . $attendanceRequest->proof_image) }}')"
                                                class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-primary-50 text-primary-700 hover:bg-primary-100 dark:bg-primary-950/50 dark:text-primary-300 border border-primary-200 dark:border-primary-800/60 transition">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                Lihat Bukti Foto
                                            </button>
                                        @endif

                                        {{-- Catatan Penolakan / Persetujuan --}}
                                        @if ($attendanceRequest->rejection_reason)
                                            <div class="w-full text-rose-600 dark:text-rose-400 text-[11px] font-medium flex items-center gap-1 mt-0.5">
                                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                <span>Alasan Ditolak: {{ $attendanceRequest->rejection_reason }}</span>
                                            </div>
                                        @elseif ($attendanceRequest->approval_note)
                                            <div class="w-full text-emerald-600 dark:text-emerald-400 text-[11px] font-medium flex items-center gap-1 mt-0.5">
                                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                <span>Catatan: {{ $attendanceRequest->approval_note }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="px-5 py-4 whitespace-nowrap">
                                    @if ($attendanceRequest->status === 'approved')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/60">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Disetujui
                                        </span>
                                    @elseif ($attendanceRequest->status === 'rejected')
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
                                    @if ($attendanceRequest->status === 'pending')
                                        <button type="button"
                                            @click="openCancelModal('{{ route('kehadiran.destroy', $attendanceRequest) }}', '{{ addslashes($attendanceRequest->type_label) }}')"
                                            class="inline-flex items-center px-3 py-1.5 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-950/40 dark:text-rose-300 dark:hover:bg-rose-900/60 border border-rose-200 dark:border-rose-800/60 transition">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Batalkan
                                        </button>
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
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">Belum ada pengajuan kehadiran</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Anda belum memiliki catatan pengajuan penyesuaian absensi.</p>
                                        <a href="{{ route('kehadiran.create') }}"
                                            class="mt-4 inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold transition shadow-sm">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" />
                                            </svg>
                                            Ajukan Kehadiran Baru
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            @if ($attendanceRequests->hasPages())
                <div class="px-5 py-3.5 bg-gray-50/70 dark:bg-slate-700/30 border-t border-gray-200 dark:border-slate-700 flex items-center justify-between">
                    {{ $attendanceRequests->links() }}
                </div>
            @endif
        </div>

        {{-- Modal Konfirmasi Batalkan Pengajuan Kehadiran --}}
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
                            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Batalkan Pengajuan Kehadiran?</h3>
                            <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                Apakah Anda yakin ingin membatalkan pengajuan <strong class="text-gray-800 dark:text-gray-200 font-semibold" x-text="cancelTypeLabel"></strong> ini? Pengajuan yang dibatalkan akan dihapus dari antrean persetujuan atasan.
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
                                Ya, Batalkan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Lightbox Pratinjau Foto Bukti --}}
        <div x-show="previewModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-gray-900/80 backdrop-blur-md" @click="previewModalOpen = false"></div>
            <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden p-4 border border-gray-200 dark:border-slate-700"
                x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-slate-700">
                    <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Bukti Gambar / Presensi
                    </h4>
                    <button type="button" @click="previewModalOpen = false"
                        class="p-1 rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-2 flex items-center justify-center bg-gray-50 dark:bg-slate-900/50 rounded-xl mt-3">
                    <img :src="previewImageUrl" alt="Foto Bukti" class="max-h-[70vh] w-auto rounded-lg object-contain" />
                </div>
            </div>
        </div>

    </div>

    <x-toast-notification />
</x-app-layout>
