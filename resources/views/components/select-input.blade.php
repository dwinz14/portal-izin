@props(['disabled' => false])

<div class="relative">
    <select @disabled($disabled)
        {{ $attributes->merge([
            'class' => 'w-full appearance-none rounded-xl border-2 border-gray-200
                                dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-3
                                text-gray-900 dark:text-gray-100 shadow-sm transition-all
                                duration-200 focus:border-primary-500 focus:ring-2
                                focus:ring-primary-500/20 sm:text-sm cursor-pointer
                                hover:border-gray-300 dark:hover:border-slate-600',
        ]) }}>
        {{ $slot }}
    </select>

    {{-- Panah dropdown di luar select --}}
    <div
        class="pointer-events-none absolute inset-y-0 right-0 flex items-center
                px-3 text-gray-400 dark:text-gray-500">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </div>
</div>
