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
            <div class="relative z-10 w-full max-w-xl px-8 xl:px-12">
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
                        Sistem Informasi Manajemen Izin/Cuti Karyawan<br>
                        <span class="text-white font-semibold">PT BPR Artha Pamenang</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Panel - Form Container -->
        <div
            class="w-full lg:w-7/12 xl:w-1/2 flex flex-col flex-1 bg-white dark:bg-slate-900 transition-colors duration-300 relative shadow-[-20px_0_40px_-10px_rgba(0,0,0,0.1)] z-20">
            <!-- Header with Theme Toggle -->
            <div class="flex items-center justify-between p-6 lg:p-8">
                <!-- Mobile Logo -->
                <a href="/" class="lg:hidden flex items-center gap-2.5">
                    <x-application-logo class="w-9 h-9 text-primary-600 dark:text-primary-400 flex-shrink-0" />
                    <h1
                        class="text-xl sm:text-2xl font-extrabold tracking-tight text-primary-500 dark:text-primary-400 drop-shadow-sm leading-none">
                        SI<span class="text-slate-900 dark:text-white">MIKA</span>
                    </h1>
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
            @include('layouts.footer')
        </div>
    </div>
</body>

</html>
