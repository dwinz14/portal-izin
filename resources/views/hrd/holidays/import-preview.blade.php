<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2
                    class="border-l-4 border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Verifikasi Import Hari Libur') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Tinjau data sebelum disimpan ke sistem. Sumber: <span
                        class="font-semibold">{{ $source }}</span>.
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('hrd.holidays.index', ['year' => $year]) }}"
                    class="inline-flex items-center px-3 py-2 bg-gray-200 dark:bg-slate-700 border border-transparent rounded-full font-medium text-xs text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-slate-600 focus:outline-none transition ease-in-out duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $counts = app(\App\Services\HolidayImportService::class)->countsByStatus($entries);
        $hasNew = $counts['new'] > 0;
    @endphp

    <div class="space-y-4">
        @if (! $hasNew)
            <div
                class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 text-yellow-800 dark:text-yellow-200 px-4 py-3 rounded-lg text-sm">
                Tidak ada baris baru yang dapat diimport dari sumber ini. Periksa status setiap baris di bawah, lalu
                kembali dan coba sumber lain.
            </div>
        @endif

        {{-- Ringkasan --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-4">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Baris</p>
                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ count($entries) }}</p>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-4">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Baru (akan diimport)</p>
                <p class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">{{ $counts['new'] }}</p>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-4">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sudah Ada (dilewati)</p>
                <p class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400">
                    {{ $counts['exists'] + $counts['duplicate'] }}
                </p>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-4">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gagal</p>
                <p class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400">{{ $counts['error'] }}</p>
            </div>
        </div>

        {{-- Tabel verifikasi --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
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
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                        @forelse ($entries as $entry)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/40 transition-colors">
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $entry['row'] }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    @if ($entry['date'])
                                        {{ \Carbon\Carbon::parse($entry['date'])->isoFormat('dddd, D MMMM YYYY') }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $entry['name'] ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if ($entry['type'] === 'national_holiday')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300">
                                            Libur Nasional
                                        </span>
                                    @elseif ($entry['type'] === 'joint_leave')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">
                                            Cuti Bersama
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @switch($entry['status'])
                                        @case('new')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                                                Baru — siap diimport
                                            </span>
                                        @break
                                        @case('exists')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                                                Sudah ada — dilewati
                                            </span>
                                        @break
                                        @case('duplicate')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">
                                                Duplikat — dilewati
                                            </span>
                                        @break
                                        @default
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300">
                                                Gagal — tidak diimport
                                            </span>
                                    @endswitch
                                    <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $entry['message'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Tidak ada baris untuk ditampilkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div
            class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200 px-4 py-3 rounded-lg text-sm">
            <span class="font-semibold">Catatan:</span> Data yang dikonfirmasi langsung aktif. Kuota cuti tahunan
            dihitung otomatis = 12 − jumlah cuti bersama hari kerja tahun tersebut. Pastikan data cuti bersama sudah
            lengkap <span class="font-semibold">sebelum</span> klik "Generate Kuota Tahunan" di menu <a
                href="{{ route('hrd.quota.index') }}" class="font-semibold underline">Kuota Cuti</a>.
        </div>

        <div class="flex justify-end space-x-2">
            <form method="POST" action="{{ route('hrd.holidays.import.cancel') }}">
                @csrf
                <button type="submit"
                    class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md transition-colors">Batal</button>
            </form>
            <form method="POST" action="{{ route('hrd.holidays.import.confirm') }}">
                @csrf
                <button type="submit" {{ $hasNew ? '' : 'disabled' }}
                    class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-md transition-colors {{ $hasNew ? '' : 'opacity-50 cursor-not-allowed' }}">
                    Konfirmasi Import ({{ $counts['new'] }})
                </button>
            </form>
        </div>
    </div>
</x-app-layout>