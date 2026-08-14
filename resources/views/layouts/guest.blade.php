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
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Left Panel - Hidden on mobile, optimized for tablet/desktop -->
        <div class="hidden lg:flex lg:w-5/12 xl:w-1/2 items-center justify-center p-6 xl:p-12 relative overflow-hidden bg-cover bg-center"
            style="background-image: url('{{ asset('img/bg-auth.jpeg') }}');">

            <!-- Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-br from-blue-950/60 via-blue-900/50 to-slate-900/70"></div>

            <!-- Content Container -->
            <div class="relative z-10 w-full max-w-lg">
                <!-- Glassmorphism Card -->
                <div
                    class="bg-gradient-to-r from-primary-700/30 to-primary-600/30 backdrop-blur-md rounded-3xl p-8 xl:p-12 shadow-2xl">
                    <!-- Logo -->
                    <a href="/" class="block mb-8">
                        <x-application-logo
                            class="w-20 h-20 xl:w-24 xl:h-24 mx-auto text-white hover:scale-105 transition-transform duration-300" />
                    </a>

                    <!-- Title -->
                    <h1 class="text-3xl xl:text-4xl font-bold mb-4 text-center text-white drop-shadow-lg">
                        PORTAL-IZIN
                    </h1>

                    <!-- Subtitle -->
                    <p class="text-white/90 text-center text-base xl:text-lg leading-relaxed">
                        Website Izin Karyawan<br>
                        <span class="text-blue-300 font-semibold">PT BPR Artha Pamenang</span>
                    </p>
                </div>
            </div>

            <!-- Animated Background Elements -->
            <div aria-hidden="true"
                class="absolute -top-32 -left-32 w-72 h-72 xl:w-96 xl:h-96 bg-gradient-to-tr from-blue-400/20 to-blue-600/30 rounded-full blur-3xl animate-pulse">
            </div>
            <div aria-hidden="true"
                class="absolute -bottom-32 -right-32 w-72 h-72 xl:w-96 xl:h-96 bg-gradient-to-br from-blue-300/20 to-blue-500/30 rounded-full blur-3xl animate-pulse"
                style="animation-delay: 1s;">
            </div>
        </div>

        <!-- Right Panel - Form Container -->
        <div
            class="w-full lg:w-7/12 xl:w-1/2 flex items-center justify-center bg-white dark:bg-slate-900 transition-colors duration-300">
            <div class="w-full h-full flex flex-col">
                <!-- Header with Theme Toggle -->
                <div class="flex items-center justify-between p-4 sm:p-6 lg:p-8">
                    <!-- Mobile Logo -->
                    <a href="/" class="lg:hidden">
                        <x-application-logo class="w-10 h-10 text-primary-600 dark:text-primary-400" />
                    </a>

                    <div class="lg:hidden"></div> <!-- Spacer -->

                    <!-- Theme Toggle - Better positioned -->
                    <div class="ml-auto">
                        <x-theme-toggle />
                    </div>
                </div>

                <!-- Form Content - Scrollable with proper padding -->
                <div class="flex-1 overflow-y-auto px-4 sm:px-6 lg:px-8 pb-8">
                    <div class="w-full max-w-md mx-auto">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
