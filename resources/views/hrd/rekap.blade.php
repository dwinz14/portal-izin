<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2
                    class=" border-l-[5px] border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Rekapitulasi Cuti Karyawan') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Ringkasan data cuti seluruh karyawan perusahaan.
                </p>
            </div>
            <div>
                <form method="GET" action="{{ route('hrd.rekap.export') }}" class="inline-block">

                    {{-- Logic ini sudah benar & efisien untuk meneruskan parameter filter --}}
                    @foreach (request()->query() as $key => $val)
                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                    @endforeach

                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 active:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800 transition-colors duration-200">

                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>

                        <span>Export Excel</span>
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6" x-data="{ showFilter: false }">
        {{-- Filter & Search --}}
        <div class="bg-white dark:bg-slate-800 shadow-lg rounded-xl p-4 transition-all">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Filter & Pencarian</h3>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        Gunakan filter untuk mencari data spesifik
                    </span>
                    <button @click="showFilter = !showFilter"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <svg class="w-4 h-4 ml-2 transition-transform" :class="showFilter ? 'rotate-180' : ''"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="gap-2" x-show="showFilter" x-collapse>
                <form method="GET" action="{{ route('hrd.rekap.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
                        {{-- Jabatan --}}
                        <div>
                            <label for="position_id"
                                class="block text-xs font-medium mb-1 text-gray-700 dark:text-gray-300">Jabatan</label>
                            <select id="position_id" name="position_id"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-xs">
                                <option value="">-- Semua Jabatan --</option>
                                @foreach ($positions as $position)
                                    <option value="{{ $position->id }}" @selected($positionId == $position->id)>
                                        {{ strtoupper($position->nama_jabatan) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Kantor --}}
                        <div>
                            <label for="office_id"
                                class="block text-xs font-medium mb-1 text-gray-700 dark:text-gray-300">Kantor</label>
                            <select id="office_id" name="office_id"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-xs">
                                <option value="">-- Semua Kantor --</option>
                                @foreach ($offices as $office)
                                    <option value="{{ $office->id }}" @selected($officeId == $office->id)>
                                        {{ strtoupper($office->nama_kantor) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Jenis Cuti --}}
                        <div>
                            <label for="leave_type_id"
                                class="block text-xs font-medium mb-1 text-gray-700 dark:text-gray-300">Jenis
                                Cuti</label>
                            <select id="leave_type_id" name="leave_type_id"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-xs">
                                <option value="">-- Semua Jenis --</option>
                                @foreach ($leaveTypes as $leaveType)
                                    <option value="{{ $leaveType->id }}" @selected($leaveTypeId == $leaveType->id)>
                                        {{ $leaveType->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tanggal Mulai --}}
                        <div>
                            <label for="start_date"
                                class="block text-xs font-medium mb-1 text-gray-700 dark:text-gray-300">Tanggal
                                Mulai</label>
                            <input type="date" id="start_date" name="start_date" value="{{ $startDate }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-xs">
                        </div>

                        {{-- Tanggal Selesai --}}
                        <div>
                            <label for="end_date"
                                class="block text-xs font-medium mb-1 text-gray-700 dark:text-gray-300">Tanggal
                                Selesai</label>
                            <input type="date" id="end_date" name="end_date" value="{{ $endDate }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-xs">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 mt-4">
                        <button type="submit"
                            class="inline-flex items-center justify-center px-3 py-1.5 bg-primary-600 rounded-md text-white font-medium text-xs hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 transition">
                            Terapkan
                        </button>
                        <a href="{{ route('hrd.rekap.index') }}"
                            class="inline-flex items-center justify-center px-3 py-1.5 bg-gray-100 dark:bg-slate-700 rounded-md text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-slate-600 transition">
                            ↺ Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- ✅ Improved compact, responsive, modern table UI -->
        <div
            class="bg-white dark:bg-slate-800 shadow-md hover:shadow-lg transition-all duration-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full table-fixed border-collapse">
                    <thead
                        class="bg-blue-100 dark:bg-blue-900 inset-shadow-sm inset-shadow-indigo-500 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">
                        <tr>
                            <th class="px-3 py-3 text-left w-32 whitespace-nowrap">Nama</th>
                            <th class="px-3 py-3 text-left w-28 whitespace-nowrap">Jabatan</th>
                            <th class="px-3 py-3 text-left w-40 whitespace-nowrap">Tanggal Cuti</th>
                            <th class="px-3 py-3 text-left w-28 whitespace-nowrap">Jenis</th>
                            <th class="px-3 py-3 text-left w-32">Alasan</th>
                            <th class="px-3 py-3 text-center w-20 whitespace-nowrap">Bukti</th>
                            <th class="px-2 py-2 text-center w-24 whitespace-nowrap">Status</th>
                            <th class="px-3 py-3 text-left w-40 whitespace-nowrap">Approver Terakhir</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($leaves as $leave)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/40 transition cursor-pointer">
                                <td class="px-3 py-3 font-medium text-gray-900 dark:text-gray-100 truncate">
                                    {{ Str::title($leave->user->name) }}</td>
                                <td class="px-3 py-3 text-gray-600 dark:text-gray-400 truncate">
                                    {{ strtoupper($leave->user->position->nama_jabatan ?? '-') }}</td>
                                <td class="px-3 py-3 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                    <div>
                                        {{ \Carbon\Carbon::parse($leave->start_date)->isoFormat('D MMM YYYY') }} —
                                        {{ \Carbon\Carbon::parse($leave->end_date)->isoFormat('D MMM YYYY') }}
                                    </div>
                                    <span class="text-xs text-gray-400 block">({{ $leave->total_hari }} hari)</span>
                                </td>
                                <td class="px-3 py-3 text-gray-600 dark:text-gray-400 whitespace-normal break-words">
                                    {{ Str::title($leave->leaveType->name ?? '-') }}
                                    @if ($leave->is_mendadak)
                                        <span
                                            class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">
                                            ⚡ Mendadak
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 max-w-[150px]">
                                    <div class="text-gray-900 dark:text-gray-100 truncate">
                                        {{ $leave->alasan }}
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @if ($leave->proof_image)
                                        <button type="button"
                                            onclick="window.open('{{ asset('storage/' . $leave->proof_image) }}', '_blank')"
                                            class="inline-flex items-center justify-center w-8 h-8 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/40 dark:hover:bg-blue-800/60 text-blue-600 dark:text-blue-300 rounded-full transition-colors"
                                            title="Lihat Bukti">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                        </button>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-500">Tidak Ada</span>
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-center">
                                    @php
                                        $statusClass =
                                            [
                                                'approved' =>
                                                    'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                                                'pending' =>
                                                    'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
                                                'rejected' =>
                                                    'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                                            ][strtolower($leave->status_final)] ??
                                            'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300';
                                    @endphp
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusClass }} whitespace-nowrap">
                                        {{ ucfirst($leave->status_final) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 max-w-xs">
                                    <div class="text-gray-600 dark:text-gray-400 whitespace-normal break-words">
                                        {{ Str::title(optional($leave->approvals()->where('step', 2)->first()?->approver)->name ?? '-') }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-14 text-center text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg class="w-14 h-14 text-gray-300 dark:text-gray-600"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5 4.5l-2.25-2.25m0 0l2.25-2.25m-2.25 2.25l2.25 2.25M12 18.75l-2.25-2.25m2.25 2.25l2.25-2.25m-2.25 2.25l-2.25-2.25M6.34 7.5h11.32a2.25 2.25 0 012.25 2.25v7.5a2.25 2.25 0 01-2.25 2.25H6.34a2.25 2.25 0 01-2.25-2.25v-7.5a2.25 2.25 0 012.25-2.25z" />
                                        </svg>
                                        <p class="text-sm font-medium">Tidak ada data cuti</p>
                                        <p class="text-xs text-gray-400">Data akan muncul ketika ada pengajuan cuti.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($leaves->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-slate-800">
                    {{ $leaves->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
