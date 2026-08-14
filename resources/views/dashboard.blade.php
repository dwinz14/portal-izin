<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2
                    class="border-l-[5px] border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Dashboard') }}
                </h2>
            </div>
            {{-- <div class="text-sm text-gray-500 dark:text-gray-400">
                {{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
            </div> --}}
        </div>
    </x-slot>

    <!-- Two-Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Left Column: Main Content -->
        <div class="space-y-6">
            <!-- Compact Welcome Section -->
            <div class="bg-gradient-to-r from-primary-600 to-primary-800 rounded-xl shadow-2xl p-6 text-white">
                <div class="flex items-center justify-between shadow-blue-500/50">
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold mb-2">
                            Selamat Datang, {{ strtoupper(auth()->user()->name) }}! 👋
                        </h1>
                        <p class="text-primary-100 text-base mb-4">
                            Ringkasan aktivitas dan status cuti Anda
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <span class="bg-white/20 backdrop-blur-sm rounded-lg px-3 py-1 text-sm">
                                {{ strtoupper(auth()->user()->position->nama_jabatan ?? '') }}
                            </span>
                            <span class="bg-white/20 backdrop-blur-sm rounded-lg px-3 py-1 text-sm">
                                {{ strtoupper(auth()->user()->office->nama_kantor ?? '') }}
                            </span>
                        </div>
                    </div>
                    <div class="hidden md:block ml-4">
                        <div
                            class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            @if (auth()->user()->role !== 'super_admin')
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Aksi Cepat</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('cuti.create') }}"
                            class="flex items-center p-3 bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/20 dark:hover:bg-primary-900/30 rounded-lg transition-all duration-200 group">
                            <div class="bg-primary-500 p-2 rounded-lg mr-3 group-hover:scale-110 transition-transform">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-primary-900 dark:text-primary-100 text-sm">Ajukan Cuti</p>
                                <p class="text-xs text-primary-600 dark:text-primary-300">Buat pengajuan baru</p>
                            </div>
                        </a>

                        <a href="{{ route('cuti.index') }}"
                            class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:hover:bg-blue-900/30 rounded-lg transition-all duration-200 group">
                            <div class="bg-blue-500 p-2 rounded-lg mr-3 group-hover:scale-110 transition-transform">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-blue-900 dark:text-blue-100 text-sm">Riwayat Cuti</p>
                                <p class="text-xs text-blue-600 dark:text-blue-300">Lihat pengajuan Anda</p>
                            </div>
                        </a>

                        @if (auth()->user()->role !== 'super_admin' && auth()->user()->role !== 'hrd')
                            <a href="{{ route('approval.index') }}"
                                class="flex items-center p-3 bg-green-50 hover:bg-green-100 dark:bg-green-900/20 dark:hover:bg-green-900/30 rounded-lg transition-all duration-200 group">
                                <div
                                    class="bg-green-500 p-2 rounded-lg mr-3 group-hover:scale-110 transition-transform">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-green-900 dark:text-green-100 text-sm">Persetujuan</p>
                                    <p class="text-xs text-green-600 dark:text-green-300">Kelola approval</p>
                                </div>
                            </a>
                        @endif

                        @if (auth()->user()->role !== 'super_admin' && auth()->user()->role !== 'hrd')
                            <a href="{{ route('approval.history') }}"
                                class="flex items-center p-3 bg-purple-50 hover:bg-purple-100 dark:bg-purple-900/20 dark:hover:bg-purple-900/30 rounded-lg transition-all duration-200 group">
                                <div
                                    class="bg-purple-500 p-2 rounded-lg mr-3 group-hover:scale-110 transition-transform">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-purple-900 dark:text-purple-100 text-sm">Riwayat Approval
                                    </p>
                                    <p class="text-xs text-purple-600 dark:text-purple-300">Lihat history</p>
                                </div>
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column: Pending Approvals and Activity -->
        <div class="space-y-6">
            <!-- Pending Leave Applications -->
            <x-stepper-progress :pendingLeaves="$pendingLeaves" />

            <!-- Summary Info Cards -->
            @if (auth()->user()->role !== 'super_admin')
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Ringkasan Cuti</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <!-- Total Leave Applications Card -->
                        <div
                            class="bg-white dark:bg-slate-800 p-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border-l-4 border-purple-500">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Pengajuan</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                        {{ $totalLeaveApplications }}
                                        <span class="text-sm text-gray-500">Pengajuan</span>
                                    </p>
                                </div>
                                <div class="bg-purple-100 dark:bg-purple-900/30 p-2 rounded-full">
                                    <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Semua pengajuan cuti Anda</p>
                        </div>

                        <!-- Remaining Leave Card -->
                        {{-- <div
                            class="bg-white dark:bg-slate-800 p-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border-l-4 border-green-500">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Cuti Yang Bisa
                                        Diambil</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                        {{ $totalRemaining }}
                                        <span class="text-sm text-gray-500">Hari</span>
                                    </p>
                                </div>
                                <div class="bg-green-100 dark:bg-green-900/30 p-2 rounded-full">
                                    <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                <div class="bg-green-500 h-1.5 rounded-full"
                                    style="width: {{ $totalQuota > 0 ? min(($totalRemaining / $totalQuota) * 100, 100) : 0 }}%">
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">dari total {{ $totalQuota }}
                                hari</p>
                        </div> --}}
                    </div>

                    <!-- Recent Leave Applications -->
                    @if ($recentLeaves->count() > 0)
                        <div class="mt-6">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Pengajuan Cuti
                                Terakhir</h4>
                            <div class="space-y-3">
                                @foreach ($recentLeaves as $leave)
                                    <div
                                        class="bg-white dark:bg-slate-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ Str::title($leave->leaveType->name ?? 'Cuti') }}
                                                    </span>
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                        @if ($leave->status_final === 'approved') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                                        @elseif($leave->status_final === 'rejected') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                                        @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 @endif">
                                                        {{ ucfirst($leave->status_final) }}
                                                    </span>
                                                </div>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    {{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }} -
                                                    {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}
                                                    ({{ $leave->total_hari }} hari)
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $leave->created_at->diffForHumans() }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
