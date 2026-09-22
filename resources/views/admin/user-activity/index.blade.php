<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2
                    class="border-l-[5px] border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    Monitoring Aktivitas Pengguna
                </h2>
                <p class="pl-5 text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    Pantau seluruh aktivitas karyawan secara real-time.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-5" x-data="{ showFilters: false }">

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $statCards = [
                    [
                        'label' => 'Aktivitas Hari Ini',
                        'value' => $stats['activities_today'],
                        'color' => 'primary',
                        'icon' =>
                            'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                    ],
                    [
                        'label' => 'User Online Sekarang',
                        'value' => $stats['online_count'],
                        'color' => 'green',
                        'icon' =>
                            'M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z',
                    ],
                    [
                        'label' => 'Login Hari Ini',
                        'value' => $stats['logins_today'],
                        'color' => 'blue',
                        'icon' =>
                            'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1',
                    ],
                    [
                        'label' => 'Aktivitas 7 Hari',
                        'value' => $stats['activities_7days'],
                        'color' => 'purple',
                        'icon' =>
                            'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                    ],
                ];
            @endphp

            @foreach ($statCards as $card)
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-4 flex items-center gap-4">
                    <div
                        class="flex-shrink-0 w-11 h-11 rounded-xl
                        {{ match ($card['color']) {
                            'green' => 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400',
                            'blue' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
                            'purple' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400',
                            default => 'bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400',
                        } }}
                        flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="{{ $card['icon'] }}" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($card['value']) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $card['label'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Filter Panel --}}
        <div
            class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-4 py-3 flex items-center justify-between cursor-pointer select-none"
                @click="showFilters = !showFilters">
                <div class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter Aktivitas
                    @if (request()->hasAny(['search', 'category', 'date_from', 'date_to']))
                        <span
                            class="bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400 text-xs px-2 py-0.5 rounded-full font-semibold">Aktif</span>
                    @endif
                </div>
                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                    :class="showFilters ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>

            <div x-show="showFilters" x-collapse class="border-t border-gray-100 dark:border-slate-700">
                <form method="GET" action="{{ route('admin.user-activity.index') }}"
                    class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Cari User</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Nama atau NIK..."
                            class="block w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white text-sm focus:ring-primary-500 focus:border-primary-500" />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Kategori</label>
                        <select name="category"
                            class="block w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white text-sm focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Semua Kategori</option>
                            <option value="auth" @selected(request('category') === 'auth')>🔑 Autentikasi</option>
                            <option value="leave" @selected(request('category') === 'leave')>📝 Cuti</option>
                            <option value="approval" @selected(request('category') === 'approval')>✅ Persetujuan</option>
                            <option value="attendance" @selected(request('category') === 'attendance')>🕒 Kehadiran</option>
                            <option value="profile" @selected(request('category') === 'profile')>⚙️ Profil</option>
                            <option value="admin" @selected(request('category') === 'admin')>🛡️ Admin</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Dari
                            Tanggal</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="block w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white text-sm focus:ring-primary-500 focus:border-primary-500
                                   [&::-webkit-calendar-picker-indicator]:dark:filter [&::-webkit-calendar-picker-indicator]:dark:invert" />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Sampai
                            Tanggal</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="block w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white text-sm focus:ring-primary-500 focus:border-primary-500
                                   [&::-webkit-calendar-picker-indicator]:dark:filter [&::-webkit-calendar-picker-indicator]:dark:invert" />
                    </div>

                    <div class="sm:col-span-2 lg:col-span-4 flex gap-2 pt-1">
                        <button type="submit"
                            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                            Terapkan Filter
                        </button>
                        <a href="{{ route('admin.user-activity.index') }}"
                            class="px-4 py-2 bg-white dark:bg-slate-700 border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-slate-600 transition-colors">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Activity Feed --}}
        <div
            class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-200 dark:border-slate-700 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    Feed Aktivitas
                </h3>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ number_format($activities->total()) }} total entri
                </span>
            </div>

            @forelse ($activities as $activity)
                @php
                    $props = $activity->properties;
                    $eventType = $props['event_type'] ?? 'other';
                    $color = $props['color'] ?? 'gray';
                    $causer = $activity->causer;
                @endphp

                <div
                    class="px-5 py-4 border-b border-gray-50 dark:border-slate-700/50 hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors flex items-start gap-4">

                    {{-- User Initials --}}
                    <div
                        class="flex-shrink-0 w-9 h-9 rounded-full bg-primary-600 flex items-center justify-center text-white text-sm font-bold uppercase">
                        {{ substr($causer?->name ?? '?', 0, 1) }}
                    </div>

                    {{-- Konten --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-0.5">
                            {{-- Nama user --}}
                            @if ($causer)
                                <a href="{{ route('admin.user-activity.show', $causer->id) }}"
                                    class="text-sm font-semibold text-gray-900 dark:text-gray-100 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                    {{ ucwords($causer->name) }}
                                </a>
                                {{-- Role badge --}}
                                <span
                                    class="text-xs px-2 py-0.5 rounded-full font-medium
                                    {{ match ($causer->role) {
                                        'super_admin' => 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400',
                                        'hrd' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400',
                                        'direksi' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/20 dark:text-teal-400',
                                        'kabag-pincab' => 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-400',
                                        'kasie' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400',
                                        default => 'bg-purple-100 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400',
                                    } }}">
                                    {{ strtoupper(str_replace('-', ' ', $causer->role)) }}
                                </span>
                            @else
                                <span class="text-sm font-medium text-gray-400 dark:text-gray-500 italic">User
                                    dihapus</span>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            {{-- Icon kategori --}}
                            <x-activity-icon :event-type="$eventType" :color="$color" size="sm" />
                            {{-- Deskripsi --}}
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                {{ $activity->description }}
                            </p>
                        </div>
                    </div>

                    {{-- Waktu --}}
                    <div class="flex-shrink-0 text-right">
                        <p class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap"
                            title="{{ $activity->created_at->format('d/m/Y H:i:s') }}">
                            {{ $activity->created_at->diffForHumans() }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                            {{ $activity->created_at->format('H:i') }}
                        </p>
                    </div>
                </div>

            @empty
                <div class="py-16 text-center">
                    <div
                        class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-slate-700 flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Belum ada aktivitas tercatat</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Aktivitas akan muncul setelah integrasi logging diaktifkan.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($activities->hasPages())
            <div class="flex justify-center">
                {{ $activities->links() }}
            </div>
        @endif

    </div>
</x-app-layout>
