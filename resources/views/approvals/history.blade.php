<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2
                    class="border-l-[5px] border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Riwayat Aktivitas Approval') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Semua aktivitas persetujuan cuti yang telah Anda lakukan.
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    Total Aktivitas: <span
                        class="font-semibold text-gray-700 dark:text-gray-300">{{ $histories->total() }}</span>
                </span>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">
        @forelse ($histories as $history)
            <div x-data="{ open: false }"
                class="bg-white dark:bg-slate-800 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 border-l-4 {{ $history->approved_by === $history->leave->user_id ? 'border-blue-500' : ($history->approved_by === $history->leave->pengganti_id ? 'border-purple-500' : 'border-indigo-500') }}">

                {{-- Header Card --}}
                <div @click="open = !open"
                    class="flex items-center p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700/50 rounded-xl">

                    {{-- Avatar & User Info --}}
                    <div class="flex-shrink-0 mr-4">
                        <div class="relative">
                            <img loading="lazy"
                                class="h-12 w-12 rounded-full border-2 border-gray-200 dark:border-gray-600"
                                src="{{ asset('img/user.png') }}" alt="{{ $history->leave->user->name }}">
                            {{-- Role Badge Icon --}}
                            <div
                                class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full {{ $history->role_badge_color }} flex items-center justify-center border-2 border-white dark:border-slate-800">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                    {!! $history->role_icon !!}
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 grid grid-cols-1 md:grid-cols-5 gap-3 items-center">
                        {{-- Column 1: Activity Description --}}
                        <div class="md:col-span-2">
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ $history->activity_description }}
                            </p>
                            <div class="flex items-center mt-1 space-x-2">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $history->role_badge_color }}">
                                    <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor">
                                        {!! $history->role_icon !!}
                                    </svg>
                                    {{ $history->role_context }}
                                </span>
                            </div>
                        </div>

                        {{-- Column 2: Leave Type --}}
                        <div class="hidden md:block">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Jenis Cuti</p>
                            <p
                                class="text-sm font-medium text-gray-900 dark:text-gray-100 whitespace-normal break-words">
                                {{ Str::title($history->leave->leaveType->name) ?? 'N/A' }}
                                @if ($history->leave->is_mendadak)
                                    <span
                                        class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">
                                        ⚡ Mendadak
                                    </span>
                                @endif
                            </p>
                        </div>

                        {{-- Column 3: Date --}}
                        <div class="hidden md:block">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Tanggal</p>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                {{ $history->created_at->isoFormat('D MMM YYYY') }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $history->created_at->isoFormat('HH:mm') }}
                            </p>
                        </div>

                        {{-- Column 4: Status Badge --}}
                        <div class="flex items-center justify-between md:justify-end">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $history->status_badge_class }}">
                                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    {!! $history->status_icon !!}
                                </svg>
                                {{ $history->status_label }}
                            </span>

                            <svg class="w-5 h-5 text-gray-400 transition-transform duration-300 ml-3"
                                :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Detail Card (Expanded) --}}
                <div x-show="open" x-collapse>
                    <div class="px-5 pb-5 pt-2 border-t border-gray-200 dark:border-slate-700">
                        <div class="grid md:grid-cols-2 gap-6">
                            {{-- Left Column: Detail Pengajuan --}}
                            <div>
                                <h4
                                    class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                    Detail Pengajuan Cuti
                                </h4>

                                <div class="space-y-2.5 text-sm">
                                    <div class="flex items-start">
                                        <span
                                            class="font-medium text-gray-600 dark:text-gray-400 w-28 flex-shrink-0">Pemohon:</span>
                                        <div class="flex-1">
                                            <p class="text-gray-900 dark:text-gray-100 font-semibold">
                                                {{ $history->leave->user->name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ strtoupper($history->leave->user->position->nama_jabatan) ?? 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <span
                                            class="font-medium text-gray-600 dark:text-gray-400 w-28 flex-shrink-0">Jenis
                                            Cuti:</span>
                                        <span
                                            class="text-gray-900 dark:text-gray-100">{{ Str::title($history->leave->leaveType->name) }}</span>
                                    </div>
                                    <div class="flex items-start">
                                        <span
                                            class="font-medium text-gray-600 dark:text-gray-400 w-28 flex-shrink-0">Tanggal
                                            Cuti:</span>
                                        <div class="flex-1">
                                            <p class="text-gray-900 dark:text-gray-100">
                                                {{ \Carbon\Carbon::parse($history->leave->start_date)->isoFormat('D MMMM YYYY') }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">sampai</p>
                                            <p class="text-gray-900 dark:text-gray-100">
                                                {{ \Carbon\Carbon::parse($history->leave->end_date)->isoFormat('D MMMM YYYY') }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                Total: <span
                                                    class="font-semibold">{{ $history->leave->total_hari }}</span> hari
                                                kerja
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <span
                                            class="font-medium text-gray-600 dark:text-gray-400 w-28 flex-shrink-0">Alasan:</span>
                                        <span
                                            class="text-gray-900 dark:text-gray-100 italic">"{{ $history->leave->alasan }}"</span>
                                    </div>

                                    {{-- Info Revisi jika ada --}}
                                    @if (in_array($history->status, ['revision_requested', 'revision_accepted', 'revision_rejected']))
                                        @php
                                            $revisionApproval = $history->leave->approvals
                                                ->where('revised_start_date', '!=', null)
                                                ->first();
                                        @endphp

                                        @if ($revisionApproval)
                                            <div class="pt-3 mt-3 border-t border-yellow-200 dark:border-yellow-800">
                                                <div
                                                    class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-3">
                                                    <p
                                                        class="text-xs font-semibold text-yellow-800 dark:text-yellow-300 mb-2 flex items-center">
                                                        <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24"
                                                            stroke-width="2" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                                        </svg>
                                                        Informasi Revisi Tanggal
                                                    </p>
                                                    <div class="text-xs text-yellow-800 dark:text-yellow-200 space-y-2">
                                                        <div>
                                                            <p class="font-semibold mb-1">Tanggal Usulan Revisi:</p>
                                                            <p class="ml-3">
                                                                {{ \Carbon\Carbon::parse($revisionApproval->revised_start_date)->isoFormat('D MMMM YYYY') }}
                                                                -
                                                                {{ \Carbon\Carbon::parse($revisionApproval->revised_end_date)->isoFormat('D MMMM YYYY') }}
                                                                <span
                                                                    class="ml-2 px-2 py-0.5 bg-yellow-200 dark:bg-yellow-800 rounded-full">
                                                                    {{ $revisionApproval->revised_total_hari }} hari
                                                                </span>
                                                            </p>
                                                        </div>
                                                        @if ($history->status === 'revision_requested')
                                                            <p class="text-yellow-700 dark:text-yellow-300">
                                                                ⏳ Menunggu tanggapan dari
                                                                {{ $history->leave->user->name }}
                                                            </p>
                                                        @elseif($history->status === 'revision_accepted')
                                                            <p class="text-green-700 dark:text-green-300">
                                                                ✅ Revisi diterima oleh pemohon
                                                            </p>
                                                        @elseif($history->status === 'revision_rejected')
                                                            <p class="text-red-700 dark:text-red-300">
                                                                ❌ Revisi ditolak oleh pemohon
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endif

                                    @if ($history->catatan)
                                        <div class="pt-2 mt-2 border-t border-gray-200 dark:border-gray-700">
                                            <div class="flex items-start">
                                                <span
                                                    class="font-medium text-gray-600 dark:text-gray-400 w-28 flex-shrink-0">Catatan:</span>
                                                <span
                                                    class="text-gray-900 dark:text-gray-100">{{ $history->catatan }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @if ($history->leave->proof_image)
                                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-2">
                                            Bukti Pendukung
                                        </p>
                                        <div class="flex justify-start">
                                            <img src="{{ asset('storage/' . $history->leave->proof_image) }}"
                                                alt="Bukti Cuti"
                                                class="max-w-full h-auto max-h-40 rounded-lg shadow-md border border-gray-200 dark:border-gray-600 cursor-pointer hover:opacity-90 transition"
                                                onclick="window.open(this.src, '_blank')">
                                        </div>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Klik untuk memperbesar
                                        </p>
                                    </div>
                                @endif
                            </div>

                            {{-- Right Column: Activity Info & Timeline --}}
                            <div>
                                <h4
                                    class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                                    </svg>
                                    Proses Cuti :
                                </h4>

                                {{-- Your Activity --}}
                                <div
                                    class="mb-4 p-4 rounded-lg {{ $history->role_badge_color }} border-2 {{ $history->approved_by === $history->leave->user_id ? 'border-blue-300 dark:border-blue-700' : ($history->approved_by === $history->leave->pengganti_id ? 'border-purple-300 dark:border-purple-700' : 'border-indigo-300 dark:border-indigo-700') }}">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 mt-1">
                                            <div
                                                class="w-10 h-10 rounded-full {{ $history->status_badge_class }} flex items-center justify-center">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="2.5" stroke="currentColor">
                                                    {!! $history->status_icon !!}
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <p class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                                {{ $history->status_label }}
                                            </p>
                                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                                                {{ $history->role_context }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                📅 {{ $history->created_at->isoFormat('dddd, D MMMM YYYY') }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                ⏰ {{ $history->created_at->isoFormat('HH:mm') }} WIB
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Divider --}}
                                <div class="my-4 border-t border-gray-200 dark:border-gray-700"></div>

                                {{-- Timeline All Approvals --}}
                                <h5 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-3">
                                    Timeline Approval Lengkap</h5>
                                <div class="space-y-3">
                                    @foreach ($history->leave->approvals->sortBy('step') as $approval)
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 mt-1">
                                                @php
                                                    $approvalStatusClass = match ($approval->status) {
                                                        'approved'
                                                            => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                                        'rejected'
                                                            => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                                        'pending'
                                                            => 'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400',
                                                        default
                                                            => 'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400',
                                                    };
                                                @endphp
                                                <div
                                                    class="w-8 h-8 rounded-full flex items-center justify-center {{ $approvalStatusClass }} border-2 border-white dark:border-slate-800">
                                                    <span class="text-xs font-bold">{{ $approval->step }}</span>
                                                </div>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $approval->approver->name }}
                                                    @if ($approval->approver_id === Auth::id())
                                                        <span
                                                            class="text-xs text-blue-600 dark:text-blue-400">(Anda)</span>
                                                    @endif
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ strtoupper($approval->approver->role) }} •
                                                    @if ($approval->status === 'pending')
                                                        <span
                                                            class="text-yellow-600 dark:text-yellow-400 font-semibold">Menunggu</span>
                                                    @else
                                                        <span
                                                            class="capitalize font-semibold">{{ $approval->status }}</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach

                                    {{-- Status Final Leave --}}
                                    <div class="pt-3 mt-3 border-t border-gray-200 dark:border-gray-700">
                                        <div
                                            class="flex items-center justify-between p-3 rounded-lg {{ $history->leave->status_final === 'approved' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : ($history->leave->status_final === 'rejected' ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800' : 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800') }}">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 mr-2 {{ $history->leave->status_final === 'approved' ? 'text-green-600 dark:text-green-400' : ($history->leave->status_final === 'rejected' ? 'text-red-600 dark:text-red-400' : 'text-yellow-600 dark:text-yellow-400') }}"
                                                    fill="none" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor">
                                                    @if ($history->leave->status_final === 'approved')
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    @elseif($history->leave->status_final === 'rejected')
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    @endif
                                                </svg>
                                                <span
                                                    class="text-sm font-semibold {{ $history->leave->status_final === 'approved' ? 'text-green-800 dark:text-green-300' : ($history->leave->status_final === 'rejected' ? 'text-red-800 dark:text-red-300' : 'text-yellow-800 dark:text-yellow-300') }}">
                                                    Status Final: {{ ucfirst($history->leave->status_final) }}
                                                </span>
                                            </div>
                                        </div>
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
                    <svg class="w-20 h-20 text-gray-300 dark:text-gray-600 mb-4" fill="none" viewBox="0 0 24 24"
                        stroke-width="1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                    <p class="text-lg font-semibold mb-1 text-gray-800 dark:text-gray-100">Belum Ada Aktivitas</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Riwayat aktivitas approval Anda akan muncul di
                        sini.</p>
                </div>
            </div>
        @endforelse

        {{-- Pagination --}}
        @if ($histories->hasPages())
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-md p-4">
                {{ $histories->links() }}
            </div>
        @endif
    </div>

    <x-toast-notification />
</x-app-layout>
