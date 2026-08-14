<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2
                    class="border-l-4 border-primary-700 pl-5 font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Edit Kantor') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Edit data kantor.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6">
            <form method="POST" action="{{ route('admin.offices.update', $office->id) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="nama_kantor" :value="__('Nama Kantor')" />
                    <x-text-input id="nama_kantor" name="nama_kantor" type="text" class="mt-1 block w-full"
                        :value="old('nama_kantor', strtoupper($office->nama_kantor))" required autofocus autocomplete="nama_kantor" />
                    <x-input-error :messages="$errors->get('nama_kantor')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ route('admin.offices.index') }}"
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
