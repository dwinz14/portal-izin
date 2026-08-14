<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2
                    class="border-l-4 border-primary-600 pl-4 text-xl font-semibold text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Profile User') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 pl-5">
                    Kelola informasi profil dan keamanan akun Anda
                </p>
            </div>
        </div>
    </x-slot>

    @php
        $details = [
            [
                'key' => 'NIK',
                'value' => strtoupper($user->nik ?? '-'),
                'icon' => 'id',
            ],
            [
                'key' => 'Alamat Email',
                'value' => $user->email ?? '-',
                'icon' => 'mail',
                'no_upper' => true,
            ],
            [
                'key' => 'Divisi',
                'value' => strtoupper($user->division->nama_divisi ?? 'N/A'),
                'icon' => 'office',
            ],
            [
                'key' => 'Lokasi Kantor',
                'value' => strtoupper($user->office->nama_kantor ?? 'N/A'),
                'icon' => 'location',
            ],
        ];

        function renderProfileIcon($name)
        {
            switch ($name) {
                case 'id':
                    return '<svg class="w-5 h-5 text-primary-600 dark:text-primary-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>';
                case 'mail':
                    return '<svg class="w-5 h-5 text-primary-600 dark:text-primary-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path></svg>';
                case 'office':
                    return '<svg class="w-5 h-5 text-primary-600 dark:text-primary-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg>';
                case 'location':
                    return '<svg class="w-5 h-5 text-primary-600 dark:text-primary-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>';
                case 'clock':
                default:
                    return '<svg class="w-5 h-5 text-primary-600 dark:text-primary-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>';
            }
        }
    @endphp


    <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6">
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="p-3 sm:p-4" x-data="{ activeTab: '{{ $errors->updatePassword->any() ? 'password' : ($errors->any() ? 'edit' : 'overview') }}' }">

                {{-- Tabs Navigation --}}
                <div class="border-b border-gray-200 dark:border-slate-700 mb-4">
                    <nav class="flex flex-wrap -mb-px gap-x-6" role="tablist">

                        {{-- Overview Tab --}}
                        <button type="button" role="tab" :aria-selected="activeTab === 'overview'"
                            @click="activeTab = 'overview'"
                            :class="activeTab === 'overview'
                                ?
                                'border-primary-600 text-primary-600 dark:text-primary-500' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="group inline-flex items-center py-2.5 px-1 border-b-2 font-medium text-xs sm:text-sm transition-colors">
                            <svg class="w-4 h-4 mr-1.5"
                                :class="activeTab === 'overview' ? 'text-primary-600 dark:text-primary-500' : 'text-gray-400'"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            {{ __('Profile Overview') }}
                        </button>

                        {{-- Edit Tab --}}
                        <button type="button" role="tab" :aria-selected="activeTab === 'edit'"
                            @click="activeTab = 'edit'"
                            :class="activeTab === 'edit'
                                ?
                                'border-primary-600 text-primary-600 dark:text-primary-500' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="group inline-flex items-center py-2.5 px-1 border-b-2 font-medium text-xs sm:text-sm transition-colors">
                            <svg class="w-4 h-4 mr-1.5"
                                :class="activeTab === 'edit' ? 'text-primary-600 dark:text-primary-500' : 'text-gray-400'"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            {{ __('Edit Profile') }}
                        </button>

                        {{-- Password Tab --}}
                        <button type="button" role="tab" :aria-selected="activeTab === 'password'"
                            @click="activeTab = 'password'"
                            :class="activeTab === 'password'
                                ?
                                'border-primary-600 text-primary-600 dark:text-primary-500' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="group inline-flex items-center py-2.5 px-1 border-b-2 font-medium text-xs sm:text-sm transition-colors">
                            <svg class="w-4 h-4 mr-1.5"
                                :class="activeTab === 'password' ? 'text-primary-600 dark:text-primary-500' : 'text-gray-400'"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            {{ __('Change Password') }}
                        </button>
                    </nav>
                </div>

                {{-- Content --}}
                <div class="mt-4">

                    {{-- Overview --}}
                    <section x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform scale-95"
                        x-transition:enter-end="opacity-100 transform scale-100" class="space-y-5">

                        {{-- Profile Header Card --}}
                        <div class="rounded-xl overflow-hidden shadow">

                            <div
                                class="bg-gradient-to-br from-primary-600 via-primary-600 to-primary-700
                            dark:from-primary-700 dark:to-primary-800 p-6 relative">

                                {{-- Decorative --}}
                                <div class="absolute inset-0 opacity-10">
                                    <div class="absolute top-0 right-0 w-48 h-48 bg-white rounded-full -mr-24 -mt-24">
                                    </div>
                                    <div class="absolute bottom-0 left-0 w-40 h-40 bg-white rounded-full -ml-20 -mb-20">
                                    </div>
                                </div>

                                {{-- Header Content --}}
                                <div
                                    class="relative flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">

                                    {{-- Avatar + Name --}}
                                    <div class="flex items-center gap-4">
                                        <div class="relative">
                                            <img loading="lazy" src="{{ $user->photo_path ?? asset('img/user.png') }}"
                                                alt="Foto {{ $user->name }}"
                                                class="h-16 w-16 sm:h-16 sm:w-16 rounded-full border-4 border-white/20 shadow-lg object-cover ring-1 ring-white/10"
                                                loading="lazy">

                                            <div
                                                class="absolute -bottom-1 -right-1 h-5 w-5 bg-green-400 rounded-full border-2 border-white shadow-sm">
                                            </div>
                                        </div>

                                        <div class="text-white">
                                            <h3 class="text-xl sm:text-2xl uppercase font-bold leading-tight mb-0.5">
                                                {{ $user->name }}
                                            </h3>
                                            <p
                                                class="text-xs sm:text-sm uppercase opacity-95 flex items-center gap-1.5">
                                                {{ $user->position->nama_jabatan ?? 'N/A' }}
                                            </p>
                                            <div
                                                class="text-[0.7rem] sm:text-xs mt-1 opacity-90 flex items-center gap-1.5">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M6 7V6a4 4 0 118 0v1h2a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V9a2 2 0 012-2h2zm2-1a2 2 0 114 0v1H8V6z" />
                                                </svg>

                                                Masa Kerja: <span
                                                    class="font-semibold">{{ $user->masaKerjaTahunBulan() }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Cuti Card --}}
                                    <div class="flex-shrink-0 w-full sm:w-auto">
                                        <div
                                            class="bg-white/15 backdrop-blur px-4 py-3 rounded-lg border border-white/20 shadow-md">
                                            <div class="text-center">

                                                @if ($annualType && $user->masaKerjaTahun() >= $annualType->min_years)
                                                    <p class="text-xs text-white/90 font-medium mb-1">Sisa Cuti Tahunan
                                                    </p>
                                                    <p class="text-2xl sm:text-3xl font-bold text-white">
                                                        {{ $annualBalance->remaining ?? 0 }}
                                                    </p>
                                                    <p class="text-[0.7rem] text-white/80 mt-1">hari tersedia</p>
                                                @else
                                                    <p class="text-xs text-white/90 font-medium mb-1">Cuti Tahunan</p>
                                                    <p class="text-xs font-semibold text-white/80">Belum Tersedia</p>
                                                @endif

                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            {{-- Details Grid --}}
                            <div class="p-5 sm:p-6 bg-white dark:bg-slate-900">

                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                        Detail Informasi
                                    </h4>
                                    <span class="text-[0.7rem] text-gray-500 dark:text-gray-400">Data Karyawan</span>
                                </div>

                                <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach ($details as $d)
                                        <div
                                            class="relative flex items-start gap-2.5 bg-gradient-to-br from-gray-50
                                        to-gray-100/50 dark:from-slate-800 dark:to-slate-800/50 p-3 rounded-lg
                                        border border-gray-200/50 dark:border-slate-700/50 hover:shadow transition-all">

                                            <div
                                                class="flex-shrink-0 p-1.5 bg-white dark:bg-slate-700 rounded-lg shadow-sm">
                                                {!! renderProfileIcon($d['icon']) !!}
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <dt
                                                    class="text-[0.7rem] font-medium text-gray-500 dark:text-gray-400 mb-0.5">
                                                    {{ $d['key'] }}
                                                </dt>
                                                <dd
                                                    class="text-sm font-semibold text-gray-900 dark:text-gray-100 break-words">
                                                    {{ $d['no_upper'] ?? false ? $d['value'] : (is_string($d['value']) ? strtoupper($d['value']) : $d['value']) }}
                                                </dd>
                                            </div>
                                        </div>
                                    @endforeach
                                </dl>

                            </div>
                        </div>
                    </section>

                    {{-- Edit --}}
                    <section x-show="activeTab === 'edit'" x-transition:enter="transition ease-out duration-200"
                        class="space-y-5">
                        <div
                            class="bg-gray-50 dark:bg-slate-900 rounded-xl p-5 border border-gray-200 dark:border-slate-700">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </section>

                    {{-- Password --}}
                    <section x-show="activeTab === 'password'" x-transition:enter="transition ease-out duration-200"
                        class="space-y-5">
                        <div
                            class="bg-gray-50 dark:bg-slate-900 rounded-xl p-5 border border-gray-200 dark:border-slate-700">
                            @include('profile.partials.update-password-form')
                        </div>
                    </section>

                </div>
            </div>
        </div>
    </div>



    <x-toast-notification />
</x-app-layout>
