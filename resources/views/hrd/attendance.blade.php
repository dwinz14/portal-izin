<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="border-l-[5px] border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                {{ __('Rekap Kehadiran') }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Monitoring pengajuan kehadiran yang sudah masuk ke sistem.
            </p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <form method="GET" action="{{ route('hrd.kehadiran.index') }}" class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-5">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select name="status" class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Semua Status</option>
                        @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jenis</label>
                    <select name="type" class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Semua Jenis</option>
                        @foreach ($typeLabels as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                        class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                        class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500">
                </div>
            </div>
            <div class="mt-4 flex items-center justify-end gap-3">
                <a href="{{ route('hrd.kehadiran.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-slate-600 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700">
                    Reset
                </a>
                <button type="submit" class="px-4 py-2 rounded-lg bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700">
                    Filter
                </button>
            </div>
        </form>

        <div class="bg-white dark:bg-slate-800 shadow-xl rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-blue-100 dark:bg-blue-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">Pemohon</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">Jenis</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">Tanggal & Waktu</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">Atasan</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">Catatan</th>
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
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                    <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $attendanceRequest->user->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ optional($attendanceRequest->user->office)->nama_kantor ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $attendanceRequest->type_label }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                    {{ $attendanceRequest->date->isoFormat('D MMM YYYY') }}
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ \Illuminate\Support\Str::of($attendanceRequest->start_time)->substr(0, 5) }}
                                        @if ($attendanceRequest->end_time)
                                            - {{ \Illuminate\Support\Str::of($attendanceRequest->end_time)->substr(0, 5) }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ $attendanceRequest->approver->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                        {{ $attendanceRequest->status }}
                                    </span>
                                    @if ($attendanceRequest->approved_at)
                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $attendanceRequest->approved_at->format('Y-m-d H:i') }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-sm">
                                    <div class="line-clamp-2">{{ $attendanceRequest->reason }}</div>
                                    @if ($attendanceRequest->approval_note)
                                        <div class="mt-1 text-xs text-green-700 dark:text-green-400">Catatan: {{ $attendanceRequest->approval_note }}</div>
                                    @endif
                                    @if ($attendanceRequest->rejection_reason)
                                        <div class="mt-1 text-xs text-red-700 dark:text-red-400">Ditolak: {{ $attendanceRequest->rejection_reason }}</div>
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
