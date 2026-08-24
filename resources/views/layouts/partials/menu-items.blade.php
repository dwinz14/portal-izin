@php
    $menuItems = getFilteredMenuItems();
@endphp

@foreach ($menuItems as $item)
    @if (isset($item['children']))
        <!-- Menggunakan Alpine.js untuk animasi yang halus -->
        <div x-data="{ expanded: {{ isMenuActive($item['active_pattern']) ? 'true' : 'false' }} }" class="mb-1">
            <button @click="expanded = !expanded"
                class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-xl transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-gold-300 dark:focus:ring-indigo-500/40
                {{ isMenuActive($item['active_pattern'])
                    ? 'bg-white/15 dark:bg-indigo-900/40 text-white dark:text-indigo-100 shadow-lg ring-1 ring-gold-300 dark:ring-indigo-700/50 backdrop-blur-sm'
                    : 'text-white/85 dark:text-indigo-300 hover:bg-white/10 dark:hover:bg-indigo-800/30 hover:text-white dark:hover:text-indigo-100' }}">

                <div class="flex items-center truncate">
                    <!-- Icon dengan aksen Gold di Light Mode, Indigo terang di Dark Mode -->
                    <span
                        class="{{ isMenuActive($item['active_pattern']) ? 'text-gold-300 dark:text-indigo-400' : 'text-white/70 dark:text-indigo-400/60 group-hover:text-gold-300 dark:group-hover:text-indigo-300' }} transition-colors mr-3 flex-shrink-0">
                        {!! $item['icon'] !!}
                    </span>
                    <span class="truncate">{{ $item['name'] }}</span>
                </div>

                <div class="flex items-center flex-shrink-0 ml-2">
                    @if (isset($item['badge_count']) && $item['badge_count'] > 0)
                        <span
                            class="bg-amber-100 dark:bg-indigo-500/30 text-amber-700 dark:text-indigo-300 border border-amber-200/50 dark:border-indigo-500/30 text-[10px] font-bold px-2 py-0.5 rounded-full mr-2 leading-none shadow-sm">
                            {{ $item['badge_count'] }}
                        </span>
                    @endif
                    <svg :class="{ 'rotate-90': expanded }"
                        class="h-4 w-4 text-white/70 dark:text-indigo-400/70 transition-transform duration-300"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </button>

            <!-- Smooth Dropdown Content -->
            <div x-show="expanded" x-collapse style="display: none;" class="mt-1 space-y-1 pl-9 pr-2">

                @foreach ($item['children'] as $child)
                    <a href="{{ route($child['route']) }}"
                        class="group flex items-center px-3 py-1.5 text-[13px] rounded-lg transition-all duration-200 relative
                        {{ isMenuActive($child['active_pattern'])
                            ? 'text-white dark:text-indigo-100 font-bold bg-white/15 dark:bg-indigo-800/40 shadow-sm'
                            : 'text-white/80 dark:text-indigo-300/80 hover:text-white dark:hover:text-indigo-100 hover:bg-white/10 dark:hover:bg-indigo-800/20' }}">

                        <!-- Garis Konektor -->
                        <div
                            class="absolute left-[-11px] top-1/2 -translate-y-1/2 w-[20px] h-[1px] {{ isMenuActive($child['active_pattern']) ? 'bg-gold-300 dark:bg-indigo-500' : 'bg-white/25 dark:bg-indigo-800' }} transition-colors">
                        </div>

                        <!-- Bullet point (Gold / Indigo) -->
                        <div
                            class="h-1.5 w-1.5 rounded-full {{ isMenuActive($child['active_pattern']) ? 'bg-gold-300 shadow-[0_0_6px_rgba(240,206,90,0.7)] dark:bg-indigo-400 dark:shadow-[0_0_6px_rgba(129,140,248,0.5)] scale-125' : 'bg-white/40 dark:bg-indigo-700 group-hover:bg-gold-300 dark:group-hover:bg-indigo-500' }} mr-3 transition-all flex-shrink-0 z-10 relative">
                        </div>

                        <span class="flex-1 truncate">{{ $child['name'] }}</span>

                        @if (isset($child['badge_count']) && $child['badge_count'] > 0)
                            <span
                                class="bg-rose-100 dark:bg-rose-500/20 text-rose-700 dark:text-rose-400 border border-rose-200/50 dark:border-rose-500/30 text-[9px] font-bold px-2 py-0.5 rounded-full ml-2 leading-none flex-shrink-0">
                                {{ $child['badge_count'] }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @else
        <div class="mb-1">
            <a href="{{ route($item['route']) }}"
                class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-xl transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-gold-300 dark:focus:ring-indigo-500/40
                {{ isMenuActive($item['active_pattern'])
                    ? 'bg-white/15 dark:bg-indigo-900/40 text-white dark:text-indigo-100 shadow-lg ring-1 ring-gold-300 dark:ring-indigo-700/50 backdrop-blur-sm'
                    : 'text-white/85 dark:text-indigo-300 hover:bg-white/10 dark:hover:bg-indigo-800/30 hover:text-white dark:hover:text-indigo-100' }}">

                <div class="flex items-center truncate">
                    <span
                        class="{{ isMenuActive($item['active_pattern']) ? 'text-gold-300 dark:text-indigo-400' : 'text-white/70 dark:text-indigo-400/60 group-hover:text-gold-300 dark:group-hover:text-indigo-300' }} transition-colors mr-3 flex-shrink-0">
                        {!! $item['icon'] !!}
                    </span>
                    <span class="truncate">{{ $item['name'] }}</span>
                </div>

                @if (isset($item['badge_count']) && $item['badge_count'] > 0)
                    <span
                        class="bg-rose-100 dark:bg-rose-500/20 text-rose-700 dark:text-rose-400 border border-rose-200/50 dark:border-rose-500/30 text-[10px] font-bold px-2 py-0.5 rounded-full ml-2 leading-none flex-shrink-0 shadow-sm">
                        {{ $item['badge_count'] }}
                    </span>
                @endif
            </a>
        </div>
    @endif
@endforeach
