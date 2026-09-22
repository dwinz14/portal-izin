<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        (function() {
            var theme = localStorage.getItem('theme');
            if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    <title>{{ config('app.name', 'Manajemen Cuti') }}
    </title>
    <link rel="icon" href="{{ asset('img/logo1.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="dns-prefetch" href="//fonts.bunny.net">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div x-data="{ sidebarOpen: false }"
        class="min-h-screen bg-slate-200 dark:bg-slate-900 dark:text-gray-300 transition-colors duration-300">
        @include('layouts.navigation')

        <div class="flex flex-col flex-1 min-h-screen">

            <div class="lg:pl-72 flex flex-col flex-1 min-h-screen">
                <header
                    class="sticky top-0 z-10 flex-shrink-0 flex h-16 bg-white/75 dark:bg-slate-800 rounded-bl-xl backdrop-blur-xl shadow-xl border-b border-gray-200 dark:border-gray-700">
                    <!-- Mobile menu button -->
                    <button @click="sidebarOpen = !sidebarOpen" type="button"
                        class="border-r border-gray-200 px-4 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500 lg:hidden dark:border-gray-700">
                        <span class="sr-only">Open sidebar</span>
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Header content -->
                    <div class="flex-1 flex items-center justify-between px-4">
                        <!-- Left section - for future menu items -->
                        <div class="flex items-center space-x-4">
                            <!-- Placeholder for additional menu items -->
                            <div id="header-left-menu" class="flex items-center space-x-2">
                                <div class="ml-2 text-gray-700 dark:text-gray-300 font-medium hidden sm:block">
                                    {{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
                                </div>
                                <!-- Future menu items can be added here -->
                            </div>
                        </div>

                        <!-- Right section - theme toggle, notifications, and user menu -->
                        <div class="flex items-center space-x-3">
                            <!-- Theme Toggle -->
                            <div class="flex items-center">
                                <x-theme-toggle size="w-5 h-5" />
                            </div>

                            <!-- Notifications -->
                            <x-notification-dropdown />

                            <!-- User Menu -->
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button
                                        class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 p-1 bg-white dark:bg-slate-700 hover:bg-gray-50 dark:hover:bg-slate-600 transition-colors duration-200">
                                        <span class="sr-only">Open user menu</span>
                                        <img loading="lazy"
                                            class="h-8 w-8 rounded-full ring-2 ring-gray-200 dark:ring-gray-600"
                                            src="{{ asset('img/user.png') }}" alt="">
                                        <span class="ml-2 text-gray-700 dark:text-gray-300 font-medium hidden sm:block">
                                            {{ strtoupper(Auth::user()->name) }}
                                        </span>
                                        <svg class="ml-1 h-4 w-4 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('profile.edit')">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                            </path>
                                        </svg>
                                        {{ __('Profile') }}
                                    </x-dropdown-link>

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                                </path>
                                            </svg>
                                            {{ __('Log Out') }}
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>
                </header>

                <main class="flex-1">
                    <div class="py-6">
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                            @isset($header)
                                <div class="pb-5 border-b border-gray-200 dark:border-gray-700 mb-5">
                                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $header }}
                                    </h1>
                                </div>
                            @endisset

                            {{ $slot }}
                        </div>
                    </div>
                </main>

                @include('layouts.footer')
            </div>
        </div>
    </div>

    @auth
        {{-- idle-auto log out --}}
        <div id="idle-timer-root" x-data="idleTimer()" x-init="init()">

            {{-- Overlay + Warning Modal --}}
            <div x-show="showWarning" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                style="display: none;">

                <div x-show="showWarning" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                    class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-sm
                        border border-gray-100 dark:border-slate-700 p-7">

                    {{-- Icon --}}
                    <div
                        class="flex items-center justify-center w-16 h-16 rounded-full
                            bg-amber-100 dark:bg-amber-900/30 mx-auto mb-5">
                        <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    {{-- Text --}}
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 text-center mb-2">
                        Sesi Akan Berakhir
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center leading-relaxed mb-5">
                        Tidak ada aktivitas terdeteksi.<br>
                        Anda akan logout otomatis dalam:
                    </p>

                    {{-- Countdown --}}
                    <div class="flex items-center justify-center mb-6">
                        <div
                            class="px-6 py-3 bg-amber-50 dark:bg-amber-900/20
                                border-2 border-amber-300 dark:border-amber-700 rounded-2xl">
                            <span class="text-4xl font-mono font-bold text-amber-600 dark:text-amber-400 tabular-nums"
                                x-text="formatCountdown(countdown)"></span>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex gap-3">
                        <button @click="stayLoggedIn()"
                            class="flex-1 py-2.5 bg-primary-600 hover:bg-primary-700 active:bg-primary-800
                               text-white font-semibold rounded-xl text-sm transition-colors shadow-sm">
                            Tetap Login
                        </button>
                        <button @click="doLogout()"
                            class="flex-1 py-2.5 bg-white dark:bg-slate-700
                               border border-gray-300 dark:border-slate-600
                               text-gray-700 dark:text-gray-300 font-semibold rounded-xl text-sm
                               hover:bg-gray-50 dark:hover:bg-slate-600 transition-colors">
                            Logout
                        </button>
                    </div>

                </div>
            </div>

            {{-- Form logout tersembunyi — dipakai saat idle timeout --}}
            <form id="idle-logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
                @csrf
            </form>

        </div>

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('idleTimer', () => ({
                    showWarning: false,
                    countdown: 60,
                    idleSeconds: 0,
                    timeoutSeconds: 600, // Durasi total idle (misal 10 menit = 600 detik)
                    warningBefore: 300, // Tampilkan modal peringatan detik sebelum logout
                    _tickTimer: null,
                    _countTimer: null,
                    _events: ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click'],
                    _resetHandler: null,

                    init() {
                        this._resetHandler = () => this.resetTimer();
                        this._events.forEach(e =>
                            document.addEventListener(e, this._resetHandler, {
                                passive: true
                            })
                        );

                        // Timer pengecekan setiap detik
                        this._tickTimer = setInterval(() => {
                            this.idleSeconds++;
                            const warningAt = this.timeoutSeconds - this.warningBefore;

                            if (this.idleSeconds >= this.timeoutSeconds) {
                                this.doLogout();
                            } else if (this.idleSeconds >= warningAt && !this.showWarning) {
                                this.countdown = this.timeoutSeconds - this.idleSeconds;
                                this.showWarning = true;
                                this._startCountdown();
                            }
                        }, 1000);
                    },

                    resetTimer() {
                        this.idleSeconds = 0;
                        if (this.showWarning) {
                            this.showWarning = false;
                            if (this._countTimer) {
                                clearInterval(this._countTimer);
                                this._countTimer = null;
                            }
                        }
                    },

                    _startCountdown() {
                        this._countTimer = setInterval(() => {
                            this.countdown--;
                            if (this.countdown <= 0) {
                                clearInterval(this._countTimer);
                                this._countTimer = null;
                                this.doLogout();
                            }
                        }, 1000);
                    },

                    stayLoggedIn() {
                        this.resetTimer();
                    },

                    doLogout() {
                        // 1. Matikan semua timer
                        if (this._tickTimer) clearInterval(this._tickTimer);
                        if (this._countTimer) clearInterval(this._countTimer);

                        // 2. Lepas semua event listener agar gerakan mouse tidak menginterupsi
                        this._events.forEach(e =>
                            document.removeEventListener(e, this._resetHandler)
                        );

                        // 3. Ambil CSRF Token
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content');

                        // 4. Kirim request logout di background lalu langsung redirect seketika
                        fetch("{{ route('logout') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            }
                        }).finally(() => {
                            window.location.replace("{{ route('login') }}?idle=1");
                        });

                        // Fallback cepat: jika fetch tertunda, paksa redirect dalam 800ms
                        setTimeout(() => {
                            window.location.replace("{{ route('login') }}?idle=1");
                        }, 800);
                    },

                    formatCountdown(s) {
                        const m = Math.floor(s / 60).toString().padStart(2, '0');
                        const sec = (s % 60).toString().padStart(2, '0');
                        return `${m}:${sec}`;
                    },
                }));
            });
        </script>
    @endauth

</body>

</html>
