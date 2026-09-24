<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2
                    class="border-l-4 border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Master Data Jabatan') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Kelola Data Jabatan Perusahaan.
                </p>
            </div>
            <div
                class="px-4 py-3 border-b border-gray-200 dark:border-slate-700 flex justify-between items-center gap-2">
                <a href="{{ route('admin.positions.sync') }}"
                    class="inline-flex items-center px-3 py-2 bg-emerald-600 border border-transparent rounded-full font-medium text-xs text-white hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Sync Jabatan
                </a>
                <a href="{{ route('admin.positions.create') }}"
                    class="inline-flex items-center px-3 py-2 bg-primary-600 border border-transparent rounded-full font-medium text-xs text-white hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Tambah Jabatan
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4" x-data="{ showFilters: false, confirmDelete: false, selectedPosition: null, selectedPositionName: '' }">
        @if (session('success'))
            <div
                class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div
                class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded-lg flex items-center">
                <svg class="w-5 h-5 mr-2 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('error') }}
            </div>
        @endif

        @if (session('info'))
            <div
                class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200 px-4 py-3 rounded-lg flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('info') }}
            </div>
        @endif

        {{-- Panel Preview Sync Jabatan --}}
        @isset($newJabatan)
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-emerald-200 dark:border-emerald-800 overflow-hidden">
                <div
                    class="px-5 py-4 border-b border-emerald-100 dark:border-emerald-800/60 bg-emerald-50/60 dark:bg-emerald-950/30 flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-emerald-800 dark:text-emerald-200">Hasil Sinkronisasi</h3>
                            <p class="text-xs text-emerald-600 dark:text-emerald-400">
                                {{ $newJabatan->count() }} jabatan baru ditemukan dari database karyawan.
                            </p>
                        </div>
                    </div>

                    @if ($newJabatan->isNotEmpty())
                        <form action="{{ route('admin.positions.insertAllFromExternal') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-full shadow-sm transition focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                Insert Semua ({{ $newJabatan->count() }})
                            </button>
                        </form>
                    @endif
                </div>

                @if ($newJabatan->isEmpty())
                    <div class="px-5 py-8 text-center">
                        <svg class="mx-auto h-10 w-10 text-emerald-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">Semua jabatan sudah sinkron.
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Tidak ada jabatan baru dari database
                            karyawan.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                            <thead class="bg-emerald-50 dark:bg-emerald-950/20">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        #</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Nama Jabatan (dari DB Karyawan)</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-100 dark:divide-slate-700/60">
                                @foreach ($newJabatan as $index => $nama)
                                    <tr class="hover:bg-emerald-50/40 dark:hover:bg-emerald-950/10 transition-colors">
                                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $index + 1 }}
                                        </td>
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-gray-200">
                                            {{ $nama }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <form action="{{ route('admin.positions.insertFromExternal') }}"
                                                method="POST">
                                                @csrf
                                                <input type="hidden" name="nama_jabatan" value="{{ $nama }}">
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold rounded-lg shadow-sm transition focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                    </svg>
                                                    Insert
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endisset

        <div
            class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                    <thead class="bg-blue-100 dark:bg-blue-900 inset-shadow-sm inset-shadow-indigo-500">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                #</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Nama Jabatan</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                        @forelse ($positions as $index => $position)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/40 transition-colors">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $positions->firstItem() + $index }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 font-medium">
                                    {{ strtoupper($position->nama_jabatan) }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('admin.positions.edit', $position->id) }}"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 transition-colors"
                                            title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <button
                                            @click="confirmDelete = true; selectedPosition = {{ $position->id }}; selectedPositionName = '{{ $position->nama_jabatan }}'"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition-colors"
                                            title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3"
                                    class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <p class="mt-2">Tidak ada data jabatan ditemukan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($positions->hasPages())
                <div class="px-4 py-3 bg-gray-50 dark:bg-slate-700/50 border-t border-gray-200 dark:border-slate-700">
                    {{ $positions->links() }}
                </div>
            @endif
        </div>

        <!-- Modal delete position -->
        <div x-show="confirmDelete" x-transition x-cloak style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-md transform transition-all duration-300 ease-out"
                x-show="confirmDelete" x-transition:enter="scale-95 opacity-0"
                x-transition:enter-end="scale-100 opacity-100" x-transition:leave="scale-100 opacity-100"
                x-transition:leave-end="scale-95 opacity-0">
                <h2 class="text-lg font-semibold mb-4">Hapus Jabatan</h2>
                <p class="mb-4">
                    Apakah Anda yakin ingin menghapus jabatan <span class="font-bold"
                        x-text="selectedPositionName"></span>?
                </p>
                <form x-bind:action="'{{ route('admin.positions.destroy', ':id') }}'.replace(':id', selectedPosition)"
                    method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="confirmDelete = false"
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md transition-colors">Batal</button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-md transition-colors">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-toast-notification />
</x-app-layout>
