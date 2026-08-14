@php
    $menuItems = getFilteredMenuItems();
@endphp

@foreach ($menuItems as $item)
    @if (isset($item['children']))
        <details class="group">
            <summary
                class="{{ isMenuActive($item['active_pattern']) ? 'bg-black/20 text-white dark:bg-slate-600 dark:text-gray-100' : 'text-indigo-100 hover:bg-black/20 hover:text-white dark:text-gray-300 dark:hover:bg-slate-600 dark:hover:text-gray-100' }} flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 hover:scale-105 cursor-pointer list-none">
                {!! $item['icon'] !!}
                <span class="flex-1">{{ $item['name'] }}</span>
                @if (isset($item['badge_count']) && $item['badge_count'] > 0)
                    <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full ml-2">
                        {{ $item['badge_count'] }}
                    </span>
                @endif
                <svg class="ml-auto h-4 w-4 transition-transform duration-200 group-open:rotate-180"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </summary>
            <div class="ml-6 mt-1 space-y-1">
                @foreach ($item['children'] as $child)
                    <a href="{{ route($child['route']) }}"
                        class="{{ isMenuActive($child['active_pattern']) ? 'bg-black/20 text-white dark:bg-slate-600 dark:text-gray-100' : 'text-indigo-100 hover:bg-black/20 hover:text-white dark:text-gray-300 dark:hover:bg-slate-600 dark:hover:text-gray-100' }} group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200 hover:scale-105">
                        {!! $child['icon'] !!}
                        <span class="flex-1">{{ $child['name'] }}</span>
                        @if (isset($child['badge_count']) && $child['badge_count'] > 0)
                            <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full ml-2">
                                {{ $child['badge_count'] }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        </details>
    @else
        <a href="{{ route($item['route']) }}"
            class="{{ isMenuActive($item['active_pattern']) ? 'bg-black/20 text-white dark:bg-slate-600 dark:text-gray-100' : 'text-indigo-100 hover:bg-black/20 hover:text-white dark:text-gray-300 dark:hover:bg-slate-600 dark:hover:text-gray-100' }} group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 hover:scale-105">
            {!! $item['icon'] !!}
            <span class="flex-1">{{ $item['name'] }}</span>
            @if (isset($item['badge_count']) && $item['badge_count'] > 0)
                <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full ml-2">
                    {{ $item['badge_count'] }}
                </span>
            @endif
        </a>
    @endif
@endforeach
