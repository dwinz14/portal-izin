<x-app-layout :title="$title" :subtitle="$subtitle">
    <div x-data="{
        restoreModalOpen: {{ session('open_restore_modal') ? 'true' : 'false' }},
        confirmText: '',
        get canConfirm() { return this.confirmText === 'RESTORE' }
    }" class="max-w-6xl mx-auto pb-10">

        {{-- ═══ FLASH MESSAGES (Modern Toast Style) ═══ --}}
        <div class="fixed top-4 right-4 z-[100] flex flex-col gap-2 w-full max-w-sm pointer-events-none">
            @if (session('db_success'))
                <div x-data="{ show: true }" x-show="show" x-transition:enter="transform ease-out duration-300 transition"
                    x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                    x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" x-init="setTimeout(() => show = false, 6000)"
                    class="pointer-events-auto w-full bg-white border-l-4 border-emerald-500 rounded-xl shadow-xl overflow-hidden"
                    role="alert">
                    <div class="p-4 flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-emerald-500" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-bold text-slate-900">Berhasil</p>
                            <p class="mt-1 text-sm text-slate-500">{{ session('db_success') }}</p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button @click="show = false"
                                class="bg-white rounded-md inline-flex text-slate-400 hover:text-slate-500 focus:outline-none">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('db_error'))
                <div x-data="{ show: true }" x-show="show"
                    x-transition:enter="transform ease-out duration-300 transition"
                    x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                    x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" x-init="setTimeout(() => show = false, 8000)"
                    class="pointer-events-auto w-full bg-white border-l-4 border-rose-500 rounded-xl shadow-xl overflow-hidden"
                    role="alert">
                    <div class="p-4 flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-bold text-slate-900">Terjadi Kesalahan</p>
                            <p class="mt-1 text-sm text-slate-500">{{ session('db_error') }}</p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button @click="show = false"
                                class="bg-white rounded-md inline-flex text-slate-400 hover:text-slate-500 focus:outline-none">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Header Page --}}
        <div class="mb-8 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div
                    class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Database</h2>
                    <p class="text-sm text-slate-500">Pusat pencadangan dan pemulihan data sistem Anda.</p>
                </div>
            </div>
        </div>

        {{-- Peringatan jika proc_open tidak tersedia --}}
        @if (!$canRunProcess)
            <div
                class="mb-8 p-5 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-2xl flex items-start gap-4 shadow-sm">
                <div class="bg-amber-100 p-2 rounded-full flex-shrink-0">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-amber-900">Fitur Backup/Restore Belum Siap</h3>
                    <p class="text-sm text-amber-700 mt-1 leading-relaxed">Fungsi sistem (<code
                            class="bg-amber-200/50 px-1 rounded">proc_open</code>) saat ini dinonaktifkan oleh server.
                        Silakan hubungi tim IT atau penyedia Hosting Anda untuk mengaktifkannya agar Anda dapat
                        mencadangkan data.</p>
                </div>
            </div>
        @endif

        {{-- ═══ TOP DASHBOARD: HEALTH CHECK ═══ --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">

            {{-- 1. Status Koneksi --}}
            <div
                class="bg-white rounded-3xl border border-slate-200 p-6 flex flex-col justify-between shadow-sm relative overflow-hidden">
                <div
                    class="absolute -right-6 -top-6 w-24 h-24 rounded-full {{ $connection['connected'] ? 'bg-emerald-50' : 'bg-rose-50' }} opacity-50 pointer-events-none">
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Status Database</p>
                    <div class="flex items-center gap-3">
                        <div class="relative flex h-4 w-4">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $connection['connected'] ? 'bg-emerald-400' : 'bg-rose-400' }} opacity-75"></span>
                            <span
                                class="relative inline-flex rounded-full h-4 w-4 {{ $connection['connected'] ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                        </div>
                        <h4
                            class="text-xl font-bold {{ $connection['connected'] ? 'text-slate-800' : 'text-rose-700' }}">
                            {{ $connection['connected'] ? 'Sistem Terhubung' : 'Koneksi Terputus' }}
                        </h4>
                    </div>
                </div>
                <div
                    class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-medium text-slate-500">
                    <span>Host: <span class="font-mono text-slate-700">{{ $connection['host'] }}</span></span>
                    <span
                        class="uppercase font-bold tracking-wider text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">{{ config('database.default') }}</span>
                </div>
            </div>

            {{-- 2. Kapasitas Penyimpanan --}}
            <div class="bg-white rounded-3xl border border-slate-200 p-6 flex flex-col justify-between shadow-sm">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Penyimpanan Server</p>
                        @if ($dbInfo['success'] && !$diskCheck['sufficient'])
                            <span
                                class="flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Penuh
                            </span>
                        @endif
                    </div>
                    @if ($dbInfo['success'])
                        <div class="flex items-end gap-1 mt-1 mb-3">
                            <h4 class="text-2xl font-bold text-slate-800">{{ $diskInfo['free_human'] }}</h4>
                            <p class="text-sm font-medium text-slate-500 mb-1">tersedia</p>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2.5 mb-2 overflow-hidden">
                            <div class="h-2.5 rounded-full transition-all duration-1000 ease-out {{ $diskInfo['used_percent'] > 85 ? 'bg-rose-500' : ($diskInfo['used_percent'] > 70 ? 'bg-amber-400' : 'bg-emerald-400') }}"
                                style="width: {{ min($diskInfo['used_percent'], 100) }}%"></div>
                        </div>
                        <p class="text-xs text-slate-400 font-medium text-right">{{ $diskInfo['used_percent'] }}%
                            Terpakai dari {{ $diskInfo['total_human'] }}</p>
                    @else
                        <p class="text-sm text-slate-500 italic mt-4">Data penyimpanan tidak tersedia.</p>
                    @endif
                </div>
            </div>

            {{-- 3. Ringkasan Data --}}
            <div class="bg-white rounded-3xl border border-slate-200 p-6 flex flex-col justify-between shadow-sm">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Volume Data Saat Ini</p>
                    @if ($dbInfo['success'])
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-sky-50 text-sky-600 rounded-2xl flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-2xl font-bold text-slate-800">{{ $dbInfo['size_human'] }}</h4>
                                <p class="text-sm font-medium text-slate-500">Tersebar di {{ $dbInfo['tables'] }}
                                    Tabel</p>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-slate-500 italic mt-2">Data ringkasan tidak tersedia.</p>
                    @endif
                </div>
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center gap-2">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Komponen Sistem:</p>
                    <div class="flex gap-1">
                        <span class="w-2 h-2 rounded-full {{ $mysqldumpFound ? 'bg-emerald-400' : 'bg-rose-400' }}"
                            title="Mesin Backup (mysqldump) {{ $mysqldumpFound ? 'Siap' : 'Error' }}"></span>
                        <span class="w-2 h-2 rounded-full {{ $mysqlFound ? 'bg-emerald-400' : 'bg-rose-400' }}"
                            title="Mesin Restore (mysql) {{ $mysqlFound ? 'Siap' : 'Error' }}"></span>
                    </div>
                </div>
            </div>

        </div>

        {{-- ═══ MAIN OPERATIONS (BACKUP vs RESTORE) ═══ --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 mb-8">

            {{-- SAFE ZONE: BACKUP (3/5) --}}
            <div class="lg:col-span-3 space-y-6">
                <div
                    class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden flex flex-col h-full">

                    <div class="px-8 py-6 bg-slate-50/50 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                </svg>
                                Pencadangan Data (Backup)
                            </h3>
                            <p class="text-sm text-slate-500 mt-1">Simpan salinan seluruh data ke dalam bentuk file
                                aman.</p>
                        </div>
                    </div>

                    <div class="p-8">
                        @if (!$diskCheck['sufficient'])
                            <div
                                class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl flex gap-3 text-rose-800 text-sm">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div>
                                    <p class="font-bold">Ruang Penyimpanan Hampir Penuh!</p>
                                    <p class="mt-0.5 opacity-90">Sistem membutuhkan setidaknya
                                        <strong>{{ $diskCheck['required'] }}</strong> untuk membuat backup baru.
                                        Silakan hapus file backup lama di bawah ini.
                                    </p>
                                </div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('database.backup') }}" x-data="{ loading: false }"
                            @submit="loading = true" class="mb-10">
                            @csrf
                            <button type="submit"
                                :disabled="loading ||
                                    {{ !$canRunProcess || !$mysqldumpFound || !$diskCheck['sufficient'] ? 'true' : 'false' }}"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-3 px-8 py-4 bg-slate-900 text-white text-base font-bold rounded-2xl hover:bg-indigo-600 focus:ring-4 focus:ring-indigo-100 transition-all shadow-lg shadow-slate-200 disabled:opacity-50 disabled:cursor-not-allowed group">
                                <svg x-show="!loading"
                                    class="w-5 h-5 group-hover:-translate-y-0.5 transition-transform" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                <svg x-show="loading" class="w-5 h-5 animate-spin" fill="none"
                                    viewBox="0 0 24 24" x-cloak>
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                                <span x-text="loading ? 'Sedang Memproses...' : 'Buat Backup Sekarang'"></span>
                            </button>
                        </form>

                        {{-- Daftar Riwayat Backup --}}
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Riwayat File
                                    Backup</h4>
                                <span
                                    class="text-xs font-bold bg-slate-100 text-slate-500 px-2 py-1 rounded-lg">{{ count($backups) }}
                                    File Tersimpan</span>
                            </div>

                            @if (empty($backups))
                                <div class="py-10 text-center border-2 border-dashed border-slate-100 rounded-2xl">
                                    <div
                                        class="w-12 h-12 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-600">Belum ada file cadangan</p>
                                    <p class="text-xs text-slate-400 mt-1">File backup Anda akan muncul di sini.</p>
                                </div>
                            @else
                                <div class="space-y-3">
                                    @foreach ($backups as $backup)
                                        <div
                                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 border border-slate-200 rounded-2xl hover:border-indigo-300 hover:shadow-md transition-all bg-white group">
                                            <div class="flex items-center gap-4 min-w-0">
                                                <div
                                                    class="w-10 h-10 bg-indigo-50 text-indigo-500 rounded-xl flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-bold text-slate-800 truncate"
                                                        title="{{ $backup['filename'] }}">
                                                        {{ $backup['filename'] }}
                                                    </p>
                                                    <div
                                                        class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 text-xs">
                                                        <span
                                                            class="font-medium text-indigo-600 bg-indigo-50 px-1.5 rounded">{{ $backup['size_human'] }}</span>
                                                        <span
                                                            class="text-slate-500">{{ $backup['created_at']->locale('id')->isoFormat('D MMM Y') }}</span>
                                                        <span class="text-slate-400 hidden sm:inline">&bull;</span>
                                                        <span
                                                            class="text-slate-400">{{ $backup['created_at']->diffForHumans() }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div
                                                class="flex items-center gap-2 sm:opacity-0 group-hover:opacity-100 focus-within:opacity-100 transition-opacity flex-shrink-0">
                                                <a href="{{ route('database.download', $backup['filename']) }}"
                                                    class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 px-4 py-2 bg-slate-50 hover:bg-indigo-50 text-slate-600 hover:text-indigo-700 text-sm font-bold rounded-xl transition-colors border border-slate-200 hover:border-indigo-200">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                    </svg>
                                                    Unduh
                                                </a>
                                                <form method="POST"
                                                    action="{{ route('database.delete-backup', $backup['filename']) }}"
                                                    onsubmit="return confirm('Yakin ingin menghapus file backup ini permanen?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors border border-transparent hover:border-rose-100"
                                                        title="Hapus File">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- DANGER ZONE: RESTORE (2/5) --}}
            <div class="lg:col-span-2">
                <div
                    class="bg-orange-100 rounded-[2rem] border-2 border-dashed border-rose-300 shadow-sm overflow-hidden h-full">

                    <div class="px-6 py-6 border-b border-rose-200/50 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-black text-rose-800 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                Area Pemulihan (Restore)
                            </h3>
                            <p class="text-xs font-bold text-amber-600 uppercase tracking-wider mt-1">Harap
                                Berhati-hati</p>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-5">
                            <div class="flex items-center gap-2 mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-rose-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01M10.29 3.86l-8.18 14A2 2 0 003.82 21h16.36a2 2 0 001.71-3.14l-8.18-14a2 2 0 00-3.42 0z" />
                                </svg>

                                <h3 class="text-sm font-bold text-rose-700">
                                    Mohon Dibaca Sebelum Memulai
                                </h3>
                            </div>

                            <ul class="space-y-3 text-sm text-slate-700">
                                <li class="flex gap-3">
                                    <span class="mt-1 h-2 w-2 rounded-full bg-rose-500 shrink-0"></span>
                                    <span>
                                        Seluruh data aplikasi Anda saat ini akan
                                        <span class="font-semibold text-rose-600">
                                            ditimpa dan hilang permanen
                                        </span>.
                                    </span>
                                </li>

                                <li class="flex gap-3">
                                    <span class="mt-1 h-2 w-2 rounded-full bg-amber-500 shrink-0"></span>
                                    <span>
                                        Sangat disarankan untuk
                                        <span class="font-semibold text-amber-600">
                                            membuat backup
                                        </span>
                                        terlebih dahulu melalui menu Backup.
                                    </span>
                                </li>

                                <li class="flex gap-3">
                                    <span class="mt-1 h-2 w-2 rounded-full bg-sky-500 shrink-0"></span>
                                    <span>
                                        Gunakan file
                                        <code
                                            class="rounded bg-slate-200 px-1.5 py-0.5 text-xs font-semibold text-slate-800">
                                            .sql
                                        </code>
                                        yang dihasilkan dari sistem ini.
                                    </span>
                                </li>
                            </ul>
                        </div>

                        @if (!$canRunProcess || !$mysqlFound)
                            <div
                                class="p-4 bg-white/50 border border-slate-200 rounded-2xl text-sm font-medium text-slate-500 text-center flex flex-col items-center gap-2">
                                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                Area Restore saat ini dikunci karena fitur tidak didukung oleh server.
                            </div>
                        @else
                            <form method="POST" action="{{ route('database.restore.upload') }}"
                                enctype="multipart/form-data" x-data="{ filename: '', isDragging: false, loading: false }" @submit="loading = true">
                                @csrf

                                {{-- Drag & Drop Area --}}
                                <div class="relative w-full rounded-2xl border-2 border-dashed bg-white transition-all duration-200 group cursor-pointer overflow-hidden mb-4"
                                    :class="isDragging ? 'border-rose-500 bg-rose-50 shadow-inner' :
                                        'border-rose-300 hover:border-rose-500 hover:bg-rose-50/50'"
                                    @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
                                    @drop.prevent="isDragging = false; if($event.dataTransfer.files.length) { $refs.sqlInput.files = $event.dataTransfer.files; filename = $event.dataTransfer.files[0].name; }"
                                    @click="$refs.sqlInput.click()">

                                    <div class="px-6 py-10 flex flex-col items-center justify-center text-center">
                                        <div
                                            class="w-14 h-14 bg-warning-soft text-rose-500 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                        </div>

                                        <div x-show="!filename">
                                            <p class="text-sm font-bold text-rose-900">Pilih File Restore</p>
                                            <p class="text-xs font-medium text-rose-600/70 mt-1">Klik di sini atau
                                                tarik file ke kotak ini</p>
                                        </div>

                                        <div x-show="filename" x-cloak class="w-full">
                                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                                                File Terpilih:</p>
                                            <p class="text-sm font-bold text-rose-700 bg-rose-100 px-3 py-1.5 rounded-lg inline-block truncate max-w-full"
                                                x-text="filename"></p>
                                        </div>

                                        <p
                                            class="text-[10px] font-bold text-rose-400 mt-4 uppercase tracking-wider border-t border-rose-200 pt-3">
                                            Format .SQL | Maks 512 MB</p>
                                    </div>
                                </div>

                                <input type="file" name="sql_file" accept=".sql" x-ref="sqlInput"
                                    class="hidden" @change="filename = $event.target.files[0]?.name ?? ''">

                                @error('sql_file')
                                    <p
                                        class="mb-4 text-xs font-bold text-rose-600 bg-rose-100/50 p-2 rounded-lg text-center">
                                        {{ $message }}</p>
                                @enderror

                                <button type="submit" :disabled="!filename || loading"
                                    class="w-full flex items-center justify-center gap-2 px-6 py-3.5 bg-rose-600 text-white text-sm font-bold rounded-xl hover:bg-rose-700 transition-colors shadow-lg shadow-rose-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg x-show="loading" class="w-5 h-5 animate-spin" fill="none"
                                        viewBox="0 0 24 24" x-cloak>
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    <span x-text="loading ? 'Menyiapkan Data...' : 'Unggah & Mulai Pemulihan'"></span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ DETAIL TEKNIS (TABLES) ═══ --}}
        @if ($dbInfo['success'] && !empty($dbInfo['table_list']))
            <div x-data="{ showDetail: false }"
                class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
                <div @click="showDetail = !showDetail"
                    class="px-8 py-5 flex items-center justify-between cursor-pointer hover:bg-slate-50 transition-colors select-none">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Rincian Teknis Struktur Tabel</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Top {{ count($dbInfo['table_list']) }} tabel dengan
                            penyimpanan terbesar. (Hanya untuk keperluan diagnostik).</p>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 transition-transform duration-300"
                        :class="showDetail ? 'rotate-180' : ''">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                <div x-show="showDetail" x-collapse x-cloak>
                    <div class="overflow-x-auto border-t border-slate-100 p-4">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="bg-slate-50 rounded-xl">
                                    <th
                                        class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider rounded-l-xl">
                                        Nama Tabel Sistem</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">
                                        Estimasi Baris</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">
                                        Kapasitas</th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider rounded-r-xl">
                                        Engine</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach ($dbInfo['table_list'] as $table)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="px-6 py-3">
                                            <span
                                                class="font-mono text-xs font-medium text-slate-700 bg-slate-100 px-2 py-1 rounded-md">{{ $table->table_name }}</span>
                                        </td>
                                        <td class="px-6 py-3 text-right">
                                            <span
                                                class="font-mono text-sm text-slate-600">{{ number_format($table->table_rows) }}</span>
                                        </td>
                                        <td class="px-6 py-3 text-right">
                                            <span class="font-mono text-sm font-bold text-indigo-600">
                                                {{ $table->size_kb >= 1024 ? round($table->size_kb / 1024, 2) . ' MB' : $table->size_kb . ' KB' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-center">
                                            <span
                                                class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                                {{ $table->engine }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif


        {{-- ═══ TERMINAL MODAL KONFIRMASI RESTORE ═══ --}}
        <div x-show="restoreModalOpen" x-cloak x-transition
            class="fixed inset-0 z-[200] flex items-center justify-center p-4 sm:p-6"
            style="background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(8px);"
            x-on:keydown.escape.window="restoreModalOpen = false">

            <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden relative border border-rose-500/30"
                @click.stop x-transition:enter="transition ease-out duration-300 delay-100"
                x-transition:enter-start="opacity-0 scale-95 translate-y-8"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-8">

                {{-- Header Terminal --}}
                <div class="bg-rose-600 px-8 py-6 relative overflow-hidden">
                    <div
                        class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4xKSIvPjwvc3ZnPg==')] opacity-30">
                    </div>
                    <div class="relative z-10 flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center flex-shrink-0 backdrop-blur-sm border border-white/30">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-black text-white text-xl tracking-wide">PERINGATAN KRITIS</h3>
                            <p class="text-rose-200 text-sm font-medium mt-0.5">Konfirmasi Penimpaan Database</p>
                        </div>
                    </div>
                </div>

                @if ($restorePending)
                    <div class="px-8 py-6 space-y-6">

                        {{-- Info File Yg Akan Direstore --}}
                        <div class="flex items-center gap-4 p-4 bg-slate-50 border border-slate-200 rounded-2xl">
                            <div
                                class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-0.5">Membaca
                                    File:</p>
                                <p
                                    class="text-sm font-bold text-slate-900 truncate bg-white px-2 py-1 rounded border border-slate-100 inline-block max-w-full">
                                    {{ $restorePending['original_name'] }}
                                </p>
                                <p class="text-xs font-medium text-slate-500 mt-1">Ukuran Data: <span
                                        class="text-slate-700 font-bold">{{ $restorePending['size_human'] }}</span>
                                </p>
                            </div>
                        </div>

                        {{-- Syarat dan Ketentuan Mutlak --}}
                        <div class="space-y-3">
                            <p class="text-sm font-bold text-rose-900 border-b border-rose-100 pb-2">Saya menyetujui
                                bahwa:</p>
                            @foreach (['Seluruh data aplikasi yang ada SAAT INI akan MUSNAH dan ditimpa.', 'Proses pemulihan TIDAK DAPAT DIBATALKAN setelah tombol diklik.', 'Saya telah memastikan file yang diunggah adalah file yang benar.'] as $item)
                                <div class="flex items-start gap-3">
                                    <div
                                        class="w-5 h-5 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-rose-800/90 leading-snug">{{ $item }}
                                    </p>
                                </div>
                            @endforeach
                        </div>

                        @error('restore_confirm')
                            <div
                                class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-sm font-bold text-rose-700 text-center">
                                {{ $message }}
                            </div>
                        @enderror

                        {{-- Input Verifikasi Manual --}}
                        <div class="bg-slate-900 rounded-2xl p-5 border border-slate-800 shadow-inner">
                            <label class="block text-sm font-medium text-slate-300 mb-3 text-center">
                                Ketik kata sandi darurat <span
                                    class="text-rose-400 font-bold font-mono text-base bg-rose-400/10 px-2 py-0.5 rounded border border-rose-400/20 mx-1">RESTORE</span>
                                di bawah ini:
                            </label>
                            <input type="text" x-model="confirmText" placeholder="Ketik persis huruf kapital..."
                                autocomplete="off"
                                class="w-full px-4 py-3 bg-slate-800 border-2 border-slate-700 rounded-xl text-center text-xl font-bold font-mono text-white placeholder:text-slate-600 focus:outline-none focus:border-rose-500 focus:ring-4 focus:ring-rose-500/20 transition-all uppercase">
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div
                        class="px-8 py-5 bg-slate-50 border-t border-slate-200 flex flex-col-reverse sm:flex-row items-center gap-3">
                        <form method="POST" action="{{ route('database.restore.dismiss') }}"
                            class="w-full sm:w-1/3">
                            @csrf
                            <button type="submit"
                                class="w-full px-5 py-3.5 border border-slate-300 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-100 focus:ring-4 focus:ring-slate-100 transition-colors">
                                Tolak & Batal
                            </button>
                        </form>

                        <form method="POST" action="{{ route('database.restore.confirm') }}"
                            class="w-full sm:w-2/3" x-data="{ submitting: false }" @submit="submitting = true">
                            @csrf
                            <input type="hidden" name="confirmation" :value="confirmText">
                            <button type="submit" :disabled="!canConfirm || submitting"
                                class="w-full flex items-center justify-center gap-2 px-6 py-3.5 bg-rose-600 text-white text-sm font-black tracking-wide rounded-xl hover:bg-rose-700 shadow-lg shadow-rose-200 focus:ring-4 focus:ring-rose-100 transition-all disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none">
                                <svg x-show="submitting" class="w-5 h-5 animate-spin" fill="none"
                                    viewBox="0 0 24 24" x-cloak>
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                                <svg x-show="!submitting" class="w-5 h-5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span x-text="submitting ? 'MEMPROSES DATA...' : 'EKSEKUSI PEMULIHAN'"></span>
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>
