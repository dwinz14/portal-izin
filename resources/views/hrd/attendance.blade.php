<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2
                    class="border-l-[5px] border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Rekap Kehadiran') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Monitoring pengajuan kehadiran yang sudah masuk ke sistem.
                </p>
            </div>
            {{-- Tombol Export --}}
            <form method="GET" action="{{ route('hrd.kehadiran.export') }}" class="inline-block">
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
    </x-slot>

    <div class="space-y-6" x-data="{ showFilter: false }">

        {{-- Panel Filter & Pencarian (Compact UI sesuai rekap.blade.php) --}}
        <div class="bg-white dark:bg-slate-800 shadow-lg rounded-xl p-4 transition-all">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Filter & Pencarian</h3>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        Gunakan filter untuk mencari data spesifik
                    </span>
                    <button type="button" @click="showFilter = !showFilter"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 focus:outline-none">
                        <svg class="w-4 h-4 ml-2 transition-transform duration-200"
                            :class="showFilter ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="gap-2" x-show="showFilter" x-collapse x-cloak>
                <form method="GET" action="{{ route('hrd.kehadiran.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">

                        {{-- Dari Tanggal --}}
                        <div>
                            <label for="date_from"
                                class="block text-xs font-medium mb-1 text-gray-700 dark:text-gray-300">Dari
                                Tanggal</label>
                            <input type="date" id="date_from" name="date_from"
                                value="{{ $filters['date_from'] ?? '' }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-xs [&::-webkit-calendar-picker-indicator]:opacity-60 dark:[&::-webkit-calendar-picker-indicator]:invert dark:[&::-webkit-calendar-picker-indicator]:opacity-80">
                        </div>

                        {{-- Sampai Tanggal --}}
                        <div>
                            <label for="date_to"
                                class="block text-xs font-medium mb-1 text-gray-700 dark:text-gray-300">Sampai
                                Tanggal</label>
                            <input type="date" id="date_to" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-xs [&::-webkit-calendar-picker-indicator]:opacity-60 dark:[&::-webkit-calendar-picker-indicator]:invert dark:[&::-webkit-calendar-picker-indicator]:opacity-80">
                        </div>

                        {{-- Jenis --}}
                        <div>
                            <label for="type"
                                class="block text-xs font-medium mb-1 text-gray-700 dark:text-gray-300">Jenis</label>
                            <select id="type" name="type"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-xs">
                                <option value="">-- Semua Jenis --</option>
                                @foreach ($typeLabels as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Status --}}
                        <div>
                            <label for="status"
                                class="block text-xs font-medium mb-1 text-gray-700 dark:text-gray-300">Status</label>
                            <select id="status" name="status"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-xs">
                                <option value="">-- Semua Status --</option>
                                @foreach (['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>
                                        {{ $label }}
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
                                    <option value="{{ $office->id }}" @selected(($filters['office_id'] ?? '') == $office->id)>
                                        {{ strtoupper($office->nama_kantor) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Jabatan --}}
                        <div>
                            <label for="position_id"
                                class="block text-xs font-medium mb-1 text-gray-700 dark:text-gray-300">Jabatan</label>
                            <select id="position_id" name="position_id"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-xs">
                                <option value="">-- Semua Jabatan --</option>
                                @foreach ($positions as $position)
                                    <option value="{{ $position->id }}" @selected(($filters['position_id'] ?? '') == $position->id)>
                                        {{ strtoupper($position->nama_jabatan) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="flex items-center justify-end gap-2 mt-4">
                        <button type="submit"
                            class="inline-flex items-center justify-center px-3 py-1.5 bg-primary-600 rounded-md text-white font-medium text-xs hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 transition">
                            Terapkan
                        </button>
                        <a href="{{ route('hrd.kehadiran.index') }}"
                            class="inline-flex items-center justify-center px-3 py-1.5 bg-gray-100 dark:bg-slate-700 rounded-md text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-slate-600 transition">
                            ↺ Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabel Data --}}
        <div class="bg-white dark:bg-slate-800 shadow-xl rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-blue-100 dark:bg-blue-900">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">
                                Pemohon</th>
                            <th
                                class="px-6 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">
                                Jenis</th>
                            <th
                                class="px-6 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">
                                Tanggal & Waktu</th>
                            <th
                                class="px-6 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">
                                Atasan</th>
                            <th
                                class="px-6 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">
                                Status</th>
                            <th
                                class="px-6 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">
                                Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($attendanceRequests as $attendanceRequest)
                            @php
                                $statusClass = match ($attendanceRequest->status) {
                                    'approved' => 'bg-status-success-bg text-status-success-text',
                                    'rejected' => 'bg-status-danger-bg text-status-danger-text',
                                    default => 'bg-status-warning-bg text-status-warning-text',
                                };
                                $statusLabel = match ($attendanceRequest->status) {
                                    'approved' => 'Disetujui',
                                    'rejected' => 'Ditolak',
                                    default => 'Menunggu',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                    <div class="font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $attendanceRequest->user->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ strtoupper($attendanceRequest->user?->position?->nama_jabatan ?? '-') }}
                                    </div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">
                                        {{ strtoupper($attendanceRequest->user?->office?->nama_kantor ?? '-') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                    {{ $attendanceRequest->type_label }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                    {{ $attendanceRequest->date->isoFormat('D MMM YYYY') }}
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ \Illuminate\Support\Str::of($attendanceRequest->start_time)->substr(0, 5) }}
                                        @if ($attendanceRequest->end_time)
                                            -
                                            {{ \Illuminate\Support\Str::of($attendanceRequest->end_time)->substr(0, 5) }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                    {{ $attendanceRequest->approver->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    <span
                                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                    @if ($attendanceRequest->approved_at)
                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            {{ $attendanceRequest->approved_at->format('d/m/Y H:i') }}
                                        </div>
                                    @elseif ($attendanceRequest->rejected_at)
                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            {{ $attendanceRequest->rejected_at->format('d/m/Y H:i') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-sm">
                                    <div class="line-clamp-2">{{ $attendanceRequest->reason }}</div>
                                    @if ($attendanceRequest->approval_note)
                                        <div class="mt-1 text-xs text-green-700 dark:text-green-400">
                                            ✓ {{ $attendanceRequest->approval_note }}
                                        </div>
                                    @endif
                                    @if ($attendanceRequest->rejection_reason)
                                        <div class="mt-1 text-xs text-red-700 dark:text-red-400">
                                            ✗ {{ $attendanceRequest->rejection_reason }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-gray-500 dark:text-gray-400">
                                    Tidak ada data pengajuan kehadiran.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-slate-700/50">
                {{ $attendanceRequests->links() }}
            </div>
        </div>
    </div>

    <x-toast-notification />
</x-app-layout>
