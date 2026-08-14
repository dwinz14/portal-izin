<div x-show="sidebarOpen" class="fixed inset-0 flex z-40 lg:hidden" role="dialog" aria-modal="true">
    <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-600 bg-opacity-75" @click="sidebarOpen = false"
        aria-hidden="true"></div>

    <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform"
        x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="relative flex-1 flex flex-col max-w-xs w-full bg-gradient-to-br from-primary-700/40 to-primary-800/80 backdrop-blur-md">

        <div class="absolute top-0 right-0 -mr-12 pt-2">
            <button @click="sidebarOpen = false" type="button"
                class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                <span class="sr-only">Close sidebar</span>
                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="flex-1 h-0 pt-5 pb-4 overflow-y-auto">
            <div class="flex-shrink-0 flex items-center px-4">
                <x-application-logo class="block h-9 w-auto" />
                <span class="ml-3 text-white font-semibold text-lg">PORTAL-IZIN</span>
            </div>
            <nav class="mt-5 px-2 space-y-1">
                {{-- Kita akan buatkan file menu-items-mobile nanti jika diperlukan --}}
                @include('layouts.partials.menu-items')
            </nav>
        </div>
        {{-- Profile section untuk mobile --}}
    </div>
    <div class="flex-shrink-0 w-14" aria-hidden="true"></div>
</div>

<div class="hidden lg:flex lg:flex-col lg:w-64 lg:fixed lg:top-4 lg:left-4 lg:bottom-4 lg:z-50">
    <div
        class="flex-1 flex flex-col min-h-0
                bg-primary-700/80 backdrop-blur-md
                rounded-2xl shadow-2xl ring-1 ring-primary-700/40">

        <div class="flex-1 flex flex-col pt-5 pb-4 overflow-y-auto">
            <div class="flex items-center flex-shrink-0 px-4">
                <x-application-logo class="block h-9 w-auto" />
                <span class="ml-3 text-white font-semibold text-lg">PORTAL-IZIN</span>
            </div>
            <nav class="mt-8 flex-1 px-3 space-y-2">
                @include('layouts.partials.menu-items') {{-- Menu items versi desktop --}}
            </nav>
        </div>
        <div class="flex-shrink-0 flex border-t border-white/20 p-4">
            <a href="{{ route('profile.edit') }}" class="flex-shrink-0 w-full group block">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <img loading="lazy" class="inline-block h-9 w-9 rounded-full" src="{{ asset('img/user.png') }}"
                            alt="">
                        <div class="ml-3">
                            <p class="text-sm font-medium text-white group-hover:text-gray-200">{{ Auth::user()->name }}
                            </p>
                            <p class="text-xs font-medium text-indigo-200 group-hover:text-white">View profile</p>
                        </div>
                    </div>

                </div>
            </a>
        </div>
    </div>
</div>
