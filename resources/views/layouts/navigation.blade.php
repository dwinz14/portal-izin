{{--
    SIDEBAR NAVIGATION
    Drop-in replacement untuk blok sidebar pada navigation.blade.php.
--}}

{{-- Mobile Off-canvas menu overlay --}}
<div x-show="sidebarOpen" class="fixed inset-0 z-50 flex lg:hidden" role="dialog" aria-modal="true" style="display: none;">

    {{-- Backdrop --}}
    <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 bg-cyan-950/60 dark:bg-slate-950/80 backdrop-blur-sm"
        @click="sidebarOpen = false" aria-hidden="true"></div>

    {{-- Mobile Sidebar Panel --}}
    <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform"
        x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="relative flex h-full min-h-0 flex-1 flex-col overflow-hidden rounded-r-2xl bg-gradient-to-b from-primary-700 to-primary-900 shadow-2xl dark:from-indigo-950 dark:to-slate-900 max-w-[260px] w-full">

        {{-- Close Button --}}
        <div class="absolute right-0 top-0 -mr-12 pt-4">
            <button @click="sidebarOpen = false" type="button"
                class="ml-1 flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur-md transition-all hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-amber-500">
                <span class="sr-only">Tutup sidebar</span>
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Mobile Header / Logo --}}
        <div
            class="z-10 flex h-16 flex-shrink-0 items-center border-b border-white/15 bg-white/10 px-5 backdrop-blur-sm dark:border-indigo-900/50 dark:bg-indigo-950/40">
            <x-application-logo class="block h-7 w-auto text-gold-300 dark:text-indigo-400 drop-shadow-sm" />
            <span class="ml-3 text-lg font-extrabold tracking-tight text-white">{{ config('app.name') }}</span>
        </div>

        {{-- Mobile Navigation: SATU-SATUNYA SCROLL CONTAINER --}}
        <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-0 pt-4 pb-4 pr-1 touch-pan-y">
            <div class="px-5 mb-2">
                <h3 class="text-[10px] font-bold uppercase tracking-widest text-white/60 dark:text-indigo-400/60">
                    Menu Utama
                </h3>
            </div>
            <nav class="px-3">
                @include('layouts.partials.menu-items')
            </nav>
        </div>

        {{-- Mobile User Profile --}}
        <div
            class="z-10 flex-shrink-0 border-t border-white/15 bg-white/10 p-3 dark:border-indigo-900/50 dark:bg-slate-900/50">
            <a href="{{ route('profile.edit') }}"
                class="group flex w-full items-center rounded-xl p-2 transition-all duration-200 hover:bg-white/15 dark:hover:bg-indigo-900/40">
                <div class="relative">
                    <img loading="lazy"
                        class="inline-block h-9 w-9 rounded-full object-cover shadow-sm ring-2 ring-white dark:ring-indigo-800"
                        src="{{ asset('img/user.png') }}" alt="User profile">
                    <div
                        class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-slate-900">
                    </div>
                </div>
                <div class="ml-3 flex-1">
                    <p class="max-w-[140px] truncate text-[13px] font-bold text-white">{{ Auth::user()->name }}</p>
                    <p class="text-[11px] font-medium text-white/70">Pengaturan Profil</p>
                </div>
            </a>
        </div>
    </div>

    <div class="w-14 flex-shrink-0" aria-hidden="true"></div>
</div>

{{-- Desktop Floating Sidebar --}}
<div class="fixed bottom-3 left-3 top-3 z-50 hidden w-[260px] transition-all duration-300 lg:flex lg:flex-col">
    <div
        class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-3xl border border-white/15 bg-gradient-to-r from-primary-700 to-primary-900 shadow-xl shadow-primary-900/20 dark:border-indigo-900/40 dark:from-indigo-950 dark:to-slate-900 dark:shadow-black/40">

        {{-- Logo Area --}}
        <div
            class="z-10 flex h-16 flex-shrink-0 items-center border-b border-white/15 bg-white/10 px-5 backdrop-blur-sm dark:border-indigo-900/50 dark:bg-indigo-950/30">
            <div
                class="flex h-9 w-9 items-center justify-center rounded-xl border border-white/20 bg-white/15 shadow-sm dark:border-indigo-800/50 dark:bg-indigo-900/50">
                <x-application-logo class="block h-5 w-auto text-gold-300 dark:text-indigo-400" />
            </div>
            <span class="ml-3 text-lg font-extrabold tracking-wide text-white">{{ config('app.name') }}</span>
        </div>

        {{-- Navigation Menu --}}
        <div class="relative min-h-0 flex-1 overflow-y-auto overscroll-contain pt-5 pb-4 pr-1 touch-pan-y">
            {{-- Decorative glow --}}
            <div
                class="pointer-events-none absolute left-0 right-0 top-0 h-40 bg-gradient-to-b from-white/10 to-transparent dark:from-indigo-900/10">
            </div>

            <nav class="relative z-10 px-3">
                @include('layouts.partials.menu-items')
            </nav>
        </div>

        {{-- User Profile Area --}}
        <div class="relative z-10 flex-shrink-0 bg-transparent p-3">
            <a href="{{ route('profile.edit') }}"
                class="group block w-full rounded-2xl border border-white/15 bg-white/10 p-2.5 shadow-sm backdrop-blur-sm transition-all duration-300 hover:border-gold-300/40 hover:bg-white/15 dark:border-indigo-800/40 dark:bg-indigo-900/20 dark:hover:border-indigo-700/50 dark:hover:bg-indigo-900/40">
                <div class="flex items-center justify-between">
                    <div class="flex min-w-0 items-center overflow-hidden">
                        <div class="relative flex-shrink-0">
                            <img loading="lazy"
                                class="inline-block h-9 w-9 rounded-full object-cover shadow-sm ring-2 ring-white transition-transform duration-300 group-hover:scale-105 dark:ring-indigo-950"
                                src="{{ asset('img/user.png') }}" alt="User profile">
                            <div
                                class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-indigo-950">
                            </div>
                        </div>
                        <div class="ml-3 truncate">
                            <p class="truncate text-[13px] font-bold text-white">PROFILE SAYA</p>
                            <p class="truncate text-[10px] font-medium text-white/70">Lihat Profil &amp; Pengaturan</p>
                        </div>
                    </div>

                    <div
                        class="ml-1 flex-shrink-0 rounded-lg border border-white/20 bg-white/15 p-1.5 transition-colors group-hover:border-gold-300/50 dark:border-indigo-900/50 dark:bg-indigo-950/50 dark:group-hover:border-indigo-600">
                        <svg class="h-4 w-4 text-white/70 transition-colors group-hover:text-gold-300 dark:text-indigo-400/70 dark:group-hover:text-indigo-300"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 001.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
