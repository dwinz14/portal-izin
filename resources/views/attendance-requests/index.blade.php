<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="border-l-[5px] border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Pengajuan Kehadiran Saya') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Riwayat pengajuan terlambat, pulang awal, meninggalkan pekerjaan, dan update absensi.
                </p>
            </div>
            <a href="{{ route('kehadiran.create') }}"
                class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:ring-offset-slate-800 transition">
                <svg class="w-4 h-4 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Ajukan Kehadiran
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-800 shadow-xl rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-blue-100 dark:bg-blue-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">Jenis</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">Tanggal & Waktu</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">Atasan</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">Alasan</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-sm font-medium text-stone-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
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
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                    {{ $attendanceRequest->type_label }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                    <div>{{ $attendanceRequest->date->isoFormat('D MMMM YYYY') }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ \Illuminate\Support\Str::of($attendanceRequest->start_time)->substr(0, 5) }}
                                        @if ($attendanceRequest->end_time)
                                            - {{ \Illuminate\Support\Str::of($attendanceRequest->end_time)->substr(0, 5) }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                    {{ $attendanceRequest->approver->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                    {{ $attendanceRequest->reason }}
                                    @if ($attendanceRequest->rejection_reason)
                                        <div class="text-xs text-red-600 dark:text-red-400 mt-1 truncate">
                                            Ditolak: {{ $attendanceRequest->rejection_reason }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                        {{ $attendanceRequest->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm whitespace-nowrap">
                                    @if ($attendanceRequest->status === 'pending')
                                        <form action="{{ route('kehadiran.destroy', $attendanceRequest) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                onclick="return confirm('Yakin ingin membatalkan pengajuan kehadiran ini?');"
                                                class="px-3 py-1 text-sm rounded bg-red-600 text-white hover:bg-red-700">
                                                Batalkan
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-gray-500 dark:text-gray-400">
                                    <p class="text-lg font-semibold text-gray-800 dark:text-gray-100">Belum ada pengajuan kehadiran</p>
                                    <p class="text-sm mt-1">Ajukan data kehadiran baru saat diperlukan.</p>
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
