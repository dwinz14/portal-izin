<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.user-activity.index') }}"
                class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors text-gray-500 dark:text-gray-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h2
                    class="border-l-[5px] border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    Aktivitas: {{ ucwords($user->name) }}
                </h2>
                <p class="pl-5 text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ $user->nik }} · {{ strtoupper(str_replace('-', ' ', $user->role)) }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-5" x-data="{ category: '{{ request('category', '') }}' }">

        {{-- User Profile Card --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-5">
            <div class="flex items-start gap-5">
                {{-- Avatar --}}
                <div
                    class="flex-shrink-0 w-16 h-16 rounded-2xl bg-primary-600 flex items-center justify-center text-white text-2xl font-bold uppercase shadow-lg">
                    {{ substr($user->name, 0, 1) }}
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 flex-wrap">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                            {{ ucwords($user->name) }}
                        </h3>
                        {{-- Online/Offline Badge --}}
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold
                            {{ $isOnline
                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                : 'bg-gray-100 text-gray-600 dark:bg-slate-700 dark:text-slate-400' }}">
                            <span
                                class="w-1.5 h-1.5 rounded-full {{ $isOnline ? 'bg-green-500 animate-pulse' : 'bg-gray-400' }}"></span>
                            {{ $isOnline ? 'Online' : 'Offline' }}
                        </span>
                        {{-- Status akun --}}
                        <span
                            class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                            {{ $user->status === 'approved'
                                ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' }}">
                            {{ ucfirst($user->status) }}
                        </span>
                    </div>

                    <div
                        class="mt-2 grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2" />
                            </svg>
                            NIK: <span
                                class="font-mono font-medium text-gray-700 dark:text-gray-300">{{ $user->nik }}</span>
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            {{ $user->division?->nama_divisi ?? 'Tanpa Divisi' }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ $user->email }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Login terakhir:
                            <span class="text-gray-700 dark:text-gray-300">
                                {{ $stats['last_login_at']
                                    ? \Carbon\Carbon::parse($stats['last_login_at'])->format('d/m/Y H:i')
                                    : 'Belum pernah' }}
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Mini Stats --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-5 pt-5 border-t border-gray-100 dark:border-slate-700">
                @foreach ([['label' => 'Total Aktivitas', 'value' => $stats['total'], 'color' => 'slate'], ['label' => 'Login Bulan Ini', 'value' => $stats['logins_month'], 'color' => 'blue'], ['label' => 'Cuti Diajukan', 'value' => $stats['leaves_month'], 'color' => 'indigo'], ['label' => 'Persetujuan Bulan Ini', 'value' => $stats['approvals_month'], 'color' => 'green']] as $s)
                    <div
                        class="text-center p-3 rounded-xl
                        {{ match ($s['color']) {
                            'blue' => 'bg-blue-50 dark:bg-blue-900/20',
                            'indigo' => 'bg-indigo-50 dark:bg-indigo-900/20',
                            'green' => 'bg-green-50 dark:bg-green-900/20',
                            default => 'bg-slate-50 dark:bg-slate-700/40',
                        } }}">
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($s['value']) }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $s['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Filter: Category Chips + Date Range --}}
        <form method="GET" action="{{ route('admin.user-activity.show', $user->id) }}"
            class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-4">
            <div class="flex flex-col sm:flex-row gap-3">
                {{-- Category chips --}}
                <div class="flex flex-wrap gap-2 flex-1">
                    @foreach ([
        '' => 'Semua',
        'auth' => '🔑 Auth',
        'leave' => '📝 Cuti',
        'approval' => '✅ Persetujuan',
        'attendance' => '🕒 Kehadiran',
        'profile' => '⚙️ Profil',
        'admin' => '🛡️ Admin',
    ] as $val => $label)
                        <button type="submit" name="category" value="{{ $val }}"
                            class="px-3 py-1.5 rounded-full text-xs font-medium transition-all duration-150
                                {{ request('category', '') === $val
                                    ? 'bg-primary-600 text-white shadow-sm'
                                    : 'bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-slate-600' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                {{-- Date range --}}
                <div class="flex items-center gap-2 flex-shrink-0">
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="text-xs rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:ring-primary-500 focus:border-primary-500
                               [&::-webkit-calendar-picker-indicator]:dark:filter [&::-webkit-calendar-picker-indicator]:dark:invert" />
                    <span class="text-xs text-gray-400">–</span>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="text-xs rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:ring-primary-500 focus:border-primary-500
                               [&::-webkit-calendar-picker-indicator]:dark:filter [&::-webkit-calendar-picker-indicator]:dark:invert" />
                    @if (request()->hasAny(['category', 'date_from', 'date_to']))
                        <a href="{{ route('admin.user-activity.show', $user->id) }}"
                            class="text-xs text-gray-500 hover:text-red-500 dark:text-gray-400 dark:hover:text-red-400 transition-colors px-2 py-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Timeline --}}
        <div class="space-y-4">
            @forelse ($grouped as $date => $dayActivities)
                @php
                    $today = today()->format('Y-m-d');
                    $yesterday = today()->subDay()->format('Y-m-d');
                    $dateLabel = match ($date) {
                        $today => 'Hari ini',
                        $yesterday => 'Kemarin',
                        default => \Carbon\Carbon::parse($date)->locale('id')->isoFormat('dddd, D MMMM Y'),
                    };
                @endphp

                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
                    {{-- Date header --}}
                    <div
                        class="px-5 py-2.5 bg-gray-50 dark:bg-slate-700/50 border-b border-gray-100 dark:border-slate-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                            {{ $dateLabel }}
                        </span>
                        <span class="ml-auto text-xs text-gray-400 dark:text-gray-500">
                            {{ $dayActivities->count() }} aktivitas
                        </span>
                    </div>

                    {{-- Activity items --}}
                    <div class="divide-y divide-gray-50 dark:divide-slate-700/50">
                        @foreach ($dayActivities as $activity)
                            @php
                                $props = $activity->properties;
                                $eventType = $props['event_type'] ?? 'other';
                                $color = $props['color'] ?? 'gray';
                            @endphp

                            <div
                                class="px-5 py-4 flex items-start gap-4 hover:bg-gray-50 dark:hover:bg-slate-700/20 transition-colors group">
                                {{-- Timeline line + icon --}}
                                <div class="flex flex-col items-center">
                                    <x-activity-icon :event-type="$eventType" :color="$color" />
                                </div>

                                {{-- Content --}}
                                <div class="flex-1 min-w-0 pt-0.5">
                                    <p class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed">
                                        {{ $activity->description }}
                                    </p>

                                    {{-- Metadata tambahan --}}
                                    @php
                                        $meta = collect($props)
                                            ->except(['event_type', 'category', 'color'])
                                            ->filter();
                                    @endphp
                                    @if ($meta->isNotEmpty())
                                        <div class="mt-1.5 flex flex-wrap gap-2">
                                            @foreach ($meta as $key => $val)
                                                @if (is_string($val) || is_numeric($val))
                                                    <span
                                                        class="text-xs px-2 py-0.5 bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-gray-400 rounded-md font-mono">
                                                        {{ str_replace('_', ' ', $key) }}: {{ $val }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                {{-- Waktu --}}
                                <div class="flex-shrink-0 text-right pt-0.5">
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                        {{ $activity->created_at->format('H:i') }}
                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5"
                                        title="{{ $activity->created_at->format('d/m/Y H:i:s') }}">
                                        {{ $activity->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            @empty
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 py-16 text-center">
                    <div
                        class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-slate-700 flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Belum ada aktivitas</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        @if (request()->hasAny(['category', 'date_from', 'date_to']))
                            Tidak ada aktivitas yang cocok dengan filter ini.
                        @else
                            Aktivitas user ini akan muncul setelah logging diaktifkan.
                        @endif
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
