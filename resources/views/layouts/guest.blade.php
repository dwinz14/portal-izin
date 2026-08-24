<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        if (localStorage.getItem('dark-mode') === 'true' || (!('dark-mode' in localStorage) && window.matchMedia(
                '(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <title>{{ config('app.name', 'ACC') }}</title>
    <link rel="icon" href="{{ asset('img/logo1.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="font-sans antialiased text-slate-900 dark:text-slate-100 bg-slate-50 dark:bg-slate-900 selection:bg-primary-500 selection:text-white">
    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Left Panel - Brand / Visual -->
        <div
            class="hidden lg:flex lg:w-5/12 xl:w-1/2 relative overflow-hidden bg-slate-900 items-center justify-center">
            <!-- Background Image with sophisticated blend -->
            <div class="absolute inset-0 z-0">
                <img src="{{ asset('img/bg-auth.jpeg') }}" alt="Background"
                    class="w-full h-full object-cover opacity-40 mix-blend-overlay">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-950/90 via-slate-900/80 to-slate-900/90"></div>
            </div>

            <!-- Decorative Elements - Subtle -->
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none">
                <div class="absolute -top-1/4 -left-1/4 w-1/2 h-1/2 bg-primary-500/10 rounded-full blur-[100px]"></div>
                <div class="absolute -bottom-1/4 -right-1/4 w-1/2 h-1/2 bg-blue-500/10 rounded-full blur-[100px]"></div>
            </div>

            <!-- Content Container -->
            <div class="relative z-10 w-full max-w-lg px-8 xl:px-12">
                <!-- Brand Element -->
                <div class="mb-10 text-center lg:text-left">
                    <a href="/"
                        class="inline-block p-4 bg-white/5 rounded-2xl backdrop-blur-md border border-white/10 shadow-2xl mb-8 transform transition hover:scale-105 duration-300">
                        <x-application-logo class="w-16 h-16 xl:w-20 xl:h-20 text-white" />
                    </a>

                    <h1 class="text-4xl xl:text-5xl font-bold tracking-tight text-white mb-4 drop-shadow-sm">
                        SI<span class="text-primary-400">MIKA</span>
                    </h1>

                    <p class="text-lg text-slate-300 leading-relaxed font-medium">
                        Sistem Informasi Manajemen Izin Karyawan<br>
                        <span class="text-white font-semibold">PT BPR Artha Pamenang</span>
                    </p>
                </div>

                <!-- Trust/Feature Badges (Enterprise feel) -->
                {{-- <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-12 border-t border-white/10 pt-10">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-primary-500/20 flex items-center justify-center border border-primary-500/30">
                            <svg class="w-5 h-5 text-primary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-white font-medium text-sm mb-1">Keamanan Data</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">Akses tersertifikasi & terenkripsi</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-primary-500/20 flex items-center justify-center border border-primary-500/30">
                            <svg class="w-5 h-5 text-primary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-white font-medium text-sm mb-1">Proses Efisien</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">Pengajuan izin cepat & terpantau</p>
                        </div>
                    </div>
                </div> --}}
            </div>
        </div>

        <!-- Right Panel - Form Container -->
        <div
            class="w-full lg:w-7/12 xl:w-1/2 flex flex-col bg-white dark:bg-slate-900 transition-colors duration-300 relative shadow-[-20px_0_40px_-10px_rgba(0,0,0,0.1)] z-20">
            <!-- Header with Theme Toggle -->
            <div class="flex items-center justify-between p-6 lg:p-8">
                <!-- Mobile Logo -->
                <a href="/" class="lg:hidden flex items-center gap-3">
                    <x-application-logo class="w-10 h-10 text-primary-600 dark:text-primary-400" />
                    <span class="font-bold text-xl tracking-tight text-slate-900 dark:text-white">SIMIKA</span>
                </a>

                <div class="ml-auto">
                    <x-theme-toggle />
                </div>
            </div>

            <!-- Form Content - Centered vertically -->
            <div class="flex-1 overflow-y-auto px-6 sm:px-10 lg:px-12 xl:px-16 pb-12 flex flex-col justify-center">
                <div class="w-full max-w-md mx-auto">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</body>

</html>
