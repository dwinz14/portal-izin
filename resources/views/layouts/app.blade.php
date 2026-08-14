<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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

        <div class="flex flex-col flex-1">

            <div class="lg:pl-72">
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
                                            {{ Auth::user()->name }}
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
            </div>
        </div>
    </div>
</body>

</html>
