<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2
                    class="border-l-4 border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Hari Libur Nasional & Cuti Bersama') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Kelola tanggal merah & cuti bersama (SKB 3 Menteri) untuk tahun {{ $year }}.
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('hrd.holidays.create', ['year' => $year]) }}"
                    class="inline-flex items-center px-3 py-2 bg-primary-600 border border-transparent rounded-full font-medium text-xs text-white hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Tambah Hari Libur
                </a>
                <button @click="$dispatch('open-import-modal')"
                    class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-full font-medium text-xs text-white hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Import Massal
                </button>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4" x-data="{ showImport: false, showDelete: false, confirmToggle: false, selectedId: null, selectedName: '', selectedAction: '', importTab: 'manual' }">
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
                class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 text-yellow-800 dark:text-yellow-200 px-4 py-3 rounded-lg flex items-center">
                <svg class="w-5 h-5 mr-2 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div
                class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Filter tahun + ringkasan --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-4">
                <form method="GET" action="{{ route('hrd.holidays.index') }}" class="flex items-end gap-2">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tahun</label>
                        <select name="year" onchange="this.form.submit()"
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-gray-200 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                            @foreach (range(now()->year, now()->year + 1) as $y)
                                <option value="{{ $y }}" @selected($y == $year)>{{ $y }}
                                </option>
                            @endforeach
                            @foreach ($years as $y)
                                @if ($y < now()->year)
                                    <option value="{{ $y }}" @selected($y == $year)>
                                        {{ $y }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-4">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Libur
                    Nasional</p>
                <p class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400">{{ $summary->national_count ?? 0 }}
                    <span class="text-sm font-normal text-gray-500 dark:text-gray-400">hari</span>
                </p>
            </div>

            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-4">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cuti Bersama
                </p>
                <p class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $summary->joint_count ?? 0 }}
                    <span class="text-sm font-normal text-gray-500 dark:text-gray-400">total,
                        {{ $summary->joint_weekday_count ?? 0 }} di hari kerja (potong kuota tahunan)</span>
                </p>
            </div>
        </div>

        <div
            class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200 px-4 py-3 rounded-lg text-sm">
            <span class="font-semibold">Catatan:</span> Kuota cuti tahunan dihitung otomatis = 12 − jumlah cuti bersama
            hari kerja tahun tersebut. Input data cuti bersama <span class="font-semibold">sebelum</span> klik "Generate
            Kuota Tahunan" di menu <a href="{{ route('hrd.quota.index') }}" class="font-semibold underline">Kuota
                Cuti</a>.
        </div>

        <div
            class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                    <thead class="bg-blue-100 dark:bg-blue-900 inset-shadow-sm inset-shadow-indigo-500">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">
                                #</th>
                            <th
                                class="px-4 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">
                                Tanggal</th>
                            <th
                                class="px-4 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">
                                Nama Hari Libur</th>
                            <th
                                class="px-4 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">
                                Tipe</th>
                            <th
                                class="px-4 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">
                                Status</th>
                            <th
                                class="px-4 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                        @forelse ($holidays as $index => $holiday)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/40 transition-colors">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $holidays->firstItem() + $index }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 font-medium">
                                    {{ $holiday->date->isoFormat('dddd, D MMMM YYYY') }}
                                    <span
                                        class="text-xs text-gray-500 dark:text-gray-400">({{ $holiday->date->isoFormat('ddd') }})</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $holiday->name }}
                                    @if ($holiday->description)
                                        <span
                                            class="block text-xs text-gray-500 dark:text-gray-400">{{ $holiday->description }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if ($holiday->type === 'national_holiday')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300">
                                            Libur Nasional
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">
                                            Cuti Bersama
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex items-center space-x-2">
                                        <button
                                            @click="confirmToggle = true; selectedId = {{ $holiday->id }}; selectedName = '{{ addslashes($holiday->name) }}'; selectedAction = '{{ $holiday->is_active ? 'nonaktifkan' : 'aktifkan' }}'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $holiday->is_active ? 'bg-green-600' : 'bg-stone-500 dark:bg-gray-700' }}">
                                            <span
                                                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $holiday->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                        </button>
                                        <span
                                            class="text-xs font-medium {{ $holiday->is_active ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200' }}">
                                            {{ $holiday->is_active ? 'Aktif' : 'Draft' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('hrd.holidays.edit', $holiday->id) }}"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 transition-colors"
                                            title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <button
                                            @click="showDelete = true; selectedId = {{ $holiday->id }}; selectedName = '{{ addslashes($holiday->name) }}'"
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
                                <td colspan="6"
                                    class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="mt-2">Belum ada data hari libur untuk tahun {{ $year }}.</p>
                                    <p class="mt-1 text-xs">Gunakan tombol "Import Massal" (Excel / API / Manual)
                                        untuk menyalin daftar dari SKB 3 Menteri.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($holidays->hasPages())
                <div class="px-4 py-3 bg-gray-50 dark:bg-slate-700/50 border-t border-gray-200 dark:border-slate-700">
                    {{ $holidays->links() }}
                </div>
            @endif
        </div>

        {{-- Modal Import Massal --}}
        <div x-show="showImport" x-transition x-cloak style="display: none;"
            x-on:open-import-modal.window="showImport = true" x-on:close-import-modal.window="showImport = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-2xl" x-show="showImport"
                x-transition:enter="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100"
                x-transition:leave="scale-100 opacity-100" x-transition:leave-end="scale-95 opacity-0">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-lg font-semibold">Import Massal Hari Libur</h2>
                    <button type="button" @click="showImport = false"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Pilih metode input: <b>Manual</b> (paste dari SKB 3 Menteri), <b>Excel</b> (template), atau
                    <b>API</b> (tarik otomatis). Data selalu ditampilkan untuk verifikasi sebelum disimpan.
                </p>

                {{-- Tab navigasi --}}
                <div class="flex space-x-1 border-b border-gray-200 dark:border-slate-700 mb-4">
                    <button type="button" @click="importTab = 'manual'"
                        class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors"
                        :class="importTab === 'manual' ? 'border-primary-600 text-primary-700 dark:text-primary-300' :
                            'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'">
                        Manual
                    </button>
                    <button type="button" @click="importTab = 'excel'"
                        class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors"
                        :class="importTab === 'excel' ? 'border-green-600 text-green-700 dark:text-green-300' :
                            'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'">
                        Excel
                    </button>
                    <button type="button" @click="importTab = 'api'"
                        class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors"
                        :class="importTab === 'api' ? 'border-blue-600 text-blue-700 dark:text-blue-300' :
                            'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'">
                        API
                    </button>
                </div>

                {{-- Tab Manual --}}
                <div x-show="importTab === 'manual'" x-cloak>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                        Salin daftar dari SKB 3 Menteri / Excel, satu baris per hari libur.
                        Format: <code class="bg-gray-100 dark:bg-slate-700 px-1 rounded">tanggal | nama | tipe</code>
                        (tipe opsional: <code class="bg-gray-100 dark:bg-slate-700 px-1 rounded">nasional</code> atau
                        <code class="bg-gray-100 dark:bg-slate-700 px-1 rounded">cuti_bersama</code>, default
                        <code class="bg-gray-100 dark:bg-slate-700 px-1 rounded">nasional</code>).
                    </p>
                    <form method="POST" action="{{ route('hrd.holidays.import') }}">
                        @csrf
                        <textarea name="lines" rows="10" required
                            placeholder="2026-01-01 | Tahun Baru 2026 Masehi | nasional&#10;2026-03-20 | Cuti Bersama Idul Fitri | cuti_bersama"
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-gray-200 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm font-mono"></textarea>
                        <div class="mt-4 flex justify-end space-x-2">
                            <button type="button" @click="showImport = false"
                                class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md transition-colors">Batal</button>
                            <button type="submit"
                                class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-md transition-colors">Import</button>
                        </div>
                    </form>
                </div>

                {{-- Tab Excel --}}
                <div x-show="importTab === 'excel'" x-cloak>
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Unduh template, isi sesuai SKB 3 Menteri, lalu unggah untuk diverifikasi sebelum
                            disimpan ke sistem.
                        </p>
                        <a href="{{ route('hrd.holidays.import.template', ['year' => $year]) }}"
                            class="inline-flex items-center px-3 py-2 bg-emerald-600 border border-transparent rounded-md font-medium text-xs text-white hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm shrink-0">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Unduh Template Excel
                        </a>
                    </div>
                    <form method="POST" action="{{ route('hrd.holidays.import.excelPreview') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                            class="block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-green-50 dark:file:bg-green-900/30 file:text-green-700 dark:file:text-green-300 hover:file:bg-green-100 dark:hover:file:bg-green-900/50 cursor-pointer" />
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Format: .xlsx, .xls, atau .csv (maks 1 MB). Kolom template: Tanggal | Nama Hari Libur |
                            Tipe. Baris yang tanggalnya sudah ada di sistem akan ditandai dan dilewati.
                        </p>
                        <div class="mt-4 flex justify-end space-x-2">
                            <button type="button" @click="showImport = false"
                                class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md transition-colors">Batal</button>
                            <button type="submit"
                                class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-md transition-colors">Preview
                                & Verifikasi</button>
                        </div>
                    </form>
                </div>

                {{-- Tab API --}}
                <div x-show="importTab === 'api'" x-cloak>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                        Tarik data hari libur nasional & cuti bersama langsung dari API publik
                        (<code class="bg-gray-100 dark:bg-slate-700 px-1 rounded">api-hari-libur.vercel.app</code>).
                        Data tampil dalam daftar verifikasi sebelum diimport.
                    </p>
                    <form method="POST" action="{{ route('hrd.holidays.import.apiPreview') }}">
                        @csrf
                        <div class="flex items-end gap-2">
                            <div class="flex-1">
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tahun</label>
                                <select name="year"
                                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-gray-200 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                                    @foreach (range(now()->year, now()->year + 1) as $y)
                                        <option value="{{ $y }}" @selected($y == $year)>
                                            {{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-md transition-colors text-sm font-medium">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Tarik Data & Preview
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Hapus --}}
        <div x-show="showDelete" x-transition x-cloak style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-md" x-show="showDelete"
                x-transition:enter="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100"
                x-transition:leave="scale-100 opacity-100" x-transition:leave-end="scale-95 opacity-0">
                <h2 class="text-lg font-semibold mb-4">Hapus Hari Libur</h2>
                <p class="mb-4">
                    Apakah Anda yakin ingin menghapus hari libur <span class="font-bold"
                        x-text="selectedName"></span>?
                </p>
                <form x-bind:action="'{{ route('hrd.holidays.destroy', ':id') }}'.replace(':id', selectedId)"
                    method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="showDelete = false"
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md transition-colors">Batal</button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-md transition-colors">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
        {{-- Modal Toggle Status --}}
        <div x-show="confirmToggle" x-transition x-cloak style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-md" x-show="confirmToggle"
                x-transition:enter="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100"
                x-transition:leave="scale-100 opacity-100" x-transition:leave-end="scale-95 opacity-0">
                <h2 class="text-lg font-semibold mb-4">Konfirmasi</h2>
                <p class="mb-4">
                    Apakah Anda yakin ingin <span class="font-bold" x-text="selectedAction"></span> hari libur
                    <span class="font-bold" x-text="selectedName"></span>?
                </p>
                <form x-bind:action="'{{ route('hrd.holidays.toggle', ':id') }}'.replace(':id', selectedId)"
                    method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="confirmToggle = false"
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md transition-colors">Batal</button>
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-md transition-colors">Ya</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-toast-notification />
</x-app-layout>
