<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2
                    class="border-l-4 border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Edit Divisi') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Perbarui informasi divisi.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div
            class="bg-white dark:bg-slate-800 shadow-xl hover:shadow-xl/30 hover:-translate-y-1 transition-all duration-300 rounded-xl p-6 md:p-8">
            <form method="POST" action="{{ route('admin.divisions.update', $division->id) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="nama_divisi" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Nama Divisi
                    </label>
                    <input type="text" id="nama_divisi" name="nama_divisi"
                        value="{{ old('nama_divisi', $division->nama_divisi) }}"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-gray-200 rounded-lg shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                        placeholder="Masukkan nama divisi...">
                    @error('nama_divisi')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ route('admin.divisions.index') }}"
                        class="inline-flex items-center justify-center px-4 py-2 bg-red-400 dark:bg-slate-600 border border-gray-300 dark:border-gray-500 rounded-full font-semibold text-xs text-slate-50 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-rose-300 dark:hover:bg-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:ring-offset-slate-800 transition ease-in-out duration-150">
                        Batal
                    </a>

                    <x-primary-button>
                        {{ __('Simpan') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
