@props(['size' => 'w-5 h-5'])

<div x-data="{
    theme: localStorage.getItem('theme') || 'light',
    init() {
        if (this.theme === 'dark' || (!this.theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            this.theme = 'dark';
        } else {
            document.documentElement.classList.remove('dark');
            this.theme = 'light';
        }
    },
    toggleTheme() {
        this.theme = this.theme === 'light' ? 'dark' : 'light';
        localStorage.setItem('theme', this.theme);
        if (this.theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
}" class="inline-flex">
    <button @click="toggleTheme()"
        class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-slate-700 dark:hover:bg-slate-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500"
        :aria-label="theme === 'light' ? 'Switch to dark mode' : 'Switch to light mode'">
        <!-- Sun Icon -->
        <svg x-show="theme === 'light'"
            {{ $attributes->merge(['class' => $size . ' text-gray-600 dark:text-gray-300']) }} fill="none"
            stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z">
            </path>
        </svg>
        <!-- Moon Icon -->
        <svg x-show="theme === 'dark'"
            {{ $attributes->merge(['class' => $size . ' text-gray-600 dark:text-gray-300']) }} fill="none"
            stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
        </svg>
    </button>
</div>
