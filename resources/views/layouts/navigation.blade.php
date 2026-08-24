<!-- Mobile Off-canvas menu overlay -->
<div x-show="sidebarOpen" class="fixed inset-0 flex z-50 lg:hidden" role="dialog" aria-modal="true" style="display: none;">
    <!-- Backdrop with blur -->
    <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 bg-cyan-950/60 dark:bg-slate-950/80 backdrop-blur-sm"
        @click="sidebarOpen = false" aria-hidden="true">
    </div>

    <!-- Mobile Sidebar Panel -->
    <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform"
        x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="relative flex-1 flex flex-col max-w-[260px] w-full bg-gradient-to-b from-primary-700 to-primary-900 dark:from-indigo-950 dark:to-slate-900 shadow-2xl rounded-r-2xl overflow-hidden">

        <!-- Close Button -->
        <div class="absolute top-0 right-0 -mr-12 pt-4">
            <button @click="sidebarOpen = false" type="button"
                class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-amber-500 bg-white/20 hover:bg-white/30 text-white backdrop-blur-md transition-all">
                <span class="sr-only">Tutup sidebar</span>
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Mobile Header / Logo -->
        <div
            class="flex-shrink-0 flex items-center px-5 h-16 border-b border-white/15 dark:border-indigo-900/50 bg-white/10 dark:bg-indigo-950/40 backdrop-blur-sm">
            <x-application-logo class="block h-7 w-auto text-gold-300 dark:text-indigo-400 drop-shadow-sm" />
            <span
                class="ml-3 text-white dark:text-white font-extrabold text-lg tracking-tight">{{ config('app.name') }}</span>
        </div>

        <!-- Mobile Navigation -->
        <div class="flex-1 h-0 pt-4 pb-4 overflow-y-auto scrollbar-hide">
            <div class="px-5 mb-2">
                <h3 class="text-[10px] font-bold text-white/50 dark:text-indigo-400/60 uppercase tracking-widest">
                    Menu Utama</h3>
            </div>
            <nav class="px-3 space-y-1">
                @include('layouts.partials.menu-items')
            </nav>
        </div>

        <!-- Mobile User Profile -->
        <div
            class="flex-shrink-0 p-3 bg-white/10 dark:bg-slate-900/50 border-t border-white/15 dark:border-indigo-900/50">
            <a href="{{ route('profile.edit') }}"
                class="flex items-center w-full group rounded-xl hover:bg-white/15 dark:hover:bg-indigo-900/40 p-2 transition-all duration-200">
                <div class="relative">
                    <img loading="lazy"
                        class="inline-block h-9 w-9 rounded-full ring-2 ring-white dark:ring-indigo-800 shadow-sm object-cover"
                        src="{{ asset('img/user.png') }}" alt="User profile">
                    <div
                        class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-slate-900">
                    </div>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-[13px] font-bold text-white dark:text-indigo-100 truncate max-w-[140px]">
                        {{ Auth::user()->name }}</p>
                    <p class="text-[11px] font-medium text-white/70 dark:text-indigo-400">Pengaturan Profil</p>
                </div>
            </a>
        </div>
    </div>
    <div class="flex-shrink-0 w-14" aria-hidden="true"></div>
</div>

<!-- Desktop Floating Sidebar (Premium Enterprise Look) -->
<div
    class="hidden lg:flex lg:flex-col lg:w-[260px] lg:fixed lg:top-3 lg:bottom-3 lg:left-3 lg:z-50 transition-all duration-300">
    <!-- Inner Floating Box with Pacific Cyan & Indigo Palette -->
    <div
        class="flex-1 flex flex-col min-h-0 bg-gradient-to-r from-primary-700 to-primary-900 dark:from-indigo-950 dark:to-slate-900 border border-white/15 dark:border-indigo-900/40 shadow-xl shadow-primary-900/20 dark:shadow-black/40 rounded-3xl overflow-hidden">

        <!-- Logo Area -->
        <div
            class="flex items-center flex-shrink-0 px-5 h-16 border-b border-white/15 dark:border-indigo-900/50 bg-white/10 dark:bg-indigo-950/30 backdrop-blur-sm z-10">
            <div
                class="flex items-center justify-center bg-white/15 dark:bg-indigo-900/50 h-9 w-9 rounded-xl shadow-sm border border-white/20 dark:border-indigo-800/50">
                <x-application-logo class="block h-5 w-auto text-gold-300 dark:text-indigo-400" />
            </div>
            <span
                class="ml-3 text-white dark:text-white font-extrabold text-lg tracking-wide">{{ config('app.name') }}</span>
        </div>

        <!-- Navigation Menu -->
        <div class="flex-1 flex flex-col pt-5 pb-4 overflow-y-auto scrollbar-hide relative">
            <!-- Decorative soft glow behind menu -->
            <div
                class="absolute top-0 left-0 right-0 h-40 bg-gradient-to-b from-white/10 dark:from-indigo-900/10 to-transparent pointer-events-none">
            </div>
            <nav class="flex-1 px-3 space-y-1 relative z-10">
                @include('layouts.partials.menu-items')
            </nav>
        </div>

        <!-- User Profile Area (Floating Pill inside sidebar) -->
        <div class="flex-shrink-0 p-3 bg-transparent relative z-10">
            <a href="{{ route('profile.edit') }}"
                class="group block w-full bg-white/10 dark:bg-indigo-900/20 border border-white/15 dark:border-indigo-800/40 rounded-2xl p-2.5 hover:bg-white/15 dark:hover:bg-indigo-900/40 hover:border-gold-300/40 dark:hover:border-indigo-700/50 transition-all duration-300 shadow-sm backdrop-blur-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center overflow-hidden">
                        <div class="relative flex-shrink-0">
                            <img loading="lazy"
                                class="inline-block h-9 w-9 rounded-full ring-2 ring-white dark:ring-indigo-950 shadow-sm object-cover group-hover:scale-105 transition-transform duration-300"
                                src="{{ asset('img/user.png') }}" alt="User profile">
                            <div
                                class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-indigo-950">
                            </div>
                        </div>
                        <div class="ml-3 truncate">
                            <p class="text-[13px] font-bold text-white dark:text-indigo-100 truncate">
                                {{ Auth::user()->name }}</p>
                            <p class="text-[10px] font-medium text-white/70 dark:text-indigo-400 truncate">Lihat Profil
                                & Pengaturan</p>
                        </div>
                    </div>
                    <div
                        class="flex-shrink-0 ml-1 bg-white/15 dark:bg-indigo-950/50 p-1.5 rounded-lg border border-white/20 dark:border-indigo-900/50 group-hover:border-gold-300/50 dark:group-hover:border-indigo-600 transition-colors">
                        <svg class="h-4 w-4 text-white/60 dark:text-indigo-400/70 group-hover:text-gold-300 dark:group-hover:text-indigo-300"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
