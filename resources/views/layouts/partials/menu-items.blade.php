@php
    $menuItems = getFilteredMenuItems();
    $currentRole = auth()->user()?->role;

    $isAllowed = static function (array $item) use ($currentRole): bool {
        if (!isset($item['roles']) || empty($item['roles'])) {
            return true;
        }

        return in_array($currentRole, $item['roles'], true);
    };

    $positiveBadge = static function ($count): bool {
        return is_numeric($count) && (int) $count > 0;
    };
@endphp

<nav aria-label="Navigasi utama" class="space-y-1">
    @foreach ($menuItems as $item)
        @php
            if (!$isAllowed($item)) {
                continue;
            }

            $type = $item['type'] ?? (isset($item['children']) ? 'section' : 'item');
        @endphp

        @if ($type === 'section' && isset($item['children']))
            @php
                $children = collect($item['children'])->filter($isAllowed)->values();

                if ($children->isEmpty()) {
                    continue;
                }

                $sectionActive = isMenuActive($item['active_pattern'] ?? []);
                $sectionId = 'menu-section-' . \Illuminate\Support\Str::slug($item['name']) . '-' . $loop->index;
            @endphp

            <div x-data="{
                expanded: {{ $sectionActive ? 'true' : 'false' }},
                toggle() { this.expanded = !this.expanded },
            }" class="pt-3 first:pt-1">
                <button type="button" @click="toggle()" :aria-expanded="expanded.toString()"
                    aria-controls="{{ $sectionId }}" {{-- WARNA PARENT --}}
                    class="group flex w-full items-center gap-3 rounded-xl px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.12em] transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-300 dark:focus-visible:ring-indigo-500/50
                        {{ $sectionActive
                            ? 'text-gold-300 dark:text-indigo-200'
                            : 'text-white hover:text-gold-200 dark:text-indigo-300/55 dark:hover:text-indigo-200' }}">
                    <span class="flex min-w-0 flex-1 items-center gap-2 text-left">
                        <span {{-- WARNA ICON PARENT --}}
                            class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg transition-colors
                                {{ $sectionActive
                                    ? 'bg-white/10 text-gold-300 dark:bg-indigo-900/50 dark:text-indigo-300'
                                    : 'text-white group-hover:bg-white/5 group-hover:text-gold-200 dark:text-indigo-400/50 dark:group-hover:bg-indigo-900/20 dark:group-hover:text-indigo-200' }}"
                            aria-hidden="true">
                            {!! $item['icon'] !!}
                        </span>
                        <span class="truncate">{{ $item['name'] }}</span>
                    </span>

                    <svg :class="expanded ? 'rotate-90' : ''"
                        class="h-4 w-4 flex-shrink-0 text-white transition-transform duration-200 group-hover:text-gold-200 dark:text-indigo-400/50"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <div id="{{ $sectionId }}" x-show="expanded" x-collapse x-cloak class="mt-1 space-y-1 pl-2">
                    @foreach ($children as $child)
                        @php
                            $childActive = isMenuActive($child['active_pattern'] ?? []);
                            $badgeCount = $child['badge_count'] ?? 0;
                            $badgeTone = $child['badge_tone'] ?? 'danger';

                            $badgeClass = match ($badgeTone) {
                                'warning'
                                    => 'bg-amber-100 text-amber-800 ring-1 ring-amber-200/70 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/20',
                                'success'
                                    => 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200/70 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/20',
                                'info'
                                    => 'bg-sky-100 text-sky-800 ring-1 ring-sky-200/70 dark:bg-sky-400/10 dark:text-sky-300 dark:ring-sky-400/20',
                                default
                                    => 'bg-rose-100 text-rose-800 ring-1 ring-rose-200/70 dark:bg-rose-400/10 dark:text-rose-300 dark:ring-rose-400/20',
                            };
                        @endphp

                        <a href="{{ route($child['route']) }}" @class([
                            'group relative flex min-h-10 items-center gap-3 rounded-xl px-3 py-2 text-sm transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-300 dark:focus-visible:ring-indigo-500/50',
                            // WARNA CHILD AKTIF
                            'bg-white/12 text-gold-300 shadow-sm ring-1 ring-white/10 dark:bg-indigo-900/45 dark:text-indigo-50 dark:ring-indigo-700/40' => $childActive,
                            // WARNA CHILD INAKTIF
                            'text-white hover:bg-white/7 hover:text-gold-200 dark:text-indigo-200/75 dark:hover:bg-indigo-900/25 dark:hover:text-indigo-50' => !$childActive,
                        ])>
                            <span
                                class="absolute inset-y-2 left-0 w-0.5 rounded-full transition-all duration-200
                                    {{ $childActive ? 'bg-gold-300 shadow-[0_0_8px_rgba(240,206,90,0.45)] dark:bg-indigo-400 dark:shadow-[0_0_8px_rgba(129,140,248,0.4)]' : 'bg-transparent' }}"
                                aria-hidden="true"></span>

                            <span
                                class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg transition-colors
                                    {{ $childActive
                                        ? 'text-gold-300 dark:text-indigo-300'
                                        : 'text-white group-hover:text-gold-200 dark:text-indigo-400/55 dark:group-hover:text-indigo-200' }}"
                                aria-hidden="true">
                                {!! $child['icon'] ?? '' !!}
                            </span>

                            <span class="min-w-0 flex-1 truncate">
                                {{ $child['name'] }}
                            </span>

                            @if ($positiveBadge($badgeCount))
                                <span
                                    class="inline-flex min-w-5 items-center justify-center rounded-full px-1.5 py-0.5 text-[10px] font-bold leading-none {{ $badgeClass }}"
                                    aria-label="{{ $badgeCount }} item menunggu">
                                    {{ $badgeCount > 99 ? '99+' : $badgeCount }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @else
            @php
                $itemActive = isMenuActive($item['active_pattern'] ?? []);
                $badgeCount = $item['badge_count'] ?? 0;
            @endphp

            <div class="mb-1">
                <a href="{{ route($item['route']) }}" @class([
                    'group relative flex min-h-11 w-full items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-300 dark:focus-visible:ring-indigo-500/50',
                    // MENU AKTIF
                    'bg-white/12 text-gold-300 shadow-md ring-1 ring-white/10 dark:bg-indigo-900/45 dark:text-indigo-50 dark:ring-indigo-700/40' => $itemActive,
                    // MENU INAKTIF
                    'text-white hover:bg-white/7 hover:text-gold-200 dark:text-indigo-200/80 dark:hover:bg-indigo-900/25 dark:hover:text-indigo-50' => !$itemActive,
                ])>
                    <span
                        class="absolute inset-y-2 left-0 w-0.5 rounded-full transition-all duration-200
                            {{ $itemActive ? 'bg-gold-300 shadow-[0_0_8px_rgba(240,206,90,0.45)] dark:bg-indigo-400 dark:shadow-[0_0_8px_rgba(129,140,248,0.4)]' : 'bg-transparent' }}"
                        aria-hidden="true"></span>

                    <span
                        class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg transition-colors
                            {{ $itemActive
                                ? 'text-gold-300 dark:text-indigo-300'
                                : 'text-white group-hover:text-gold-200 dark:text-indigo-400/60 dark:group-hover:text-indigo-200' }}"
                        aria-hidden="true">
                        {!! $item['icon'] !!}
                    </span>

                    <span class="min-w-0 flex-1 truncate">{{ $item['name'] }}</span>

                    @if ($positiveBadge($badgeCount))
                        <span
                            class="inline-flex min-w-5 items-center justify-center rounded-full bg-rose-100 px-1.5 py-0.5 text-[10px] font-bold leading-none text-rose-800 ring-1 ring-rose-200/70 dark:bg-rose-400/10 dark:text-rose-300 dark:ring-rose-400/20"
                            aria-label="{{ $badgeCount }} item menunggu">
                            {{ $badgeCount > 99 ? '99+' : $badgeCount }}
                        </span>
                    @endif
                </a>
            </div>
        @endif
    @endforeach
</nav>
